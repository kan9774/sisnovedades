# Análisis de Rutas con Route::resource

## Resumen

Se han identificado 5 definiciones de rutas que utilizan `Route::resource` en el proyecto:

## Rutas encontradas

### 1. Guardias
- **Ruta**: `guardias`
- **Controlador**: `GuardiaController`
- **Métodos permitidos**: `index`, `create`, `store`, `show`
- **Archivo**: `routes/web.php` (línea 225)

### 2. Unidades
- **Ruta**: `unidades`
- **Controlador**: `UnidadController`
- **Métodos permitidos**: `show` solamente
- **Archivo**: `routes/web.php` (línea 272)

### 3. Conductores
- **Ruta**: `conductores`
- **Controlador**: `ConductorController`
- **Métodos excluidos**: `show`
- **Archivo**: `routes/web.php` (línea 295)

### 4. Palomares
- **Ruta**: `palomares`
- **Controlador**: `PalomarController`
- **Parámetros personalizados**: `palomar`
- **Archivo**: `routes/web.php` (línea 311)

### 5. Palomas
- **Ruta**: `palomas`
- **Controlador**: `PalomaController`
- **Parámetros personalizados**: `paloma`
- **Archivo**: `routes/web.php` (línea 314)

### 6. Vuelos
- **Ruta**: `vuelos`
- **Controlador**: `VueloController`
- **Parámetros personalizados**: `vuelo`
- **Archivo**: `routes/web.php` (línea 323)

### 7. Estados Paloma
- **Ruta**: `estados-paloma`
- **Controlador**: `EstadoPalomaController`
- **Parámetros personalizados**: `estado`
- **Archivo**: `routes/web.php` (línea 326)

## Archivos revisados

- `routes/web.php`: Contiene 5 definiciones de rutas con Route::resource
- `routes/console.php`: No contiene definiciones de rutas con Route::resource
- `routes/settings.php`: No contiene definiciones de rutas con Route::resource

## Notas adicionales

Las rutas que usan Route::resource generan automáticamente las siguientes rutas HTTP:
- `GET /{resource}` - index
- `POST /{resource}` - store
- `GET /{resource}/{id}` - show
- `PUT/PATCH /{resource}/{id}` - update
- `DELETE /{resource}/{id}` - destroy

Algunas rutas tienen configuraciones especiales:
- `unidades`: Solo permite el método `show`
- `conductores`: Excluye el método `show`
- Algunas rutas usan parámetros personalizados para mejorar la legibilidad de las URLs