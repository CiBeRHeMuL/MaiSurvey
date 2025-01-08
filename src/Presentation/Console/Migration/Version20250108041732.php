<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250108041732 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP VIEW my_survey_item');
        $this->addSql(
            <<<SQL
        CREATE VIEW my_survey_item(id, survey_id, user_id, teacher_subject_id, position) AS
        (
        SELECT si.id, ms.id, ms.user_id, ss.teacher_subject_id, si.position
        FROM my_survey ms
                 INNER JOIN survey s ON s.id = ms.id
                 INNER JOIN survey_item si ON si.survey_id = s.id
                 INNER JOIN student_subject ss ON ms.user_id = ss.user_id
                 INNER JOIN teacher_subject ts ON ts.type = si.subject_type AND ts.subject_id = s.subject_id AND ts.id = ss.teacher_subject_id
        );
        SQL,
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP VIEW my_survey_item');
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
    }
}
