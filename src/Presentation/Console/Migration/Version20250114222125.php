<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250114222125 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "CREATE INDEX user_data_full_name_idx ON user_data((last_name || ' ' || first_name || (CASE patronymic IS NULL WHEN TRUE THEN '' ELSE ' ' || patronymic END)))",
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX user_data_full_name_idx');
    }
}
