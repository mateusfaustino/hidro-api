# Migration Guide - Enabling Hot Reload

## For Existing Projects

If you already have the containers running, follow these steps to enable hot reload.

## Step 1: Stop Existing Containers

```powershell
# Stop and remove containers
docker-compose down

# Optional: Remove the old volume (if you want a clean start)
docker volume rm hidro-api_app-data
```

## Step 2: Verify Changes

The following files should already be updated:

- ✅ `docker-compose.yml` - Volume mapping changed
- ✅ `Dockerfile` - Optimized for caching
- ✅ `.dockerignore` - Created
- ✅ `compose.dev.yaml` - Created
- ✅ `dev.ps1` - Created

You can verify by checking:

```powershell
# Check docker-compose.yml
cat docker-compose.yml | Select-String "volumes:" -Context 0,2
# Should show: - .:/var/www

# Check if dev.ps1 exists
Test-Path .\dev.ps1
# Should return: True
```

## Step 3: Rebuild Containers (One-Time)

```powershell
# Using the helper script (recommended)
.\dev.ps1 rebuild

# Or manually
docker-compose up -d --build
```

This will:
- Build new images with updated configuration
- Create containers with volume mapping
- Start all services

## Step 4: Install Dependencies

```powershell
# Using helper script
.\dev.ps1 composer install

# Or manually
docker-compose exec app composer install
```

## Step 5: Run Migrations (If Needed)

```powershell
# Using helper script
.\dev.ps1 migrate

# Or manually
docker-compose exec app php bin/console doctrine:migrations:migrate --no-interaction
```

## Step 6: Verify Hot Reload Works

### Test 1: Quick Change

1. Edit a controller file, for example:
   ```php
   // src/Controller/Api/V1/HelloWorldController.php
   return $this->json(['message' => 'Hot reload works!']);
   ```

2. Test immediately (no rebuild):
   ```powershell
   curl http://localhost:8000/api/v1/hello
   ```

3. You should see the new message immediately! ✅

### Test 2: Check Volume Mapping

```powershell
# Access container
.\dev.ps1 shell

# Create a test file
echo "test" > /var/www/test.txt
exit

# Check if file appears on your host
cat test.txt
# Should show: test

# Clean up
rm test.txt
```

If you see the file on your host, volume mapping is working! ✅

## Step 7: Update Your Workflow

### Old Workflow (Don't Do This Anymore)

```powershell
# ❌ Don't do this for code changes anymore
docker-compose up --build  # Too slow!
```

### New Workflow (Do This Instead)

```powershell
# ✅ Daily development
.\dev.ps1 start    # First time today
# Edit code
# Test immediately!

# ✅ Clear cache when needed
.\dev.ps1 cache-clear

# ✅ Only rebuild when truly necessary
.\dev.ps1 rebuild  # Only for Dockerfile/dependency changes
```

## Common Migration Issues

### Issue 1: Old Containers Still Running

**Symptoms**: Changes not reflecting

**Solution**:
```powershell
# Force remove all containers and volumes
docker-compose down -v
docker-compose up -d --build
.\dev.ps1 composer install
```

### Issue 2: Permission Errors

**Symptoms**: Cannot write to files in container

**Solution**:
```powershell
.\dev.ps1 shell
chown -R www-data:www-data /var/www
exit
```

### Issue 3: Vendor Directory Issues

**Symptoms**: Class not found errors

**Solution**:
```powershell
# Reinstall dependencies
.\dev.ps1 composer install

# Or with more memory
docker-compose exec app php -d memory_limit=-1 /usr/bin/composer install
```

### Issue 4: Cache Problems

**Symptoms**: Old routes/config still active

**Solution**:
```powershell
# Clear all caches
.\dev.ps1 cache-clear

# Or manually
docker-compose exec app php bin/console cache:clear
docker-compose exec app php bin/console cache:warmup
```

### Issue 5: Port Already in Use

**Symptoms**: Cannot start containers, port conflict

**Solution**:
```powershell
# Check what's using the ports
netstat -ano | findstr :8000
netstat -ano | findstr :3307

# Either:
# 1. Stop the conflicting service
# 2. Or change ports in docker-compose.yml:
#    ports:
#      - "8080:80"  # Change 8000 to 8080
```

## Verification Checklist

After migration, verify everything works:

- [ ] Containers start successfully: `.\dev.ps1 status`
- [ ] API responds: `curl http://localhost:8000/api/v1/hello`
- [ ] Database is accessible: `.\dev.ps1 shell` → `php bin/console doctrine:migrations:status`
- [ ] Hot reload works: Edit a file → Test → See changes immediately
- [ ] Composer works: `.\dev.ps1 composer --version`
- [ ] Cache clear works: `.\dev.ps1 cache-clear`
- [ ] Logs are accessible: `.\dev.ps1 logs`

## Rollback (If Needed)

If you need to go back to the old setup:

```powershell
# 1. Stop containers
docker-compose down

# 2. Restore old docker-compose.yml from git
git checkout HEAD~1 docker-compose.yml

# 3. Restore old Dockerfile
git checkout HEAD~1 Dockerfile

# 4. Rebuild
docker-compose up -d --build
```

## Performance Comparison

### Before Migration

```
Edit code → docker-compose up --build → 3-5 minutes → Test
```

### After Migration

```
Edit code → Test immediately (5 seconds)
```

**Time saved per iteration**: ~3-5 minutes
**Daily time saved** (20 iterations): ~1-1.5 hours! 🚀

## Best Practices After Migration

### 1. Use the Helper Script

```powershell
# Instead of docker-compose commands
.\dev.ps1 start
.\dev.ps1 stop
.\dev.ps1 logs
```

### 2. Clear Cache When Needed

```powershell
# After changing routes, config, or services
.\dev.ps1 cache-clear
```

### 3. Only Rebuild When Necessary

```powershell
# Only run this when:
# - Adding Composer packages
# - Modifying Dockerfile
# - Changing environment variables
.\dev.ps1 rebuild
```

### 4. Use WSL 2 (Recommended)

For best performance on Windows:
- Enable WSL 2
- Run Docker Desktop with WSL 2 backend
- Much faster file I/O

## Summary

✅ **Migration Complete!**

You now have:
- 🚀 Hot reload enabled
- ⚡ 36-60x faster development
- 🛠️ Convenient helper scripts
- 📚 Comprehensive documentation

**Next**: Start coding and enjoy instant feedback!

For questions, check:
- [`QUICK_START.md`](../QUICK_START.md) - Quick reference
- [`dev-docs/DOCKER_HOT_RELOAD.md`](DOCKER_HOT_RELOAD.md) - Full guide
- [`dev-docs/HOT_RELOAD_SUMMARY.md`](HOT_RELOAD_SUMMARY.md) - Summary

---

**Happy coding! 🎉**
