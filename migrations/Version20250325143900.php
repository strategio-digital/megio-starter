<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250325143900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE "article" (id UUID NOT NULL, author_id UUID DEFAULT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, content TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_23A0E66F675F31B ON "article" (author_id)');
        $this->addSql('CREATE TABLE article_has_article_tag (article_id UUID NOT NULL, article_tag_id UUID NOT NULL, PRIMARY KEY(article_id, article_tag_id))');
        $this->addSql('CREATE INDEX IDX_1BB324607294869C ON article_has_article_tag (article_id)');
        $this->addSql('CREATE INDEX IDX_1BB32460D015F491 ON article_has_article_tag (article_tag_id)');
        $this->addSql('CREATE TABLE "article_author" (id UUID NOT NULL, first_name VARCHAR(64) NOT NULL, last_name VARCHAR(64) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE "article_author_profile" (id UUID NOT NULL, author_id UUID DEFAULT NULL, nickname VARCHAR(64) NOT NULL, biography TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6256B9C4A188FE64 ON "article_author_profile" (nickname)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6256B9C4F675F31B ON "article_author_profile" (author_id)');
        $this->addSql('CREATE TABLE "article_tag" (id UUID NOT NULL, name VARCHAR(32) NOT NULL, slug VARCHAR(32) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_919694F9989D9B62 ON "article_tag" (slug)');
        $this->addSql('ALTER TABLE "article" ADD CONSTRAINT FK_23A0E66F675F31B FOREIGN KEY (author_id) REFERENCES "article_author" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE article_has_article_tag ADD CONSTRAINT FK_1BB324607294869C FOREIGN KEY (article_id) REFERENCES "article" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE article_has_article_tag ADD CONSTRAINT FK_1BB32460D015F491 FOREIGN KEY (article_tag_id) REFERENCES "article_tag" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "article_author_profile" ADD CONSTRAINT FK_6256B9C4F675F31B FOREIGN KEY (author_id) REFERENCES "article_author" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "article" DROP CONSTRAINT FK_23A0E66F675F31B');
        $this->addSql('ALTER TABLE article_has_article_tag DROP CONSTRAINT FK_1BB324607294869C');
        $this->addSql('ALTER TABLE article_has_article_tag DROP CONSTRAINT FK_1BB32460D015F491');
        $this->addSql('ALTER TABLE "article_author_profile" DROP CONSTRAINT FK_6256B9C4F675F31B');
        $this->addSql('DROP TABLE "article"');
        $this->addSql('DROP TABLE article_has_article_tag');
        $this->addSql('DROP TABLE "article_author"');
        $this->addSql('DROP TABLE "article_author_profile"');
        $this->addSql('DROP TABLE "article_tag"');
    }
}
