<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250219221538 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE survey_template (id UUID NOT NULL, name VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE survey_template_item (id UUID NOT NULL, survey_template_id UUID NOT NULL, answer_required BOOLEAN DEFAULT true NOT NULL, type VARCHAR(255) NOT NULL, text VARCHAR(255) NOT NULL, position INT NOT NULL, data JSONB NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, subject_type VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A1CE8C8BBD22D0BD ON survey_template_item (survey_template_id)');
        $this->addSql('ALTER TABLE survey_template_item ADD CONSTRAINT FK_A1CE8C8BBD22D0BD FOREIGN KEY (survey_template_id) REFERENCES survey_template (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE survey_template_item DROP CONSTRAINT FK_A1CE8C8BBD22D0BD');
        $this->addSql('DROP TABLE survey_template');
        $this->addSql('DROP TABLE survey_template_item');
    }
}
