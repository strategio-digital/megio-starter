<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240402225956 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "blog_article" (id CHAR(36) NOT NULL --(DC2Type:guid)
        , author_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_EECCB3E5F675F31B FOREIGN KEY (author_id) REFERENCES "blog_author" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_EECCB3E5F675F31B ON "blog_article" (author_id)');
        $this->addSql('CREATE TABLE blog_article_has_tag (article_id CHAR(36) NOT NULL --(DC2Type:guid)
        , tag_id CHAR(36) NOT NULL --(DC2Type:guid)
        , PRIMARY KEY(article_id, tag_id), CONSTRAINT FK_6862D24D7294869C FOREIGN KEY (article_id) REFERENCES "blog_article" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_6862D24DBAD26311 FOREIGN KEY (tag_id) REFERENCES "blog_article_tag" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_6862D24D7294869C ON blog_article_has_tag (article_id)');
        $this->addSql('CREATE INDEX IDX_6862D24DBAD26311 ON blog_article_has_tag (tag_id)');
        $this->addSql('CREATE TABLE "blog_article_tag" (id CHAR(36) NOT NULL --(DC2Type:guid)
        , name VARCHAR(32) NOT NULL, slug VARCHAR(32) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_48A60807989D9B62 ON "blog_article_tag" (slug)');
        $this->addSql('CREATE TABLE "blog_author" (id CHAR(36) NOT NULL --(DC2Type:guid)
        , first_name VARCHAR(64) NOT NULL, last_name VARCHAR(64) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE "blog_author_profile" (id CHAR(36) NOT NULL --(DC2Type:guid)
        , author_id CHAR(36) DEFAULT NULL --(DC2Type:guid)
        , nickname VARCHAR(64) NOT NULL, biography CLOB NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id), CONSTRAINT FK_801A9EF5F675F31B FOREIGN KEY (author_id) REFERENCES "blog_author" (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_801A9EF5A188FE64 ON "blog_author_profile" (nickname)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_801A9EF5F675F31B ON "blog_author_profile" (author_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE "blog_article"');
        $this->addSql('DROP TABLE blog_article_has_tag');
        $this->addSql('DROP TABLE "blog_article_tag"');
        $this->addSql('DROP TABLE "blog_author"');
        $this->addSql('DROP TABLE "blog_author_profile"');
    }
}
