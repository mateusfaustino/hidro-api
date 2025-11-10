<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration para criar tabela de usuários
 * 
 * Suporta multi-tenant via school_id
 * Implementa todas as 5 personas do sistema
 */
final class Version20250110150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Cria tabela users com suporte a multi-tenant e 5 personas (SCHOOL_ADMIN, SECRETARY, TEACHER, GUARDIAN, SAAS_SUPPORT)';
    }

    public function up(Schema $schema): void
    {
        // Criar tabela users
        $this->addSql('
            CREATE TABLE users (
                id VARCHAR(36) NOT NULL COMMENT \'UUID do usuário\',
                email VARCHAR(255) NOT NULL COMMENT \'Email único do usuário\',
                password VARCHAR(255) NOT NULL COMMENT \'Senha hash (bcrypt)\',
                name VARCHAR(255) NOT NULL COMMENT \'Nome completo\',
                phone VARCHAR(20) DEFAULT NULL COMMENT \'Telefone (obrigatório para GUARDIAN)\',
                roles JSON NOT NULL COMMENT \'Roles do usuário (RBAC)\',
                status VARCHAR(20) NOT NULL COMMENT \'Status: active, inactive, suspended, pending\',
                school_id VARCHAR(36) DEFAULT NULL COMMENT \'ID da escola (multi-tenant)\',
                created_at DATETIME NOT NULL COMMENT \'Data de criação\',
                updated_at DATETIME DEFAULT NULL COMMENT \'Data de atualização\',
                last_login_at DATETIME DEFAULT NULL COMMENT \'Último login\',
                PRIMARY KEY(id),
                UNIQUE INDEX UNIQ_users_email (email),
                INDEX IDX_users_school_id (school_id),
                INDEX IDX_users_status (status),
                INDEX IDX_users_created_at (created_at)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB
            COMMENT = \'Tabela de usuários com suporte multi-tenant e RBAC\'
        ');

        // Comentários nas colunas para documentação
        $this->addSql('
            ALTER TABLE users 
            MODIFY COLUMN roles JSON NOT NULL 
            COMMENT \'Roles: ROLE_SCHOOL_ADMIN, ROLE_SECRETARY, ROLE_TEACHER, ROLE_GUARDIAN, ROLE_SAAS_SUPPORT, ROLE_USER\'
        ');
    }

    public function down(Schema $schema): void
    {
        // Reverter: Drop tabela users
        $this->addSql('DROP TABLE users');
    }
}
