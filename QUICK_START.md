# 🚀 Quick Start - Hidro API

## First Time Setup

### 1. Start Docker Containers

```powershell
# Using the helper script (recommended)
.\dev.ps1 start

# Or manually
docker-compose up -d --build
```

### 2. Install Dependencies

```powershell
.\dev.ps1 composer install
```

### 3. Run Migrations

```powershell
.\dev.ps1 migrate
```

### 4. Test the API

```powershell
# Check health
curl http://localhost:8000/api/v1/health

# Test endpoint
curl http://localhost:8000/api/v1/hello
```

## Daily Development

### Start Your Day

```powershell
# Just start - no rebuild needed!
.\dev.ps1 start
```

### Edit Code

1. Open your favorite editor (VSCode, PHPStorm, etc.)
2. Edit any PHP file
3. **Changes are immediately available!** ✨
4. Test your API

### Common Tasks

```powershell
# Clear cache (when changing config/routes)
.\dev.ps1 cache-clear

# View logs
.\dev.ps1 logs

# Run tests
.\dev.ps1 test

# Access container shell
.\dev.ps1 shell
```

### Stop Working

```powershell
.\dev.ps1 stop
```

## Hot Reload in Action

### Example Workflow

1. **Edit a controller:**
   ```php
   // src/Controller/Api/V1/HelloWorldController.php
   return $this->json(['message' => 'Hello, new world!']);
   ```

2. **Test immediately** (no rebuild needed!):
   ```powershell
   curl http://localhost:8000/api/v1/hello
   # {"message":"Hello, new world!"}
   ```

3. **Done!** 🎉

## When You Need to Rebuild

Only rebuild when you:

- ✅ Install new Composer packages
- ✅ Modify Dockerfile
- ✅ Change environment variables

```powershell
.\dev.ps1 rebuild
```

## Authentication Flow

### 1. Create a User

```powershell
# Access the container
.\dev.ps1 shell

# Run the create user command
php bin/console app:create-user admin@example.com password123 ROLE_ADMIN
exit
```

### 2. Login

```powershell
curl -X POST http://localhost:8000/api/v1/auth/login `
  -H "Content-Type: application/json" `
  -d '{"email":"admin@example.com","password":"password123"}'
```

Response:
```json
{
  "access_token": "eyJ0eXAi...",
  "refresh_token": "abc123...",
  "expires_in": 3600
}
```

### 3. Use Protected Endpoints

```powershell
curl http://localhost:8000/api/v1/protected `
  -H "Authorization: Bearer eyJ0eXAi..."
```

## Helpful Tips

### Check Container Status

```powershell
.\dev.ps1 status
```

### View Real-time Logs

```powershell
.\dev.ps1 logs
# Press Ctrl+C to exit
```

### Clear Cache

```powershell
# If routes or config changes don't reflect
.\dev.ps1 cache-clear
```

### Run Specific Composer Commands

```powershell
# Examples
.\dev.ps1 composer require symfony/mailer
.\dev.ps1 composer update
.\dev.ps1 composer dump-autoload
```

## Need Help?

```powershell
# Show all available commands
.\dev.ps1 help
```

## Documentation

- 📖 **Full Docker Guide**: [`dev-docs/DOCKER_HOT_RELOAD.md`](dev-docs/DOCKER_HOT_RELOAD.md)
- 📖 **API Documentation**: [`README.md`](README.md)
- 📖 **Architecture**: [`dev-docs/ARCHITECTURE_SUMMARY.md`](dev-docs/ARCHITECTURE_SUMMARY.md)
- 📖 **JWT Auth**: [`dev-docs/JWT_AUTH_IMPLEMENTATION_COMPLETE.md`](dev-docs/JWT_AUTH_IMPLEMENTATION_COMPLETE.md)

## Summary

**Before Hot Reload:**
```
Edit code → docker-compose up --build → Wait 2-5 minutes → Test
```

**After Hot Reload:**
```
Edit code → Test immediately! 🚀
```

No more waiting for rebuilds during development!
