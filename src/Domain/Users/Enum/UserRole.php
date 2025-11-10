<?php

declare(strict_types=1);

namespace App\Domain\Users\Enum;

/**
 * Enum para Roles de Usuário
 * 
 * Define os papéis disponíveis no sistema de acordo com as personas de negócio
 * 
 * Personas:
 * - SCHOOL_ADMIN: Administrador da Escola (configura mensalidades e relatórios)
 * - SECRETARY: Secretaria (gerencia alunos, pagamentos e turmas)
 * - TEACHER: Professor (marca presenças e registra observações)
 * - GUARDIAN: Responsável (visualiza histórico e pendências)
 * - SAAS_SUPPORT: Suporte SaaS (acesso restrito a metadados)
 * - USER: Usuário padrão do sistema
 */
enum UserRole: string
{
    case SCHOOL_ADMIN = 'ROLE_SCHOOL_ADMIN';    // Administrador da Escola
    case SECRETARY = 'ROLE_SECRETARY';          // Secretaria
    case TEACHER = 'ROLE_TEACHER';              // Professor
    case GUARDIAN = 'ROLE_GUARDIAN';            // Responsável pelo aluno
    case SAAS_SUPPORT = 'ROLE_SAAS_SUPPORT';    // Suporte SaaS
    case USER = 'ROLE_USER';                    // Usuário padrão

    public function label(): string
    {
        return match($this) {
            self::SCHOOL_ADMIN => 'Administrador da Escola',
            self::SECRETARY => 'Secretaria',
            self::TEACHER => 'Professor',
            self::GUARDIAN => 'Responsável',
            self::SAAS_SUPPORT => 'Suporte SaaS',
            self::USER => 'Usuário',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::SCHOOL_ADMIN => 'Configura mensalidades, acessa relatórios e gerencia configurações da escola',
            self::SECRETARY => 'Gerencia alunos, pagamentos e turmas',
            self::TEACHER => 'Marca presenças e registra observações sobre evolução dos alunos',
            self::GUARDIAN => 'Visualiza histórico e pendências dos alunos',
            self::SAAS_SUPPORT => 'Acesso restrito a metadados sob consentimento',
            self::USER => 'Usuário padrão do sistema',
        };
    }

    /**
     * Retorna as permissões associadas a cada role
     * Baseado em RBAC (Role-Based Access Control)
     */
    public function permissions(): array
    {
        return match($this) {
            // Admin da Escola: Todas permissões da escola
            self::SCHOOL_ADMIN => [
                // Configurações
                'school.settings.manage',
                'school.reports.view',
                'school.reports.export',
                // Mensalidades e Financeiro
                'fees.view',
                'fees.create',
                'fees.update',
                'fees.delete',
                'fees.configure',
                'payments.view',
                'payments.manage',
                'payments.reports',
                // Gestão de Usuários
                'users.view',
                'users.create',
                'users.update',
                'users.delete',
                // Alunos e Turmas
                'students.view',
                'students.create',
                'students.update',
                'classes.view',
                'classes.create',
                'classes.update',
                // Relatórios
                'attendances.reports',
                'evolutions.reports',
                'financial.reports',
            ],
            
            // Secretaria: Gestão operacional
            self::SECRETARY => [
                // Alunos
                'students.view',
                'students.create',
                'students.update',
                'students.enroll',
                'students.unenroll',
                // Turmas
                'classes.view',
                'classes.create',
                'classes.update',
                'classes.manage_enrollments',
                // Pagamentos
                'payments.view',
                'payments.create',
                'payments.update',
                'payments.reconcile',
                // Mensalidades
                'fees.view',
                'fees.assign',
                // Responsáveis
                'guardians.view',
                'guardians.create',
                'guardians.update',
                'guardians.link_students',
                // Relatórios básicos
                'payments.reports',
                'students.reports',
            ],
            
            // Professor: Gestão pedagógica
            self::TEACHER => [
                // Presenças
                'attendances.view',
                'attendances.create',
                'attendances.update',
                'attendances.mark',
                // Evoluções
                'evolutions.view',
                'evolutions.create',
                'evolutions.update',
                'evolutions.add_observations',
                // Alunos (visualização)
                'students.view',
                'students.view_details',
                // Turmas (suas turmas)
                'classes.view_own',
                'classes.view_students',
                // Relatórios pedagógicos
                'attendances.reports_own',
                'evolutions.reports_own',
            ],
            
            // Responsável: Visualização de dados dos filhos
            self::GUARDIAN => [
                'students.view_own',
                'attendances.view_own',
                'evolutions.view_own',
                'fees.view_own',
                'payments.view_own',
                'payments.create_own',
                'payments.history_own',
                'profile.view',
                'profile.update',
            ],
            
            // Suporte SaaS: Acesso técnico restrito
            self::SAAS_SUPPORT => [
                'system.metadata.view',
                'system.logs.view',
                'system.health.view',
                'system.diagnostics.run',
                'support.tickets.manage',
                // Sem acesso a dados sensíveis dos alunos
            ],
            
            // Usuário padrão
            self::USER => [
                'profile.view',
                'profile.update',
            ],
        };
    }

    /**
     * Verifica se a role tem acesso multi-tenant (vinculada a escola)
     */
    public function isSchoolBound(): bool
    {
        return match($this) {
            self::SCHOOL_ADMIN,
            self::SECRETARY,
            self::TEACHER,
            self::GUARDIAN => true,
            self::SAAS_SUPPORT,
            self::USER => false,
        };
    }

    /**
     * Verifica se a role pode acessar dados de outras escolas
     */
    public function canAccessMultipleSchools(): bool
    {
        return match($this) {
            self::SAAS_SUPPORT => true,
            default => false,
        };
    }

    /**
     * Verifica se a role requer aprovação para criação
     */
    public function requiresApproval(): bool
    {
        return match($this) {
            self::GUARDIAN => true,
            default => false,
        };
    }

    /**
     * Retorna o nível hierárquico (maior = mais poder)
     */
    public function hierarchyLevel(): int
    {
        return match($this) {
            self::SAAS_SUPPORT => 100,
            self::SCHOOL_ADMIN => 90,
            self::SECRETARY => 70,
            self::TEACHER => 50,
            self::GUARDIAN => 30,
            self::USER => 10,
        };
    }

    public static function fromString(string $role): self
    {
        return match($role) {
            'ROLE_SCHOOL_ADMIN' => self::SCHOOL_ADMIN,
            'ROLE_SECRETARY' => self::SECRETARY,
            'ROLE_TEACHER' => self::TEACHER,
            'ROLE_GUARDIAN' => self::GUARDIAN,
            'ROLE_SAAS_SUPPORT' => self::SAAS_SUPPORT,
            'ROLE_USER' => self::USER,
            default => self::USER,
        };
    }

    /**
     * Retorna todas as roles disponíveis
     * 
     * @return array<UserRole>
     */
    public static function all(): array
    {
        return [
            self::SCHOOL_ADMIN,
            self::SECRETARY,
            self::TEACHER,
            self::GUARDIAN,
            self::SAAS_SUPPORT,
            self::USER,
        ];
    }

    /**
     * Retorna roles que podem ser atribuídas por um admin da escola
     * 
     * @return array<UserRole>
     */
    public static function schoolAssignable(): array
    {
        return [
            self::SECRETARY,
            self::TEACHER,
        ];
    }
}
