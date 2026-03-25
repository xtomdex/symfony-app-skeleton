<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324210609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates system event log and users tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE system_event_log (id VARCHAR(36) NOT NULL, event_id VARCHAR(64) NOT NULL, event_name VARCHAR(255) NOT NULL, payload JSON NOT NULL, context JSON DEFAULT NULL, recorded_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_system_event_log_event_name ON system_event_log (event_name)');
        $this->addSql('CREATE INDEX idx_system_event_log_recorded_at ON system_event_log (recorded_at)');
        $this->addSql('CREATE TABLE users (id VARCHAR(36) NOT NULL, username VARCHAR(64) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, type VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9F85E0677 ON users (username)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE system_event_log');
        $this->addSql('DROP TABLE users');
    }
}
