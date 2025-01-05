<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241230192244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE student_subject ADD COLUMN id UUID NOT NULL DEFAULT gen_random_uuid()');
        $this->addSql('ALTER TABLE student_subject ALTER COLUMN id DROP DEFAULT');
        $this->addSql('ALTER TABLE student_subject DROP CONSTRAINT user_subject_pkey');
        $this->addSql('ALTER TABLE student_subject ADD CONSTRAINT student_subject_pkey PRIMARY KEY (id)');
        $this->addSql(
            <<<SQL
ALTER TABLE student_subject ADD CONSTRAINT student_subject_idx EXCLUDE USING gist (
    user_id WITH =,
    teacher_subject_id WITH =,
    tsrange(actual_from, actual_to, '[]') WITH &&
);
SQL,
        );
        $this->addSql('ALTER INDEX idx_a3c32070a76ed395 RENAME TO IDX_16F88B82A76ED395');
        $this->addSql('ALTER INDEX idx_a3c3207088217e27 RENAME TO IDX_16F88B8288217E27');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE student_subject DROP CONSTRAINT student_subject_idx');
        $this->addSql('ALTER TABLE student_subject DROP CONSTRAINT student_subject_pkey');
        $this->addSql('ALTER TABLE student_subject ADD CONSTRAINT user_subject_pkey PRIMARY KEY (user_id, teacher_subject_id)');
        $this->addSql('ALTER TABLE student_subject DROP COLUMN id');
        $this->addSql('ALTER INDEX idx_16f88b8288217e27 RENAME TO idx_a3c3207088217e27');
        $this->addSql('ALTER INDEX idx_16f88b82a76ed395 RENAME TO idx_a3c32070a76ed395');
    }
}
