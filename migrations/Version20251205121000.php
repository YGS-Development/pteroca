<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Core\Enum\SettingEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251205121000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add manual payment settings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'INSERT INTO setting (name, value, type, context, hierarchy) VALUES (?, ?, ?, ?, ?)',
            [
                SettingEnum::MANUAL_PAYMENT_ENABLED->value,
                '0',
                'boolean',
                'payment_settings',
                10,
            ]
        );

        $this->addSql(
            'INSERT INTO setting (name, value, type, context, hierarchy) VALUES (?, ?, ?, ?, ?)',
            [
                SettingEnum::MANUAL_PAYMENT_INSTRUCTIONS->value,
                '',
                'textarea',
                'payment_settings',
                11,
            ]
        );

        $this->addSql(
            'INSERT INTO setting (name, value, type, context, hierarchy) VALUES (?, ?, ?, ?, ?)',
            [
                SettingEnum::MANUAL_PAYMENT_REQUIRE_PROOF->value,
                '0',
                'boolean',
                'payment_settings',
                12,
            ]
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            'DELETE FROM setting WHERE name = ?',
            [SettingEnum::MANUAL_PAYMENT_ENABLED->value]
        );

        $this->addSql(
            'DELETE FROM setting WHERE name = ?',
            [SettingEnum::MANUAL_PAYMENT_INSTRUCTIONS->value]
        );

        $this->addSql(
            'DELETE FROM setting WHERE name = ?',
            [SettingEnum::MANUAL_PAYMENT_REQUIRE_PROOF->value]
        );
    }
}

