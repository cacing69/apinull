<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241113033049 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'create a table called post details, which is used to store post detail.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable("instagram.post_details");

        $table->addColumn('id', 'bigint', ['autoincrement' => true]); // bigInt untuk ID
        $table->addColumn('post_id', 'bigint'); // Kolom post_id sebagai foreign key
        $table->addColumn("image_url", "string");

        $table->setPrimaryKey(['id']);

        // Menambahkan foreign key constraint ke tabel posts
        $table->addForeignKeyConstraint(
            'instagram.posts',    // Nama tabel yang direferensikan
            ['post_id'],          // Kolom pada tabel ini (post_details)
            ['id'],               // Kolom pada tabel posts yang direferensikan
            ['onDelete' => 'CASCADE'] // Hapus detail jika post dihapus
        );

    }

    public function down(Schema $schema): void
    {
        // Menghapus tabel post_details
        $schema->dropTable("instagram.post_details");
    }
}
