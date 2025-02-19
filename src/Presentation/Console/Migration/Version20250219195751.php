<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250219195751 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
CREATE OR REPLACE FUNCTION trigger_refresh_views()
    RETURNS TRIGGER AS $$
DECLARE
    views text[];
    view text;
    is_mat_view boolean;
BEGIN
    views := TG_ARGV;
    FOREACH view IN ARRAY views
        LOOP
            SELECT EXISTS (
                SELECT 1
                FROM pg_class c
                         INNER JOIN pg_namespace n ON n.oid = c.relnamespace
                WHERE c.relname = view
                  AND n.nspname = current_schema()
                  AND c.relkind = 'm'
            ) INTO is_mat_view;
            IF is_mat_view THEN
                EXECUTE format('REFRESH MATERIALIZED VIEW %I', view);
            ELSE
                RAISE NOTICE 'Материализованное представление "%" не существует.', view;
            END IF;
        END LOOP;
    RETURN NULL;
END;
$$ LANGUAGE plpgsql;
SQL,
        );
        $this->addSql(
            <<<SQL
CREATE OR REPLACE TRIGGER refresh_views_completed_survey
    AFTER UPDATE OR DELETE OR INSERT OR TRUNCATE
    ON completed_survey
EXECUTE PROCEDURE trigger_refresh_views('my_survey');
SQL,
        );
        $this->addSql(
            <<<SQL
CREATE OR REPLACE TRIGGER refresh_views_student_subject
    AFTER UPDATE OR DELETE OR INSERT OR TRUNCATE
    ON student_subject
EXECUTE PROCEDURE trigger_refresh_views('my_survey', 'my_survey_item');
SQL,
        );
        $this->addSql(
            <<<SQL
CREATE OR REPLACE TRIGGER refresh_views_survey_item
    AFTER UPDATE OR DELETE OR INSERT OR TRUNCATE
    ON survey_item
EXECUTE PROCEDURE trigger_refresh_views('my_survey_item');
SQL,
        );
        $this->addSql(
            <<<SQL
CREATE OR REPLACE TRIGGER refresh_views_survey
    AFTER UPDATE OR DELETE OR INSERT OR TRUNCATE
    ON survey
EXECUTE PROCEDURE trigger_refresh_views('my_survey');
SQL,
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TRIGGER refresh_views_survey ON survey;');
        $this->addSql('DROP TRIGGER refresh_views_survey_item ON survey_item;');
        $this->addSql('DROP TRIGGER refresh_views_student_subject ON student_subject;');
        $this->addSql('DROP TRIGGER refresh_views_completed_survey ON completed_survey;');
        $this->addSql('DROP FUNCTION trigger_refresh_views;');
    }
}
