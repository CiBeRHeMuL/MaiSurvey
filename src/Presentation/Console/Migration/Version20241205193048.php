<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241205193048 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<SQL
CREATE OR REPLACE VIEW my_survey AS
(
SELECT s.*, u.id AS user_id, cs IS NOT NULL AS completed, cs.created_at AS completed_at
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

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP VIEW my_survey');
    }
}
