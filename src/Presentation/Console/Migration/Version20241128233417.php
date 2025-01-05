<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241128233417 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_84184eb66ff8bf36');
        $this->addSql('ALTER TABLE user_data_group DROP CONSTRAINT user_data_group_pkey');
        $this->addSql('ALTER TABLE user_data_group ADD PRIMARY KEY (user_data_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_data_group DROP CONSTRAINT user_data_group_pkey');
        $this->addSql('CREATE UNIQUE INDEX uniq_84184eb66ff8bf36 ON user_data_group (user_data_id)');
        $this->addSql('ALTER TABLE user_data_group ADD PRIMARY KEY (user_data_id, group_id)');
    }
}
