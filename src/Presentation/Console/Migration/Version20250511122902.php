<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250511122902 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD notices_enabled boolean DEFAULT FALSE NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD notice_channels text[] DEFAULT '{}' NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD notice_types text[] DEFAULT '{}' NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ADD telegram_connect_id UUID
        SQL);
        $this->addSql(<<<'SQL'
            UPDATE "user" SET telegram_connect_id = gen_random_uuid()
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" ALTER telegram_connect_id SET NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8D93D649ED8E15A6 ON "user" (telegram_connect_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_8D93D649ED8E15A6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP notices_enabled
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP notice_channels
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP notice_types
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE "user" DROP telegram_connect_id
        SQL);
    }
}
