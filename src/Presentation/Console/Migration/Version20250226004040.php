<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250226004040 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE survey_stat (id UUID NOT NULL, available_count INT NOT NULL, completed_count INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE survey_stat_item (id UUID NOT NULL, survey_id UUID NOT NULL, available_count INT NOT NULL, completed_count INT NOT NULL, stats JSONB NOT NULL, type VARCHAR(255) NOT NULL, position INTEGER NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9B059C67B3FE509D ON survey_stat_item (survey_id)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT GENERATED BY DEFAULT AS IDENTITY NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('ALTER TABLE survey_stat ADD CONSTRAINT FK_E651FFEABF396750 FOREIGN KEY (id) REFERENCES survey (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE survey_stat_item ADD CONSTRAINT FK_9B059C67B3FE509D FOREIGN KEY (survey_id) REFERENCES survey_stat (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE survey_stat_item ADD CONSTRAINT FK_9B059C67BF396750 FOREIGN KEY (id) REFERENCES survey_item (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql(
            <<<SQL
CREATE FUNCTION array_from_jsonb(IN jsonb jsonb) RETURNS jsonb[]
    LANGUAGE sql
AS
$$
SELECT array_agg(t.*)
FROM jsonb_array_elements(jsonb) AS t
$$;
SQL,
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP FUNCTION array_from_jsonb;');
        $this->addSql('ALTER TABLE survey_stat DROP CONSTRAINT FK_E651FFEABF396750');
        $this->addSql('ALTER TABLE survey_stat_item DROP CONSTRAINT FK_9B059C67B3FE509D');
        $this->addSql('ALTER TABLE survey_stat_item DROP CONSTRAINT FK_9B059C67BF396750');
        $this->addSql('DROP TABLE survey_stat');
        $this->addSql('DROP TABLE survey_stat_item');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
