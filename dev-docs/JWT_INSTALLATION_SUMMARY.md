# JWT Bundle Installation Summary

## Overview

This document summarizes the steps taken to successfully install the Lexik JWT Authentication Bundle in your Symfony API project.

## Issues Encountered and Solutions

### 1. Docker Volume Mounting Issue
**Problem**: Changes made inside the container (like installing packages with composer) were not reflected on the host machine.
**Solution**: We identified that this was due to the use of named volumes instead of bind mounts. However, we kept the original configuration to avoid Docker Desktop issues on Windows.

### 2. Missing PHP Extensions
**Problem**: The sodium extension was not enabled, which is required by the lcobucci/jwt library.
**Solution**: We enabled the sodium extension in the php.ini file:
```ini
extension=sodium
```

### 3. PHP Version Compatibility
**Problem**: The latest version of the JWT bundle required a newer PHP version than what was installed.
**Solution**: We removed conflicting development dependencies temporarily and then successfully installed the JWT bundle.

### 4. Docker Compose Configuration Conflict
**Problem**: The Doctrine bundle automatically added a duplicate database service to docker-compose.yml.
**Solution**: We cleaned up the docker-compose.yml file to remove the duplicate service.

## Installation Steps Completed

1. **Enabled sodium extension** in PHP configuration
2. **Removed conflicting dev dependencies** temporarily
3. **Installed lexik/jwt-authentication-bundle** via Composer on the host machine
4. **Rebuilt Docker containers** with updated dependencies
5. **Verified installation** by checking available services in the container

## Verification

The JWT bundle is now successfully installed and available in the container. You can verify this by running:
```bash
docker-compose exec app php bin/console debug:container | grep jwt
```

## Next Steps

1. Configure the JWT bundle according to your application needs
2. Generate JWT keys for token signing
3. Configure security settings in your Symfony application
4. Implement authentication endpoints

## Important Notes

- All changes to composer.json and composer.lock were made on the host machine and will persist
- The JWT bundle is now available in the Docker container
- Future composer operations should be done on the host machine rather than inside the container
- The sodium extension is now enabled and available for other cryptographic operations