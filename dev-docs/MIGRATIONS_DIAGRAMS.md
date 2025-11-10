# Diagramas de Migrations - DoctrineMigrationsBundle

## 📊 Diagramas Visuais para Entender Migrations

---

## 1. Fluxo Completo de Migration

```mermaid
graph TB
    A[Desenvolvedor cria/modifica Entity] --> B{Tipo de Migration?}
    B -->|Automática| C[doctrine:migrations:diff]
    B -->|Manual| D[doctrine:migrations:generate]
    
    C --> E[Doctrine compara Entity vs Schema]
    E --> F[Gera SQL automaticamente]
    F --> G[Cria arquivo Migration]
    
    D --> H[Cria arquivo vazio]
    H --> I[Desenvolvedor escreve SQL manualmente]
    I --> G
    
    G --> J[Revisar Migration]
    J --> K{SQL está correto?}
    K -->|Não| L[Ajustar manualmente]
    L --> J
    K -->|Sim| M[doctrine:migrations:migrate]
    
    M --> N[Doctrine verifica doctrine_migration_versions]
    N --> O{Migration já executada?}
    O -->|Sim| P[Pula migration]
    O -->|Não| Q[Executa up método]
    
    Q --> R[Atualiza Schema]
    R --> S[Registra em doctrine_migration_versions]
    S --> T[Migration Completa!]
    
    P --> T
```

---

## 2. Como Doctrine Rastreia Migrations

```mermaid
graph LR
    A[Aplicação] --> B[DoctrineMigrations]
    B --> C[(doctrine_migration_versions)]
    
    C --> D[version: Version20250110120000]
    C --> E[executed_at: 2025-01-10 12:00:00]
    C --> F[execution_time: 150ms]
    
    B --> G{Migration executada?}
    G -->|Sim| H[Pula]
    G -->|Não| I[Executa]
```

---

## 3. Ciclo de Vida de uma Migration

```mermaid
stateDiagram-v2
    [*] --> Criada: doctrine:migrations:diff
    Criada --> Revisada: Desenvolvedor revisa SQL
    Revisada --> Testada: doctrine:migrations:migrate (local)
    Testada --> Commitada: git commit
    Commitada --> Deploy: git push
    Deploy --> Executada: migrate --no-interaction
    Executada --> [*]: Migration completa
    
    Testada --> Ajustada: Erro encontrado
    Ajustada --> Revisada
```

---

## 4. Comparação: Com vs Sem Migrations

### Sem Migrations (Problemático)
```mermaid
graph TB
    A[Dev A: ALTER TABLE users...] --> B[(DB Dev A)]
    C[Dev B: Não sabe da mudança] --> D[(DB Dev B)]
    E[Produção: Schema desatualizado] --> F[(DB Produção)]
    
    G[Deploy] --> H{Funciona?}
    H -->|Não| I[ERRO! Schema inconsistente 💥]
    
    style I fill:#ff6b6b
```

### Com Migrations (Solução)
```mermaid
graph TB
    A[Dev cria Migration] --> B[Git commit]
    B --> C[Dev A puxa migration]
    B --> D[Dev B puxa migration]
    B --> E[CI/CD puxa migration]
    
    C --> F[migrate local]
    D --> G[migrate local]
    E --> H[migrate produção]
    
    F --> I[(DB Dev A)]
    G --> J[(DB Dev B)]
    H --> K[(DB Produção)]
    
    I --> L[Schemas Idênticos! ✅]
    J --> L
    K --> L
    
    style L fill:#51cf66
```

---

## 5. Estrutura de Arquivos

```
hidro-api/
├── migrations/                          ← Pasta de migrations
│   ├── Version20250108000001.php       ← Migration 1 (executada)
│   ├── Version20250109000001.php       ← Migration 2 (executada)
│   └── Version20250110120000.php       ← Migration 3 (pendente)
│
├── src/
│   └── Domain/
│       ├── Users/
│       │   └── User.php                ← Entity modificada
│       └── Schools/
│           └── School.php              ← Entity nova
│
├── config/
│   └── packages/
│       └── doctrine_migrations.yaml    ← Configuração
│
└── .env                                 ← DATABASE_URL
```

---

## 6. Fluxo de Deploy em Produção

```mermaid
sequenceDiagram
    participant Dev as Desenvolvedor
    participant Git as Git Repository
    participant CI as CI/CD Pipeline
    participant App as Aplicação
    participant DB as Banco de Dados
    
    Dev->>Dev: Cria Migration
    Dev->>Dev: Testa localmente
    Dev->>Git: git push
    
    CI->>Git: git pull
    CI->>CI: Testes automatizados
    CI->>App: Deploy código
    
    App->>DB: Backup do banco
    App->>DB: doctrine:migrations:migrate
    
    DB-->>App: Migrations executadas ✅
    App->>App: Aplicação atualizada
    
    Note over App,DB: Tabela doctrine_migration_versions<br/>registra execução
```

---

## 7. Anatomia de uma Migration

```php
// migrations/Version20250110120000.php

┌─────────────────────────────────────────────────────┐
│ Namespace: DoctrineMigrations                       │
├─────────────────────────────────────────────────────┤
│ Classe: Version20250110120000                       │
│         └─ Timestamp único evita conflitos          │
├─────────────────────────────────────────────────────┤
│ getDescription(): string                            │
│ └─ "Add email field to User entity"                │
├─────────────────────────────────────────────────────┤
│ up(Schema $schema): void                            │
│ ├─ SQL para APLICAR mudança                        │
│ └─ addSql('ALTER TABLE users ADD email...')        │
├─────────────────────────────────────────────────────┤
│ down(Schema $schema): void                          │
│ ├─ SQL para REVERTER mudança                       │
│ └─ addSql('ALTER TABLE users DROP email')          │
└─────────────────────────────────────────────────────┘
```

---

## 8. Tabela doctrine_migration_versions

```
┌──────────────────────────────────────────────────────────┐
│              doctrine_migration_versions                  │
├───────────────────────────┬──────────────┬───────────────┤
│ version (VARCHAR 192)     │ executed_at  │ execution_time│
├───────────────────────────┼──────────────┼───────────────┤
│ DoctrineMigrations\       │ 2025-01-08   │ 120 ms        │
│ Version20250108000001     │ 10:00:00     │               │
├───────────────────────────┼──────────────┼───────────────┤
│ DoctrineMigrations\       │ 2025-01-09   │ 85 ms         │
│ Version20250109000001     │ 11:30:00     │               │
├───────────────────────────┼──────────────┼───────────────┤
│ DoctrineMigrations\       │ 2025-01-10   │ 150 ms        │
│ Version20250110120000     │ 12:00:00     │               │
└───────────────────────────┴──────────────┴───────────────┘

→ Doctrine consulta esta tabela para saber quais migrations já foram executadas
→ Apenas migrations NÃO presentes aqui serão executadas
```

---

## 9. Workflow Automático vs Manual

```mermaid
graph TB
    subgraph "Automático (Recomendado)"
        A1[Entity criada/modificada] --> A2[doctrine:migrations:diff]
        A2 --> A3[Doctrine analisa diferenças]
        A3 --> A4[Gera SQL automaticamente]
        A4 --> A5[Migration pronta]
    end
    
    subgraph "Manual (Casos Especiais)"
        M1[Migration de dados] --> M2[doctrine:migrations:generate]
        M2 --> M3[Arquivo vazio criado]
        M3 --> M4[Desenvolvedor escreve SQL]
        M4 --> M5[Migration customizada]
    end
    
    A5 --> Final[doctrine:migrations:migrate]
    M5 --> Final
```

---

## 10. Processo de Comparação (diff)

```mermaid
graph LR
    A[Entities no código] --> C[Doctrine Comparador]
    B[(Schema no banco)] --> C
    
    C --> D{Diferenças?}
    D -->|Tabelas novas| E[CREATE TABLE]
    D -->|Campos novos| F[ALTER TABLE ADD]
    D -->|Campos removidos| G[ALTER TABLE DROP]
    D -->|Tipos alterados| H[ALTER TABLE MODIFY]
    D -->|Índices novos| I[CREATE INDEX]
    D -->|Constraints| J[ADD CONSTRAINT]
    
    E --> K[Gera Migration]
    F --> K
    G --> K
    H --> K
    I --> K
    J --> K
```

---

## 11. Reversibilidade (up vs down)

```mermaid
graph TB
    A[Estado Inicial do Banco] --> B[up método]
    B --> C[Estado Modificado]
    C --> D[down método]
    D --> A
    
    B -.->|Exemplo| E[ALTER TABLE users<br/>ADD email VARCHAR]
    D -.->|Reverso| F[ALTER TABLE users<br/>DROP email]
    
    style A fill:#e3f2fd
    style C fill:#c8e6c9
```

---

## 12. Estratégia de Versionamento

```
Timeline de Migrations:

2025-01-08 10:00:00 → Version20250108100000
                      └─ Create users table
                      
2025-01-09 11:30:00 → Version20250109113000
                      └─ Add email to users
                      
2025-01-10 12:00:00 → Version20250110120000
                      └─ Create products table
                      
2025-01-10 15:45:00 → Version20250110154500
                      └─ Add foreign key users_products

Ordem de execução = Ordem cronológica (timestamp)
```

---

## 13. Cenário de Erro e Recuperação

```mermaid
stateDiagram-v2
    [*] --> Executando: doctrine:migrations:migrate
    Executando --> Sucesso: SQL executou OK
    Executando --> Erro: SQL falhou
    
    Sucesso --> Registrada: Grava em doctrine_migration_versions
    Registrada --> [*]: Migration completa
    
    Erro --> Rollback: Transação revertida
    Rollback --> Corrigir: Editar migration
    Corrigir --> Remover: --delete da tabela
    Remover --> Executando: Tentar novamente
```

---

## 14. Múltiplos Ambientes

```mermaid
graph TB
    subgraph "Repositório Git"
        M[migrations/Version20250110120000.php]
    end
    
    M --> D1[Dev Local]
    M --> D2[Dev Staging]
    M --> D3[Dev Produção]
    
    D1 --> DB1[(DB Local)]
    D2 --> DB2[(DB Staging)]
    D3 --> DB3[(DB Produção)]
    
    DB1 --> S1[doctrine_migration_versions]
    DB2 --> S2[doctrine_migration_versions]
    DB3 --> S3[doctrine_migration_versions]
    
    S1 -.-> R[Mesmo schema em todos!]
    S2 -.-> R
    S3 -.-> R
```

---

## 15. Decisão: Quando Usar Cada Comando

```mermaid
graph TB
    Start{Qual sua necessidade?}
    
    Start -->|Criar tabela/campo| A[Modificou Entity?]
    A -->|Sim| B[doctrine:migrations:diff]
    A -->|Não, é manual| C[doctrine:migrations:generate]
    
    Start -->|Executar migrations| D[doctrine:migrations:migrate]
    
    Start -->|Ver status| E[doctrine:migrations:status]
    
    Start -->|Listar todas| F[doctrine:migrations:list]
    
    Start -->|Pular migration| G[doctrine:migrations:version --add]
    
    Start -->|Reverter específica| H[doctrine:migrations:execute --down]
    
    Start -->|Problema sync| I[doctrine:migrations:sync-metadata-storage]
```

---

## 16. Dependências e Ordem de Execução

```mermaid
graph TB
    M1[Version20250108100000<br/>Create users table] --> M2[Version20250109113000<br/>Add email to users]
    
    M2 --> M3[Version20250110120000<br/>Create products table]
    
    M1 --> M4[Version20250110121000<br/>Create orders table]
    
    M3 --> M5[Version20250110130000<br/>Add user_id FK to orders]
    M4 --> M5
    
    Note1[Migrations são executadas<br/>em ordem cronológica<br/>baseada no timestamp]
    
    style Note1 fill:#fff3cd
```

---

## Resumo Visual

### Regra de Ouro
```
┌───────────────────────────────────────────────────┐
│  Entity mudou → diff → migrate → commit → deploy  │
└───────────────────────────────────────────────────┘
```

### Comandos Mais Usados
```
1. doctrine:migrations:status  ← Ver situação atual
2. doctrine:migrations:diff    ← Criar automaticamente
3. doctrine:migrations:migrate ← Executar
```

### Arquivos Importantes
```
migrations/Version*.php        ← Código SQL versionado
doctrine_migration_versions    ← Rastreamento no banco
config/packages/doctrine_migrations.yaml ← Configuração
```

---

**Para mais detalhes**: Veja [`GUIA_MIGRATIONS.md`](GUIA_MIGRATIONS.md)
