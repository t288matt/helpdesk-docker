# VATPAC Helpdesk Docker Setup

This directory contains the Docker configuration for the VATPAC Helpdesk.

## Files

- `Dockerfile` - Container definition with PHP 8.0, Apache, and MariaDB.
- `docker-compose.yml` - Service orchestration and volume mounts
- `docker-entrypoint.sh` - Startup script for database and web server
- `.dockerignore` - Files to exclude from Docker build context

## Usage

From the parent directory (helpdesk/):

```bash
# Build the container
docker-compose -f docker/docker-compose.yml build

# Start the services
docker-compose -f docker/docker-compose.yml up -d

# Stop the services
docker-compose -f docker/docker-compose.yml down

# View logs
docker-compose -f docker/docker-compose.yml logs
```

## Access

- **Web Application**: http://localhost:80
- **Database**: localhost:3306 (user: helpdesk, password: helpdesk123)

## Volumes

- `../uploads/` - File uploads and attachments
- `../logs/` - Application logs
- `../mysql_data/` - Database files
