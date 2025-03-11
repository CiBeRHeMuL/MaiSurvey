<?php

declare(strict_types=1);

namespace App\Presentation\Console\Migration;

use App\Domain\Enum\SurveyStatusEnum;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250310175126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE survey ADD status VARCHAR(255)');
        $this->addSql(
            'UPDATE survey SET status = :status WHERE actual_to >= now()',
            ['status' => SurveyStatusEnum::Active->value],
        );
        $this->addSql(
            'UPDATE survey SET status = :status WHERE actual_to < now()',
            ['status' => SurveyStatusEnum::Closed->value],
        );
        $this->addSql('ALTER TABLE survey ALTER status SET NOT NULL');
        $this->addSql('ALTER TABLE survey ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE survey ALTER actual_to DROP NOT NULL');
        $this->addSql('ALTER TABLE survey_item ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE survey_item DROP updated_at');
        $this->addSql('ALTER TABLE survey DROP status');
        $this->addSql('ALTER TABLE survey DROP updated_at');
        $this->addSql('ALTER TABLE survey ALTER actual_to SET NOT NULL');
    }
}
