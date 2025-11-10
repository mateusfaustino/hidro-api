# 📚 Documentação do Projeto Hidro API

Bem-vindo à documentação técnica do Hidro API! Este índice organiza toda a documentação disponível para facilitar seu desenvolvimento.

---

## 🚀 Início Rápido

### Para Novos Desenvolvedores

1. **[QUICK_START.md](../QUICK_START.md)** - Guia de início rápido
   - Setup inicial em 5 minutos
   - Primeiros passos com Docker
   - Comandos essenciais

2. **[ENVIRONMENT_VARIABLES.md](ENVIRONMENT_VARIABLES.md)** - Variáveis de ambiente
   - Configuração do `.env`
   - Conexão com banco de dados
   - JWT e autenticação

3. **[DOCKER_HOT_RELOAD.md](DOCKER_HOT_RELOAD.md)** - Hot Reload com Docker
   - Desenvolvimento sem rebuilds
   - 36-60x mais rápido
   - Configuração de volumes

---

## 🗄️ Database & Migrations

### Guias de Migrations

1. **[GUIA_MIGRATIONS.md](GUIA_MIGRATIONS.md)** ⭐ **NOVO!**
   - Tutorial completo em português
   - Como o DoctrineMigrationsBundle funciona (chain-of-thought)
   - Exemplos práticos passo a passo
   - Boas práticas e troubleshooting
   - **Recomendado para todos os desenvolvedores**

2. **[MIGRATIONS_QUICK_REFERENCE.md](MIGRATIONS_QUICK_REFERENCE.md)** ⭐ **NOVO!**
   - Referência rápida de comandos
   - Workflows comuns
   - Troubleshooting rápido
   - **Ideal para consultas diárias**

3. **[MIGRATIONS_DIAGRAMS.md](MIGRATIONS_DIAGRAMS.md)** ⭐ **NOVO!**
   - Diagramas visuais Mermaid
   - Fluxos de trabalho ilustrados
   - Arquitetura interna
   - **Ótimo para aprendizado visual**

### Configuração de Banco

- **[configuracao-banco-dbeaver.md](configuracao-banco-dbeaver.md)** - Configurar DBeaver
- **[create_test_user.php](create_test_user.php)** - Script para criar usuários de teste

---

## 🔐 Autenticação & Segurança

### JWT Authentication

1. **[JWT_AUTH_IMPLEMENTATION_COMPLETE.md](JWT_AUTH_IMPLEMENTATION_COMPLETE.md)**
   - Implementação completa de JWT
   - LexikJWTAuthenticationBundle
   - Access e Refresh tokens

2. **[JWT_AUTH_IMPLEMENTATION_PLAN.md](JWT_AUTH_IMPLEMENTATION_PLAN.md)**
   - Planejamento da implementação
   - Arquitetura de autenticação

3. **[JWT_CONFIGURATION_SUMMARY.md](JWT_CONFIGURATION_SUMMARY.md)**
   - Resumo das configurações
   - Variáveis de ambiente JWT

4. **[JWT_INSTALLATION_SUMMARY.md](JWT_INSTALLATION_SUMMARY.md)**
   - Instalação passo a passo
   - Dependências necessárias

5. **[AUTH_API_CONTRACT.md](AUTH_API_CONTRACT.md)**
   - Contratos da API de autenticação
   - Endpoints e payloads
   - Códigos de status

---

## 🐳 Docker & DevOps

### Configuração Docker

1. **[DOCKER_HOT_RELOAD.md](DOCKER_HOT_RELOAD.md)**
   - Hot reload completo
   - Desenvolvimento sem rebuilds
   - Otimização de performance

2. **[HOT_RELOAD_SUMMARY.md](HOT_RELOAD_SUMMARY.md)**
   - Resumo técnico do hot reload
   - Comparação antes/depois
   - Benefícios de performance

3. **[MIGRATION_HOT_RELOAD.md](MIGRATION_HOT_RELOAD.md)**
   - Guia de migração para hot reload
   - Passo a passo para projetos existentes
   - Troubleshooting

4. **[ENV_SETUP_COMPLETE.md](ENV_SETUP_COMPLETE.md)**
   - Configuração completa de ambiente
   - Variáveis criadas
   - Testes e verificação

### Scripts de Desenvolvimento

- **[DEV_SCRIPTS_README.md](DEV_SCRIPTS_README.md)** - Scripts de automação
- **[WINDOWS_DEV_SCRIPTS_SUMMARY.md](WINDOWS_DEV_SCRIPTS_SUMMARY.md)** - Scripts Windows

---

## 🏗️ Arquitetura & Implementação

### Arquitetura do Sistema

1. **[ARCHITECTURE_SUMMARY.md](ARCHITECTURE_SUMMARY.md)**
   - Visão geral da arquitetura
   - DDD + Arquitetura Hexagonal
   - Camadas e responsabilidades

2. **[IMPLEMENTATION_COMPLETE.md](IMPLEMENTATION_COMPLETE.md)**
   - Implementações completas
   - Features implementadas
   - Status do projeto

---

## 📖 Bibliotecas Externas

### Doctrine Migrations

- **[libs/DoctrineMigrationsBundle/index.rst](libs/DoctrineMigrationsBundle/index.rst)**
  - Documentação oficial do bundle
  - Referência completa

### JWT Bundles

#### LexikJWTAuthenticationBundle
- **[libs/LexikJWTAuthenticationBundle/01-configuration-reference.rst](libs/LexikJWTAuthenticationBundle/01-configuration-reference.rst)**
- **[libs/LexikJWTAuthenticationBundle/02-data-customization.rst](libs/LexikJWTAuthenticationBundle/02-data-customization.rst)**
- **[libs/LexikJWTAuthenticationBundle/04-cors-requests.rst](libs/LexikJWTAuthenticationBundle/04-cors-requests.rst)**
- **[libs/LexikJWTAuthenticationBundle/05-encoder-service.rst](libs/LexikJWTAuthenticationBundle/05-encoder-service.rst)**
- **[libs/LexikJWTAuthenticationBundle/06-extending-jwt-authenticator.rst](libs/LexikJWTAuthenticationBundle/06-extending-jwt-authenticator.rst)**
- **[libs/LexikJWTAuthenticationBundle/07-manual-token-creation.rst](libs/LexikJWTAuthenticationBundle/07-manual-token-creation.rst)**
- **[libs/LexikJWTAuthenticationBundle/08-jwt-user-provider.rst](libs/LexikJWTAuthenticationBundle/08-jwt-user-provider.rst)**
- **[libs/LexikJWTAuthenticationBundle/09-access-authenticated-jwt-token.rst](libs/LexikJWTAuthenticationBundle/09-access-authenticated-jwt-token.rst)**
- **[libs/LexikJWTAuthenticationBundle/10-web-token.rst](libs/LexikJWTAuthenticationBundle/10-web-token.rst)**
- **[libs/LexikJWTAuthenticationBundle/11-invalidate-token.rst](libs/LexikJWTAuthenticationBundle/11-invalidate-token.rst)**

#### JWTRefreshTokenBundle
- **[libs/JWTRefreshTokenBundle/README.md](libs/JWTRefreshTokenBundle/README.md)**
- **[libs/JWTRefreshTokenBundle/SECURITY.md](libs/JWTRefreshTokenBundle/SECURITY.md)**
- **[libs/JWTRefreshTokenBundle/UPGRADE-2.0.md](libs/JWTRefreshTokenBundle/UPGRADE-2.0.md)**

---

## 📋 Índice por Categoria

### 🆕 Para Iniciantes

| Documento | Descrição | Tempo de Leitura |
|-----------|-----------|------------------|
| [QUICK_START.md](../QUICK_START.md) | Início rápido | 5 min |
| [ENVIRONMENT_VARIABLES.md](ENVIRONMENT_VARIABLES.md) | Variáveis de ambiente | 10 min |
| [GUIA_MIGRATIONS.md](GUIA_MIGRATIONS.md) | Migrations completo | 30 min |
| [MIGRATIONS_QUICK_REFERENCE.md](MIGRATIONS_QUICK_REFERENCE.md) | Referência rápida | 5 min |

### 🔧 Para Desenvolvimento Diário

| Documento | Uso |
|-----------|-----|
| [MIGRATIONS_QUICK_REFERENCE.md](MIGRATIONS_QUICK_REFERENCE.md) | Comandos de migrations |
| [DOCKER_HOT_RELOAD.md](DOCKER_HOT_RELOAD.md) | Configuração Docker |
| [ENV_SETUP_COMPLETE.md](ENV_SETUP_COMPLETE.md) | Variáveis de ambiente |
| [AUTH_API_CONTRACT.md](AUTH_API_CONTRACT.md) | API de autenticação |

### 🎓 Para Aprendizado

| Documento | Tópico |
|-----------|--------|
| [GUIA_MIGRATIONS.md](GUIA_MIGRATIONS.md) | Migrations em detalhes |
| [MIGRATIONS_DIAGRAMS.md](MIGRATIONS_DIAGRAMS.md) | Diagramas visuais |
| [ARCHITECTURE_SUMMARY.md](ARCHITECTURE_SUMMARY.md) | Arquitetura DDD |
| [JWT_AUTH_IMPLEMENTATION_COMPLETE.md](JWT_AUTH_IMPLEMENTATION_COMPLETE.md) | JWT completo |

### 🚨 Para Troubleshooting

| Documento | Quando Usar |
|-----------|-------------|
| [MIGRATIONS_QUICK_REFERENCE.md](MIGRATIONS_QUICK_REFERENCE.md) | Problemas com migrations |
| [ENVIRONMENT_VARIABLES.md](ENVIRONMENT_VARIABLES.md) | Problemas de conexão |
| [MIGRATION_HOT_RELOAD.md](MIGRATION_HOT_RELOAD.md) | Problemas com Docker |
| [ENV_SETUP_COMPLETE.md](ENV_SETUP_COMPLETE.md) | Problemas de configuração |

---

## 🎯 Fluxos de Trabalho Recomendados

### Setup Inicial (Primeira Vez)

```
1. QUICK_START.md → Configurar ambiente
2. ENVIRONMENT_VARIABLES.md → Entender variáveis
3. DOCKER_HOT_RELOAD.md → Configurar hot reload
4. GUIA_MIGRATIONS.md → Aprender migrations
5. AUTH_API_CONTRACT.md → Entender autenticação
```

### Desenvolvimento Diário

```
1. Modificar Entity
2. MIGRATIONS_QUICK_REFERENCE.md → Criar migration
3. Testar localmente
4. Commit
```

### Deploy em Produção

```
1. GUIA_MIGRATIONS.md (seção Deploy) → Executar migrations
2. ENVIRONMENT_VARIABLES.md → Verificar variáveis
3. Monitorar logs
```

---

## 🔍 Busca Rápida

### Procurando por...

- **Comandos de migrations?** → [MIGRATIONS_QUICK_REFERENCE.md](MIGRATIONS_QUICK_REFERENCE.md)
- **Como migrations funcionam?** → [GUIA_MIGRATIONS.md](GUIA_MIGRATIONS.md)
- **Diagramas visuais?** → [MIGRATIONS_DIAGRAMS.md](MIGRATIONS_DIAGRAMS.md)
- **Configurar banco de dados?** → [ENVIRONMENT_VARIABLES.md](ENVIRONMENT_VARIABLES.md)
- **Setup inicial?** → [QUICK_START.md](../QUICK_START.md)
- **Hot reload não funciona?** → [DOCKER_HOT_RELOAD.md](DOCKER_HOT_RELOAD.md)
- **Endpoints de autenticação?** → [AUTH_API_CONTRACT.md](AUTH_API_CONTRACT.md)
- **Arquitetura do projeto?** → [ARCHITECTURE_SUMMARY.md](ARCHITECTURE_SUMMARY.md)

---

## 📝 Convenções de Documentação

### Emojis Usados

- ⭐ **NOVO!** - Documentação recém-criada
- 🚀 - Início rápido / Setup
- 🔐 - Segurança / Autenticação
- 🗄️ - Banco de dados / Migrations
- 🐳 - Docker / DevOps
- 🏗️ - Arquitetura / Design
- 📚 - Bibliotecas / Dependências
- 🎯 - Fluxos de trabalho
- 🔧 - Ferramentas / Utilitários
- ✅ - Boas práticas
- ❌ - Evitar / Não fazer
- ⚠️ - Atenção / Cuidado
- 💡 - Dicas / Truques

### Níveis de Prioridade

1. **Essencial** - Deve ler antes de começar
2. **Recomendado** - Importante para o dia a dia
3. **Referência** - Consultar quando necessário
4. **Avançado** - Para casos específicos

---

## 🆕 Últimas Atualizações

### 2025-01-10

- ⭐ **NOVO**: [GUIA_MIGRATIONS.md](GUIA_MIGRATIONS.md) - Guia completo de migrations em português
- ⭐ **NOVO**: [MIGRATIONS_QUICK_REFERENCE.md](MIGRATIONS_QUICK_REFERENCE.md) - Referência rápida
- ⭐ **NOVO**: [MIGRATIONS_DIAGRAMS.md](MIGRATIONS_DIAGRAMS.md) - Diagramas visuais
- ✅ Atualizado: [README.md](../README.md) - Adicionada seção de migrations

### 2025-01-09

- ⭐ **NOVO**: [ENVIRONMENT_VARIABLES.md](ENVIRONMENT_VARIABLES.md) - Guia de variáveis de ambiente
- ⭐ **NOVO**: [ENV_SETUP_COMPLETE.md](ENV_SETUP_COMPLETE.md) - Setup completo
- ⭐ **NOVO**: Arquivo `.env` criado com todas as variáveis
- ✅ Atualizado: `dev.ps1` com comandos `setup` e `db-connect`

### 2025-01-08

- ⭐ **NOVO**: [DOCKER_HOT_RELOAD.md](DOCKER_HOT_RELOAD.md) - Hot reload completo
- ⭐ **NOVO**: [HOT_RELOAD_SUMMARY.md](HOT_RELOAD_SUMMARY.md) - Resumo técnico
- ⭐ **NOVO**: [MIGRATION_HOT_RELOAD.md](MIGRATION_HOT_RELOAD.md) - Guia de migração
- ⭐ **NOVO**: Script `dev.ps1` para automação

---

## 🤝 Contribuindo

Para adicionar nova documentação:

1. Crie o arquivo em `dev-docs/`
2. Use markdown (.md)
3. Adicione ao índice neste README
4. Use emojis para categorização
5. Mantenha consistência com documentação existente

### Template de Documentação

```markdown
# Título do Documento

## Índice
- [Seção 1](#seção-1)
- [Seção 2](#seção-2)

## Introdução
Breve descrição do que este documento cobre.

## Seção 1
Conteúdo...

## Resumo
Principais pontos...

---

**Criado em**: YYYY-MM-DD
**Versão**: X.Y
**Projeto**: Hidro API
```

---

## 📞 Suporte

### Problemas Comuns

1. **Migration não funciona** → [GUIA_MIGRATIONS.md](GUIA_MIGRATIONS.md) seção Troubleshooting
2. **Docker lento** → [DOCKER_HOT_RELOAD.md](DOCKER_HOT_RELOAD.md) seção Performance
3. **Banco não conecta** → [ENVIRONMENT_VARIABLES.md](ENVIRONMENT_VARIABLES.md) seção Database
4. **JWT erro** → [JWT_AUTH_IMPLEMENTATION_COMPLETE.md](JWT_AUTH_IMPLEMENTATION_COMPLETE.md)

### Recursos Externos

- [Symfony Documentation](https://symfony.com/doc/current/index.html)
- [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html)
- [Doctrine Migrations](https://www.doctrine-project.org/projects/migrations.html)
- [Docker Documentation](https://docs.docker.com/)

---

**Última atualização**: 2025-01-10  
**Mantido por**: Equipe Hidro API  
**Versão do Projeto**: 1.0
