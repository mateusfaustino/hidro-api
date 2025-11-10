# Environment Variables Guide

## Overview

This guide explains all environment variables used in the Hidro API project.

## File Structure

- **`.env`** - Local development configuration (Docker)
- **`.env.example`** - Template with example values
- **`.env.dev`** - Development-specific overrides
- **`.env.test`** - Testing environment configuration

## Database Configuration

### Docker Internal Connection

When running **inside Docker containers** (recommended for development):

```env
DATABASE_URL="mysql://hidro_user:hidro_password@database:3306/hidro_api?serverVersion=8.0&charset=utf8mb4"
```

- **Host**: `database` (Docker service name)
- **Port**: `3306` (internal port)
- **User**: `hidro_user`
- **Password**: `hidro_password`
- **Database**: `hidro_api`

### External Connection (DBeaver, etc.)

When connecting **from your host machine** (e.g., DBeaver):

```
Host: 127.0.0.1 or localhost
Port: 3307 (mapped port)
Database: hidro_api
User: hidro_user
Password: hidro_password
```

### Database Variables Explained

```env
DB_HOST=database              # Service name in docker-compose.yml
DB_PORT=3306                  # Internal Docker port
DB_NAME=hidro_api            # Database name
DB_USER=hidro_user           # Application database user
DB_PASSWORD=hidro_password   # Application user password
DB_ROOT_PASSWORD=rootpassword # MySQL root password
```

## Application Configuration

### Symfony Framework

```env
APP_ENV=dev                   # Environment: dev, prod, test
APP_DEBUG=1                   # Debug mode: 1 = enabled, 0 = disabled
APP_SECRET=b08006...          # Secret key for CSRF, cookies, etc.
```

**Important**: Change `APP_SECRET` in production!

### Default URI

```env
DEFAULT_URI=http://localhost:8000
```

The base URL where your API is accessible.

## JWT Authentication

### JWT Keys

```env
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=your_jwt_passphrase_here
```

**Setup Required**: Generate JWT keys before using authentication:

```powershell
# Inside container
.\dev.ps1 shell

# Generate keys
php bin/console lexik:jwt:generate-keypair
exit
```

This creates:
- `config/jwt/private.pem` - Private key for signing tokens
- `config/jwt/public.pem` - Public key for verifying tokens

### JWT Settings

```env
JWT_TOKEN_TTL=3600           # Access token lifetime (seconds) - 1 hour
JWT_REFRESH_TTL=604800       # Refresh token lifetime (seconds) - 7 days
JWT_AUDIENCE=https://hidro.local    # Token audience claim
JWT_ISSUER=https://hidro.local      # Token issuer claim
```

**Token Lifetimes**:
- `JWT_TOKEN_TTL=3600` = 1 hour (short-lived for security)
- `JWT_REFRESH_TTL=604800` = 7 days (604800 seconds)

## Messenger

```env
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
```

Symfony Messenger configuration for async tasks (if used).

## Environment-Specific Files

### `.env` (Main Configuration)

Used for local development with Docker. Contains actual values.

**Status**: ❌ Not committed to Git (in `.gitignore`)

### `.env.example` (Template)

Template file with example values. Commit this to help other developers.

**Status**: ✅ Committed to Git

### `.env.dev` (Development Overrides)

Additional development-specific settings.

**Status**: ✅ Committed to Git

### `.env.test` (Testing)

Configuration for running tests.

**Status**: ✅ Committed to Git

## Quick Setup for New Developers

### 1. Copy Environment File

```powershell
# Copy the example file
cp .env.example .env
```

### 2. Adjust Database Settings (If Needed)

The default values match `docker-compose.yml`, so usually no changes needed.

### 3. Generate JWT Keys

```powershell
# Start containers
.\dev.ps1 start

# Generate JWT keys
.\dev.ps1 shell
php bin/console lexik:jwt:generate-keypair
exit
```

### 4. Update JWT Passphrase (Optional)

Edit `.env` and change:
```env
JWT_PASSPHRASE=your_secure_passphrase_here
```

### 5. Start Development

```powershell
.\dev.ps1 start
```

## Docker Compose Environment Variables

The database credentials in `.env` must match `docker-compose.yml`:

```yaml
# docker-compose.yml
database:
  environment:
    MYSQL_DATABASE: hidro_api        # = DB_NAME
    MYSQL_ROOT_PASSWORD: rootpassword # = DB_ROOT_PASSWORD
    MYSQL_USER: hidro_user           # = DB_USER
    MYSQL_PASSWORD: hidro_password   # = DB_PASSWORD
```

## Production Considerations

When deploying to production:

### 1. Change Sensitive Values

```env
APP_ENV=prod
APP_DEBUG=0
APP_SECRET=generate_new_random_secret_here
JWT_PASSPHRASE=strong_random_passphrase
```

### 2. Use Strong Passwords

```env
DB_PASSWORD=very_strong_random_password
DB_ROOT_PASSWORD=very_strong_root_password
```

### 3. Update URLs

```env
DEFAULT_URI=https://your-production-domain.com
JWT_AUDIENCE=https://your-production-domain.com
JWT_ISSUER=https://your-production-domain.com
```

### 4. Use External Database

```env
DATABASE_URL="mysql://prod_user:prod_pass@prod-db-host:3306/prod_db?serverVersion=8.0"
```

## Testing Configuration

The `.env.test` file is used when running tests:

```env
APP_ENV=test
DATABASE_URL="sqlite:///%kernel.project_dir%/var/test.db"
```

Tests use SQLite in-memory database for speed.

## Troubleshooting

### Connection Refused

**Problem**: Can't connect to database

**Solutions**:

1. **Check if database container is running**:
   ```powershell
   .\dev.ps1 status
   ```

2. **Verify DATABASE_URL host**:
   - Inside Docker: use `database`
   - Outside Docker: use `127.0.0.1:3307`

3. **Check credentials match docker-compose.yml**

### JWT Configuration Errors

**Problem**: JWT authentication not working

**Solutions**:

1. **Generate JWT keys**:
   ```powershell
   .\dev.ps1 shell
   php bin/console lexik:jwt:generate-keypair
   ```

2. **Check file permissions**:
   ```powershell
   .\dev.ps1 shell
   chmod 644 config/jwt/private.pem
   chmod 644 config/jwt/public.pem
   ```

### Environment Not Loading

**Problem**: `.env` changes not reflected

**Solutions**:

1. **Clear cache**:
   ```powershell
   .\dev.ps1 cache-clear
   ```

2. **Restart containers**:
   ```powershell
   .\dev.ps1 restart
   ```

## Security Best Practices

### ✅ DO:

- Use strong, random values for `APP_SECRET` and `JWT_PASSPHRASE`
- Keep `.env` out of version control (it's in `.gitignore`)
- Use different credentials for each environment
- Rotate secrets regularly in production
- Use environment variables in production (not `.env` files)

### ❌ DON'T:

- Commit `.env` to Git
- Use default/weak passwords in production
- Share production credentials in Slack/email
- Use production credentials in development
- Hardcode sensitive values in code

## Environment Variables Reference

| Variable | Default | Description |
|----------|---------|-------------|
| `APP_ENV` | `dev` | Application environment (dev/prod/test) |
| `APP_DEBUG` | `1` | Debug mode (1=on, 0=off) |
| `APP_SECRET` | (generated) | Symfony secret key |
| `DEFAULT_URI` | `http://localhost:8000` | Base application URL |
| `DATABASE_URL` | (see above) | Database connection string |
| `DB_HOST` | `database` | Database host |
| `DB_PORT` | `3306` | Database port |
| `DB_NAME` | `hidro_api` | Database name |
| `DB_USER` | `hidro_user` | Database user |
| `DB_PASSWORD` | `hidro_password` | Database password |
| `DB_ROOT_PASSWORD` | `rootpassword` | MySQL root password |
| `JWT_SECRET_KEY` | (path) | Path to JWT private key |
| `JWT_PUBLIC_KEY` | (path) | Path to JWT public key |
| `JWT_PASSPHRASE` | (custom) | JWT key passphrase |
| `JWT_TOKEN_TTL` | `3600` | Access token lifetime (seconds) |
| `JWT_REFRESH_TTL` | `604800` | Refresh token lifetime (seconds) |
| `JWT_AUDIENCE` | `https://hidro.local` | JWT audience claim |
| `JWT_ISSUER` | `https://hidro.local` | JWT issuer claim |

## Additional Resources

- [Symfony Environment Configuration](https://symfony.com/doc/current/configuration.html#configuration-environments)
- [Doctrine Database Configuration](https://symfony.com/doc/current/doctrine.html)
- [LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/2.x/Resources/doc/index.rst)
- [Docker Compose Environment Variables](https://docs.docker.com/compose/environment-variables/)

---

**Summary**: The `.env` file contains all necessary configuration for running the application with Docker. Database credentials match `docker-compose.yml`, and JWT keys need to be generated on first setup.
