<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241117160942 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE "group" (id UUID NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))',
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6DC044C55E237E06 ON "group" (name)');
        $this->addSql(
            'CREATE TABLE user_data_group (user_data_id UUID NOT NULL, group_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(user_data_id, group_id))',
        );
        $this->addSql('CREATE UNIQUE INDEX UNIQ_84184EB66FF8BF36 ON user_data_group (user_data_id)');
        $this->addSql('CREATE INDEX IDX_84184EB6FE54D947 ON user_data_group (group_id)');
        $this->addSql(
            'ALTER TABLE user_data_group ADD CONSTRAINT FK_84184EB66FF8BF36 FOREIGN KEY (user_data_id) REFERENCES user_data (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE',
        );
        $this->addSql(
            'ALTER TABLE user_data_group ADD CONSTRAINT FK_84184EB6FE54D947 FOREIGN KEY (group_id) REFERENCES "group" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE',
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_data_group DROP CONSTRAINT FK_84184EB66FF8BF36');
        $this->addSql('ALTER TABLE user_data_group DROP CONSTRAINT FK_84184EB6FE54D947');
        $this->addSql('DROP TABLE "group"');
        $this->addSql('DROP TABLE user_data_group');
    }
}
