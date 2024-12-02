<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241202113232 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_8d93d649e7927c74');
        $this->addSql('CREATE UNIQUE INDEX uniq_8d93d649e7927c74 ON "user"((lower(email)))');
        $this->addSql('DROP INDEX uniq_fbce3e7a5e237e06');
        $this->addSql('CREATE UNIQUE INDEX uniq_fbce3e7a5e237e06 ON "subject" ((lower(name)))');
        $this->addSql('DROP INDEX uniq_6dc044c55e237e06');
        $this->addSql('CREATE UNIQUE INDEX uniq_6dc044c55e237e06 ON "group" ((lower(name)))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_8d93d649e7927c74');
        $this->addSql('CREATE UNIQUE INDEX uniq_8d93d649e7927c74 ON "user" (email)');
        $this->addSql('DROP INDEX uniq_fbce3e7a5e237e06');
        $this->addSql('CREATE UNIQUE INDEX uniq_fbce3e7a5e237e06 ON "subject" (name)');
        $this->addSql('DROP INDEX uniq_6dc044c55e237e06');
        $this->addSql('CREATE UNIQUE INDEX uniq_6dc044c55e237e06 ON "group" (name)');
    }
}
