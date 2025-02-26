<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250224165621 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_E4DCE0EFDADD4A25 ON survey_item_answer (answer)');
        $this->addSql('CREATE INDEX IDX_E4DCE0EF8CDE5729 ON survey_item_answer (type)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_E4DCE0EFDADD4A25');
        $this->addSql('DROP INDEX IDX_E4DCE0EF8CDE5729');
    }
}
