# Configuração do Banco de Dados no DBeaver

Este guia descreve como preparar o ambiente de banco de dados local do projeto **Hidro API** e como acessar os dados utilizando o **DBeaver**. Todos os passos consideram a stack Docker já versionada no repositório.

## 1. Visão Geral do Ambiente

| Recurso | Valor padrão |
| --- | --- |
| Engine | MySQL 8.0 |
| Container Docker | `hidro-api-db` |
| Host de acesso (host machine) | `127.0.0.1` |
| Porta exposta | `3307` |
| Banco padrão | `hidro_api` |
| Usuário de aplicação | `hidro_user` |
| Senha do usuário | `hidro_password` |

As credenciais acima estão definidas em [`docker-compose.yml`](../docker-compose.yml) e são referenciadas por `DATABASE_URL` nos arquivos `.env`.

## 2. Preparar o Banco de Dados com Docker

1. Certifique-se de ter **Docker** e **Docker Compose** instalados.
2. No diretório raiz do projeto, suba os serviços essenciais:
   ```bash
   docker compose up -d database app
   ```
   *O serviço `database` disponibiliza o MySQL; o serviço `app` expõe o PHP-FPM para execução das migrations.*
3. (Opcional) Verifique se o banco está aceitando conexões:
   ```bash
   docker compose logs -f database
   ```
   Aguarde a mensagem `ready for connections` antes de prosseguir.

## 3. Configurar a Conexão no DBeaver

1. Abra o DBeaver e clique em **Database → New Database Connection**.
2. Selecione **MySQL** como tipo de banco.
3. Preencha os campos:
   - **Server Host:** `127.0.0.1`
   - **Port:** `3307`
   - **Database:** `hidro_api`
   - **Username:** `hidro_user`
   - **Password:** `hidro_password`
4. Clique em **Test Connection** para validar o acesso. Se for solicitado o driver JDBC, permita que o DBeaver faça o download automático.
5. Salve a conexão. Após conectar, o schema `hidro_api` estará disponível no painel esquerdo (**Database Navigator**).

### Dicas

- Caso tenha ajustado credenciais locais via `.env.local`, utilize os valores atualizados.
- Se o host for diferente (por exemplo, ao executar o Docker em WSL2 ou remoto), substitua `127.0.0.1` pelo IP correspondente.
- É recomendável ativar o **Time Zone** como `UTC` na aba **Driver Properties** para manter consistência com o backend.

## 4. Executar e Gerenciar Migrations

O projeto utiliza **Doctrine Migrations**. Os comandos abaixo devem ser executados no container `app` (ou em um ambiente PHP com as dependências instaladas).

1. **Instalar dependências PHP** (se ainda não tiver feito):
   ```bash
   docker compose exec app composer install
   ```
2. **Aplicar migrations pendentes**:
   ```bash
   docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction
   ```
3. **Verificar status** (opcional):
   ```bash
   docker compose exec app php bin/console doctrine:migrations:status
   ```
4. **Criar uma nova migration** (caso precise versionar mudanças de schema):
   ```bash
   docker compose exec app php bin/console doctrine:migrations:diff
   ```
   Depois de gerar o arquivo em `migrations/`, execute novamente o passo 2 para aplicá-la.

### Boas Práticas

- Sempre garanta que a URL de conexão (`DATABASE_URL`) no arquivo `.env` ou `.env.local` aponta para o mesmo banco que você deseja alterar.
- Nunca edite tabelas manualmente em produção via DBeaver; utilize migrations para manter o histórico das alterações.
- Antes de rodar migrations em ambientes compartilhados, faça backup ou snapshot do banco.

## 5. Troubleshooting

| Sintoma | Possíveis causas e soluções |
| --- | --- |
| **Erro de conexão no DBeaver** | Verifique se o container `hidro-api-db` está ativo (`docker ps`). Confira firewall/local network. |
| **`Access denied for user`** | Usuário/senha incorretos ou banco não criado. Rode `docker compose exec database mysql -u root -p` e confira permissões. |
| **Migrations falham com `Unknown database`** | Garanta que o banco `hidro_api` existe (`docker compose exec database mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS hidro_api;"`). |
| **Timeout ao conectar** | MySQL pode ainda estar inicializando; aguarde alguns segundos após subir o container. |
| **`Public Key Retrieval is not allowed` ao testar a conexão** | Na aba **Driver Properties** da conexão, defina `allowPublicKeyRetrieval` como `TRUE`, salve e teste novamente. Essa opção autoriza o DBeaver a solicitar a chave pública do servidor MySQL ao estabelecer a conexão inicial. |

Com esses passos, o ambiente local estará pronto para inspeção e manipulação de dados via DBeaver e para o gerenciamento das migrations com Doctrine.
