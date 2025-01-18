<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250118072758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP VIEW my_survey_item');
        $this->addSql(<<<SQL
        CREATE OR REPLACE VIEW my_survey AS (
            SELECT DISTINCT ON (s.id, u.id) s.id,
                                            u.id           AS user_id,
                                            cs IS NOT NULL AS completed,
                                            cs.created_at  AS completed_at
            FROM "user" u
                     INNER JOIN student_subject us ON us.user_id = u.id
                     INNER JOIN teacher_subject ts ON us.teacher_subject_id = ts.id
                     INNER JOIN survey s ON s.subject_id = ts.subject_id
                     LEFT JOIN completed_survey cs ON s.id = cs.survey_id AND u.id = cs.user_id
            WHERE u.status::text = 'active'::text
              AND u.roles @> ARRAY ['student'::text]
              AND (s.actual_to > now() OR cs IS NOT NULL)
        )
        SQL,
        );
        $this->addSql(<<<SQL
        CREATE VIEW my_survey_item(id, survey_id, user_id, teacher_subject_id, position) AS
        SELECT si.id,
               ms.id AS survey_id,
               ms.user_id,
               ss.teacher_subject_id,
               si."position"
        FROM my_survey ms
                 JOIN survey s ON s.id = ms.id
                 JOIN survey_item si ON si.survey_id = s.id
                 JOIN student_subject ss ON ms.user_id = ss.user_id
                 JOIN teacher_subject ts ON ts.type::text = si.subject_type::text AND ts.subject_id = s.subject_id AND ts.id = ss.teacher_subject_id
        SQL,
        );
    }

    public function down(Schema $schema): void
    {

        $this->addSql('DROP VIEW my_survey_item');
        $this->addSql(<<<SQL
        CREATE OR REPLACE VIEW my_survey AS (
            SELECT s.id,
                   u.id           AS user_id,
                   cs IS NOT NULL AS completed,
                   cs.created_at  AS completed_at
            FROM "user" u
                     INNER JOIN student_subject us ON us.user_id = u.id
                     INNER JOIN teacher_subject ts ON us.teacher_subject_id = ts.id
                     INNER JOIN survey s ON s.subject_id = ts.subject_id
                     LEFT JOIN completed_survey cs ON s.id = cs.survey_id AND u.id = cs.user_id
            WHERE u.status::text = 'active'::text
              AND u.roles @> ARRAY ['student'::text]
              AND (s.actual_to > now() OR cs IS NOT NULL)
        )
        SQL,
        );
        $this->addSql(<<<SQL
        CREATE VIEW my_survey_item(id, survey_id, user_id, teacher_subject_id, position) AS
        SELECT si.id,
               ms.id AS survey_id,
               ms.user_id,
               ss.teacher_subject_id,
               si."position"
        FROM my_survey ms
                 JOIN survey s ON s.id = ms.id
                 JOIN survey_item si ON si.survey_id = s.id
                 JOIN student_subject ss ON ms.user_id = ss.user_id
                 JOIN teacher_subject ts ON ts.type::text = si.subject_type::text AND ts.subject_id = s.subject_id AND ts.id = ss.teacher_subject_id
        SQL,
        );
    }
}
