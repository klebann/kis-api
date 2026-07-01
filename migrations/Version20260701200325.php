<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260701200325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add UNIQUE constraint for book.serial_number';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            DO $$
            BEGIN
                IF EXISTS (
                    SELECT 1
                    FROM book
                    GROUP BY serial_number
                    HAVING COUNT(*) > 1
                ) THEN
                    RAISE EXCEPTION 'Duplicate serial_number values exist. Migration aborted.';
                END IF;
            END $$;
        ");

        $this->addSql('CREATE UNIQUE INDEX UNIQ_BOOK_SERIAL_NUMBER ON book (serial_number)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_BOOK_SERIAL_NUMBER');
    }
}
