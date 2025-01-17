<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250117192103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "blog_article" (id UUID NOT NULL, author_id UUID DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EECCB3E5F675F31B ON "blog_article" (author_id)');
        $this->addSql('CREATE TABLE blog_article_has_tag (article_id UUID NOT NULL, tag_id UUID NOT NULL, PRIMARY KEY(article_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_6862D24D7294869C ON blog_article_has_tag (article_id)');
        $this->addSql('CREATE INDEX IDX_6862D24DBAD26311 ON blog_article_has_tag (tag_id)');
        $this->addSql('CREATE TABLE "blog_article_tag" (id UUID NOT NULL, name VARCHAR(32) NOT NULL, slug VARCHAR(32) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_48A60807989D9B62 ON "blog_article_tag" (slug)');
        $this->addSql('CREATE TABLE "blog_author" (id UUID NOT NULL, first_name VARCHAR(64) NOT NULL, last_name VARCHAR(64) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE "blog_author_profile" (id UUID NOT NULL, author_id UUID DEFAULT NULL, nickname VARCHAR(64) NOT NULL, biography TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_801A9EF5A188FE64 ON "blog_author_profile" (nickname)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_801A9EF5F675F31B ON "blog_author_profile" (author_id)');
        $this->addSql('ALTER TABLE "blog_article" ADD CONSTRAINT FK_EECCB3E5F675F31B FOREIGN KEY (author_id) REFERENCES "blog_author" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE blog_article_has_tag ADD CONSTRAINT FK_6862D24D7294869C FOREIGN KEY (article_id) REFERENCES "blog_article" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE blog_article_has_tag ADD CONSTRAINT FK_6862D24DBAD26311 FOREIGN KEY (tag_id) REFERENCES "blog_article_tag" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "blog_author_profile" ADD CONSTRAINT FK_801A9EF5F675F31B FOREIGN KEY (author_id) REFERENCES "blog_author" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "blog_article" DROP CONSTRAINT FK_EECCB3E5F675F31B');
        $this->addSql('ALTER TABLE blog_article_has_tag DROP CONSTRAINT FK_6862D24D7294869C');
        $this->addSql('ALTER TABLE blog_article_has_tag DROP CONSTRAINT FK_6862D24DBAD26311');
        $this->addSql('ALTER TABLE "blog_author_profile" DROP CONSTRAINT FK_801A9EF5F675F31B');
        $this->addSql('DROP TABLE "blog_article"');
        $this->addSql('DROP TABLE blog_article_has_tag');
        $this->addSql('DROP TABLE "blog_article_tag"');
        $this->addSql('DROP TABLE "blog_author"');
        $this->addSql('DROP TABLE "blog_author_profile"');
    }
}
