<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241128220534 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE subject (id UUID NOT NULL, name VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FBCE3E7A5E237E06 ON subject (name)');
        $this->addSql('CREATE TABLE user_subject (user_id UUID NOT NULL, subject_id UUID NOT NULL, teacher_id UUID NOT NULL, actual_from TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, actual_to TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(user_id, subject_id, teacher_id))');
        $this->addSql('CREATE INDEX IDX_A3C32070A76ED395 ON user_subject (user_id)');
        $this->addSql('CREATE INDEX IDX_A3C3207023EDC87 ON user_subject (subject_id)');
        $this->addSql('CREATE INDEX IDX_A3C3207041807E1D ON user_subject (teacher_id)');
        $this->addSql('ALTER TABLE user_subject ADD CONSTRAINT FK_A3C32070A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_subject ADD CONSTRAINT FK_A3C3207023EDC87 FOREIGN KEY (subject_id) REFERENCES subject (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_subject ADD CONSTRAINT FK_A3C3207041807E1D FOREIGN KEY (teacher_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_subject DROP CONSTRAINT FK_A3C32070A76ED395');
        $this->addSql('ALTER TABLE user_subject DROP CONSTRAINT FK_A3C3207023EDC87');
        $this->addSql('ALTER TABLE user_subject DROP CONSTRAINT FK_A3C3207041807E1D');
        $this->addSql('DROP TABLE subject');
        $this->addSql('DROP TABLE user_subject');
    }
}
