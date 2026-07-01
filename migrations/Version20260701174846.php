<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260701174846 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add status field to book table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE book ADD status VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE book DROP status');
    }
}
