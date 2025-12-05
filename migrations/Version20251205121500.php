<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251205121500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add provider and manual payment fields to payment table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE payment ADD provider VARCHAR(50) NOT NULL DEFAULT 'stripe' AFTER status");
        $this->addSql('ALTER TABLE payment ADD manual_instructions LONGTEXT DEFAULT NULL AFTER balance_amount');
        $this->addSql('ALTER TABLE payment ADD manual_proof_path VARCHAR(255) DEFAULT NULL AFTER manual_instructions');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE payment DROP manual_proof_path');
        $this->addSql('ALTER TABLE payment DROP manual_instructions');
        $this->addSql('ALTER TABLE payment DROP provider');
    }
}

