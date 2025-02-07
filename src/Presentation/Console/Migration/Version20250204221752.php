<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250204221752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE semester (id UUID NOT NULL, year INT NOT NULL, spring BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))',
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F7388EEDBB8273376B3E1469 ON semester (year, spring)');
        $this->addSql('ALTER TABLE student_subject ADD semester_id UUID');
        $this->addSql(
            <<<SQL
INSERT INTO semester (id, year, spring, created_at)
VALUES (gen_random_uuid(), 2024, FALSE, DEFAULT);
SQL,
        );
        $this->addSql(
            <<<SQL
UPDATE student_subject SET semester_id = s.id
FROM (
    SELECT id FROM semester LIMIT 1
) s
WHERE TRUE;
SQL,
        );
        $this->addSql('ALTER TABLE student_subject ALTER COLUMN semester_id SET NOT NULL');
        $this->addSql('ALTER TABLE student_subject DROP CONSTRAINT student_subject_idx');
        $this->addSql('ALTER TABLE student_subject DROP actual_from');
        $this->addSql('ALTER TABLE student_subject DROP actual_to');
        $this->addSql(
            'ALTER TABLE student_subject ADD CONSTRAINT FK_16F88B824A798B6F FOREIGN KEY (semester_id) REFERENCES semester (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE',
        );
        $this->addSql('CREATE INDEX IDX_16F88B824A798B6F ON student_subject (semester_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE semester');
        $this->addSql('ALTER TABLE student_subject DROP CONSTRAINT FK_16F88B824A798B6F');
        $this->addSql('DROP INDEX IDX_16F88B824A798B6F');
        $this->addSql('ALTER TABLE student_subject ADD actual_from TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE student_subject ADD actual_to TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql(
            <<<SQL
UPDATE student_subject
SET actual_from = s.af, actual_to = s.at
FROM (
    SELECT ss.id,
           sem.year::text || CASE sem.spring WHEN TRUE THEN '-02-01' ELSE '09-01' END AS af,
           sem.year::text || CASE sem.spring WHEN TRUE THEN '-05-31' ELSE '12-31' END AS at
    FROM student_subject ss
    INNER JOIN semester sem ON sem.id = ss.semester_id
) s
WHERE s.id = student_subject.id;
SQL,
        );
        $this->addSql('ALTER TABLE student_subject ALTER COLUMN actual_from SET NOT NULL');
        $this->addSql('ALTER TABLE student_subject ALTER COLUMN actual_to SET NOT NULL');
        $this->addSql('ALTER TABLE student_subject DROP semester_id');
        $this->addSql(
            <<<SQL
ALTER TABLE student_subject ADD CONSTRAINT student_subject_idx EXCLUDE USING gist (
    user_id WITH =,
    teacher_subject_id WITH =,
    tsrange(actual_from, actual_to, '[]') WITH &&
);
SQL,
        );
    }
}
