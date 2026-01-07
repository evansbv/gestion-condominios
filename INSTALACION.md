# Guía de Instalación - Sistema de Gestión de Condominios

## Requisitos Previos

- PHP >= 8.2
- Composer
- Node.js >= 18
- PostgreSQL >= 14
- NPM o Yarn

## Paso 1: Configuración de Base de Datos

1. Crear la base de datos en PostgreSQL:
```sql
CREATE DATABASE db_xxx;
CREATE USER xxx WITH PASSWORD 'xxx*';
GRANT ALL PRIVILEGES ON DATABASE db_xxx TO xxx;
```

2. Verificar la conexión en el archivo `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=db_xxx
DB_USERNAME=xxx
DB_PASSWORD=xxx*
```

## Paso 2: Instalación de Dependencias

### Backend (Laravel)
```bash
composer install
```

### Frontend (Vue + Inertia)
```bash
npm install
```

## Paso 3: Configuración del Entorno

1. Copiar el archivo de ejemplo (si no existe):
```bash
cp .env.example .env
```

2. Generar la clave de aplicación:
```bash
php artisan key:generate
```

3. Configurar el correo electrónico en `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=condominio@tecnoweb.org.bo
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=condominio@tecnoweb.org.bo
MAIL_FROM_NAME="Condominio Tecnoweb"
```

4. Configurar mora automática (opcional, valores por defecto):
```env
MORA_PORCENTAJE=5
MORA_DIAS_CALCULO=30
```

## Paso 4: Ejecutar Migraciones y Seeders

```bash
# Ejecutar migraciones
php artisan migrate

# Poblar base de datos con datos de prueba
php artisan db:seed
```

## Paso 5: Crear el Storage Link

```bash
php artisan storage:link
```

## Paso 6: Compilar Assets

### Desarrollo
```bash
npm run dev
```

### Producción
```bash
npm run build
```

## Paso 7: Iniciar el Servidor

```bash
php artisan serve
```

El sistema estará disponible en: http://localhost:8000

## Usuarios de Prueba

Después de ejecutar el seeder, podrá acceder con:

### Administrador
- **Email:** admin@tecnoweb.org.bo
- **Password:** admin123

### Miembro Directorio
- **Email:** directorio@tecnoweb.org.bo
- **Password:** directorio123

### Propietario
- **Email:** maria@example.com
- **Password:** propietario123

## Permisos de Archivos

Asegúrese de que los siguientes directorios tengan permisos de escritura:

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## Configuración de Producción

### Apache
Apuntar el DocumentRoot a la carpeta `public`:
```apache
DocumentRoot "/ruta/al/proyecto/public"
<Directory "/ruta/al/proyecto/public">
    AllowOverride All
    Require all granted
</Directory>
```

### Nginx
```nginx
server {
    listen 80;
    server_name www.tecnoweb.org.bo;
    root /ruta/al/proyecto/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Optimizaciones para Producción

```bash
# Optimizar composer
composer install --optimize-autoloader --no-dev

# Compilar assets para producción
npm run build

# Cachear configuración
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimizar autoload
composer dump-autoload --optimize
```

## Solución de Problemas

### Error: "No application encryption key has been specified"
```bash
php artisan key:generate
```

### Error de permisos en storage
```bash
sudo chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Error de conexión a base de datos
Verificar:
1. PostgreSQL está corriendo
2. Credenciales en `.env` son correctas
3. El usuario tiene permisos en la base de datos

### Assets no se cargan
```bash
npm run build
php artisan storage:link
```

## Mantenimiento

### Actualizar moras automáticamente (Cron)
Agregar a crontab:
```bash
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

### Backup de Base de Datos
```bash
pg_dump -U grupo30sa db_grupo30sa > backup_$(date +%Y%m%d).sql
```

### Logs
Los logs se encuentran en: `storage/logs/laravel.log`

## Soporte

Para reportar problemas o solicitar ayuda, contactar a:
- Email: condominio@tecnoweb.org.bo
- Host: www.tecnoweb.org.bo
