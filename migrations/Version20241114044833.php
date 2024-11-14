<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241114044833 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create a table called users';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable("public.users");

        $table->addColumn('id', 'bigint', ['autoincrement' => true, 'unsigned' => true]); // bigInt untuk ID
        $table->addColumn('name', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('email', 'string', ['length' => 255, 'notnull' => true]);
        $table->addColumn('verified_at', 'datetime', ['notnull' => false]);
        $table->addColumn('username', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn("avatar", "string",  ['notnull' => false]);
        $table->addColumn("password", "string",  ['notnull' => true]);
        $table->addColumn("remember_token", "string",  ['notnull' => false]);
        $table->addColumn('created_by', 'bigint', ['notnull' => false, 'unsigned' => true]);
        $table->addColumn('updated_by', 'bigint', ['notnull' => false, 'unsigned' => true]);
        $table->addColumn('deleted_by', 'bigint', ['notnull' => false, 'unsigned' => true]);
        $table->addColumn('created_at', 'datetime', ['notnull' => false, 'default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_at', 'datetime', ['notnull' => false]);
        $table->addColumn('deleted_at', 'datetime', ['notnull' => false]);

        $table->setPrimaryKey(['id']);

        $table->addUniqueConstraint(['email', 'username']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable("public.users");

    }
}
