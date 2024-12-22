<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241222142048 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
        CREATE VIEW my_teacher_subject AS (
            SELECT ts.id AS teacher_subject_id,
                   coalesce(count(ss.user_id), 0) AS students_count
            FROM teacher_subject ts
            LEFT JOIN student_subject ss ON ts.id = ss.teacher_subject_id
            GROUP BY ts.id
        )
        SQL,
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP VIEW my_teacher_subject');
    }
}
