<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250206230933 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subject ADD semester_id UUID');
        $this->addSql('UPDATE subject SET semester_id = semester.id FROM semester WHERE semester.year = 2024 AND semester.spring IS FALSE');
        $this->addSql('ALTER TABLE subject ALTER COLUMN semester_id SET NOT NULL');
        $this->addSql('ALTER TABLE subject ADD CONSTRAINT FK_FBCE3E7A4A798B6F FOREIGN KEY (semester_id) REFERENCES semester (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_FBCE3E7A4A798B6F ON subject (semester_id)');
        $this->addSql('DROP INDEX uniq_fbce3e7a5e237e06');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FBCE3E7A5E237E064A798B6F ON subject ((lower(name)), semester_id)');
    }

    public function down(Schema $schema): void
    {
        // TODO
        $this->addSql('ALTER TABLE subject DROP CONSTRAINT FK_FBCE3E7A4A798B6F');
        $this->addSql('DROP INDEX IDX_FBCE3E7A4A798B6F');
        $this->addSql('DROP INDEX UNIQ_FBCE3E7A5E237E064A798B6F');
        $this->addSql('ALTER TABLE subject DROP semester_id');
        $this->addSql('CREATE UNIQUE INDEX uniq_fbce3e7a5e237e06 ON subject((lower(name)))');
    }
}
