# Sistema de Gestión de Condominios

Sistema web completo para la gestión integral de condominios, desarrollado con Laravel 12, Vue.js 3 e Inertia.js.

## 🌟 Características Principales

### Gestión de Usuarios y RBAC
- **5 Roles de Usuario:**
  - Administrador
  - Miembro del Directorio
  - Propietario
  - Residente
  - Inquilino

- Control de acceso basado en roles (RBAC)
- Autenticación segura con Laravel Sanctum
- Gestión de sesiones y permisos

### Módulo de Residentes
- ✅ CRUD completo de residentes
- ✅ Carga de fotografías
- ✅ Asociación con viviendas
- ✅ Gestión de tipos (Propietario, Inquilino, Familiar)
- ✅ Búsqueda y filtros avanzados
- ✅ Historial de participación en reuniones

### Módulo de Viviendas
- ✅ CRUD completo de viviendas
- ✅ **Ubicación GPS (latitud/longitud)**
- ✅ **Mapa interactivo con Leaflet + OpenStreetMap**
- ✅ Vista de lista y vista de mapa
- ✅ Gestión de residentes por vivienda
- ✅ Resumen de deuda por vivienda
- ✅ Soft delete

### Módulo de Actividades
- ✅ CRUD completo de actividades de mantenimiento
- ✅ Tipos: Churrasquera, Aceras, Calles, Jardinería, Seguridad, Otro
- ✅ **Tablero público visible para todos**
- ✅ Estados: Planificada, En Progreso, Completada, Cancelada
- ✅ Gestión de presupuesto (aprobado vs ejecutado)
- ✅ Porcentaje de avance
- ✅ Asignación de responsables
- ✅ **Generación automática de aportes para todas las viviendas**

### Módulo de Aportes Económicos
- ✅ CRUD completo de aportes
- ✅ **Cálculo automático de mora (5% cada 30 días configurable)**
- ✅ Registro de pagos con múltiples métodos
- ✅ Carga de comprobantes (PDF, imágenes)
- ✅ Estados: Pendiente, Pagado, Vencido, Parcial
- ✅ Historial completo de pagos
- ✅ Resumen de deuda por vivienda
- ✅ Estadísticas generales
- ✅ **Notificaciones automáticas por email**

### Módulo de Reuniones
- ✅ CRUD completo de reuniones
- ✅ Estados: Convocada, Realizada, Cancelada
- ✅ **Convocatorias por email automáticas**
- ✅ Registro de participantes y asistencia
- ✅ Orden del día
- ✅ Actas digitales
- ✅ Registro de acuerdos con responsables
- ✅ Calendario de reuniones
- ✅ Filtros por año y estado

### Módulo de Comunicaciones
- ✅ CRUD completo de comunicaciones
- ✅ Tipos: Comunicado, Convocatoria, Queja, Recomendación
- ✅ Prioridades: Baja, Media, Alta, Urgente
- ✅ Sistema de borradores
- ✅ **Envío por email**
- ✅ Archivos adjuntos
- ✅ **Tracking de lectura** (leído/no leído)
- ✅ Tasa de lectura por comunicación
- ✅ Estadísticas generales

### Dashboard
- ✅ Estadísticas generales del condominio
- ✅ Resumen de deuda personalizado
- ✅ Reuniones próximas
- ✅ Comunicaciones recientes
- ✅ Estadísticas de mora

## 🛠️ Tecnologías

### Backend
- **Laravel 12** - Framework PHP
- **PostgreSQL** - Base de datos
- **Laravel Sanctum** - Autenticación API
- **Eloquent ORM** - Gestión de base de datos

### Frontend
- **Vue.js 3** - Framework JavaScript
- **Inertia.js** - SPA sin API
- **Tailwind CSS v4** - Framework CSS
- **Leaflet** - Mapas interactivos
- **Pinia** - State management

### Servicios
- **MoraService** - Cálculo automático de moras
- **EmailService** - Notificaciones por email
- **Middleware CheckRole** - Control de acceso RBAC

## 📁 Estructura del Proyecto

```
gestion-condominios/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ResidenteController.php
│   │   │   ├── ViviendaController.php
│   │   │   ├── ActividadController.php
│   │   │   ├── AporteController.php
│   │   │   ├── ReunionController.php
│   │   │   └── ComunicacionController.php
│   │   └── Middleware/
│   │       └── CheckRole.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Residente.php
│   │   ├── Vivienda.php
│   │   ├── Actividad.php
│   │   ├── Aporte.php
│   │   ├── Pago.php
│   │   ├── Reunion.php
│   │   └── Comunicacion.php
│   └── Services/
│       ├── MoraService.php
│       └── EmailService.php
├── database/
│   ├── migrations/
│   │   ├── 2026_01_06_002041_add_role_to_users_table.php
│   │   ├── 2026_01_06_002058_create_viviendas_table.php
│   │   ├── 2026_01_06_002058_create_residentes_table.php
│   │   ├── 2026_01_06_002058_create_reuniones_table.php
│   │   ├── 2026_01_06_002059_create_actividades_table.php
│   │   ├── 2026_01_06_002059_create_aportes_table.php
│   │   ├── 2026_01_06_002059_create_pagos_table.php
│   │   ├── 2026_01_06_002059_create_participacion_reunion_table.php
│   │   └── 2026_01_06_002100_create_comunicaciones_table.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── resources/
│   └── js/
│       ├── Components/
│       │   ├── Form/
│       │   │   ├── Input.vue
│       │   │   ├── Select.vue
│       │   │   ├── Textarea.vue
│       │   │   └── FileInput.vue
│       │   ├── Button.vue
│       │   ├── Card.vue
│       │   ├── Modal.vue
│       │   ├── Alert.vue
│       │   ├── Pagination.vue
│       │   ├── Table.vue
│       │   └── Map.vue
│       ├── Layouts/
│       │   └── AppLayout.vue
│       └── Pages/
│           ├── Auth/
│           │   └── Login.vue
│           ├── Dashboard.vue
│           ├── Residentes/
│           │   ├── Index.vue
│           │   ├── Create.vue
│           │   ├── Edit.vue
│           │   └── Show.vue
│           ├── Viviendas/
│           │   ├── Index.vue (con mapa)
│           │   ├── Create.vue
│           │   ├── Edit.vue
│           │   └── Show.vue
│           ├── Actividades/
│           │   ├── Index.vue (tablero público)
│           │   ├── Create.vue
│           │   ├── Edit.vue
│           │   └── Show.vue
│           ├── Aportes/
│           │   ├── Index.vue
│           │   ├── Create.vue
│           │   └── Show.vue (con registro de pago)
│           ├── Reuniones/
│           │   └── Index.vue
│           └── Comunicaciones/
│               └── Index.vue
└── routes/
    └── web.php
```

## 🔐 Seguridad

### Implementaciones OWASP
- ✅ Protección contra inyección SQL (Eloquent ORM)
- ✅ Protección XSS (escape automático Vue/Blade)
- ✅ Tokens CSRF en formularios
- ✅ Validación de entrada en servidor
- ✅ Autenticación segura con bcrypt
- ✅ Control de acceso basado en roles
- ✅ Sanitización de archivos subidos
- ✅ Headers de seguridad HTTP

### Validaciones
- Validación exhaustiva en backend (Laravel)
- Validación reactiva en frontend (Vue)
- Reglas de negocio estrictas
- Prevención de operaciones no autorizadas

## 📊 Funcionalidades Especiales

### Cálculo Automático de Mora
```php
// Configurable en .env
MORA_PORCENTAJE=5        // 5% de mora
MORA_DIAS_CALCULO=30     // Cada 30 días

// Ejemplo: Aporte de Bs. 500 vencido hace 45 días
// Periodos de mora: 45 / 30 = 1 periodo
// Mora: 500 * 0.05 * 1 = Bs. 25
```

### Generación Automática de Aportes
Al crear una actividad, se pueden generar automáticamente aportes para todas las viviendas activas con:
- Monto por vivienda configurable
- Fecha de vencimiento automática
- Estado inicial: PENDIENTE

### Sistema de Notificaciones
- Convocatorias a reuniones por email
- Notificaciones de pago recibido
- Alertas de aportes vencidos
- Comunicaciones institucionales

### Mapas Interactivos
- Visualización de viviendas en mapa
- Marcadores clickeables
- Edición de ubicación arrastrando marcador
- Integración con OpenStreetMap (sin API key)

## 📈 Estadísticas y Reportes

- Total de viviendas activas
- Total de residentes
- Reuniones realizadas
- Actividades completadas
- Monto total recaudado
- Monto total pendiente
- Mora acumulada
- Tasa de cumplimiento de pagos
- Tasa de lectura de comunicaciones

## 🚀 Próximas Mejoras Sugeridas

1. **Reportes en PDF**
   - Estados de cuenta por vivienda
   - Actas de reuniones
   - Comprobantes de pago

2. **Módulo de Reservas**
   - Reserva de áreas comunes
   - Calendario de disponibilidad
   - Sistema de turnos

3. **App Móvil**
   - Notificaciones push
   - Pagos móviles
   - Consulta de saldo

4. **Panel de Control Avanzado**
   - Gráficos interactivos
   - Métricas en tiempo real
   - Exportación de datos

5. **Integración de Pagos**
   - Pasarelas de pago online
   - QR para pagos
   - Conciliación bancaria

## 📝 Configuración

### Base de Datos
- **Host:** www.tecnoweb.org.bo
- **Base de datos:** db_grupo30sa
- **Usuario:** grupo30sa
- **Email institucional:** condominio@tecnoweb.org.bo

### Mora Automática
Configurable en `.env`:
```env
MORA_PORCENTAJE=5
MORA_DIAS_CALCULO=30
```

## 👥 Usuarios por Defecto

| Rol | Email | Password |
|-----|-------|----------|
| Administrador | admin@tecnoweb.org.bo | admin123 |
| Directorio | directorio@tecnoweb.org.bo | directorio123 |
| Propietario | maria@example.com | propietario123 |

## 📄 Licencia

Sistema desarrollado para Tecnoweb - Universidad Mayor de San Andrés

## 🤝 Soporte

Para soporte técnico o consultas:
- Email: condominio@tecnoweb.org.bo
- Documentación: Ver `INSTALACION.md`
