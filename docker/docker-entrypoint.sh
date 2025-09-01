#!/bin/bash
set -e

# Initialize MariaDB if not already done
if [ ! -d "/var/lib/mysql/mysql" ]; then
    echo "Initializing MariaDB..."
    mysql_install_db --user=mysql --datadir=/var/lib/mysql
fi

# Start MariaDB service
echo "Starting MariaDB..."
service mariadb start

# Wait for MariaDB to be ready
echo "Waiting for MariaDB to be ready..."
while ! mysqladmin ping -h"localhost" --silent; do
    sleep 1
done
echo "MariaDB is ready!"

# Create database and user if they don't exist
mysql -e "CREATE DATABASE IF NOT EXISTS helpdesk;"
mysql -e "CREATE USER IF NOT EXISTS 'helpdesk'@'localhost' IDENTIFIED BY 'helpdesk123';"
mysql -e "GRANT ALL PRIVILEGES ON helpdesk.* TO 'helpdesk'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

# Start Apache in foreground
echo "Starting Apache..."
apache2-foreground
