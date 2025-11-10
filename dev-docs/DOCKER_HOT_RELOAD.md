# Docker Hot Reload Configuration

## Overview

The Docker setup now supports **hot reload** for development, meaning you can edit your code on your host machine and see changes immediately reflected in the container without rebuilding.

## How It Works

### Volume Mapping

The `docker-compose.yml` has been configured with bind mounts:

```yaml
volumes:
  - .:/var/www              # Maps your local code to the container
  - /var/www/vendor         # Keeps vendor separate (faster performance)
```

### What This Means

- ✅ **Changes to PHP files** are immediately available in the container
- ✅ **No rebuild needed** for code changes
- ✅ **Faster development cycle**
- ⚠️ **Vendor dependencies** stay in the container (better performance on Windows)

## Usage

### First Time Setup

```powershell
# Build and start containers
docker-compose up -d --build

# Install dependencies (first time only)
docker-compose exec app composer install
```

### Daily Development

```powershell
# Start containers (no rebuild needed)
docker-compose up -d

# Your code changes will automatically be reflected!
```

### When to Rebuild

You only need to rebuild when:

1. **Dockerfile changes** (new PHP extensions, system packages)
2. **Composer dependencies added/updated** (new packages in composer.json)
3. **Environment variables changed**

```powershell
# Rebuild when needed
docker-compose up -d --build
```

## Development Workflow

### 1. Edit Code Locally
Edit any PHP file in your IDE (VSCode, PHPStorm, etc.)

### 2. Test Immediately
The changes are instantly available:
```powershell
# Test your API
curl http://localhost:8000/api/v1/hello
```

### 3. Clear Cache if Needed
```powershell
# Clear Symfony cache if routes/config changed
docker-compose exec app php bin/console cache:clear
```

## Managing Dependencies

### Adding New Composer Packages

```powershell
# Option 1: Run composer inside container
docker-compose exec app composer require vendor/package

# Option 2: Run on host (if you have PHP/Composer locally)
composer require vendor/package
docker-compose restart app
```

### Installing Dependencies

```powershell
# Install all dependencies
docker-compose exec app composer install

# Update dependencies
docker-compose exec app composer update
```

## Advanced: Development Mode

For even better development experience, use the development compose file:

```powershell
# Start with development settings
docker-compose -f docker-compose.yml -f compose.dev.yaml up -d

# This automatically runs composer install on startup
```

## Troubleshooting

### Changes Not Reflecting?

1. **Clear Symfony cache**:
   ```powershell
   docker-compose exec app php bin/console cache:clear
   ```

2. **Restart PHP-FPM**:
   ```powershell
   docker-compose restart app
   ```

3. **Check file permissions**:
   ```powershell
   docker-compose exec app chown -R www-data:www-data /var/www
   ```

### Slow Performance on Windows?

The volume mapping keeps `/vendor` separate for performance. If still slow:

1. Consider using WSL2 for better I/O performance
2. Use the `compose.dev.yaml` which adds more volume exclusions

### Composer Install Fails?

```powershell
# Run with more memory
docker-compose exec app php -d memory_limit=-1 /usr/bin/composer install
```

## Performance Tips

1. **Use WSL2** on Windows for better Docker performance
2. **Exclude vendor** from antivirus scans
3. **Use development compose file** for auto-dependency installation
4. **Keep cache/logs** in separate volumes for better performance

## File Structure

```
your-project/
├── src/                    # ✅ Hot reload enabled
├── config/                 # ✅ Hot reload enabled  
├── public/                 # ✅ Hot reload enabled
├── templates/              # ✅ Hot reload enabled
├── vendor/                 # ⚠️  Lives in container (for performance)
├── var/cache/              # ⚠️  Lives in container (optional)
└── var/log/                # ⚠️  Lives in container (optional)
```

## Summary

**Before**: Edit code → `docker-compose up --build` → Wait 2-5 minutes → Test

**After**: Edit code → Test immediately! 🚀

No more waiting for rebuilds during development!
