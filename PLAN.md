# PLAN DE TRABAJO - SISNOVEDADES

## 📋 Resumen General
Sistema de gestión integral para la administración de novedades, palomar, vehículos, conductores y guardia de correos.

---

## ✅ TAREAS REALIZADAS

### 1. **Estructura del Proyecto**
- [x] Instalación de Laravel 13 con Livewire 4
- [x] Configuración de base de datos SQLite
- [x] Implementación de AdminLTE 3 + Tailwind CSS 4 + Bootstrap 5
- [x] Configuración de Vite para frontend
- [x] Instalación de dependencias PHP (PHPStan, Pint, Pest)

### 2. **Sistema de Autenticación y Autorización**
- [x] Implementación de Laravel Fortify (autenticación social)
- [x] Sistema de roles y permisos (RBAC)
- [x] Control de acceso verificado
- [x] Passkeys para autenticación biométrica
- [x] Superadministrador con acceso completo

### 3. **Módulo de Novedades**
- [x] Tabla de novedades (`news_table`)
- [x] Asignación de organismo a cada novedad
- [x] Definición de destinos
- [x] Gestión de adjuntos (archivos)
- [x] Estados de atención a novedades
- [x] Sistema de notificaciones
- [x] Vista pública para visitantes

### 4. **Módulo de Palomar**
- [x] Gestión de palomares
- [x] Gestión de palomas individuales
- [x] Historial de palomas
- [x] Gestión de vuelos
- [x] Estados de palomas por vuelo
- [x] Resultado de vuelos y guardado de datos
- [x] Relación paloma-vuelo (pivot table)

### 5. **Módulo de Vehículos**
- [x] CRUD completo de vehículos
- [x] Gestión de tipos de vehículos
- [x] Datos técnicos de vehículos
- [x] Asignación de unidad a vehículos
- [x] Relación vehículo-tipo
- [x] Relación vehículo-unidad
- [x] Mantenimientos de vehículos
- [x] Estado de vehículos (activo/inactivo)
- [x] Soft deletes para vehículos y conductores
- [x] Exportación de vehículos
- [x] Resumen diario de vehículos

### 6. **Módulo de Conductores**
- [x] CRUD completo de conductores
- [x] Número de carnet habilitante
- [x] Asignación a vehículos
- [x] Soft deletes

### 7. **Módulo de Guardia de Correos**
- [x] CRUD de guardias
- [x] Cierre y reactivación de guardias
- [x] Salidas de vehículos desde guardia
- [x] Novedades anidadas por guardia
- [x] Novedades de personal
- [x] Novedades de rancho
- [x] Menús del rancho
- [x] Correos fallidos
- [x] Historial de guardias corrientes fallidos
- [x] Generación de PDF de guardias
- [x] Envío de emails con guardia
- [x] Publicación de guardias para visitantes
- [x] Descarga de adjuntos por visitantes
- [x] Vista pública de guardias y novedades

### 8. **Módulo de Documentación**
- [x] CRUD de documentos
- [x] Categorías de documentos
- [x] Vista previa de documentos
- [x] Descarga de documentos
- [x] Trash bin con restauración
- [x] Soft deletes

### 9. **Módulo de Oficinas**
- [x] CRUD de oficinas
- [x] Asignación de oficina a usuarios
- [x] Relación oficina-novedades

### 10. **Sistema de Actividad**
- [x] Tabla de actividad (Spatie Activity Log)
- [x] Registro de eventos del sistema
- [x] Columna de evento personalizada
- [x] Batch UUID para trazabilidad

### 11. **Frontend**
- [x] Landing page con Livewire
- [x] Componentes: Hero, Navbar, Footer, Contacto
- [x] Componentes: Servicios, ContactoSeccion, Nosotros
- [x] Panel de administración con AdminLTE
- [x] Layouts personalizados con Tailwind
- [x] Componentes reutilizables

### 12. **Base de Datos**
- [x] Migraciones completas para todas las tablas
- [x] Relaciones foreign keys
- [x] Índices para optimización
- [x] Soft deletes implementados
- [x] Tablas de auditoría

### 13. **Testing y Calidad**
- [x] Laravel Pint para linting
- [x] PHPStan para tipos
- [x] Pest PHP para testing
- [x] Debugbar para desarrollo
- [x] Scripts de CI/CD

---

## 🔧 TAREAS PENDIENTES / POR ARREGLAR

### 1. **Errores en Rutas web.php**
- [ ] **Falta la ruta POST para guardar usuarios** en `/users`
- [ ] **Falta la ruta PUT para editar usuarios** en `/users/{id}`
- [ ] Verificar que todas las rutas de CRUD tengan los métodos completos

### 2. **Problemas de Palomar**
- [ ] **Migración removió datos de palomas de vuelos**: `2026_07_04_121119_remove_paloma_data_from_vuelos_table.php`
  - Necesita migración de restauración o backup de datos
- [ ] **Relación paloma-vuelo**: Migración `2026_07_04_130000_add_estado_tracking_to_vuelos_and_pivot.php`
  - Verificar lógica de estados en vuelos

### 3. **Problemas de Novedades**
- [ ] **Cambio de columna de oficina**: `2026_07_07_230115_change_office_to_office_id_on_news_table.php`
  - Verificar consistencia en las relaciones
- [ ] **Estado de atención**: `2026_07_08_221658_add_estado_atencion_to_news_table.php`
  - Implementar lógica de estados en el controlador

### 4. **Problemas de Vehículos**
- [ ] **Migración duplicada de datos técnicos**: `2026_07_06_195314_add_datos_tecnicos_to_veiculos_table.php`
  - Verificar si duplica datos en tablas existentes
- [ ] **Soft deletes en vehículos**: `2026_07_02_230905_add_deleted_at_to_vehiculos_table.php`
  - Implementar recuperación de soft deletes

### 5. **Problemas de Conductores**
- [ ] **Número de carnet**: `2026_07_06_211014_add_numero_carne_habilitante_to_conductores_table.php`
  - Validar formato y longitud del campo
- [ ] **Estado de vehículos**: `2026_07_06_224615_add_estado_to_vehiculos_table.php`
  - Implementar lógica de estado

### 6. **Problemas de Rancho**
- [ ] **Menús del rancho**: `2026_07_09_210827_remove_menu_to_novedades_rancho_table.php`
  - Migración de datos de `rancho_menus` a `novedades_rancho`
  - Verificar integridad de datos

### 7. **Problemas de Palomas**
- [ ] **Historial de palomas**: `2026_07_03_174656_create_historial_palomas_table.php`
  - Implementar lógica de historial
- [ ] **Vuelos y resultados**: Migraciones `2026_07_03_174710_create_vuelos_table.php` y relacionadas
  - Verificar consistencia de datos

### 8. **Problemas de Documentación**
- [ ] **Categorías de documentos**: `2026_07_05_010341_create_categorias_documentos_table.php`
  - Implementar lógica de categorías
- [ ] **Documentos**: `2026_07_05_010351_create_documentos_table.php`
  - Implementar lógica de subida de archivos

### 9. **Problemas de Usuarios**
- [ ] **Roles y permisos**: `2026_06_28_203237_create_rols_table.php` y relacionadas
  - Implementar lógica de asignación de roles
- [ ] **Permiso de usuarios**: `2026_07_04_023950_create_user_permission_table.php`
  - Implementar lógica de permisos

### 10. **Problemas de Notificaciones**
- [ ] **Notificaciones**: `2026_07_08_221602_create_notifications_table.php`
  - Implementar lógica de notificaciones
- [ ] **Tomar notificaciones**: `NotificationController::class, 'tomar'`
  - Verificar implementación

### 11. **Problemas de Guardia**
- [ ] **Soft deletes de guardias**: `2026_07_02_173206_add_soft_deletes_to_guards_table.php`
  - Implementar recuperación de guardias borrados
- [ ] **Guardia Clerk**: `2026_06_28_204917_create_guard_clerk_table.php`
  - Verificar relación con guardias

### 12. **Problemas de Unidades**
- [ ] **Unidades**: `2026_07_07_175643_create_unidades_table.php`
  - Verificar relación con vehículos y usuarios
- [ ] **Unidades en usuarios**: `2026_07_07_175947_add_unidad_id_to_users_table.php`
  - Implementar lógica de asignación

### 13. **Problemas de Palomares**
- [ ] **Palomares**: `2026_07_03_174606_create_palomares_table.php`
  - Implementar lógica de palomares
- [ ] **Estados de paloma**: `2026_07_03_174626_create_estados_paloma_table.php`
  - Implementar lógica de estados

### 14. **Problemas de Tipos de Vehículo**
- [ ] **Tipos de vehículo**: `2026_07_07_170020_create_tipos_vehiculo_table.php`
  - Implementar lógica de tipos

### 15. **Problemas de Mantenimientos**
- [ ] **Mantenimientos**: `2026_07_06_195436_create_mantenimiento_veiculo_table.php`
  - Implementar lógica de mantenimientos

### 16. **Problemas de Resumen Diario**
- [ ] **Resumen vehículos diario**: `2026_07_02_153056_create_resumen_vehiculos_diario_table.php`
  - Implementar lógica de resúmenes diarios
- [ ] **Resumen combustible diario**: `2026_07_02_153104_create_resumen_combustible_diario_table.php`
  - Implementar lógica de resúmenes de combustible

### 17. **Problemas de Actividades**
- [ ] **Actividad log**: `2026_07_07_170020_create_activity_log_table.php` y relacionadas
  - Implementar registro de actividades
  - Verificar columnas de evento y batch UUID

### 18. **Problemas de Correo Fallido**
- [ ] **Correo fallido**: `2026_07_10_190208_create_guardia_correos_fallidos_table.php`
  - Implementar lógica de correos fallidos

### 19. **Problemas de Palomas-Vuelos**
- [ ] **Paloma-vuelo**: `2026_07_04_121020_create_paloma_vuelo_table.php`
  - Implementar lógica de relación paloma-vuelo
- [ ] **Remoción de datos de palomas**: `2026_07_04_121119_remove_paloma_data_from_vuelos_table.php`
  - Migración de restauración necesaria

### 20. **Problemas de Estados Tracking**
- [ ] **Estado tracking**: `2026_07_04_130000_add_estado_tracking_to_vuelos_and_pivot.php`
  - Implementar tracking de estados en vuelos

### 21. **Problemas de Trash Bin**
- [ ] **Trash bin**: Implementar en múltiples entidades (guardias, vehículos, conductores, documentos)
  - Verificar consistencia entre soft deletes y trash bin

### 22. **Problemas de Seguridad**
- [ ] **Soft deletes**: Implementar recuperación completa
- [ ] **Validaciones**: Verificar todas las validaciones en controladores
- [ ] **XSS/SQL Injection**: Verificar sanitización de inputs

### 23. **Problemas de Performance**
- [ ] **Índices**: Verificar y añadir índices en tablas grandes
- [ ] **Ejecución de queries**: Verificar con Debugbar
- [ ] **Optimización de N+1**: Implementar eager loading

### 24. **Problemas de Frontend**
- [ ] **Componentes Livewire**: Verificar todos los componentes Livewire
- [ ] **Tailwind**: Verificar consistencia de clases
- [ ] **Bootstrap**: Verificar compatibilidad con Tailwind
- [ ] **Responsividad**: Verificar en diferentes dispositivos

### 25. **Problemas de Testing**
- [ ] **Tests**: Implementar tests con Pest PHP
- [ ] **Cobertura**: Verificar cobertura de tests
- [ ] **CI/CD**: Implementar scripts de testing automático

### 26. **Problemas de Documentación**
- [ ] **README**: Actualizar con características del proyecto
- [ ] **PLAN.md**: Crear plan de trabajo (este archivo)
- [ ] **API**: Documentar endpoints de API
- [ ] **Diagramas**: Crear diagramas de base de datos y arquitectura

---

## 📊 ESTADO DEL PROYECTO

| Área | Estado |
|------|--------|
| Base de datos | ✅ Completado (con migraciones pendientes de validación) |
| Autenticación | ✅ Completado |
| Novedades | ✅ Completado |
| Palomar | ⚠️ Parcial (requiere migración de datos) |
| Vehículos | ✅ Completado |
| Conductores | ✅ Completado |
| Guardia | ✅ Completado |
| Documentación | ✅ Completado |
| Frontend | ✅ Completado |
| Testing | ⚠️ En desarrollo |
| Performance | ⚠️ Requiere optimización |

---

## 🎯 PRÓXIMOS PASOS PRIORITARIOS

1. **Validar todas las migraciones pendientes** (problemas 1-21)
2. **Implementar lógica de negocio faltante** (problemas 2-4, 9-14)
3. **Implementar soft deletes** (problemas 11, 16, 21)
4. **Implementar tests** (problema 25)
5. **Optimizar performance** (problema 23)
6. **Corregir errores en rutas** (problema 1)

---

## 📅 FECHA DE ACTUALIZACIÓN: 2026-07-11
