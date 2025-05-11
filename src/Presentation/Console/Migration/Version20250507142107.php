<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use App\Domain\Enum\SurveyStatusEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250507142107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP MATERIALIZED VIEW my_survey_item');
        $this->addSql('DROP MATERIALIZED VIEW my_survey');
        $active = SurveyStatusEnum::Active->value;
        $draft = SurveyStatusEnum::Draft->value;
        $this->addSql(
            <<<SQL
CREATE MATERIALIZED VIEW my_survey AS
SELECT DISTINCT ON (s.id, u.id) s.id,
                                u.id                AS user_id,
                                cs.* IS NOT NULL    AS completed,
                                cs.created_at       AS completed_at,
                                s.subject_id,
                                s.actual_to > now() AND s.status = '$active' AS actual
FROM "user" u
         JOIN student_subject us ON us.user_id = u.id
         JOIN teacher_subject ts ON us.teacher_subject_id = ts.id
         JOIN survey s ON s.subject_id = ts.subject_id
         LEFT JOIN completed_survey cs ON s.id = cs.survey_id AND u.id = cs.user_id
WHERE u.status::text = 'active'::text
  AND (u.deleted IS FALSE OR cs IS NOT NULL)
  AND u.roles @> ARRAY ['student'::text]
  AND s.status != '$draft';
SQL,
        );
        $this->addSql(
            <<<SQL
CREATE MATERIALIZED VIEW my_survey_item AS
SELECT si.id,
       ms.id AS survey_id,
       ms.user_id,
       ss.teacher_subject_id,
       ss.id AS student_subject_id,
       si."position"
FROM my_survey ms
JOIN survey_item si ON si.survey_id = ms.id
JOIN student_subject ss ON ms.user_id = ss.user_id
JOIN teacher_subject ts ON ts.type::text = si.subject_type::text AND ts.subject_id = ms.subject_id AND ts.id = ss.teacher_subject_id
UNION ALL
SELECT si.id,
       ms.id AS survey_id,
       ms.user_id,
       NULL::uuid AS teacher_subject_id,
       NULL::uuid AS student_subject_id,
       si."position"
FROM my_survey ms
JOIN survey_item si ON si.survey_id = ms.id
WHERE si.subject_type IS NULL;
SQL,
        );
        $this->addSql('CREATE INDEX my_survey_id_user_id_idx ON my_survey (id, user_id);');
        $this->addSql('CREATE INDEX my_survey_completed_actual_idx ON my_survey (completed, actual);');
        $this->addSql('CREATE INDEX my_survey_item_id_idx ON my_survey_item (id);');
        $this->addSql('CREATE INDEX my_survey_item_survey_id_user_id_idx ON my_survey_item (survey_id, user_id);');

        $this->addSql(
            <<<SQL
CREATE TRIGGER refresh_views_user
    AFTER INSERT
        OR UPDATE OF status, deleted
        OR DELETE
        OR TRUNCATE
    ON "user"
EXECUTE PROCEDURE trigger_refresh_views('my_survey', 'my_survey_item');
SQL,
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP MATERIALIZED VIEW my_survey_item');
        $this->addSql('DROP MATERIALIZED VIEW my_survey');
        $active = SurveyStatusEnum::Active->value;
        $draft = SurveyStatusEnum::Draft->value;
        $this->addSql(
            <<<SQL
CREATE MATERIALIZED VIEW my_survey AS
SELECT DISTINCT ON (s.id, u.id) s.id,
                                u.id                AS user_id,
                                cs.* IS NOT NULL    AS completed,
                                cs.created_at       AS completed_at,
                                s.subject_id,
                                s.actual_to > now() AND s.status = '$active' AS actual
FROM "user" u
         JOIN student_subject us ON us.user_id = u.id
         JOIN teacher_subject ts ON us.teacher_subject_id = ts.id
         JOIN survey s ON s.subject_id = ts.subject_id
         LEFT JOIN completed_survey cs ON s.id = cs.survey_id AND u.id = cs.user_id
WHERE u.status::text = 'active'::text
  AND u.roles @> ARRAY ['student'::text]
  AND s.status != '$draft';
SQL,
        );
        $this->addSql(
            <<<SQL
CREATE MATERIALIZED VIEW my_survey_item AS
SELECT si.id,
       ms.id AS survey_id,
       ms.user_id,
       ss.teacher_subject_id,
       ss.id AS student_subject_id,
       si."position"
FROM my_survey ms
JOIN survey_item si ON si.survey_id = ms.id
JOIN student_subject ss ON ms.user_id = ss.user_id
JOIN teacher_subject ts ON ts.type::text = si.subject_type::text AND ts.subject_id = ms.subject_id AND ts.id = ss.teacher_subject_id
UNION ALL
SELECT si.id,
       ms.id AS survey_id,
       ms.user_id,
       NULL::uuid AS teacher_subject_id,
       NULL::uuid AS student_subject_id,
       si."position"
FROM my_survey ms
JOIN survey_item si ON si.survey_id = ms.id
WHERE si.subject_type IS NULL;
SQL,
        );
        $this->addSql('CREATE INDEX my_survey_id_user_id_idx ON my_survey (id, user_id);');
        $this->addSql('CREATE INDEX my_survey_completed_actual_idx ON my_survey (completed, actual);');
        $this->addSql('CREATE INDEX my_survey_item_id_idx ON my_survey_item (id);');
        $this->addSql('CREATE INDEX my_survey_item_survey_id_user_id_idx ON my_survey_item (survey_id, user_id);');

        $this->addSql('DROP TRIGGER refresh_views_user ON "user";');
    }
}
