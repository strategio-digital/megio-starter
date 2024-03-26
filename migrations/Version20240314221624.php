<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240314221624 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "admin" (id CHAR(36) NOT NULL --(DC2Type:guid)
        , created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, email VARCHAR(64) NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_880E0D76E7927C74 ON "admin" (email)');
        $this->addSql('CREATE TABLE "auth_resource" (id CHAR(36) NOT NULL --(DC2Type:guid)
        , name VARCHAR(255) NOT NULL, type VARCHAR(32) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_986B73905E237E06 ON "auth_resource" (name)');
        $this->addSql('CREATE TABLE "auth_role" (id CHAR(36) NOT NULL --(DC2Type:guid)
        , name VARCHAR(32) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_794F6ADE5E237E06 ON "auth_role" (name)');
        $this->addSql('CREATE TABLE "auth_role_has_resource" (role_id CHAR(36) NOT NULL --(DC2Type:guid)
        , resource_id CHAR(36) NOT NULL --(DC2Type:guid)
        , PRIMARY KEY(role_id, resource_id), CONSTRAINT FK_2923B54FD60322AC FOREIGN KEY (role_id) REFERENCES "auth_role" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_2923B54F89329D25 FOREIGN KEY (resource_id) REFERENCES "auth_resource" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_2923B54FD60322AC ON "auth_role_has_resource" (role_id)');
        $this->addSql('CREATE INDEX IDX_2923B54F89329D25 ON "auth_role_has_resource" (resource_id)');
        $this->addSql('CREATE TABLE "auth_token" (id CHAR(36) NOT NULL --(DC2Type:guid)
        , source VARCHAR(32) NOT NULL, source_id CHAR(36) NOT NULL --(DC2Type:guid)
        , expiration DATETIME NOT NULL, token CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9315F04E5F8A7F73953C1C61 ON "auth_token" (source, source_id)');
        $this->addSql('CREATE TABLE "user" (id CHAR(36) NOT NULL --(DC2Type:guid)
        , created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, email VARCHAR(64) NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('CREATE TABLE user_has_role (user_id CHAR(36) NOT NULL --(DC2Type:guid)
        , role_id CHAR(36) NOT NULL --(DC2Type:guid)
        , PRIMARY KEY(user_id, role_id), CONSTRAINT FK_EAB8B535A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_EAB8B535D60322AC FOREIGN KEY (role_id) REFERENCES "auth_role" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_EAB8B535A76ED395 ON user_has_role (user_id)');
        $this->addSql('CREATE INDEX IDX_EAB8B535D60322AC ON user_has_role (role_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE "admin"');
        $this->addSql('DROP TABLE "auth_resource"');
        $this->addSql('DROP TABLE "auth_role"');
        $this->addSql('DROP TABLE "auth_role_has_resource"');
        $this->addSql('DROP TABLE "auth_token"');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('DROP TABLE user_has_role');
    }
}
