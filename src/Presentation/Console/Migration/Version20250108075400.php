<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250108075400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE survey_item_answer ADD COLUMN type VARCHAR(255)');
        $this->addSql('UPDATE survey_item_answer SET type = s.type FROM (SELECT type, id FROM survey_item) s WHERE s.id = survey_item_answer.survey_item_id');
        $this->addSql('ALTER TABLE survey_item_answer ALTER COLUMN type SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE survey_item_answer DROP COLUMN type');
    }
}
