# Migrations - Referência Rápida

## 🚀 Comandos Rápidos

### Verificar Status
```bash
php bin/console doctrine:migrations:status
```

### Criar Migration Automaticamente
```bash
# Compara Entities com banco e gera migration
php bin/console doctrine:migrations:diff
```

### Executar Migrations
```bash
# Todas pendentes
php bin/console doctrine:migrations:migrate

# Modo não-interativo (CI/CD)
php bin/console doctrine:migrations:migrate --no-interaction

# Até versão específica
php bin/console doctrine:migrations:migrate 'DoctrineMigrations\Version20250110120000'
```

### Criar Migration Manual
```bash
# Gera arquivo em branco
php bin/console doctrine:migrations:generate
```

### Listar Migrations
```bash
php bin/console doctrine:migrations:list
```

### Executar/Reverter Migration Específica
```bash
# Executar (up)
php bin/console doctrine:migrations:execute 'DoctrineMigrations\Version20250110120000' --up

# Reverter (down)
php bin/console doctrine:migrations:execute 'DoctrineMigrations\Version20250110120000' --down
```

### Marcar Migration Manualmente
```bash
# Marcar como executada (sem rodar)
php bin/console doctrine:migrations:version 'DoctrineMigrations\Version20250110120000' --add

# Marcar todas
php bin/console doctrine:migrations:version --add --all

# Desmarcar
php bin/console doctrine:migrations:version 'DoctrineMigrations\Version20250110120000' --delete
```

---

## 📝 Workflows Comuns

### Workflow 1: Nova Entity

```bash
# 1. Criar Entity em src/Domain/
# 2. Gerar migration
php bin/console doctrine:migrations:diff

# 3. Revisar arquivo gerado em migrations/
# 4. Executar
php bin/console doctrine:migrations:migrate

# 5. Commit
git add migrations/ src/
git commit -m "Add new entity with migration"
```

### Workflow 2: Modificar Entity

```bash
# 1. Modificar Entity existente
# 2. Gerar migration da diferença
php bin/console doctrine:migrations:diff

# 3. Executar
php bin/console doctrine:migrations:migrate

# 4. Testar
php bin/phpunit

# 5. Commit
git add migrations/ src/
git commit -m "Update entity schema"
```

### Workflow 3: Deploy Produção

```bash
# 1. Pull código
git pull origin main

# 2. Verificar migrations pendentes
php bin/console doctrine:migrations:status

# 3. BACKUP!
mysqldump -u root -p hidro_api > backup_$(date +%Y%m%d_%H%M%S).sql

# 4. Executar migrations
php bin/console doctrine:migrations:migrate --no-interaction

# 5. Verificar sucesso
php bin/console doctrine:migrations:status
```

---

## 🐳 Com Docker

### Usando dev.ps1
```powershell
# Status
.\dev.ps1 shell
php bin/console doctrine:migrations:status
exit

# Criar e executar
.\dev.ps1 shell
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
exit

# Ou comando direto
.\dev.ps1 migrate
```

### Comandos Docker Diretos
```bash
# Status
docker-compose exec app php bin/console doctrine:migrations:status

# Gerar
docker-compose exec app php bin/console doctrine:migrations:diff

# Executar
docker-compose exec app php bin/console doctrine:migrations:migrate
```

---

## 📋 Estrutura de Migration

```php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250110120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add email field to User entity';
    }

    public function up(Schema $schema): void
    {
        // SQL para APLICAR mudança
        $this->addSql('ALTER TABLE users ADD email VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // SQL para REVERTER mudança
        $this->addSql('ALTER TABLE users DROP email');
    }
}
```

---

## ✅ Boas Práticas

### DO (Faça)
- ✅ Revisar migrations geradas antes de executar
- ✅ Testar localmente antes de produção
- ✅ Fazer backup antes de migrar em produção
- ✅ Versionar migrations no Git
- ✅ Adicionar descrições claras
- ✅ Uma migration por mudança lógica

### DON'T (Não Faça)
- ❌ Editar migrations já executadas em produção
- ❌ Usar `doctrine:schema:update` em produção
- ❌ Executar migrations sem backup
- ❌ Commitar migrations não testadas
- ❌ Ignorar erros de migration

---

## 🔧 Troubleshooting Rápido

### Erro: "metadata storage is not up to date"
```bash
# Verificar DATABASE_URL no .env
# Para MariaDB:
DATABASE_URL="mysql://user:pass@host:3306/db?serverVersion=mariadb-10.4.11"

# Sincronizar
php bin/console doctrine:migrations:sync-metadata-storage
```

### Migration Falhou
```bash
# 1. Verificar status
php bin/console doctrine:migrations:status

# 2. Remover da tabela de controle
php bin/console doctrine:migrations:version 'Version...' --delete

# 3. Corrigir migration

# 4. Executar novamente
php bin/console doctrine:migrations:migrate
```

### Banco Criado Sem Migrations
```bash
# Marcar todas como executadas
php bin/console doctrine:migrations:version --add --all
```

---

## 📊 Exemplo Completo

### 1. Criar Entity
```php
// src/Domain/Products/Product.php
namespace App\Domain\Products;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'products')]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private float $price;
}
```

### 2. Gerar Migration
```bash
php bin/console doctrine:migrations:diff
```

### 3. Migration Gerada
```php
// migrations/Version20250110120000.php
public function up(Schema $schema): void
{
    $this->addSql('CREATE TABLE products (
        id INT AUTO_INCREMENT NOT NULL, 
        name VARCHAR(255) NOT NULL, 
        price NUMERIC(10, 2) NOT NULL, 
        PRIMARY KEY(id)
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
}

public function down(Schema $schema): void
{
    $this->addSql('DROP TABLE products');
}
```

### 4. Executar
```bash
php bin/console doctrine:migrations:migrate
```

### 5. Verificar
```bash
php bin/console doctrine:migrations:status

# Saída:
# >> Executed Migrations: 1
# >> New Migrations: 0
```

---

## 🎯 Checklist Rápido

### Criar Migration
- [ ] Entity criada/modificada
- [ ] `doctrine:migrations:diff`
- [ ] Revisar SQL gerado
- [ ] Testar localmente
- [ ] Commit no Git

### Deploy Produção
- [ ] Pull código
- [ ] Verificar status
- [ ] Fazer backup
- [ ] Executar migrations
- [ ] Verificar sucesso
- [ ] Testar aplicação

---

## 📚 Links Úteis

- [Guia Completo](GUIA_MIGRATIONS.md)
- [Documentação Oficial](https://www.doctrine-project.org/projects/doctrine-migrations)
- [Environment Variables](ENVIRONMENT_VARIABLES.md)
- [Quick Start](../QUICK_START.md)

---

**Dica**: Para consultar detalhes, exemplos avançados e troubleshooting completo, veja [`GUIA_MIGRATIONS.md`](GUIA_MIGRATIONS.md)
