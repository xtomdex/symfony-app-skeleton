<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260314132548 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates users table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE users (id VARCHAR(36) NOT NULL, username VARCHAR(64) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, type VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1483A5E9F85E0677 (username), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE users');
    }
}
