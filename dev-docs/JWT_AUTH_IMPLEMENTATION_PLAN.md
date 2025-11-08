# Plano de Implementação — Autenticação JWT com Refresh Token

## 1. Objetivos
- Implementar autenticação stateless baseada em **LexikJWTAuthenticationBundle** com suporte a refresh tokens usando **gesdinet/jwt-refresh-token-bundle**.
- Respeitar Clean Architecture, DDD, SOLID e PSR-12, mantendo camadas desacopladas (Presentation → Application → Domain → Infrastructure).
- Garantir aderência às recomendações de segurança (RFC 8725, OWASP JWT Cheat Sheet, OWASP Top 10) e evitar vulnerabilidades comuns.
- Adotar TDD em toda a implementação, cobrindo testes unitários, integração e funcionais.

## 2. Pré-requisitos e Preparação
1. **Chaves e Segredos**
   - Gerar par RSA de 4096 bits dentro de `config/jwt/` usando o container `app`.
   - Proteger `private.pem` com passphrase forte armazenada em variável de ambiente (`JWT_PASSPHRASE`).
   - Ajustar permissões das chaves para impedir leitura indevida (`chmod 600`).
2. **Dependências**
   - `composer require lexik/jwt-authentication-bundle gesdinet/jwt-refresh-token-bundle symfony/security-bundle symfony/uid`
   - Se necessário, instalar `symfony/monolog-bundle` e `symfony/rate-limiter` para auditoria e anti-bruteforce.
3. **Variáveis de Ambiente**
   - Definir em `.env`/`.env.local`: `JWT_SECRET_KEY`, `JWT_PUBLIC_KEY`, `JWT_PASSPHRASE`, `JWT_TOKEN_TTL`, `JWT_REFRESH_TTL`, `JWT_AUDIENCE`, `JWT_ISSUER`.
   - Configurar `CORS_ALLOW_ORIGIN` e rate limiting adequado.
4. **Migrações**
   - Preparar repositório `RefreshToken` em `src/Infrastructure/Persistence/Doctrine/Entity/RefreshToken.php` (ou pasta equivalente) estendendo a classe base do bundle.
   - Gerar e aplicar migrations para tabela `refresh_tokens` no MySQL com índices em `refresh_token` e `username`.

## 3. Plano de Implementação (Chain-of-Thought)
1. **Entendimento do Domínio de Autenticação**
   - Mapear agregados de `User` existentes e assegurar presença de interfaces em `src/Domain/Users/UsersRepository.php`.
   - Confirmar DTOs de entrada/saída na camada Application para representar credenciais e tokens.
2. **Planejar Fluxos**
   - Autenticação inicial: credenciais → `AuthenticationController` → `AuthenticateUserUseCase` → `UsersRepository` → emitir JWT/Refresh.
   - Refresh: refresh token válido → `RefreshTokenController` → `RefreshAccessTokenUseCase`.
   - Logout/Revogação: invalidar refresh token persistido + blocklist opcional para JWT.
3. **Modelagem DDD**
   - Domínio: Value Object `HashedPassword`, entidade `User`, eventos `UserAuthenticated`.
   - Application: casos de uso `AuthenticateUser`, `RefreshAccessToken`, `InvalidateRefreshToken`.
   - Infrastructure: adaptadores para Lexik (token factory), Doctrine repositories para `User` e `RefreshToken`.
4. **Interface HTTP**
   - Controllers em `src/Presentation/Http/Controller/Auth/` utilizando DTOs de request/response.
   - Serialização com normalizers e resposta `application/json` padrão RFC 7807 para erros.
5. **Segurança**
   - JWT Claims: `iss`, `aud`, `iat`, `exp`, `nbf`, `sub`, `jti`, roles (`roles`).
   - TTL curto para access token (15 min) e refresh tokens (7 dias) com rotação de refresh (emitir novo refresh a cada uso).
   - Habilitar blocklist (`lexik_jwt_authentication.blocklist_token.enabled=true`) com cache centralizado (Redis) para suportar logout imediato.
   - Registrar tentativas e aplicar rate limit em `/api/v1/auth/login`.
6. **Testes (TDD)**
   - Criar testes de caso de uso primeiro (unit → integration).
   - Fixtures para usuários de teste usando `tests/Functional/Fixture/`.
   - Testes funcionais de endpoints cobrindo login, refresh, revogação, acesso autorizado/negado.
7. **Observabilidade**
   - Logar eventos de autenticação e refresh via Monolog com contexto (IP, user agent, jti).
   - Emissão de métricas para requisições de auth (contadores, tempos).

## 4. Passo a Passo da Implementação
### 4.1 Instalação e Configuração
1. **Adicionar Bundles**
   ```bash
   docker compose exec app composer require lexik/jwt-authentication-bundle gesdinet/jwt-refresh-token-bundle
   ```
2. **Gerar Chaves**
   ```bash
   docker compose exec app mkdir -p config/jwt
   docker compose exec app openssl genrsa -out config/jwt/private.pem 4096
   docker compose exec app openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
   docker compose exec app chmod 600 config/jwt/private.pem config/jwt/public.pem
   ```
3. **Configuração do Lexik JWT** (`config/packages/lexik_jwt_authentication.yaml`)
   ```yaml
   lexik_jwt_authentication:
       secret_key: '%kernel.project_dir%/config/jwt/private.pem'
       public_key: '%kernel.project_dir%/config/jwt/public.pem'
       pass_phrase: '%env(string:JWT_PASSPHRASE)%'
       token_ttl: '%env(int:JWT_TOKEN_TTL)%'
       clock_skew: 60
       user_identity_field: email
       token_extractors:
           authorization_header:
               enabled: true
               prefix: 'Bearer'
               name: 'Authorization'
       blocklist_token:
           enabled: true
           cache: cache.app
   ```
4. **Configuração do Refresh Token** (`config/packages/gesdinet_jwt_refresh_token.yaml`)
   ```yaml
   gesdinet_jwt_refresh_token:
       refresh_token_class: App\Infrastructure\Persistence\Doctrine\Entity\RefreshToken
       user_identity_field: email
       ttl: '%env(int:JWT_REFRESH_TTL)%'
       ttl_update: true
       single_use: true
   ```
5. **Entidade Refresh Token** (`src/Infrastructure/Persistence/Doctrine/Entity/RefreshToken.php`)
   - Estender `Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken`.
   - Adicionar índices e campos extras (`createdByIp`, `userAgent`, `revokedAt`).
6. **Security Firewall** (`config/packages/security.yaml`)
   - Definir firewall `api` com `json_login`, `jwt`, `refresh_jwt`.
   - Permitir acesso público a `/api/v1/auth/login` e `/api/v1/auth/token/refresh`.
   - Declarar provider utilizando `UsersRepositoryInterface`.
7. **Rotas** (`config/routes.yaml` ou atributos nos controllers)
   - `/api/v1/auth/login`
   - `/api/v1/auth/token/refresh`
   - `/api/v1/auth/logout`
8. **Migrations**
   ```bash
   docker compose exec app php bin/console make:migration
   docker compose exec app php bin/console doctrine:migrations:migrate
   ```

### 4.2 Camada de Domínio
1. **Entidades/Objetos de Valor**
   - `User` com métodos `verifyPassword(HashedPassword $hash, PasswordEncoderInterface $encoder)`.
   - `RefreshToken` como agregado independente caso precise de regras adicionais (opcional).
2. **Serviços de Domínio**
   - `TokenSigner` (interface) para abstrair geração de tokens.
   - `TokenRotationPolicy` definindo regras de rotação e revogação.

### 4.3 Camada de Aplicação
1. **DTOs**
   - `LoginRequestDTO` (email, password).
   - `TokenPairDTO` (accessToken, refreshToken, expiresAt, refreshExpiresAt, issuedAt, tokenType).
   - `RefreshTokenRequestDTO`.
2. **Casos de Uso**
   - `AuthenticateUserUseCase`: valida credenciais, dispara evento `UserAuthenticated` e retorna `TokenPairDTO`.
   - `RefreshAccessTokenUseCase`: valida refresh token persistido, aplica rotação e retorna novo `TokenPairDTO`.
   - `LogoutUserUseCase`: revoga refresh token (marca `revokedAt` e adiciona JWT jti à blocklist se habilitada).
3. **Handlers/Services**
   - `TokenService` para interagir com Lexik JWT Manager e RefreshTokenManager.
   - Interfaces definidas no domínio com implementações na infraestrutura.

### 4.4 Camada de Apresentação
1. **Controllers**
   - `AuthController::login` usando `LoginRequestDTO` + Serializer/Validator.
   - `AuthController::refresh` validando `RefreshTokenRequestDTO`.
   - `AuthController::logout` recebendo refresh token atual.
2. **Responses**
   - Padronizar resposta:
     ```json
     {
       "token_type": "Bearer",
       "access_token": "...",
       "expires_in": 900,
       "refresh_token": "...",
       "refresh_expires_in": 604800,
       "scope": ["ROLE_USER"]
     }
     ```
3. **Erros**
   - Utilizar `ProblemDetailsFactory` para respostas 401/403/400.

### 4.5 Infraestrutura
1. **Implementações**
   - `DoctrineUsersRepository` com métodos `findByEmail` otimizados (índices + evitar N+1 com fetch joins sob medida).
   - `DoctrineRefreshTokenRepository` com métodos `save`, `revoke`, `findValidToken`.
   - `LexikJwtTokenSigner` implementando `TokenSigner` e delegando para `JWTTokenManagerInterface`.
2. **Configuração de Serviços** (`config/services.yaml`)
   - Registrar factories, handlers, normalizers.
   - Injetar dependências via constructor, nunca via service locator.
3. **Eventos**
   - Listener para `Symfony\Component\Security\Http\Event\LoginSuccessEvent` para emitir logs/metrics.
   - Listener para `JWTInvalidEvent`, `JWTNotFoundEvent`, etc., para respostas customizadas.

## 5. Estratégia de Testes (TDD)
1. **Unitários**
   - Domínio: validação de senha, políticas de rotação, regras de revogação.
   - Aplicação: casos de uso `AuthenticateUserUseCase`, `RefreshAccessTokenUseCase`, `LogoutUserUseCase` usando mocks de repositórios.
2. **Integração**
   - Testar repositórios Doctrine com banco em memória / transacional.
   - Validar interação com `JWTTokenManagerInterface` usando `KernelTestCase`.
3. **Funcionais**
   - `tests/Functional/Auth/LoginTest`: fluxo feliz, credenciais inválidas, usuário bloqueado.
   - `tests/Functional/Auth/RefreshTokenTest`: refresh válido, refresh expirado, refresh reutilizado.
   - `tests/Functional/Auth/LogoutTest`: revogação e bloqueio de token.
4. **Contratos de Segurança**
   - Verificar que tokens incluem `jti`, `iss`, `aud`, `nbf`.
   - Garantir que refresh tokens são single-use (rotação) e expirados não funcionam.
   - Testar rate limiting e bloqueio após múltiplas falhas (se implementado).

## 6. Boas Práticas e Considerações de Segurança
- **Transporte Seguro**: exigir HTTPS, configurar `secure` e `httpOnly` para cookies se utilizados.
- **Armazenamento Seguro**: nunca persistir refresh tokens em texto claro; usar hashing (ex.: SHA-512 + salt) antes de armazenar.
- **Revogação**: remover refresh tokens em logout e job periódico para limpeza de tokens expirados.
- **Rotação Obrigatória**: sempre emitir novo par access/refresh em cada uso e invalidar o refresh antigo.
- **Proteção CSRF**: preferir envio de tokens em header `Authorization`; se usar cookies, habilitar SameSite=strict e CSRF tokens.
- **Proteção contra Replay**: usar `jti` único, armazenar `jti` de tokens revogados e validar carimbo temporal (`nbf`, `iat`).
- **Monitoramento**: logar todas as falhas de autenticação com metadados mínimos, mantendo LGPD.
- **Auditoria**: armazenar eventos de autenticação em MongoDB (linha do tempo) conforme arquitetura prevista.

## 7. Checklist de Entrega
- [ ] Dependências instaladas e chaves geradas.
- [ ] Configurações de pacotes e variáveis de ambiente definidas.
- [ ] Entidades, repositórios e serviços implementados respeitando DDD.
- [ ] Controllers expostos sob `/api/v1/auth/*` com respostas padronizadas.
- [ ] Testes unitários, integração e funcionais passando no CI.
- [ ] Documentação OpenAPI atualizada (esquemas de request/response, security schemes).
- [ ] Monitoramento e logs de autenticação habilitados.

## 8. Referências
- LexikJWTAuthenticationBundle — documentação oficial (`dev-docs/LexikJWTAuthenticationBundle/*`).
- JWTRefreshTokenBundle — README (`dev-docs/JWTRefreshTokenBundle/README.md`).
- RFC 8725 — JSON Web Token Best Current Practices.
- OWASP Cheat Sheets: JSON Web Token, Authentication, Password Storage.
- Symfony Security Docs e OWASP Top 10 (2021).
