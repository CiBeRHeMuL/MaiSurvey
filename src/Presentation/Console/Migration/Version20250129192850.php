<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250129192850 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE survey_item ALTER subject_type DROP NOT NULL');
        $this->addSql('ALTER TABLE survey_item_answer ALTER teacher_subject_id DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE survey_item ALTER subject_type SET NOT NULL');
        $this->addSql('ALTER TABLE survey_item_answer ALTER teacher_subject_id SET NOT NULL');
    }
}
