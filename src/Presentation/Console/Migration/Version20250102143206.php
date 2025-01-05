<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use App\Domain\Enum\TeacherSubjectTypeEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250102143206 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP VIEW my_survey');
        $this->addSql('ALTER TABLE survey DROP CONSTRAINT fk_ad5f9bfc41807e1d');
        $this->addSql('DROP INDEX idx_ad5f9bfc41807e1d');
        $this->addSql('ALTER TABLE survey DROP teacher_id');
        $this->addSql('ALTER TABLE survey_item ADD subject_type VARCHAR(255)');
        $type = TeacherSubjectTypeEnum::Lecture->value;
        $this->addSql("UPDATE survey_item SET subject_type = '$type'");
        $this->addSql('ALTER TABLE survey_item ALTER COLUMN subject_type SET NOT NULL');
        $this->addSql(
            <<<SQL
        CREATE VIEW my_survey(id, user_id, completed, completed_at) as
        SELECT s.id,
               u.id             AS user_id,
               cs.* IS NOT NULL AS completed,
               cs.created_at    AS completed_at
        FROM "user" u
                 JOIN student_subject us ON us.user_id = u.id
                 JOIN teacher_subject ts ON us.teacher_subject_id = ts.id
                 JOIN survey s ON s.subject_id = ts.subject_id
                 LEFT JOIN completed_survey cs ON s.id = cs.survey_id AND u.id = cs.user_id
        WHERE u.status::text = 'active'::text
          AND u.roles @> ARRAY ['student'::text]
          AND (s.actual_to > now() OR cs.* IS NOT NULL);
        SQL,
        );
        $this->addSql(
            <<<SQL
        CREATE VIEW my_survey_item(id, survey_id, user_id, teacher_subject_id) AS
        (
        SELECT si.id, ms.id, ms.user_id, ss.teacher_subject_id
        FROM my_survey ms
                 INNER JOIN survey s ON s.id = ms.id
                 INNER JOIN survey_item si ON si.survey_id = s.id
                 INNER JOIN student_subject ss ON ms.user_id = ss.user_id
                 INNER JOIN teacher_subject ts ON ts.type = si.subject_type AND ts.subject_id = s.subject_id AND ts.id = ss.teacher_subject_id
        );
        SQL,
        );
        $this->addSql(
            'ALTER TABLE survey_item_answer ADD COLUMN teacher_subject_id uuid NOT NULL REFERENCES teacher_subject(id) ON DELETE CASCADE INITIALLY IMMEDIATE DEFERRABLE',
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE survey_item_answer DROP COLUMN teacher_subject_id');
        $this->addSql('DROP VIEW my_survey_item');
        $this->addSql('DROP VIEW my_survey');
        $this->addSql('ALTER TABLE survey ADD teacher_id UUID DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE survey ADD CONSTRAINT fk_ad5f9bfc41807e1d FOREIGN KEY (teacher_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE',
        );
        $this->addSql('CREATE INDEX idx_ad5f9bfc41807e1d ON survey (teacher_id)');
        $this->addSql('ALTER TABLE survey_item DROP subject_type');
        $this->addSql(
            <<<SQL
        create view my_survey(id, user_id, completed, completed_at) as
        SELECT s.id,
               u.id             AS user_id,
               cs.* IS NOT NULL AS completed,
               cs.created_at    AS completed_at
        FROM "user" u
                 JOIN student_subject us ON us.user_id = u.id
                 JOIN teacher_subject ts ON us.teacher_subject_id = ts.id
                 JOIN survey s ON s.subject_id = ts.subject_id AND (s.teacher_id IS NULL OR s.teacher_id = ts.teacher_id)
                 LEFT JOIN completed_survey cs ON s.id = cs.survey_id AND u.id = cs.user_id
        WHERE u.status::text = 'active'::text
          AND u.roles @> ARRAY ['student'::text]
          AND (s.actual_to > now() OR cs.* IS NOT NULL);
        SQL,
        );
    }
}
