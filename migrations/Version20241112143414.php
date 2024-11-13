<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241112143414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create a table called posts, which is used to store post data and media details from instagram.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('CREATE TABLE posts (id SERIAL PRIMARY KEY, media_url VARCHAR(255) NOT NULL)');
        $table = $schema->createTable("instagram.posts");

        $table->addColumn('id', 'bigint', ['autoincrement' => true]); // bigInt untuk ID
        $table->addColumn("media_url", "string");
        $table->addColumn("media_id", "string");
        $table->addColumn("caption", "string", ['notnull' => false]);
        $table->addColumn("category", "string", ['notnull' => false]);
        $table->addColumn("brand", "string", ['notnull' => false]);
        $table->addColumn("design", "string", ['notnull' => false]);
        $table->addColumn("hash", "string", ['notnull' => false]);
        $table->addColumn('is_sold', 'boolean', ['default' => false]);
        $table->addColumn('price', 'integer', ['notnull' => false, 'default' => null]);
        $table->addColumn('created_by', 'bigint', ['notnull' => false]);
        $table->addColumn('updated_by', 'bigint', ['notnull' => false]);
        $table->addColumn('deleted_by', 'bigint', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime', ['notnull' => false, 'default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('deleted_at', 'datetime', ['notnull' => false]);

        $table->setPrimaryKey(['id']);

        $table->addUniqueConstraint(['media_url', 'media_id']);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE instagram.posts');
    }
}
