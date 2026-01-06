# 📝 Comandos Útiles - Sistema de Gestión de Condominios

## 🚀 Instalación Inicial

### Instalación Rápida
```bash
./install.sh
```

### Instalación Manual Completa
```bash
# 1. Instalar dependencias
composer install
npm install

# 2. Configurar entorno
cp .env.example .env
php artisan key:generate

# 3. Base de datos
php artisan migrate
php artisan db:seed

# 4. Storage
php artisan storage:link

# 5. Compilar assets
npm run build
```

---

## 🗄️ Base de Datos

### Migraciones
```bash
# Ejecutar migraciones
php artisan migrate

# Ejecutar con fuerza (producción)
php artisan migrate --force

# Revertir última migración
php artisan migrate:rollback

# Refrescar base de datos (CUIDADO: borra todo)
php artisan migrate:fresh

# Refrescar y sembrar
php artisan migrate:fresh --seed
```

### Seeders
```bash
# Ejecutar todos los seeders
php artisan db:seed

# Ejecutar seeder específico
php artisan db:seed --class=DatabaseSeeder
```

### Verificar Estado
```bash
# Ver migraciones pendientes
php artisan migrate:status

# Ver estructura de tabla
php artisan db:show --table=usuarios
```

---

## 🎨 Frontend (Assets)

### Desarrollo
```bash
# Compilar y observar cambios
npm run dev

# En otra terminal, iniciar servidor
php artisan serve
```

### Producción
```bash
# Compilar para producción
npm run build

# Preview de producción
npm run preview
```

### Limpiar y Reinstalar
```bash
# Limpiar node_modules
rm -rf node_modules package-lock.json

# Reinstalar
npm install
```

---

## 🔧 Artisan Útiles

### Caché
```bash
# Limpiar todas las cachés
php artisan optimize:clear

# Limpiar cache de configuración
php artisan config:clear

# Limpiar cache de rutas
php artisan route:clear

# Limpiar cache de vistas
php artisan view:clear

# Crear cachés (producción)
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Storage
```bash
# Crear enlace simbólico
php artisan storage:link

# Ver información del sistema de archivos
php artisan storage:info
```

### Cola de Trabajos (Queue)
```bash
# Procesar trabajos en cola
php artisan queue:work

# Reiniciar workers después de cambios
php artisan queue:restart

# Ver trabajos fallidos
php artisan queue:failed
```

---

## 🌐 Servidor de Desarrollo

### Laravel Built-in Server
```bash
# Iniciar servidor (puerto 8000)
php artisan serve

# Iniciar en puerto específico
php artisan serve --port=8080

# Iniciar en host específico
php artisan serve --host=0.0.0.0
```

### Con Vite Dev Server
```bash
# Terminal 1: Vite (assets)
npm run dev

# Terminal 2: Laravel (backend)
php artisan serve
```

---

## 🔍 Debugging y Logs

### Ver Logs
```bash
# Seguir logs en tiempo real
tail -f storage/logs/laravel.log

# Ver últimas 50 líneas
tail -n 50 storage/logs/laravel.log

# Limpiar logs
> storage/logs/laravel.log
```

### Tinker (REPL de Laravel)
```bash
# Iniciar consola interactiva
php artisan tinker

# Ejemplos dentro de tinker:
User::count()
Vivienda::with('residentes')->first()
Aporte::where('estado', 'VENCIDO')->get()
```

### Modo Debug
```bash
# Activar debug en .env
APP_DEBUG=true
APP_ENV=local
```

---

## 🧪 Testing

### PHPUnit
```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar tests específicos
php artisan test --filter=UserTest

# Con cobertura
php artisan test --coverage
```

---

## 📦 Gestión de Dependencias

### Composer
```bash
# Actualizar dependencias
composer update

# Instalar dependencia específica
composer require vendor/package

# Eliminar dependencia
composer remove vendor/package

# Optimizar autoload
composer dump-autoload -o
```

### NPM
```bash
# Actualizar dependencias
npm update

# Instalar dependencia
npm install package-name

# Instalar dev dependency
npm install --save-dev package-name

# Ver paquetes desactualizados
npm outdated
```

---

## 👥 Usuarios y Roles

### Crear Usuario (Tinker)
```bash
php artisan tinker

# Crear administrador
$user = User::create([
    'name' => 'Nuevo Admin',
    'email' => 'admin2@example.com',
    'password' => bcrypt('password123'),
    'rol' => 'ADMINISTRADOR',
    'activo' => true
]);
```

---

## 💾 Backup y Restore

### Backup Manual
```bash
# Backup de base de datos PostgreSQL
pg_dump -U grupo30sa db_grupo30sa > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup con compresión
pg_dump -U grupo30sa db_grupo30sa | gzip > backup_$(date +%Y%m%d_%H%M%S).sql.gz

# Backup de archivos
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/app/public/
```

### Restore
```bash
# Restore de base de datos
psql -U grupo30sa db_grupo30sa < backup_20260105.sql

# Restore de archivos
tar -xzf storage_backup_20260105.tar.gz
```

---

## 🔒 Seguridad

### Generar Nueva Key
```bash
php artisan key:generate
```

### Limpiar Sesiones
```bash
php artisan session:flush
```

### Ver Rutas Protegidas
```bash
# Listar todas las rutas
php artisan route:list

# Filtrar por middleware
php artisan route:list --middleware=auth

# Filtrar por nombre
php artisan route:list --name=aportes
```

---

## 📊 Performance

### Optimizar para Producción
```bash
# Script completo de optimización
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Ver Performance
```bash
# Ver tiempo de ejecución de rutas
php artisan route:cache --timing
```

---

## 🐛 Solución de Problemas

### Limpiar Todo
```bash
# Limpieza completa del sistema
php artisan optimize:clear
composer dump-autoload
npm run build
php artisan storage:link
```

### Permisos
```bash
# Corregir permisos de storage
sudo chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Para desarrollo local
sudo chown -R $USER:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

### Regenerar Autoload
```bash
composer dump-autoload
```

---

## 📱 Accesos Rápidos

### URLs Importantes
```
Página Principal: http://localhost:8000
Login: http://localhost:8000/login
Dashboard: http://localhost:8000/dashboard
Residentes: http://localhost:8000/residentes
Viviendas: http://localhost:8000/viviendas (vista mapa)
Actividades: http://localhost:8000/actividades (tablero público)
Aportes: http://localhost:8000/aportes
Reuniones: http://localhost:8000/reuniones
Comunicaciones: http://localhost:8000/comunicaciones
```

---

## 🎯 Comandos Personalizados

### Actualizar Moras (Manual)
```bash
php artisan tinker
\App\Services\MoraService::actualizarMorasVencidas();
```

### Ver Estadísticas
```bash
php artisan tinker
$stats = \App\Services\MoraService::obtenerEstadisticasMora();
dd($stats);
```

---

## 📝 Git

### Comandos Básicos
```bash
# Ver estado
git status

# Agregar cambios
git add .

# Commit
git commit -m "Descripción del cambio"

# Push
git push origin main

# Ver historial
git log --oneline

# Crear rama
git checkout -b nombre-rama
```

---

## 🔄 Actualización del Sistema

### Actualizar Laravel
```bash
# Actualizar todas las dependencias
composer update

# Solo Laravel
composer update laravel/framework
```

### Actualizar Vue/Vite
```bash
npm update
```

---

## 💡 Tips

### Desarrollo Rápido
```bash
# Iniciar todo en un comando
php artisan serve & npm run dev
```

### Ver Consultas SQL
```bash
# En .env
DB_LOG=true

# O en código específico:
\DB::enableQueryLog();
// ... tu código ...
dd(\DB::getQueryLog());
```

### Verificar Instalación
```bash
php artisan about
php artisan env
```

---

## 📞 Comandos de Ayuda

### Ver Ayuda de Comando
```bash
# Ayuda general
php artisan

# Ayuda de comando específico
php artisan help migrate
php artisan help make:controller
```

### Listar Comandos Disponibles
```bash
php artisan list
```

---

**Nota:** Todos estos comandos deben ejecutarse desde el directorio raíz del proyecto.
