# Development Environment

This directory contains scripts and configuration for local development.

## Quick Start

Start the development environment:
```bash
./bin-dev/start
```

Reset the database and start fresh:
```bash
./bin-dev/start --reset
```

## What It Does

The `start` script will:
1. Check for required dependencies (PHP 8.4, Composer, Docker)
2. Start MariaDB in a Docker container
3. Create `.env` file if it doesn't exist
4. Install Composer dependencies
5. Run database migrations
6. Start PHP built-in development server on http://127.0.0.1:8080

## Stopping

Press `Ctrl+C` to gracefully stop all services (MariaDB container and PHP server).

## Architecture

### Development vs Production

**Development** (`./bin-dev/start`):
- MariaDB only in Docker
- PHP runs on host machine (better for debugging/xdebug)
- Uses PHP built-in server
- Fast startup, lightweight

**Production/Testing** (`docker compose up`):
- Full containerized stack (PHP + Apache + MariaDB)
- Uses production Dockerfile
- Good for testing production-like environment

## Database Access

When using the development environment, you can connect to MariaDB:
- **Host**: 127.0.0.1
- **Port**: 3306
- **Database**: journal
- **Username**: journal
- **Password**: journal

## Troubleshooting

**Port 8080 already in use:**
Edit `bin-dev/start` and change the `PHP_SERVER_PORT` variable.

**Port 3306 already in use:**
Stop your local MySQL/MariaDB service:
```bash
brew services stop mysql
brew services stop mariadb
```

**Migrations failing:**
Try resetting the database:
```bash
./bin-dev/start --reset
```
