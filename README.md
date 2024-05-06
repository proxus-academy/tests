- **Crear la instancia**: Ve a AWS Lightsail y crea una nueva instancia. Selecciona la pila de LAMP (Apache, MySQL/MariaDB, PHP) proporcionada por Bitnami.
- **Conectar a la instancia**: Una vez creada, usa SSH para conectarte a tu instancia. AWS Lightsail proporciona un botón de conexión rápida mediante su propia interfaz web, o puedes usar tu propio cliente SSH con las claves privadas descargadas.


* Ver la contraseña del usuario sudo del servidor
```bash
cat bitnami_credentials
```

* Instalar git si es necesario

```bash
sudo apt install git
```


- **Configuración de la base de datos**: Bitnami LAMP viene con MariaDB instalada. Necesitarás crear una base de datos y un usuario para tu aplicación. Puedes hacer esto usando phpMyAdmin (incluido en Bitnami LAMP) o la línea de comandos. Aquí está el comando para crear una base de datos y un usuario:

```bash
/opt/bitnami/mariadb/bin/mariadb -u root -p
CREATE DATABASE flexam;
CREATE USER 'flexam'@'localhost' IDENTIFIED BY 'Flexam24';
GRANT ALL PRIVILEGES ON flexam.* TO 'flexam'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

* Descargar archivos php
```
git clone https://github.com/proxus-academy/tests.git
cd tests
unzip tests_proxus.zip
cd
```
* Modificar el archivo config.php
```php
<?php define('DB_NAME', 'flexam');
    // Entorno de desarrollo local
define('DB_HOST', 'localhost');
define('DB_USER', 'flexam');
define('DB_PASSWORD', 'Flexam24');
define('BASE_URL', 'https://proxustools.com/');
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/');
define('COMMON_PATH', BASE_PATH . 'code/comun/');
define('VIEWS_PATH',
BASE_PATH . 'code/views/');
// Definir otras rutas relativas a BASE_URL si es necesario
define('STYLES_URL', BASE_URL . 'styles/');
define('RESOURCES_URL', BASE_URL . 'resources/');
?>
```
* Configurar la base de datos con la info inicial

```
/opt/bitnami/mariadb/bin/mariadb -u root -p flexam < tests/tests_proxus/includes/BBDD/test_prueba_fal.sql
```

* Mover los archivos a la ruta de apache
```
 cp -r tests/tests_proxus/* /opt/bitnami/apache2/htdocs/

```

* Reiniciar servicios
```
sudo /opt/bitnami/ctlscript.sh restart apache
sudo /opt/bitnami/ctlscript.sh restart mariadb

```

* Eliminar la página por defecto
``` 
 rm /opt/bitnami/apache/htdocs/index.html
``` 


## Actualiza la ultima versión
1. Subir el zip a github
2. Actualizar el repositorio en el servidor
```bash
cd tests
git pull
```
3. descomprimir el zip
```bash
unzip tests_proxus.zip
```
4. Borrar archivos antiguos y cargar los nuevos
```bash
 cd
rm -rf /opt/bitnami/apache2/htdocs
 cp -r tests/tests_proxus/* /opt/bitnami/apache2/htdocs/
```
5. Borrar y volver a crear la base de datos
```bash
/opt/bitnami/mariadb/bin/mariadb -u root -p
drop DATABASE flexam;
CREATE DATABASE flexam;
CREATE USER 'flexam'@'localhost' IDENTIFIED BY 'Flexam24';
GRANT ALL PRIVILEGES ON flexam.* TO 'flexam'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

6. Configurar la base de datos con la info inicial

```bash
/opt/bitnami/mariadb/bin/mariadb -u root -p flexam < tests/tests_proxus/includes/BBDD/test_prueba_fal.sql
```
