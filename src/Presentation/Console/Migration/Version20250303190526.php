<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250303190526 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE survey_item_answer ALTER answer TYPE JSONB');
        $this->addSql('ALTER TABLE survey_item_answer ALTER answer SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE survey_item_answer ALTER answer TYPE JSONB');
        $this->addSql('ALTER TABLE survey_item_answer ALTER answer DROP NOT NULL');
    }
}
