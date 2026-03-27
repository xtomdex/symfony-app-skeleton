<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260325084509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates documents table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE system_documents (id VARCHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, content TEXT NOT NULL, type VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, edited_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A16F7A35989D9B62 ON system_documents (slug)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE system_documents');
    }
}
