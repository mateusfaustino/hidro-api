# Development Scripts

This folder contains Windows batch scripts to help you run PHP and Symfony commands from your Laragon installation.

## Scripts Included

1. **run-php.bat** - Run PHP commands from your Laragon installation
2. **setup-dev-env.bat** - Temporarily add PHP to your PATH for the current session
3. **add-php-to-path.bat** - Permanently add PHP to your system PATH (requires Administrator privileges)
4. **symfony.bat** - Run Symfony console commands

## Usage Instructions

### 1. run-php.bat
```cmd
run-php.bat -v
run-php.bat your-script.php
```

### 2. setup-dev-env.bat
Double-click to run or execute from command line:
```cmd
setup-dev-env.bat
```

### 3. add-php-to-path.bat
Right-click and "Run as administrator":
```cmd
add-php-to-path.bat
```

### 4. symfony.bat
Run from your Symfony project root:
```cmd
symfony.bat list
symfony.bat cache:clear
```

## Installation

1. Create a dedicated folder for development scripts (e.g., C:\dev-scripts)
2. Move all .bat files to this folder
3. (Optional) Run add-php-to-path.bat as administrator to permanently add PHP to your PATH
4. (Alternative) Run setup-dev-env.bat before each development session

## Notes

- All scripts are configured to use PHP from: `C:\laragon\bin\php\php-8.2.14--b`
- If your PHP installation is in a different location, update the PHP_PATH variable in each script
- The symfony.bat script must be run from your Symfony project root directory