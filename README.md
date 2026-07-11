# 🌿 SISNOVEDADES - Sistema Integral de Novedades

> Sistema de gestión integral para la administración de novedades, palomar, vehículos, conductores y guardia de correos.

---

## 📦 Descripción General

SISNOVEDADES es una aplicación web completa desarrollada con **Laravel 13** y **Livewire 4**, diseñada para gestionar todas las operaciones relacionadas con el palomar, vehículos, conductores, guardia de correos y documentación en una organización.

El sistema permite a los usuarios:
- Gestionar novedades de personal y rancho
- Controlar el palomar (palomares, palomas, vuelos)
- Administrar flota de vehículos y mantenimientos
- Gestionar conductores y sus carnets
- Operar guardia de correos con cierre y reactivación
- Subir y gestionar documentos
- Generar reportes y PDFs
- Interfaz pública para visitantes

---

## 🛠️ Tecnologías

| Tecnología | Versión | Descripción |
|------------|---------|-------------|
| **PHP** | 8.3+ | Framework principal |
| **Laravel** | 13.17 | Framework web |
| **Livewire** | 4.1 | Componentes interactivos |
| **Blaze** | 1.0 | Componentes avanzados |
| **Flux** | 2.13.1 | Componentes UI modernos |
| **AdminLTE** | 3.16 | Panel de administración |
| **Tailwind CSS** | 4.0.7 | Estilos CSS |
| **Bootstrap** | 5.2.3 | Componentes UI |
| **Vite** | 8.0+ | Build tool |
| **DomPDF** | 3.1 | Generación de PDF |
| **PHPSpreadsheet** | 5.8 | Excel y PDF |
| **Spatie ActivityLog** | 4.12 | Auditoría de acciones |

---

## 🎯 Características del Sistema

### 📋 Módulo de Novedades
- **Gestión completa de novedades** con CRUD
- **Asignación de organismo** a cada novedad
- **Definición de destinos**
- **Gestión de adjuntos** (archivos)
- **Sistema de estados** de atención
- **Notificaciones** en tiempo real
- **Vista pública** para visitantes
- **Descarga de adjuntos** por visitantes

### 🐝 Módulo de Palomar
- **Gestión de palomares** (asignación de oficina)
- **Gestión de palomas** individuales
- **Historial de palomas** por palomar
- **Gestión de vuelos** con tracking de estados
- **Resultados de vuelos** con guardado de datos
- **Relación paloma-vuelo** (pivot table)
- **Relación palomar-vuelo**

### 🚗 Módulo de Vehículos
- **CRUD completo** de vehículos
- **Gestión de tipos de vehículos**
- **Datos técnicos** de vehículos
- **Asignación de unidad** a vehículos
- **Relación vehículo-tipo**
- **Relación vehículo-unidad**
- **Mantenimientos** de vehículos
- **Estado de vehículos** (activo/inactivo)
- **Soft deletes** con recuperación
- **Exportación** de datos
- **Resumen diario** de vehículos

### 👤 Módulo de Conductores
- **CRUD completo** de conductores
- **Número de carnet** habilitante
- **Asignación a vehículos**
- **Soft deletes** con recuperación
- **Validación de carnet**

### 📧 Módulo de Guardia de Correos
- **CRUD de guardias** con cierre y reactivación
- **Salidas de vehículos** desde guardia
- **Novedades anidadas** por guardia:
  - Novedades de personal
  - Novedades de rancho
- **Menús del rancho**
- **Correos fallidos** con historial
- **Generación de PDF** de guardias
- **Envío de emails** con guardia
- **Publicación para visitantes**
- **Descarga de adjuntos** por visitantes
- **Vista pública** de guardias y novedades

### 📄 Módulo de Documentación
- **CRUD de documentos**
- **Categorías de documentos**
- **Vista previa** de documentos
- **Descarga de documentos**
- **Trash bin** con restauración
- **Soft deletes** con recuperación
- **Subida de archivos** con adjuntos

### 🏢 Módulo de Oficinas
- **CRUD de oficinas**
- **Asignación de oficina** a usuarios
- **Relación oficina-novedades**
- **Relación oficina-vehículos**

### 🔐 Sistema de Autenticación y Autorización
- **Laravel Fortify** con autenticación social
- **Sistema de roles** y permisos (RBAC)
- **Control de acceso** verificado
- **Passkeys** para autenticación biométrica
- **Superadministrador** con acceso completo
- **Validaciones** de permisos en rutas

### 📊 Sistema de Actividad
- **Registro de actividades** con Spatie Activity Log
- **Columna de evento** personalizada
- **Batch UUID** para trazabilidad
- **Auditoría completa** del sistema

---

## 🗂️ Estructura de Archivos

```
sisnovedades/
├── app/
│   ├── Actions/                    # Lógica de negocio (Actions)
│   ├── Console/                    # Artisan commands
│   ├── Http/
│   │   ├── Controllers/           # Controladores MVC
│   │   ├── Livewire/              # Componentes Livewire
│   │   ├── Middleware/            # Middleware personalizados
│   │   └── Policies/              # Políticas de autorización
│   ├── Livewire/Landing/          # Componentes Livewire Landing
│   ├── Mail/                      # Plantillas de email
│   ├── Models/                    # Modelos de base de datos
│   ├── Observers/                 # Observadores
│   └── Providers/                 # Proveedores de servicios
├── bootstrap/                      # Bootstrap de Laravel
├── config/                         # Configuración
├── database/
│   ├── migrations/                # Migraciones de base de datos
│   └── seeders/                   # Seeds de datos
├── public/                         # Archivos públicos
├── resources/
│   ├── views/
│   │   ├── admin/                 # Vistas del panel admin
│   │   ├── auth/                  # Vistas de autenticación
│   │   ├── components/            # Componentes reutilizables
│   │   ├── emails/                # Plantillas de email
│   │   ├── livewire/              # Vistas Livewire
│   │   ├── layouts/               # Layouts principales
│   │   ├── partials/              # Partes de vistas
│   │   └── web/                   # Vistas del frontend
│   └── js/                        # Scripts JavaScript
├── routes/                         # Definición de rutas
│   ├── web.php                    # Rutas principales
│   ├── console.php                # Rutas de consola
│   └── settings.php               # Rutas adicionales
├── storage/                        # Almacenamiento (uploads, logs)
├── tests/                          # Tests con Pest PHP
├── .env.example                    # Ejemplo de variables de entorno
├── composer.json                   # Dependencias PHP
├── package.json                    # Dependencias JavaScript
├── vite.config.js                  # Configuración de Vite
├── artisan                         # Artisan Laravel
└── README.md                       # Este archivo
```

---

## 🚀 Instalación

### Requisitos Previos
- PHP 8.3 o superior
- Composer
- Node.js y NPM
- Base de datos (SQLite recomendado para desarrollo)

### Pasos de Instalación

```bash
# 1. Clonar el proyecto
cd sisnovedades

# 2. Instalar dependencias PHP
composer install

# 3. Instalar dependencias JavaScript
npm install

# 4. Copiar archivo .env.example
cp .env.example .env

# 5. Configurar base de datos
php artisan key:generate
php artisan migrate --force

# 6. Construir frontend
npm run build

# 7. Iniciar servidor de desarrollo
npm run dev

# O usar Laravel server
php artisan serve
```

### Instalación Automatizada

```bash
composer setup
```

Este script ejecuta automáticamente:
- `composer install`
- Copia `.env.example` a `.env`
- Genera key de Laravel
- Migra base de datos
- Instala dependencias de Node
- Construye frontend

---

## 📚 Características Técnicas

### Base de Datos
- **Base de datos SQLite** (desarrollo) / MySQL (producción)
- **Soft deletes** implementados en múltiples tablas
- **Relaciones foreign keys** bien definidas
- **Índices** para optimización de queries
- **Auditoría** con Spatie Activity Log

### Frontend
- **Tailwind CSS 4** con clases utilitarias
- **Bootstrap 5** para componentes
- **AdminLTE 3** para panel de administración
- **Vite** para build optimization
- **Livewire 4** para componentes interactivos
- **Responsive design** para móviles

### Seguridad
- **Password hashing** con bcrypt
- **CSRF protection** en todas las rutas
- **XSS protection** con sanitización
- **SQL injection** prevención con Eloquent
- **Rate limiting** en autenticación
- **Soft deletes** con recuperación

### Performance
- **Eager loading** para evitar N+1 queries
- **Caching** con Redis (opcional)
- **Optimization** con `php artisan optimize`
- **Lazy loading** para relaciones grandes
- **CDN** para assets (opcional)

---

## 🧪 Testing

### Scripts de Testing

```bash
# Ejecutar todos los tests
composer test

# Ejecutar tests con cobertura
composer test -- --coverage

# Tests de tipo
composer types:check

# Linting
composer lint:check
```

### Testing Framework
- **Pest PHP** para tests
- **Mockery** para mocking
- **PHPUnit** para tests de base

---

## 🔧 Configuración

### Variables de Entorno

```env
# Base de datos
DB_CONNECTION=sqlite
DB_DATABASE=database.sqlite

# Autenticación
APP_NAME="SISNOVEDADES"
APP_ENV=production
APP_KEY=
APP_TIMEZONE=UTC
APP_URL=http://localhost

# AdminLTE
ADMINLTE_THEME=light

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"

# Redis (opcional)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

---

## 📖 Rutas Principales

### Rutas Públicas
- `GET /` - Home page
- `GET /guardias-publicas/{guardia}` - Vista pública de guardias
- `GET /guardias-publicas/{guardia}/novedades/{novedad}` - Vista pública de novedades
- `GET /guardias-publicas/{guardia}/novedades/{novedad}/adjuntos/{adjunto}/download` - Descarga adjunto

### Rutas de Administración (requieren autenticación)
- `GET /admin` - Panel principal
- `GET /admin/novedades` - Lista de novedades
- `GET /admin/vehiculos` - CRUD de vehículos
- `GET /admin/conductores` - CRUD de conductores
- `GET /admin/guardias` - CRUD de guardias
- `GET /admin/palomares` - CRUD de palomares
- `GET /admin/palomas` - CRUD de palomas
- `GET /admin/vuelos` - CRUD de vuelos
- `GET /admin/notificaciones` - Gestión de notificaciones
- `GET /admin/usuarios` - Gestión de usuarios
- `GET /admin/roles` - Gestión de roles
- `GET /admin/permisos` - Gestión de permisos

### Rutas de Palomar
- `GET /admin/palomares/{palomar}/reporte` - Reporte de palomar
- `GET /admin/palomares/{palomar}/palomas` - Lista de palomas
- `GET /admin/palomares/{palomar}/vuelos` - Lista de vuelos
- `GET /admin/vuelos/{vuelo}/resultados` - Resultados de vuelo

---

## 📝 Migraciones Recientes

Las migraciones más recientes (julio 2026) incluyen:
- Mejoras en soft deletes
- Corrección de relaciones
- Añadido de columnas para estado
- Implementación de tracking de vuelos
- Corrección de datos técnicos de vehículos

---

## 🐛 Bug Reports y Contribuciones

Si encuentras un bug o quieres contribuir:
1. **Reportar bugs** en el repositorio de GitHub
2. **Crear issue** con descripción detallada
3. **Proveer steps** para reproducir el problema
4. **Adjuntar logs** si es posible

---

## 📄 Licencia

MIT License

---

## 📧 Contacto

**Desarrollado por:** [Tu Equipo]  
**Fecha de creación:** 2024-2026  
**Última actualización:** 2026-07-11

---

## 📌 Notas Importantes

⚠️ **Verificar migraciones pendientes:**
- Revisar `PLAN.md` para las tareas pendientes
- Validar todas las migraciones de julio 2026
- Restaurar datos de palomas en vuelos si es necesario

⚠️ **Soft deletes:**
- Implementar recuperación completa de soft deletes
- Verificar consistencia entre soft deletes y trash bin

⚠️ **Seguridad:**
- Implementar validaciones completas en controladores
- Verificar sanitización de inputs
- Implementar rate limiting

⚠️ **Testing:**
- Implementar tests con Pest PHP
- Verificar cobertura de tests
- Automatizar tests en CI/CD

---

**SISNOVEDADES v1.0** - Sistema Integral de Novedades
