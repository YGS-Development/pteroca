<?php

namespace App\Core\Entity;

use App\Core\Trait\PaymentEntityTrait;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: "App\Core\Repository\PaymentRepository")]
#[ORM\HasLifecycleCallbacks]
#[Vich\Uploadable]
class Payment
{
    use PaymentEntityTrait;
}
