<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241206111744 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE survey_item ADD data JSONB NOT NULL');
        $this->addSql('ALTER TABLE survey_item_answer ALTER answer TYPE JSONB');
        $this->addSql('ALTER TABLE survey_item_answer ALTER answer DROP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE survey_item DROP data');
        $this->addSql('ALTER TABLE survey_item_answer ALTER answer TYPE JSON');
        $this->addSql('ALTER TABLE survey_item_answer ALTER answer SET NOT NULL');
    }
}
