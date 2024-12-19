<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use App\Domain\Enum\TeacherSubjectTypeEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241219212546 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE teacher_subject (id UUID NOT NULL, teacher_id UUID NOT NULL, subject_id UUID NOT NULL, type VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_360CB33B41807E1D ON teacher_subject (teacher_id)');
        $this->addSql('CREATE INDEX IDX_360CB33B23EDC87 ON teacher_subject (subject_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_360CB33B41807E1D23EDC878CDE5729 ON teacher_subject (teacher_id, subject_id, type)');
        $this->addSql('ALTER TABLE teacher_subject ADD CONSTRAINT FK_360CB33B41807E1D FOREIGN KEY (teacher_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE teacher_subject ADD CONSTRAINT FK_360CB33B23EDC87 FOREIGN KEY (subject_id) REFERENCES subject (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $type = TeacherSubjectTypeEnum::Lecture->value;
        $this->addSql(<<<SQL
        INSERT INTO teacher_subject (id, teacher_id, subject_id, type, created_at)
        SELECT DISTINCT ON (teacher_id, subject_id)
            gen_random_uuid() AS id,
            teacher_id,
            subject_id,
            '$type' AS type,
            now() AS created_at
        FROM user_subject
        SQL,
        );

        $this->addSql('ALTER TABLE user_subject DROP CONSTRAINT fk_a3c3207023edc87');
        $this->addSql('ALTER TABLE user_subject DROP CONSTRAINT fk_a3c3207041807e1d');
        $this->addSql('DROP INDEX idx_a3c3207041807e1d');
        $this->addSql('DROP INDEX idx_a3c3207023edc87');
        $this->addSql('ALTER TABLE user_subject DROP CONSTRAINT user_subject_pkey');
        $this->addSql('ALTER TABLE user_subject ADD teacher_subject_id UUID');
        $this->addSql('ALTER TABLE user_subject ADD CONSTRAINT FK_A3C3207088217E27 FOREIGN KEY (teacher_subject_id) REFERENCES teacher_subject (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_A3C3207088217E27 ON user_subject (teacher_subject_id)');
        $this->addSql('ALTER TABLE user_subject ADD PRIMARY KEY (user_id, teacher_subject_id)');

        $this->addSql(<<<SQL
        UPDATE user_subject SET teacher_subject_id = s.id
        FROM (
            SELECT * FROM teacher_subject
        ) s
        WHERE user_subject.teacher_id = s.teacher_id
          AND user_subject.subject_id = s.subject_id
          AND s.type = '$type'
        SQL,
        );

        $this->addSql('ALTER TABLE user_subject ALTER COLUMN teacher_subject_id SET NOT NULL');

        $this->addSql('DROP VIEW my_survey');

        $this->addSql('ALTER TABLE user_subject DROP subject_id');
        $this->addSql('ALTER TABLE user_subject DROP teacher_id');

        $this->addSql('ALTER TABLE user_subject RENAME TO student_subject');
        $this->addSql(
            <<<SQL
        CREATE VIEW my_survey AS
        (
        SELECT s.id, u.id AS user_id, cs IS NOT NULL AS completed, cs.created_at AS completed_at
        FROM "user" u
                 INNER JOIN student_subject ss ON ss.user_id = u.id
                 INNER JOIN teacher_subject ts ON ss.teacher_subject_id = ts.id
                 INNER JOIN survey s ON s.subject_id = ts.subject_id AND (s.teacher_id IS NULL OR s.teacher_id = ts.teacher_id)
                 LEFT JOIN completed_survey cs ON s.id = cs.survey_id AND u.id = cs.user_id
        WHERE u.status = 'active'
          AND u.roles @> ARRAY ['student']::text[]
          AND (s.actual_to > now()
            OR cs IS NOT NULL)
        );
        SQL,
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE student_subject RENAME TO user_subject');
        $this->addSql('ALTER TABLE user_subject ADD teacher_id UUID');
        $this->addSql('ALTER TABLE user_subject ADD subject_id UUID');
        $this->addSql('ALTER TABLE user_subject ADD CONSTRAINT fk_a3c3207023edc87 FOREIGN KEY (subject_id) REFERENCES subject (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_subject ADD CONSTRAINT fk_a3c3207041807e1d FOREIGN KEY (teacher_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $type = TeacherSubjectTypeEnum::Lecture->value;
        $this->addSql(<<<SQL
        UPDATE user_subject
        SET teacher_id = s.teacher_id, subject_id = s.subject_id
        FROM (
            SELECT DISTINCT ON (teacher_id, subject_id) * FROM teacher_subject WHERE type = '$type'
        ) s
        WHERE s.id = user_subject.teacher_subject_id
        SQL,
        );

        $this->addSql('ALTER TABLE user_subject ALTER COLUMN teacher_id SET NOT NULL');
        $this->addSql('ALTER TABLE user_subject ALTER COLUMN subject_id SET NOT NULL');

        $this->addSql('DROP VIEW my_survey');

        $this->addSql('ALTER TABLE user_subject DROP COLUMN teacher_subject_id');

        $this->addSql('DROP TABLE teacher_subject');
        $this->addSql('CREATE INDEX idx_a3c3207041807e1d ON user_subject (teacher_id)');
        $this->addSql('CREATE INDEX idx_a3c3207023edc87 ON user_subject (subject_id)');
        $this->addSql('ALTER TABLE user_subject ADD PRIMARY KEY (user_id, subject_id, teacher_id)');

        $this->addSql(
            <<<SQL
        CREATE VIEW my_survey AS
        (
        SELECT s.id, u.id AS user_id, cs IS NOT NULL AS completed, cs.created_at AS completed_at
        FROM "user" u
                 INNER JOIN user_subject us ON us.user_id = u.id
                 INNER JOIN survey s ON s.subject_id = us.subject_id AND (s.teacher_id IS NULL OR s.teacher_id = us.teacher_id)
                 LEFT JOIN completed_survey cs ON s.id = cs.survey_id AND u.id = cs.user_id
        WHERE u.status = 'active'
          AND u.roles @> ARRAY ['student']::text[]
          AND (s.actual_to > now()
            OR cs IS NOT NULL)
        );
        SQL,
        );
    }
}
