<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250511121831 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE notice
            (
                id           uuid                                                     NOT NULL,
                type         varchar(255)                                             NOT NULL,
                channel      varchar(255)                                             NOT NULL,
                user_id      uuid                                                     NOT NULL,
                status       varchar(255)                   DEFAULT 'created'         NOT NULL,
                context      jsonb                                                    NOT NULL,
                sent_at      timestamp(0) without time zone DEFAULT NULL,
                delivered_at timestamp(0) without time zone DEFAULT NULL,
                text         text                                                     NOT NULL,
                recipient_id varchar(255)                                             NOT NULL,
                send_error   text                           DEFAULT NULL,
                external_id  varchar(255)                   DEFAULT NULL,
                created_at   timestamp(0) without time zone DEFAULT current_timestamp NOT NULL,
                updated_at   timestamp(0) without time zone DEFAULT current_timestamp NOT NULL,
                PRIMARY KEY (id)
            )
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_480D45C2A76ED395 ON notice (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_480D45C2A76ED3957B00651C ON notice (user_id, status)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_480D45C2A76ED3958CDE5729 ON notice (user_id, type)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_480D45C2A76ED395A2F98E47 ON notice (user_id, channel)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_480D45C2E92F8F78 ON notice (recipient_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notice ADD CONSTRAINT FK_480D45C2A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE notice DROP CONSTRAINT FK_480D45C2A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE notice
        SQL);
    }
}
