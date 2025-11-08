<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251108000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create refresh_tokens table with hashed tokens and metadata.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('refresh_tokens');
        $table->addColumn('id', 'string', ['length' => 36]);
        $table->addColumn('username', 'string', ['length' => 255]);
        $table->addColumn('refresh_token', 'string', ['length' => 128]);
        $table->addColumn('valid_until', 'datetime_immutable');
        $table->addColumn('created_at', 'datetime_immutable');
        $table->addColumn('created_by_ip', 'string', ['length' => 45, 'notnull' => false]);
        $table->addColumn('user_agent', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('revoked_at', 'datetime_immutable', ['notnull' => false]);
        $table->addColumn('updated_at', 'datetime_immutable', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['refresh_token'], 'UNIQ_REFRESH_TOKEN_TOKEN');
        $table->addIndex(['username'], 'IDX_REFRESH_TOKEN_USERNAME');
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('refresh_tokens');
    }
}
