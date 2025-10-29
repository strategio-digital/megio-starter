<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251029093412 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "admin" (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, email VARCHAR(64) NOT NULL, password VARCHAR(255) NOT NULL, last_login TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_880E0D76E7927C74 ON "admin" (email)');
        $this->addSql('CREATE TABLE "auth_resource" (name VARCHAR(255) NOT NULL, type VARCHAR(32) NOT NULL, id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_986B73905E237E06 ON "auth_resource" (name)');
        $this->addSql('CREATE TABLE "auth_role" (name VARCHAR(32) NOT NULL, id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_794F6ADE5E237E06 ON "auth_role" (name)');
        $this->addSql('CREATE TABLE "auth_role_has_resource" (role_id UUID NOT NULL, resource_id UUID NOT NULL, PRIMARY KEY (role_id, resource_id))');
        $this->addSql('CREATE INDEX IDX_2923B54FD60322AC ON "auth_role_has_resource" (role_id)');
        $this->addSql('CREATE INDEX IDX_2923B54F89329D25 ON "auth_role_has_resource" (resource_id)');
        $this->addSql('CREATE TABLE "auth_token" (source VARCHAR(32) NOT NULL, source_id UUID NOT NULL, expiration TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, token TEXT NOT NULL, id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_9315F04E5F8A7F73953C1C61 ON "auth_token" (source, source_id)');
        $this->addSql('CREATE TABLE "queue" (worker VARCHAR(255) NOT NULL, priority INT DEFAULT 0 NOT NULL, status VARCHAR(255) NOT NULL, payload JSON NOT NULL, delay_until TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, delay_reason VARCHAR(255) DEFAULT NULL, worker_id INT DEFAULT NULL, error_retries INT DEFAULT 0 NOT NULL, error_message TEXT DEFAULT NULL, id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_7FFD7F639FB2BF6262A6DC27 ON "queue" (worker, priority)');
        $this->addSql('CREATE TABLE "user" (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, email VARCHAR(64) NOT NULL, password VARCHAR(255) NOT NULL, last_login TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('CREATE TABLE user_has_role (user_id UUID NOT NULL, role_id UUID NOT NULL, PRIMARY KEY (user_id, role_id))');
        $this->addSql('CREATE INDEX IDX_EAB8B535A76ED395 ON user_has_role (user_id)');
        $this->addSql('CREATE INDEX IDX_EAB8B535D60322AC ON user_has_role (role_id)');
        $this->addSql('ALTER TABLE "auth_role_has_resource" ADD CONSTRAINT FK_2923B54FD60322AC FOREIGN KEY (role_id) REFERENCES "auth_role" (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE "auth_role_has_resource" ADD CONSTRAINT FK_2923B54F89329D25 FOREIGN KEY (resource_id) REFERENCES "auth_resource" (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_has_role ADD CONSTRAINT FK_EAB8B535A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE user_has_role ADD CONSTRAINT FK_EAB8B535D60322AC FOREIGN KEY (role_id) REFERENCES "auth_role" (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "auth_role_has_resource" DROP CONSTRAINT FK_2923B54FD60322AC');
        $this->addSql('ALTER TABLE "auth_role_has_resource" DROP CONSTRAINT FK_2923B54F89329D25');
        $this->addSql('ALTER TABLE user_has_role DROP CONSTRAINT FK_EAB8B535A76ED395');
        $this->addSql('ALTER TABLE user_has_role DROP CONSTRAINT FK_EAB8B535D60322AC');
        $this->addSql('DROP TABLE "admin"');
        $this->addSql('DROP TABLE "auth_resource"');
        $this->addSql('DROP TABLE "auth_role"');
        $this->addSql('DROP TABLE "auth_role_has_resource"');
        $this->addSql('DROP TABLE "auth_token"');
        $this->addSql('DROP TABLE "queue"');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE user_has_role');
    }
}
