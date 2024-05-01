#!/bin/bash

# Definir las rutas base para el proyecto y el archivo SQL
PROJECT_DIR="$(dirname "$(realpath "$0")")"
SQL_PATH="$PROJECT_DIR/tests_proxus/includes/BBDD/test_prueba_fal.sql"

# Actualizar paquetes del sistema
sudo apt update && sudo apt upgrade -y

# Instalar Apache, MySQL, PHP y extensiones necesarias
sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql -y

# Asegurarse de que el servicio MySQL esté activo
sudo systemctl start mysql.service

# Esperar a que MySQL esté completamente operativo
while ! sudo mysqladmin ping --silent; do
    sleep 1
    echo "Esperando a que el servidor MySQL esté disponible..."
done

# Configurar MySQL
DB_NAME=test_database
DB_USER=root
DB_PASS=rooptsw

# Crear base de datos si no existe
sudo mysql -u root <<MYSQL_SCRIPT
CREATE DATABASE IF NOT EXISTS $DB_NAME;
MYSQL_SCRIPT

# Configurar contraseña para usuario existente
sudo mysql -u root <<MYSQL_SCRIPT
ALTER USER '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASS';
FLUSH PRIVILEGES;
MYSQL_SCRIPT

echo "Base de datos $DB_NAME y usuario $DB_USER configurados con éxito."

# Importar archivo SQL a la base de datos
if [ -f "$SQL_PATH" ]; then
    sudo mysql -u $DB_USER -p$DB_PASS $DB_NAME < $SQL_PATH
    echo "Archivo SQL importado correctamente."
else
    echo "Archivo SQL no encontrado."
fi

# Mover el proyecto al directorio de Apache
APACHE_PATH="/var/www/html/flexam"

# Si no existe el directorio en Apache, crearlo
sudo mkdir -p $APACHE_PATH

# Mover el contenido del subdirectorio 'tests_proxus' al directorio de Apache
sudo rsync -av --exclude 'launch.sh' $PROJECT_DIR/tests_proxus/ $APACHE_PATH/

# Ajustar permisos del directorio
sudo chown -R www-data:www-data $APACHE_PATH
sudo chmod -R 755 $APACHE_PATH

echo "Permisos de directorio configurados."

# Reiniciar Apache para aplicar cambios
sudo systemctl restart apache2

echo "Apache reiniciado. Tu proyecto ahora debería estar accesible en http://localhost/flexam/index.php"

