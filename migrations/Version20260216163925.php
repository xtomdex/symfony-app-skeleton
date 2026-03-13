<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260216163925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates system event logs table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE system_event_log (id VARCHAR(36) NOT NULL, event_id VARCHAR(64) NOT NULL, event_name VARCHAR(255) NOT NULL, payload JSON NOT NULL, context JSON DEFAULT NULL, recorded_at DATETIME NOT NULL, INDEX idx_system_event_log_event_name (event_name), INDEX idx_system_event_log_recorded_at (recorded_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE system_event_log');
    }
}
