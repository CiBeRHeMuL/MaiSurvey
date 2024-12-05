<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241205173219 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE completed_survey (user_id UUID NOT NULL, survey_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(user_id, survey_id))');
        $this->addSql('CREATE INDEX IDX_137B8012A76ED395 ON completed_survey (user_id)');
        $this->addSql('CREATE INDEX IDX_137B8012B3FE509D ON completed_survey (survey_id)');
        $this->addSql('CREATE TABLE survey (id UUID NOT NULL, subject_id UUID NOT NULL, teacher_id UUID DEFAULT NULL, actual_to TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AD5F9BFC23EDC87 ON survey (subject_id)');
        $this->addSql('CREATE INDEX IDX_AD5F9BFC41807E1D ON survey (teacher_id)');
        $this->addSql('CREATE TABLE survey_item (id UUID NOT NULL, survey_id UUID NOT NULL, answer_required BOOLEAN DEFAULT true NOT NULL, type VARCHAR(255) NOT NULL, text VARCHAR(255) NOT NULL, position INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D9F225D5B3FE509D ON survey_item (survey_id)');
        $this->addSql('CREATE TABLE survey_item_answer (id UUID NOT NULL, survey_item_id UUID NOT NULL, answer JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E4DCE0EF564371E5 ON survey_item_answer (survey_item_id)');
        $this->addSql('ALTER TABLE completed_survey ADD CONSTRAINT FK_137B8012A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE completed_survey ADD CONSTRAINT FK_137B8012B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE survey ADD CONSTRAINT FK_AD5F9BFC23EDC87 FOREIGN KEY (subject_id) REFERENCES subject (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE survey ADD CONSTRAINT FK_AD5F9BFC41807E1D FOREIGN KEY (teacher_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE survey_item ADD CONSTRAINT FK_D9F225D5B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE survey_item_answer ADD CONSTRAINT FK_E4DCE0EF564371E5 FOREIGN KEY (survey_item_id) REFERENCES survey_item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE completed_survey DROP CONSTRAINT FK_137B8012A76ED395');
        $this->addSql('ALTER TABLE completed_survey DROP CONSTRAINT FK_137B8012B3FE509D');
        $this->addSql('ALTER TABLE survey DROP CONSTRAINT FK_AD5F9BFC23EDC87');
        $this->addSql('ALTER TABLE survey DROP CONSTRAINT FK_AD5F9BFC41807E1D');
        $this->addSql('ALTER TABLE survey_item DROP CONSTRAINT FK_D9F225D5B3FE509D');
        $this->addSql('ALTER TABLE survey_item_answer DROP CONSTRAINT FK_E4DCE0EF564371E5');
        $this->addSql('DROP TABLE completed_survey');
        $this->addSql('DROP TABLE survey');
        $this->addSql('DROP TABLE survey_item');
        $this->addSql('DROP TABLE survey_item_answer');
    }
}
