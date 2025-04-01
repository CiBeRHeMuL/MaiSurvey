<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250401215708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD need_change_password boolean NOT NULL DEFAULT FALSE');
        $this->addSql('ALTER TABLE "user" ADD password_changed_at timestamp(0) without time zone DEFAULT NULL');

        $this->addSql(
            <<<SQL
UPDATE "user" SET need_change_password = TRUE WHERE roles && ARRAY['student', 'teacher']::text[]
SQL,
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" DROP need_change_password');
        $this->addSql('ALTER TABLE "user" DROP password_changed_at');
    }
}
