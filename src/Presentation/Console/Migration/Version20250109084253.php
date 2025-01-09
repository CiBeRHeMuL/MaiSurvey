<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250109084253 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<SQL
        UPDATE survey_item SET data = ('{"type": "rating", "min": ' || s.min || ', "max": ' || s.max || '}')::jsonb
        FROM (
            SELECT id, min(r.rate::int) AS min, max(r.rate::int) AS max
            FROM survey_item
                INNER JOIN jsonb_array_elements_text(data->'ratings') r(rate) ON TRUE
            WHERE type = 'rating'
            GROUP BY id
        ) s
        WHERE s.id = survey_item.id
        SQL,
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<SQL
        UPDATE survey_item SET data = ('{"type": "rating", "ratings": [' || s.rates || ']}')::jsonb
        FROM (
            SELECT id, string_agg(r.rate::text, ', ') as rates
            FROM survey_item
                INNER JOIN generate_series((data->>'min')::int, (data->>'max')::int, 1) r(rate) ON TRUE
            WHERE type = 'rating'
            GROUP BY id
        ) s
        WHERE s.id = survey_item.id
        SQL,
        );
    }
}
