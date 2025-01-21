<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250121061301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
        UPDATE survey_item SET data = data || '{"max_length": 255}'::jsonb WHERE type = 'comment'
        SQL,
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<SQL
        UPDATE survey_item SET data = data - 'max_length' WHERE type = 'comment'
        SQL,
        );
    }
}
