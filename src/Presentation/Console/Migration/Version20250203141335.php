<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250203141335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP VIEW my_survey_item;');
        $this->addSql(
            <<<SQL
            CREATE VIEW my_survey_item(id, survey_id, user_id, teacher_subject_id, student_subject_id, position) AS
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
                   ms.id      AS survey_id,
                   ms.user_id,
                   NULL::uuid AS teacher_subject_id,
                   NULL::uuid AS student_subject_id,
                   si."position"
            FROM my_survey ms
                     JOIN survey_item si ON si.survey_id = ms.id
            WHERE si.subject_type IS NULL;
            SQL,
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP VIEW my_survey_item;');
        $this->addSql(
            <<<SQL
            CREATE VIEW my_survey_item(id, survey_id, user_id, teacher_subject_id, position) AS
            SELECT si.id,
                   ms.id AS survey_id,
                   ms.user_id,
                   ss.teacher_subject_id,
                   si."position"
            FROM my_survey ms
                     JOIN survey_item si ON si.survey_id = ms.id
                     JOIN student_subject ss ON ms.user_id = ss.user_id
                     JOIN teacher_subject ts ON ts.type::text = si.subject_type::text AND ts.subject_id = ms.subject_id AND ts.id = ss.teacher_subject_id
            UNION ALL
            SELECT si.id,
                   ms.id      AS survey_id,
                   ms.user_id,
                   NULL::uuid AS teacher_subject_id,
                   si."position"
            FROM my_survey ms
                     JOIN survey_item si ON si.survey_id = ms.id
            WHERE si.subject_type IS NULL;
            SQL,
        );
    }
}
