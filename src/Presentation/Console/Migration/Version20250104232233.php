<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250104232233 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE survey ADD title VARCHAR(255)');
        $this->addSql(
            <<<SQL
        UPDATE survey
        SET title = 'Опрос по предмету "' || s.name || '"'
        FROM (SELECT s1.id, s2.name FROM survey s1 INNER JOIN subject s2 ON s1.subject_id = s2.id) s
        WHERE s.id = survey.id
        SQL,
        );
        $this->addSql('ALTER TABLE survey ALTER COLUMN title SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE survey DROP title');
    }
}
