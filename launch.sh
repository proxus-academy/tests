#!/bin/bash

# Define base paths for the project and SQL file
PROJECT_DIR="$(dirname "$(realpath "$0")")"
SQL_PATH="$PROJECT_DIR/tests_proxus/includes/BBDD/test_prueba_fal.sql"

# Update system packages
sudo yum update -y

# Install Apache, MySQL, PHP and necessary extensions
sudo yum install httpd mariadb-server php php-mysqlnd -y

# Ensure the MySQL service is active
sudo systemctl start mariadb.service

# Wait until MySQL is fully operational
while ! sudo mysqladmin ping --silent; do
    sleep 1
    echo "Waiting for MySQL server to be available..."
done

# Configure MySQL
DB_NAME=test_database
DB_USER=root
DB_PASS=rooptsw

# Create database if it does not exist
sudo mysql -u root <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS $DB_NAME;
MYSQL_SCRIPT

# Set password for existing user using mysql_native_password
sudo mysql -u root <<MYSQL_SCRIPT
ALTER USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
FLUSH PRIVILEGES;
MYSQL_SCRIPT

echo "Database $DB_NAME and user $DB_USER configured successfully."

# Import SQL file into the database
if [ -f "$SQL_PATH" ]; then
    sudo mysql -u $DB_USER -p$DB_PASS $DB_NAME < $SQL_PATH
    echo "SQL file imported successfully."
else
    echo "SQL file not found."
fi

# Move the project to the Apache directory
APACHE_PATH="/var/www/html/flexam"

# If the Apache directory does not exist, create it
sudo mkdir -p $APACHE_PATH

# Move the contents of the 'tests_proxus' subdirectory to the Apache directory
sudo rsync -av --exclude 'launch.sh' $PROJECT_DIR/tests_proxus/ $APACHE_PATH/

# Adjust permissions for the directory
sudo chown -R apache:apache $APACHE_PATH
sudo chmod -R 755 $APACHE_PATH

echo "Directory permissions configured."

# Restart Apache to apply changes
sudo systemctl restart httpd

echo "Apache restarted. Your project should now be accessible at http://localhost/flexam/index.php"
