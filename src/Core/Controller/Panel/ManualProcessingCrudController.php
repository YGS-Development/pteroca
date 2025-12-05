<?php

namespace App\Core\Controller\Panel;

use App\Core\Entity\Payment;
use App\Core\Enum\CrudTemplateContextEnum;
use App\Core\Enum\PaymentStatusEnum;
use App\Core\Enum\UserRoleEnum;
use App\Core\Service\Crud\PanelCrudService;
use App\Core\Service\Payment\PaymentService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

class ManualProcessingCrudController extends AbstractPanelController
{
    public function __construct(
        PanelCrudService $panelCrudService,
        private readonly TranslatorInterface $translator,
        private readonly PaymentService $paymentService,
    ) {
        parent::__construct($panelCrudService);
    }

    public static function getEntityFqcn(): string
    {
        return Payment::class;
    }

    public function approveManual(AdminContext $context): RedirectResponse
    {
        $entity = $context->getEntity()->getInstance();
        if (!$entity instanceof Payment) {
            throw new \Exception('Invalid entity type');
        }

        $error = $this->paymentService->approveManualPayment($entity);
        if (!empty($error)) {
            $this->addFlash('danger', $error);
        } else {
            $this->addFlash('success', $this->translator->trans('pteroca.manual_processing.approved'));
        }

        return $this->redirectToRoute('panel', [
            'crudAction' => 'index',
            'crudControllerFqcn' => self::class,
        ]);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('sessionId', $this->translator->trans('pteroca.crud.payment.session_id')),
            TextField::new('status', $this->translator->trans('pteroca.crud.payment.status'))
                ->formatValue(fn ($value) => sprintf(
                    "<span class='badge %s'>%s</span>",
                    $value === 'paid' ? 'badge-success' : 'badge-danger',
                    $value,
                )),
            TextField::new('provider', $this->translator->trans('pteroca.crud.payment.provider')),
            NumberField::new('amount', $this->translator->trans('pteroca.crud.payment.amount'))
                ->setNumDecimals(2),
            TextField::new('currency', $this->translator->trans('pteroca.crud.payment.currency'))
                ->formatValue(fn ($value) => strtoupper($value)),
            NumberField::new('balanceAmount', $this->translator->trans('pteroca.crud.payment.balance_amount'))
                ->setNumDecimals(2),
            AssociationField::new('usedVoucher', $this->translator->trans('pteroca.crud.payment.used_voucher')),
            AssociationField::new('user', $this->translator->trans('pteroca.crud.payment.user')),
            TextField::new('manualInstructions', $this->translator->trans('pteroca.crud.payment.manual_instructions'))
                ->onlyOnDetail(),
            ImageField::new('manualProofPath', $this->translator->trans('pteroca.crud.payment.manual_proof'))
                ->setBasePath('/uploads/manual_payments')
                ->onlyOnDetail(),
            DateTimeField::new('createdAt', $this->translator->trans('pteroca.crud.payment.created_at')),
            DateTimeField::new('updatedAt', $this->translator->trans('pteroca.crud.payment.updated_at')),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {
        $approve = Action::new('approveManual', $this->translator->trans('pteroca.manual_processing.approve'), 'fa fa-check')
            ->linkToCrudAction('approveManual')
            ->displayIf(static function (Payment $payment) {
                return $payment->getProvider() === 'manual' && $payment->getStatus() !== PaymentStatusEnum::PAID->value;
            });

        return $actions
            ->disable(Action::NEW, Action::EDIT, Action::DELETE)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $approve)
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $this->appendCrudTemplateContext(CrudTemplateContextEnum::PAYMENT->value);

        $crud
            ->setEntityLabelInSingular($this->translator->trans('pteroca.manual_processing.item'))
            ->setEntityLabelInPlural($this->translator->trans('pteroca.manual_processing.items'))
            ->setEntityPermission(UserRoleEnum::ROLE_ADMIN->name)
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->showEntityActionsInlined()
        ;

        return parent::configureCrud($crud);
    }

    public function configureFilters(Filters $filters): Filters
    {
        $filters
            ->add('provider')
            ->add('status')
            ->add('user')
            ->add('amount')
            ->add('currency')
            ->add('createdAt')
            ->add('updatedAt')
        ;
        return parent::configureFilters($filters);
    }
}

