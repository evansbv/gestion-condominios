# Sistema de Gestión de Condominios

## Descripción
Sistema web completo para la administración integral de condominios residenciales, desarrollado con Laravel 12 + Vue 3 + Inertia.js + PostgreSQL.

## Configuración del Proyecto

### Base de Datos
- **Host**: www.tecnoweb.org.bo
- **Base de datos**: db_xxx
- **Usuario**: xxx
- **Contraseña**: xxx*
- **Motor**: xxx

### Correo Institucional
- **Email**: condominio@tecnoweb.org.bo
- **Host SMTP**: smtp.tecnoweb.org.bo
- **Puerto**: 587

## Arquitectura del Sistema

### Backend (Laravel 12)
- **Modelos Eloquent**:
  - User (con roles RBAC)
  - Residente
  - Vivienda (con coordenadas GPS)
  - Reunion
  - ParticipanteReunion
  - Actividad
  - Aporte (con cálculo automático de mora)
  - Comunicacion
  - DestinatarioComunicacion

- **Servicios**:
  - `MoraService`: Cálculo y gestión de moras
  - `EmailService`: Envío de notificaciones por correo

- **Middleware**:
  - `CheckRole`: Control de acceso basado en roles (RBAC)

### Frontend (Vue 3 + Inertia.js)
- **Framework UI**: Tailwind CSS v4
- **Manejo de Estado**: Pinia
- **Mapas**: Leaflet + Vue-Leaflet (OpenStreetMap)

## Roles del Sistema (RBAC)

1. **ADMINISTRADOR**: Acceso completo al sistema
2. **MIEMBRO_DIRECTORIO**: Gestión de reuniones, actividades y comunicaciones
3. **PROPIETARIO**: Visualización de aportes, pagos y participación en reuniones
4. **INQUILINO**: Visualización limitada de información
5. **RESIDENTE**: Acceso básico a información general

## Funcionalidades Principales

### 1. Gestión de Residentes
- Registro completo de datos personales
- Subida de fotografías
- Asociación obligatoria con vivienda
- Tipos: Propietario, Inquilino, Familiar

### 2. Gestión de Viviendas
- Ubicación GPS (latitud/longitud)
- Visualización en mapa interactivo (OpenStreetMap)
- Información detallada (área, tipo, número de habitantes)

### 3. Gestión de Reuniones
- Creación de orden del día
- Registro de participantes y asistencia
- Generación de actas digitales
- Sistema de representación

### 4. Actividades de Mantenimiento
- Tipos: Churrasquera, Aceras, Calles, Jardines, etc.
- Fechas de inicio y fin obligatorias
- Presupuesto aprobado y ejecutado
- Porcentaje de avance
- Estados: Planificada, En Progreso, Completada, Cancelada

### 5. Sistema de Aportes
- Cálculo automático por vivienda
- Fecha de vencimiento
- **Cálculo automático de mora**:
  - Porcentaje configurable (default: 5%)
  - Período de cálculo configurable (default: 30 días)
  - Estados: Pendiente, Pagado, Vencido, Parcial

### 6. Tablero de Actividades
- Visible para todos los residentes
- Información en tiempo real
- Transparencia en costos y avances

### 7. Sistema de Comunicaciones
- Tipos: Convocatorias, Comunicados, Reclamos, Recomendaciones
- Envío por correo institucional
- Notificaciones internas
- Gestión de destinatarios y lectura

## Instalación

```bash
# Instalar dependencias de PHP
composer install

# Instalar dependencias de Node.js
npm install

# Copiar archivo de entorno
cp .env.example .env

# Configurar variables de entorno en .env
# (Ya configurado con los datos proporcionados)

# Generar key de aplicación
php artisan key:generate

# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders (para datos de prueba)
php artisan db:seed

# Compilar assets
npm run build

# Para desarrollo
npm run dev
```

## Comandos Útiles

### Actualizar moras vencidas
```bash
php artisan app:actualizar-moras
```

### Enviar notificaciones de pagos vencidos
```bash
php artisan app:notificar-pagos-vencidos
```

## Estructura de Directorios

```
gestion-condominios/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ResidenteController.php
│   │   │   ├── ViviendaController.php
│   │   │   ├── ReunionController.php
│   │   │   ├── ActividadController.php
│   │   │   ├── AporteController.php
│   │   │   └── ComunicacionController.php
│   │   └── Middleware/
│   │       ├── CheckRole.php
│   │       └── HandleInertiaRequests.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Residente.php
│   │   ├── Vivienda.php
│   │   ├── Reunion.php
│   │   ├── ParticipanteReunion.php
│   │   ├── Actividad.php
│   │   ├── Aporte.php
│   │   ├── Comunicacion.php
│   │   └── DestinatarioComunicacion.php
│   └── Services/
│       ├── MoraService.php
│       └── EmailService.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── js/
│   │   ├── Pages/
│   │   │   ├── Auth/
│   │   │   ├── Dashboard.vue
│   │   │   ├── Residentes/
│   │   │   ├── Viviendas/
│   │   │   ├── Reuniones/
│   │   │   ├── Actividades/
│   │   │   ├── Aportes/
│   │   │   └── Comunicaciones/
│   │   ├── Components/
│   │   ├── Layouts/
│   │   └── app.js
│   └── views/
│       └── app.blade.php
├── routes/
│   ├── web.php
│   └── console.php
└── .env
```

## Seguridad (OWASP)

El sistema implementa las siguientes medidas de seguridad:

1. **Autenticación segura** con hash de contraseñas
2. **Control de acceso basado en roles (RBAC)**
3. **Protección CSRF** (Laravel)
4. **Validación de datos** en todos los formularios
5. **SQL Injection Prevention** (Eloquent ORM)
6. **XSS Protection** (Vue.js escaping automático)
7. **Rate Limiting** en rutas de API
8. **Headers de seguridad** (Helmet middleware)

## Próximos Pasos

1. Completar todas las vistas Vue
2. Implementar tests unitarios y de integración
3. Configurar el servidor de producción
4. Implementar sistema de backup automático
5. Agregar reportes en PDF
6. Implementar dashboard con gráficas

## Soporte

Para reportar problemas o sugerencias, contactar al equipo de desarrollo.

## Licencia

Propietario - Evans Balcazar Veizaga
