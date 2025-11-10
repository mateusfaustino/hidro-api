# Guia Completo de Migrations com DoctrineMigrationsBundle

## 📋 Índice

1. [O que são Migrations?](#o-que-são-migrations)
2. [Por que usar Migrations?](#por-que-usar-migrations)
3. [Como Funciona?](#como-funciona)
4. [Comandos Principais](#comandos-principais)
5. [Fluxo de Trabalho](#fluxo-de-trabalho)
6. [Exemplos Práticos](#exemplos-práticos)
7. [Boas Práticas](#boas-práticas)
8. [Troubleshooting](#troubleshooting)

---

## O que são Migrations?

**Migrations** (migrações) são uma maneira **segura** de atualizar o schema do seu banco de dados tanto localmente quanto em produção.

### Analogia Simples

Pense nas migrations como um **sistema de controle de versão (Git) para seu banco de dados**:

- Cada migration é como um "commit" que registra mudanças no schema
- Você pode avançar (`up`) ou reverter (`down`) mudanças
- O histórico é rastreado em uma tabela especial (`doctrine_migration_versions`)
- Qualquer desenvolvedor pode replicar o estado do banco de dados

### Chain of Thought: Como o Doctrine Rastreia Migrations?

```
1. Você cria uma nova migration
   ↓
2. Doctrine gera uma classe com timestamp único (ex: Version20250110120000)
   ↓
3. Ao executar a migration, Doctrine:
   a. Verifica a tabela doctrine_migration_versions
   b. Identifica quais migrations ainda não foram executadas
   c. Executa o método up() de cada migration pendente
   d. Registra a execução na tabela de versões
   ↓
4. Seu banco de dados está atualizado!
```

---

## Por que usar Migrations?

### ❌ Sem Migrations (Problemas)

```bash
# Desenvolvedor A faz mudanças manuais
ALTER TABLE users ADD COLUMN email VARCHAR(255);

# Desenvolvedor B não sabe disso
# Produção fica inconsistente
# Erros em produção! 💥
```

### ✅ Com Migrations (Solução)

```bash
# Migration versionada e rastreada
php bin/console doctrine:migrations:diff

# Todos executam a mesma migration
php bin/console doctrine:migrations:migrate

# Banco consistente em dev, staging e produção! ✅
```

### Vantagens

1. **Versionamento**: Histórico completo de mudanças no schema
2. **Reprodutibilidade**: Mesmo schema em todos os ambientes
3. **Reversibilidade**: Pode reverter mudanças (`down()`)
4. **Segurança**: Testa localmente antes de aplicar em produção
5. **Colaboração**: Equipe sincronizada via Git
6. **Automação**: Integração fácil com CI/CD

---

## Como Funciona?

### Arquitetura Interna

```
┌─────────────────────────────────────────────────────────┐
│  Desenvolvedor                                          │
│  ├─ Cria/Modifica Entity                               │
│  └─ Executa comando de migration                       │
└───────────────┬─────────────────────────────────────────┘
                │
                ▼
┌─────────────────────────────────────────────────────────┐
│  DoctrineMigrationsBundle                              │
│  ├─ Compara Entity com Schema atual                    │
│  ├─ Gera SQL (CREATE, ALTER, DROP, etc.)              │
│  └─ Cria classe Migration                              │
└───────────────┬─────────────────────────────────────────┘
                │
                ▼
┌─────────────────────────────────────────────────────────┐
│  Classe Migration                                       │
│  ├─ up(): SQL para aplicar mudança                     │
│  └─ down(): SQL para reverter mudança                  │
└───────────────┬─────────────────────────────────────────┘
                │
                ▼
┌─────────────────────────────────────────────────────────┐
│  doctrine:migrations:migrate                            │
│  ├─ Verifica doctrine_migration_versions                │
│  ├─ Executa migrations pendentes                       │
│  └─ Registra versão executada                          │
└───────────────┬─────────────────────────────────────────┘
                │
                ▼
┌─────────────────────────────────────────────────────────┐
│  Banco de Dados Atualizado ✅                          │
└─────────────────────────────────────────────────────────┘
```

### Tabela de Controle

O Doctrine cria automaticamente uma tabela `doctrine_migration_versions`:

```sql
CREATE TABLE doctrine_migration_versions (
    version VARCHAR(192) PRIMARY KEY,
    executed_at DATETIME DEFAULT NULL,
    execution_time INT DEFAULT NULL
);
```

**Exemplo de dados:**
```
+---------------------------+---------------------+----------------+
| version                   | executed_at         | execution_time |
+---------------------------+---------------------+----------------+
| DoctrineMigrations\       | 2025-01-10 10:00:00 | 150            |
| Version20250110100000     |                     |                |
+---------------------------+---------------------+----------------+
```

---

## Comandos Principais

### 1. `doctrine:migrations:status`

**Verifica o status das migrations**

```bash
php bin/console doctrine:migrations:status
```

**Saída:**
```
 >> Configuration
    >> Storage: Table Storage
    >> Database: hidro_api
    >> Version Table Name: doctrine_migration_versions

 >> Available Migrations: 5
 >> Executed Migrations: 3
 >> New Migrations: 2
 >> Already Executed Unavailable Migrations: 0
```

**Quando usar**: Antes de criar ou executar migrations

---

### 2. `doctrine:migrations:diff`

**Gera migration automaticamente** comparando Entities com o schema atual

```bash
php bin/console doctrine:migrations:diff
```

**O que faz:**
1. Analisa suas Entities (anotações/atributos)
2. Compara com o schema atual do banco
3. Detecta diferenças (tabelas/colunas faltando, alteradas, etc.)
4. Gera classe Migration com SQL necessário

**Saída:**
```
Generated new migration class to "migrations/Version20250110120000.php"

To run this migration execute:
php bin/console doctrine:migrations:migrate
```

**Quando usar**: Após criar/modificar Entities

---

### 3. `doctrine:migrations:migrate`

**Executa migrations pendentes**

```bash
# Executa todas pendentes
php bin/console doctrine:migrations:migrate

# Executa até uma versão específica
php bin/console doctrine:migrations:migrate 'DoctrineMigrations\Version20250110120000'

# Modo não-interativo (CI/CD)
php bin/console doctrine:migrations:migrate --no-interaction
```

**O que faz:**
1. Consulta `doctrine_migration_versions`
2. Identifica migrations não executadas
3. Executa método `up()` de cada uma
4. Registra execução na tabela

**Quando usar**: Deploy, setup inicial, sincronização de ambiente

---

### 4. `doctrine:migrations:generate`

**Gera migration em branco** para customização manual

```bash
php bin/console doctrine:migrations:generate
```

**Gera:**
```php
final class Version20250110120000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // Adicione seu SQL aqui
    }

    public function down(Schema $schema): void
    {
        // SQL para reverter
    }
}
```

**Quando usar**: Migrations customizadas (dados, procedures, etc.)

---

### 5. `doctrine:migrations:execute`

**Executa UMA migration específica** manualmente

```bash
# Executar (up)
php bin/console doctrine:migrations:execute 'DoctrineMigrations\Version20250110120000' --up

# Reverter (down)
php bin/console doctrine:migrations:execute 'DoctrineMigrations\Version20250110120000' --down
```

**Quando usar**: Testes, debugging, reversão específica

---

### 6. `doctrine:migrations:version`

**Adiciona/remove versão manualmente** da tabela de controle

```bash
# Marcar como executada (sem rodar SQL)
php bin/console doctrine:migrations:version 'DoctrineMigrations\Version20250110120000' --add

# Marcar como não executada
php bin/console doctrine:migrations:version 'DoctrineMigrations\Version20250110120000' --delete

# Adicionar TODAS as migrations (útil após schema:create)
php bin/console doctrine:migrations:version --add --all
```

**Quando usar**: Pular migrations, sincronizar após `schema:create`

---

### 7. `doctrine:migrations:list`

**Lista todas as migrations disponíveis**

```bash
php bin/console doctrine:migrations:list
```

**Saída:**
```
 >> DoctrineMigrations\Version20250108000001 ✓ (2025-01-08)
 >> DoctrineMigrations\Version20250109000001 ✓ (2025-01-09)
 >> DoctrineMigrations\Version20250110120000   (2025-01-10) [pending]
```

---

### 8. `doctrine:migrations:sync-metadata-storage`

**Sincroniza estrutura da tabela** de controle

```bash
php bin/console doctrine:migrations:sync-metadata-storage
```

**Quando usar**: Após upgrade do bundle, erro de "metadata storage out of sync"

---

## Fluxo de Trabalho

### Cenário 1: Nova Entity

```bash
# 1. Criar Entity
# src/Domain/Students/Student.php

# 2. Verificar status
php bin/console doctrine:migrations:status

# 3. Gerar migration automaticamente
php bin/console doctrine:migrations:diff

# 4. Revisar migration gerada
# migrations/Version20250110120000.php

# 5. Executar migration
php bin/console doctrine:migrations:migrate

# 6. Verificar sucesso
php bin/console doctrine:migrations:status
```

---

### Cenário 2: Modificar Entity Existente

```bash
# 1. Modificar Entity (adicionar campo, alterar tipo, etc.)
# src/Domain/Users/User.php

# 2. Gerar migration da diferença
php bin/console doctrine:migrations:diff

# 3. Revisar SQL gerado
cat migrations/Version20250110130000.php

# 4. Testar localmente
php bin/console doctrine:migrations:migrate

# 5. Commit no Git
git add migrations/Version20250110130000.php
git commit -m "Add email field to User entity"

# 6. Deploy em produção
php bin/console doctrine:migrations:migrate --no-interaction
```

---

### Cenário 3: Migration Manual (Dados/Seeds)

```bash
# 1. Gerar migration em branco
php bin/console doctrine:migrations:generate

# 2. Editar manualmente
# migrations/Version20250110140000.php
```

```php
public function up(Schema $schema): void
{
    // Inserir dados padrão
    $this->addSql("
        INSERT INTO schools (name, code, created_at) 
        VALUES ('Escola Padrão', 'EP001', NOW())
    ");
}

public function down(Schema $schema): void
{
    $this->addSql("DELETE FROM schools WHERE code = 'EP001'");
}
```

```bash
# 3. Executar
php bin/console doctrine:migrations:migrate
```

---

### Cenário 4: Deploy em Produção

```bash
# No servidor de produção

# 1. Fazer pull do código
git pull origin main

# 2. Verificar migrations pendentes
php bin/console doctrine:migrations:status

# 3. Fazer backup do banco (IMPORTANTE!)
mysqldump -u root -p hidro_api > backup_antes_migration.sql

# 4. Executar migrations (modo não-interativo)
php bin/console doctrine:migrations:migrate --no-interaction

# 5. Verificar sucesso
php bin/console doctrine:migrations:status

# 6. Testar aplicação
curl http://api.example.com/health
```

---

## Exemplos Práticos

### Exemplo 1: Criar Tabela

**Entity:**
```php
namespace App\Domain\Schools;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'schools')]
class School
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private string $code;
}
```

**Gerar migration:**
```bash
php bin/console doctrine:migrations:diff
```

**Migration gerada:**
```php
public function up(Schema $schema): void
{
    $this->addSql('
        CREATE TABLE schools (
            id INT AUTO_INCREMENT NOT NULL, 
            name VARCHAR(255) NOT NULL, 
            code VARCHAR(50) NOT NULL, 
            UNIQUE INDEX UNIQ_schools_code (code), 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
    ');
}

public function down(Schema $schema): void
{
    $this->addSql('DROP TABLE schools');
}
```

---

### Exemplo 2: Adicionar Campo

**Modificar Entity:**
```php
#[ORM\Entity]
class School
{
    // ... campos existentes

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;
}
```

**Migration gerada:**
```php
public function up(Schema $schema): void
{
    $this->addSql('ALTER TABLE schools ADD address VARCHAR(255) DEFAULT NULL');
    $this->addSql('ALTER TABLE schools ADD created_at DATETIME NOT NULL');
}

public function down(Schema $schema): void
{
    $this->addSql('ALTER TABLE schools DROP address');
    $this->addSql('ALTER TABLE schools DROP created_at');
}
```

---

### Exemplo 3: Criar Relacionamento

**Entities com relacionamento:**
```php
#[ORM\Entity]
class Student
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: School::class, inversedBy: 'students')]
    #[ORM\JoinColumn(nullable: false)]
    private School $school;
}
```

**Migration gerada:**
```php
public function up(Schema $schema): void
{
    $this->addSql('
        ALTER TABLE students 
        ADD school_id INT NOT NULL
    ');
    
    $this->addSql('
        ALTER TABLE students 
        ADD CONSTRAINT FK_students_school_id 
        FOREIGN KEY (school_id) REFERENCES schools (id)
    ');
    
    $this->addSql('
        CREATE INDEX IDX_students_school_id 
        ON students (school_id)
    ');
}

public function down(Schema $schema): void
{
    $this->addSql('ALTER TABLE students DROP FOREIGN KEY FK_students_school_id');
    $this->addSql('DROP INDEX IDX_students_school_id ON students');
    $this->addSql('ALTER TABLE students DROP school_id');
}
```

---

### Exemplo 4: Migration de Dados

**Cenário**: Migrar dados de uma estrutura antiga para nova

```php
public function up(Schema $schema): void
{
    // 1. Criar nova coluna
    $this->addSql('ALTER TABLE users ADD full_name VARCHAR(255) DEFAULT NULL');
    
    // 2. Migrar dados
    $this->addSql("
        UPDATE users 
        SET full_name = CONCAT(first_name, ' ', last_name)
    ");
    
    // 3. Tornar obrigatório
    $this->addSql('ALTER TABLE users MODIFY full_name VARCHAR(255) NOT NULL');
    
    // 4. Remover colunas antigas
    $this->addSql('ALTER TABLE users DROP first_name');
    $this->addSql('ALTER TABLE users DROP last_name');
}

public function down(Schema $schema): void
{
    // Reverter mudanças
    $this->addSql('ALTER TABLE users ADD first_name VARCHAR(255) DEFAULT NULL');
    $this->addSql('ALTER TABLE users ADD last_name VARCHAR(255) DEFAULT NULL');
    
    // Não é possível reverter dados perfeitamente!
    $this->addSql("
        UPDATE users 
        SET 
            first_name = SUBSTRING_INDEX(full_name, ' ', 1),
            last_name = SUBSTRING_INDEX(full_name, ' ', -1)
    ");
    
    $this->addSql('ALTER TABLE users DROP full_name');
}
```

---

## Boas Práticas

### ✅ DO (Faça)

#### 1. **Sempre revisar migrations geradas**

```bash
# Após gerar
php bin/console doctrine:migrations:diff

# SEMPRE revisar
cat migrations/Version20250110120000.php

# Verificar SQL faz sentido
```

#### 2. **Testar localmente antes de produção**

```bash
# Ambiente local
php bin/console doctrine:migrations:migrate

# Testar aplicação
php bin/phpunit

# Se OK, commit
git add migrations/
git commit -m "Add migration for new feature"
```

#### 3. **Fazer backup antes de migrar em produção**

```bash
# Backup completo
mysqldump -u root -p hidro_api > backup_$(date +%Y%m%d_%H%M%S).sql

# Ou backup apenas schema
mysqldump -u root -p --no-data hidro_api > schema_backup.sql
```

#### 4. **Usar transações quando possível**

A configuração padrão já usa transações:

```yaml
# config/packages/doctrine_migrations.yaml
doctrine_migrations:
    transactional: true  # ✅ Rollback automático em erro
    all_or_nothing: true # ✅ Tudo ou nada
```

#### 5. **Versionar migrations no Git**

```bash
git add migrations/
git commit -m "Add User email field migration"
git push
```

#### 6. **Adicionar descrições claras**

```php
public function getDescription(): string
{
    return 'Add email field to User entity and migrate existing data';
}
```

#### 7. **Migrations atômicas**

```php
// ✅ BOM: Uma migration para uma mudança
class Version20250110120000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD email VARCHAR(255)');
    }
}

// ❌ EVITAR: Múltiplas mudanças não relacionadas
class Version20250110120000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD email VARCHAR(255)');
        $this->addSql('CREATE TABLE products (...)'); // Não relacionado!
    }
}
```

---

### ❌ DON'T (Não Faça)

#### 1. **Não editar migrations já executadas em produção**

```php
// ❌ NUNCA faça isso se já foi executada em produção
class Version20250108000001 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // Editando migration antiga...
    }
}

// ✅ Crie uma NOVA migration
php bin/console doctrine:migrations:diff
```

#### 2. **Não usar `doctrine:schema:update` em produção**

```bash
# ❌ PERIGOSO em produção
php bin/console doctrine:schema:update --force

# ✅ Use migrations
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

#### 3. **Não ignorar migrations falhadas**

```bash
# ❌ Não faça
php bin/console doctrine:migrations:migrate
# Erro! Migration falhou
# Ignora e continua... 💥

# ✅ Investigue e corrija
php bin/console doctrine:migrations:status
# Analise o erro
# Corrija a migration
# Execute novamente
```

#### 4. **Não executar migrations sem backup**

```bash
# ❌ Direto em produção sem backup
php bin/console doctrine:migrations:migrate

# ✅ Sempre com backup
mysqldump ... > backup.sql
php bin/console doctrine:migrations:migrate
```

#### 5. **Não commitar migrations não testadas**

```bash
# ❌ Gerar e commitar sem testar
php bin/console doctrine:migrations:diff
git add migrations/
git commit
git push

# ✅ Testar primeiro
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
# Testar aplicação
php bin/phpunit
# Se OK, então commit
```

---

## Troubleshooting

### Problema 1: "The metadata storage is not up to date"

**Erro:**
```
[ERROR] The metadata storage is not up to date, 
please run the sync-metadata-storage command to fix this issue.
```

**Causa**: Versão do servidor MySQL/MariaDB incorreta no `DATABASE_URL`

**Solução:**

```env
# ❌ Errado
DATABASE_URL="mysql://user:pass@database:3306/hidro_api?serverVersion=8.0"

# ✅ Correto para MariaDB
DATABASE_URL="mysql://user:pass@database:3306/hidro_api?serverVersion=mariadb-10.4.11"

# ✅ Correto para MySQL
DATABASE_URL="mysql://user:pass@database:3306/hidro_api?serverVersion=8.0.32"
```

Depois sincronize:
```bash
php bin/console doctrine:migrations:sync-metadata-storage
```

---

### Problema 2: Migration já executada sendo detectada como pendente

**Causa**: Banco de dados criado manualmente sem migrations

**Solução:**

```bash
# Marcar TODAS como executadas
php bin/console doctrine:migrations:version --add --all

# Ou marcar uma específica
php bin/console doctrine:migrations:version 'DoctrineMigrations\Version20250110120000' --add
```

---

### Problema 3: Migration falhou no meio

**Erro:**
```
[ERROR] Migration DoctrineMigrations\Version20250110120000 failed during Execution.
```

**Solução:**

```bash
# 1. Verificar status
php bin/console doctrine:migrations:status

# 2. Se transacional, foi feito rollback automático
# Corrigir a migration e executar novamente

# 3. Se não transacional, pode precisar limpar manualmente
# Verificar o que foi executado
mysql -u root -p hidro_api

# 4. Remover versão da tabela de controle
php bin/console doctrine:migrations:version 'DoctrineMigrations\Version20250110120000' --delete

# 5. Corrigir migration e executar novamente
php bin/console doctrine:migrations:migrate
```

---

### Problema 4: Conflito de migrations (múltiplos desenvolvedores)

**Cenário:**
- Dev A cria `Version20250110120000`
- Dev B cria `Version20250110120000` (mesmo timestamp!)

**Solução:**

```bash
# 1. Renomear uma das migrations
mv migrations/Version20250110120000.php migrations/Version20250110130000.php

# 2. Atualizar o nome da classe
# Version20250110130000 extends AbstractMigration

# 3. Executar em ordem
php bin/console doctrine:migrations:migrate
```

**Prevenção**: Comunicação da equipe, rebases frequentes

---

### Problema 5: Doctrine ignora tabelas manuais

**Cenário**: Você tem tabelas manuais que não são Entities

**Solução**: Configurar filtro de schema

```yaml
# config/packages/doctrine.yaml
doctrine:
    dbal:
        schema_filter: ~^(?!legacy_|temp_)~ # Ignora tabelas com prefixo legacy_ e temp_
```

---

## Integração com Docker e Helper Scripts

### Usando com dev.ps1

```powershell
# Status das migrations
.\dev.ps1 shell
php bin/console doctrine:migrations:status
exit

# Criar e executar migration
.\dev.ps1 shell
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
exit

# Ou usar o comando migrate direto
.\dev.ps1 migrate
```

### Adicionar comando ao dev.ps1

Você pode adicionar atalhos personalizados:

```powershell
# No dev.ps1
function Migration-Diff {
    Write-Host "📝 Generating migration..." -ForegroundColor Cyan
    docker-compose exec app php bin/console doctrine:migrations:diff
}

function Migration-Status {
    Write-Host "📊 Migration status:" -ForegroundColor Cyan
    docker-compose exec app php bin/console doctrine:migrations:status
}
```

Uso:
```powershell
.\dev.ps1 migration-diff
.\dev.ps1 migration-status
```

---

## Checklist de Migration

### Antes de Criar

- [ ] Entity criada/modificada
- [ ] Mapeamento Doctrine correto (anotações/atributos)
- [ ] Verificar status atual: `doctrine:migrations:status`

### Ao Criar

- [ ] Executar `doctrine:migrations:diff`
- [ ] Revisar SQL gerado
- [ ] Adicionar descrição clara em `getDescription()`
- [ ] Verificar método `down()` está correto

### Antes de Executar

- [ ] Fazer backup do banco (produção)
- [ ] Testar localmente primeiro
- [ ] Verificar ambiente correto

### Ao Executar

- [ ] Executar `doctrine:migrations:migrate`
- [ ] Verificar sucesso: `doctrine:migrations:status`
- [ ] Testar aplicação

### Após Executar

- [ ] Commit migration no Git
- [ ] Atualizar documentação se necessário
- [ ] Deploy em outros ambientes

---

## Resumo Rápido

### Comandos Essenciais

```bash
# Ver status
php bin/console doctrine:migrations:status

# Gerar automaticamente
php bin/console doctrine:migrations:diff

# Executar
php bin/console doctrine:migrations:migrate

# Listar
php bin/console doctrine:migrations:list
```

### Workflow Diário

```
1. Modificar Entity
2. doctrine:migrations:diff
3. Revisar migration
4. doctrine:migrations:migrate
5. Testar
6. Commit
```

### Regras de Ouro

1. ✅ **Sempre** revisar migrations geradas
2. ✅ **Sempre** testar localmente primeiro
3. ✅ **Sempre** fazer backup antes de produção
4. ❌ **Nunca** editar migrations já executadas em produção
5. ❌ **Nunca** usar `schema:update` em produção

---

## Recursos Adicionais

- [Documentação Oficial Doctrine Migrations](https://www.doctrine-project.org/projects/doctrine-migrations/en/current/index.html)
- [Symfony Doctrine Documentation](https://symfony.com/doc/current/doctrine.html)
- [DoctrineMigrationsBundle no GitHub](https://github.com/doctrine/DoctrineMigrationsBundle)

---

**Criado em**: 2025-01-10  
**Versão**: 1.0  
**Projeto**: Hidro API
