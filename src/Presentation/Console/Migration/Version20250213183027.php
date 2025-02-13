<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250213183027 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE student_subject DROP CONSTRAINT fk_16f88b824a798b6f');
        $this->addSql('DROP INDEX uniq_16f88b82a76ed39588217e274a798b6f');
        $this->addSql('DROP INDEX idx_16f88b824a798b6f');
        $this->addSql('ALTER TABLE student_subject DROP semester_id');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_16F88B82A76ED39588217E27 ON student_subject (user_id, teacher_subject_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_16F88B82A76ED39588217E27');
        $this->addSql('ALTER TABLE student_subject ADD semester_id UUID');
        $this->addSql(
            <<<SQL
UPDATE student_subject SET semester_id = s.semester_id
FROM (
    SELECT ss.id, s.semester_id
    FROM student_subject ss
    INNER JOIN teacher_subject ts ON ss.teacher_subject_id = ts.id
    INNER JOIN subject s ON ts.subject_id = s.id
) s WHERE student_subject.id = s.id
SQL,
        );
        $this->addSql('ALTER TABLE student_subject ALTER semester_id SET NOT NULL ');
        $this->addSql('ALTER TABLE student_subject ADD CONSTRAINT fk_16f88b824a798b6f FOREIGN KEY (semester_id) REFERENCES semester (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_16f88b82a76ed39588217e274a798b6f ON student_subject (user_id, teacher_subject_id, semester_id)');
        $this->addSql('CREATE INDEX idx_16f88b824a798b6f ON student_subject (semester_id)');
    }
}
