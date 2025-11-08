# Documentação Técnica — Hidro API (REST)

## 1) Visão Geral

O **Hidro API** é um serviço REST stateless em **Symfony 7.3** (PHP 8.2+), construído com **Domain-Driven Design (DDD)** e **Arquitetura Hexagonal**, seguindo práticas de **Test-Driven Development (TDD)**. Utiliza **MySQL 8.0** para dados transacionais e **MongoDB 7** para trilhas de auditoria/linha do tempo. A API fornece autenticação via **JWT**, versionamento explícito, paginação, filtros, ordenação e documentação **OpenAPI 3**.

---

## 2) Tecnologias Utilizadas

### 2.1 Backend

* **Linguagem:** PHP 8.2+
* **Framework:** Symfony 7.3 (minimal API; sem Twig)
* **ORM/ODM:** Doctrine ORM (MySQL) + Doctrine ODM (MongoDB)
* **Autenticação:** **LexikJWTAuthenticationBundle** (JWT + refresh token)
* **Autorização:** Symfony Security (roles + voters)
* **Validação:** Symfony Validator (constraints + DTOs)
* **Serialização:** Symfony Serializer (JSON, JSON:API opcional)
* **Rate limit:** **symfony/rate-limiter**
* **CORS:** **nelmio/cors-bundle**
* **Doc OpenAPI/Swagger:** **NelmioApiDocBundle** (ou **API Platform** caso queira CRUD auto)
* **HATEOAS (opcional):** **willdurand/hateoas** para links de navegação
* **Cache:** Symfony Cache (PSR-6/16)
* **Logs:** Monolog (JSON estruturado)
* **Métricas/Tracing (opcional):** **open-telemetry/opentelemetry-php** (OTLP)

> Alternativa "bala de prata": **API Platform** em cima de Symfony acelera CRUD, paginação, filtros, docs OpenAPI, content-negotiation e validação. Se preferir controle fino, mantenha os bundles listados acima.

### 2.2 Frontend

Não acoplado. Consumidores típicos: SPA, mobile, integradores externos. (A antiga AuroraUserInterface pode seguir como projeto separado.)

### 2.3 Testes

* **TDD:** Test-Driven Development com PHPUnit 12
* **Unitários/Integração:** PHPUnit 12 + Doctrine fixtures
* **Funcionais/API:** **Symfony HttpKernel + HttpClient** ou **API Platform Test Client**
* **Contratos/End-to-End de API:** **Behat** ou **Pact** (consumer-driven)
* **Estática:** PHP_CodeSniffer, PHP-CS-Fixer, (opcional) **Psalm**/**PHPStan**
* **(Removido)** Cypress para UI — só faria sentido em um projeto web cliente.

### 2.4 Infra

* **Contêineres:** Docker
* **Servidor Web:** Nginx (reverse proxy para PHP-FPM)
* **Orquestração:** Docker Compose (dev/stg/prod) — opcional K8s/Helm
* **Ambiente:** 12-factor (configs via env)

---

## 3) Arquitetura da API

### 3.1 Camadas (Arquitetura Hexagonal + DDD)

```
HTTP (Controllers)
   → Application (Services/UseCases)
   → Domain (Entities/ValueObjects/DTOs/Aggregates/Policies)
   → Infrastructure (Repositories/Clients/Bus/Adapters)
```

* **Domain Layer**: Contém as entidades de negócio, agregados, value objects, domain events e domain services. Esta é a camada mais interna e não deve depender de nenhuma outra.
* **Application Layer**: Contém os casos de uso da aplicação, services e DTOs. Coordena a execução dos casos de uso do domínio.
* **Infrastructure Layer**: Implementações concretas de interfaces definidas nas camadas internas (repositórios, adapters, etc.).
* **Presentation Layer**: Controllers HTTP e serializers.

* Eventos de domínio → ouvintes/handlers publicam no MongoDB "timeline".
* DTOs isolam entrada/saída; Entities não "sabem" de HTTP.

### 3.2 Estrutura de Diretórios (resumida) - DDD/Hexagonal

```
src/
  Controller/              # REST controllers (v1)
  Application/             # Application services/use cases
    Service/
    Command/
    Query/
    Event/
  Domain/                  # Core business logic
    Model/                 # Entities, Value Objects, Aggregates
    Repository/            # Domain repository interfaces
    Event/                 # Domain events
    Exception/             # Domain exceptions
    Service/               # Domain services
    Policy/                # Business policies
  Infrastructure/          # Implementation details
    Repository/            # ORM/ODM repository implementations
    Bus/                   # Event/command bus implementations
    Adapter/               # External service adapters
    Persistence/           # Database migrations, etc.
  Security/
  Serializer/
```

### 3.3 Convenções REST

* **Versionamento:** prefixo de rota (`/api/v1/...`) e header `Accept: application/json`.
* **Recursos nomeados no plural:** `/users`, `/entities`, `/audits`.
* **Paginação:** `page`, `per_page` (default 20; máx 100).
* **Ordenação:** `sort=field` e `order=asc|desc` (multi-sort opcional: `sort=field1,-field2`).
* **Filtros:** query params simples (`?status=active&created_from=2025-01-01`).
* **Erros:** **RFC 7807** (`application/problem+json`).
* **Idempotência:** `Idempotency-Key` em POST sensíveis (opcional).
* **Rate limit:** cabeçalhos `X-RateLimit-*`.

---

---

## 4) Recursos & Endpoints (exemplos)

Base path: `/api/v1`

### 4.1 Auth

* `POST /auth/login` → `{ email, password }` → `200 { access_token, refresh_token, expires_in }`
* `POST /auth/refresh` → `{ refresh_token }` → novos tokens
* `POST /auth/logout` → invalida refresh token
* **Segurança:** `Authorization: Bearer <JWT>`

### 4.2 Users

* `GET /users` (admin; paginação/filtros)
* `POST /users` (admin)
* `GET /users/{id}`
* `PATCH /users/{id}` (JSON Merge Patch) ou `PUT`
* `DELETE /users/{id}` (soft delete opcional)

### 4.3 Entities (domínio principal)

* `GET /entities` `?q=...&status=...&page=&per_page=&sort=&order=`
* `POST /entities` (cria entidade)
* `GET /entities/{id}`
* `PATCH /entities/{id}`
* `DELETE /entities/{id}`

### 4.4 Timeline/Auditoria (MongoDB)

* `GET /audits` `?entity_id=&user_id=&from=&to=&event_type=...`
* `GET /entities/{id}/timeline` — histórico temporal consolidado
* (opcional) `GET /audits/{id}`

### 4.5 Saúde/Utilitários

* `GET /health` → status de dependências (MySQL, MongoDB, cache)
* `GET /metrics` (protegido) → Prometheus/OTel (se ativado)

**Códigos de status**: `200/201/204/400/401/403/404/409/422/429/500`.

---

## 5) Modelo de Respostas

### 5.1 Sucesso (lista paginada)

``json
{
  "data": [{ "id": "uuid", "name": "..." }],
  "meta": { "page": 1, "per_page": 20, "total": 156 },
  "links": {
    "self": "/api/v1/entities?page=1",
    "next": "/api/v1/entities?page=2"
  }
}
```

### 5.2 Erro (RFC 7807)

``json
{
  "type": "https://Hidro.dev/errors/validation",
  "title": "Unprocessable Entity",
  "status": 422,
  "detail": "Campos inválidos.",
  "errors": {
    "name": ["Não pode ser vazio"]
  }
}
```

---

## 6) Segurança

* **JWT** (access + refresh) via LexikJWT; expiração curta do access token.
* **CORS** via NelmioCorsBundle (origens confiáveis por ambiente).
* **Rate limiting**: Symfony RateLimiter por rota (`/auth/*` mais restrito).
* **RBAC**: roles & voters por recurso; atributos em controllers (`#[IsGranted(...)]`).
* **Input hardening**: validação em DTOs + normalizers/denormalizers.
* **Proteções**: SQLi (Doctrine), XSS (não há views), CSRF não aplicável a REST; ainda assim manter tokens em endpoints formulários se houver fluxo web separado.
* **Auditoria**: grava no MongoDB: usuário, ação, diffs, timestamps, origem (ip/ua), correlation id.

---

## 7) Performance & Otimização

* **HTTP cache** (ETag/Last-Modified) para GETs idempotentes.
* **Cache de aplicação**: metadados Doctrine + resultados quentes.
* **DB**: índices, `EXPLAIN`, *eager* vs *lazy* tuning; transações com savepoints.
* **Payloads**: paginação obrigatória, campos selecionáveis (`fields=id,name` opcional).
* **N+1**: evitar com joins/eager relations.

---

## 8) Documentação & Descoberta

* **OpenAPI 3** com **NelmioApiDocBundle** (ou nativo do API Platform).
  Expor `/api/docs` e `/api/docs.json`.
* **Exemplos executáveis** via `curl`/HTTPie no swagger UI.
* **Guides**: autenticação, paginação, erros, versionamento, migrações de contrato.

---

## 9) Testes & Qualidade

### 9.1 Test-Driven Development (TDD)

O projeto segue uma abordagem rigorosa de **Test-Driven Development (TDD)** onde:

* **Ciclo Red-Green-Refactor**: Todos os desenvolvimentos começam com a escrita de testes falhando (Red), implementação mínima para passar (Green), e refatoração para qualidade (Refactor).
* **Testes Unitários**: Focam em unidades individuais de código (métodos, classes) sem dependências externas.
* **Testes de Integração**: Verificam a interação entre componentes e integração com sistemas externos (banco de dados, APIs).
* **Testes Funcionais/API**: Validam o comportamento completo da aplicação através de cenários end-to-end.

### 9.2 Cobertura e Qualidade

* **Unitários**: serviços, policies, validadores.
* **Integração**: repositórios (fixtures), serialização, filtros.
* **Funcionais/API**: cenários end-to-end de recursos; contratos OpenAPI (asserts de schema).
* **Contratos**: Pact (consumers) — opcional se houver muitos clientes.
* **Cobertura**: alvo ≥ 80% em domínio/aplicação.
* **CI gates**: linters (PCS/CS-Fixer), estática (Psalm/PHPStan), testes, mutation testing (Infection — opcional).

---

## 10) Deploy & CI/CD

* **Pipelines**:

  1. `composer install --no-dev --optimize-autoloader`
  2. Linters/estática/testes
  3. Build imagem Docker (variável `APP_ENV=prod`)
  4. Migrações **MySQL** (Doctrine Migrations) com "safe deploy"
  5. **Symfony Messenger**/cron (se usar jobs) com workers separados
* **Runtime**: Nginx → PHP-FPM; healthcheck `/health`.
* **Config**: env-vars (DB, Mongo, JWT_PASSPHRASE, CORS_ALLOW_ORIGIN, RATE_LIMITS).
* **Rollback**: imagens versionadas + migrações reversas.

---

## 11) Observabilidade

* **Logs**: JSON (Monolog), `correlation_id` por request (middleware).
* **Tracing**: OpenTelemetry (OTLP) → Jaeger/Tempo (opcional).
* **Métricas**: Prometheus endpoints (requests, latência, 5xx, DB pool).
* **Alertas**: limites de erro/latência; picos de 429.

---

## 12) Estratégia de Dados

* **MySQL**: entidades principais (usuários, entidades de negócio, relacionamentos).
* **MongoDB**: `audits` e `entity_timelines` (event sourcing leve).
* **Consistência**: transações no MySQL; write-behind de audit assíncrono (Messenger) para não travar a request.

---

## 13) Migração do Monolito Web → API (checklist rápido)

1. Remover dependências de UI/Twig do projeto API.
2. Isolar **Controllers** para somente HTTP/JSON.
3. Revisar **DTOs** de entrada/saída (Serializer + Validator).
4. Implementar **RFC 7807** para erros.
5. Habilitar **CORS** e **rate limiting**.
6. Documentar com **OpenAPI** e publicar `/api/docs`.
7. Criar **métricas** e **correlation id** middleware.
8. Adaptar **testes** (remover Cypress; adicionar testes de contrato).
9. Preparar **versionamento** `/api/v1` e política de depreciação.
10. Revisar **políticas de autorização** por recurso (voters/attributes).

---

## 14) Exemplo de Rota (Symfony Controller — esqueleto)

``php
#[Route('/api/v1/entities', name: 'entities_list', methods: ['GET'])]
public function list(Request $req, EntityService $svc): JsonResponse
{
    $q = $req->query->get('q');
    $page = max(1, (int) $req->query->get('page', 1));
    $perPage = min(100, max(1, (int) $req->query->get('per_page', 20)));
    $sort = $req->query->get('sort', 'created_at');
    $order = strtolower($req->query->get('order', 'desc')) === 'asc' ? 'asc' : 'desc';

    $result = $svc->search($q, $page, $perPage, $sort, $order);

    return $this->json([
        'data' => $result->items,
        'meta' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $result->total,
        ],
        'links' => [
            'self' => sprintf('/api/v1/entities?page=%d&per_page=%d', $page, $perPage),
            'next' => $page * $perPage < $result->total ? sprintf('/api/v1/entities?page=%d&per_page=%d', $page + 1, $perPage) : null,
        ],
    ]);
}
```

---

### Pacotes a adicionar (composer)

```bash
composer require lexik/jwt-authentication-bundle nelmio/cors-bundle
composer require nelmio/api-doc-bundle --dev
composer require symfony/rate-limiter
# opcionais
composer require willdurand/hateoas
composer require open-telemetry/sdk # + exporters
composer require vimeo/psalm --dev
```

---

**Resumo:** você mantém o coração (Symfony + Doctrine/ODM + JWT + MySQL/Mongo), troca a "cara" web por **contratos REST sólidos** (OpenAPI, RFC 7807, CORS, paginação, filtros, rate limit), fortalece observabilidade e testes de API. Isso dá base para múltiplos clientes consumirem o Hidro com segurança e escala.

Se quiser, eu converto um módulo específico (ex.: `entities`) para **API Platform** e te entrego o esquema OpenAPI pronto e as rotas geradas.

## Running with Docker

1. Build and start the containers:
```bash
docker-compose up -d
```

2. Access the application:
- API: http://localhost:8000
- Database: localhost:3307 (instead of default 3306 to avoid conflicts)

3. Stopping the containers:
```bash
docker-compose down
```

### Docker Services

- **app**: PHP 8.2 FPM service with all required extensions
- **nginx**: Web server exposing port 8000
- **database**: MySQL 8.0 database with volume persistence (exposed on port 3307)

### Environment Variables

Check the [docker-compose.yml](file:///c:/dev/PHP/hidro-api/docker-compose.yml) file for database credentials and update them as needed.

### Development Workflow

The application code is built into the Docker image during the build process. To make changes:

1. Modify your code
2. Rebuild the containers:
   ```bash
   docker-compose up -d --build
   ```

For a faster development workflow on Windows, consider using WSL 2 with Docker Desktop.

### Troubleshooting

If you encounter the error "error while creating mount source path", try these solutions:

1. **Check Docker Desktop File Sharing Settings**:
   - Open Docker Desktop
   - Go to Settings > Resources > File Sharing
   - Make sure your project directory is added to the file sharing list

2. **Use WSL 2 Backend** (Recommended):
   - Open Docker Desktop
   - Go to Settings > General
   - Check "Use the WSL 2 based engine"
   - Restart Docker Desktop

3. **Run Docker from WSL 2 Terminal**:
   - Install WSL 2: `wsl --install`
   - Install a Linux distribution (like Ubuntu) from the Microsoft Store
   - Run your Docker commands from the WSL terminal
