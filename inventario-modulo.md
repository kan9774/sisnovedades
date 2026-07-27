# Módulo Inventario — SIS-Novedades

Documentación funcional y técnica del módulo de gestión de inventario del BCOM1, a fecha de este documento.

## 1. Qué resuelve

El módulo administra dos formas distintas de controlar bienes:

- **Por cantidad**: insumos y consumibles fungibles (uniformes, útiles, combustible, etc.) donde solo importa "cuánto hay" en cada ubicación — no unidades individualizadas.
- **Por unidad individual**: bienes que se rastrean uno por uno con número de serie (PCs, radios, sillas, armamento, etc.), cada uno con su propio historial de asignación, reparación y baja.

Todo movimiento de stock o de una unidad individual queda registrado como un `Movimiento`, formando un historial auditable.

## 2. Modelo de datos

| Tabla | Rol |
|---|---|
| `items` | Catálogo maestro: qué cosas existen (código, nombre, categoría, talla opcional, tipo de seguimiento) |
| `categorias` | Agrupación de ítems del catálogo |
| `tallas` | Catálogo de tallas (para uniformes/calzado, opcional por ítem) |
| `ubicaciones` | Dónde puede estar un bien: depósito, oficina, vehículo o persona (ver sección 2.1) |
| `stock` | Cantidad actual de un ítem "por cantidad" en una ubicación puntual (par `item_id` + `ubicacion_id`) |
| `item_unidades` | Cada unidad física individual de un ítem "individual", con nº de serie, estado y ubicación actual |
| `movimientos` | Historial: toda entrada, salida, transferencia, ajuste o baja queda registrada acá |

### 2.1 Ubicaciones: tipo + referencia

`ubicaciones` no es una lista libre de texto — cada fila tiene:
- `tipo` (enum): `deposito`, `oficina`, `vehiculo`, `persona`
- `referencia_id` (nullable): el ID del registro real en `oficinas`, `vehiculos` o `users` según el tipo. `deposito` no tiene tabla propia, así que va `null`.

Esto permite responder "¿dónde está este bien?" apuntando a una entidad real del sistema (una oficina concreta, un vehículo concreto, una persona concreta) en vez de un string suelto que se puede desincronizar.

### 2.2 Tipos de movimiento

| Tipo | Quién lo genera | Qué representa |
|---|---|---|
| `entrada` | Manual (form) o alta de unidad individual | Suma stock / da de alta una unidad |
| `salida` | Manual | Resta stock de una ubicación |
| `transferencia` | Manual (cantidad) o `asignarUnidad` (individual) | Mueve stock/unidad de una ubicación a otra |
| `ajuste` | Manual (conteo físico) o `marcarEnReparacion` (individual) | Corrige la cantidad real contada, o marca una unidad en reparación |
| `baja` | Solo generado por el sistema (`darDeBajaUnidad`) | Unidad individual dada de baja definitiva (no seleccionable a mano) |

## 3. Componentes Livewire

| Componente | Ruta | Función |
|---|---|---|
| `ItemsCatalogo` | `admin/inventario/items` | ABM del catálogo de ítems, con filtro por categoría y búsqueda |
| `CategoriasCatalogo` | `admin/inventario/categorias` | ABM de categorías (bloquea borrado si tiene ítems asociados) |
| `UbicacionesCatalogo` | `admin/inventario/ubicaciones` | ABM de ubicaciones; selector dinámico de oficina/vehículo/persona según el tipo elegido, con autocompletado del nombre |
| `MovimientosInventario` | `admin/inventario/movimientos` | Form de registro de movimientos (solo ítems "por cantidad") + historial con filtros |
| `UnidadesIndividuales` | `admin/inventario/unidades-individuales` | Listado de unidades individuales + alta, asignación/transferencia, envío a reparación y baja |

Todas las rutas están wrappeadas en una vista intermedia (`*-layout.blade.php` con `@extends('layouts.app')` + `<livewire:...>`), siguiendo la misma convención que el resto de módulos de la app (Organismos, Unidades, Documentos) — **no** se enrutan directo a la clase del componente.

## 4. `InventarioService` — reglas de negocio centralizadas

Toda la lógica de stock vive acá, no en los componentes:

- **`registrarEntrada` / `registrarSalida` / `registrarTransferencia`**: operan sobre `Stock`, con lock pesimista (`lockForUpdate`) para evitar condiciones de carrera entre movimientos simultáneos del mismo ítem+ubicación.
- **`registrarAjuste`**: no suma/resta, fija el valor real contado y calcula la diferencia como el `cantidad` del movimiento (puede ser negativa).
- **`darDeAltaUnidad` / `asignarUnidad` / `marcarEnReparacion` / `darDeBajaUnidad`**: equivalentes para ítems de seguimiento individual.
- Cada operación valida que el ítem sea del tipo de seguimiento correcto (`asegurarPorCantidad` / `asegurarIndividual`) antes de tocar nada.
- Todo corre dentro de `DB::transaction()`.

## 5. Permisos

Sistema de doble capa (igual que el resto de SIS-Novedades):

1. **Policies** (`ItemPolicy`, `MovimientoPolicy`, `ItemUnidadPolicy`, `CategoriaPolicy`, `UbicacionPolicy`) — autorizan las acciones reales dentro de los componentes Livewire (`$this->authorize(...)`), registradas vía `Gate::policy(...)` en `AppServiceProvider`.
2. **Gates con nombre string** (`viewAny-item`, `viewAny-movimiento`, `viewAny-unidad`, `viewAny-categoria`, `viewAny-ubicacion`) — controlan qué ítems del sidebar de AdminLTE se muestran.

Permiso base compartido para ver el módulo: `ver_item` (más `isAdmin()` que siempre pasa). Acciones sensibles (crear/editar/borrar categorías y ubicaciones, dar de baja una unidad) están más restringidas.

## 6. Estilo visual de los modales

Los formularios de alta/edición usan el mismo patrón visual que "Registrar Novedad" (paneles de pantalla completa estilo consola de operaciones — header azul marino con borde amarillo, contenido centrado en tarjeta blanca), en vez del modal Bootstrap estándar de AdminLTE. La variante para "Dar de baja" usa acento rojo por ser una acción destructiva.

El CSS vive en `public/css/ops-panel.css`, cargado una sola vez desde el layout maestro (no se repite por componente).

## 7. Pendientes conocidos

- El historial de `MovimientosInventario` no permite filtrar por ítems de seguimiento individual (el `<select>` de filtro solo lista ítems "por cantidad"), aunque sus movimientos (`entrada`, `transferencia`, `baja`, etc.) sí aparecen en la tabla.
- `UnidadesIndividuales::asignar()` no limpia el `responsable_id` anterior si se transfiere una unidad sin elegir un responsable nuevo.
- Condición de carrera de baja probabilidad sin manejar en `obtenerOCrearStockConLock` (creación concurrente de la misma fila de stock).
- `marcarEnReparacion` reutiliza el tipo de movimiento `ajuste` en vez de tener uno propio (`reparacion`), mezclando semánticamente dos conceptos distintos en el historial.
- Falta decidir si `UbicacionesCatalogo` debe excluir vehículos inactivos (`activo = false`) o usuarios dados de baja del selector de referencia.

## 8. Flujo típico de uso

1. Se da de alta el ítem en el **Catálogo** (definiendo si es "por cantidad" o "individual").
2. Si es por cantidad: se registran **entradas** desde **Movimientos** para cargar stock inicial en una ubicación.
3. Si es individual: se da de alta cada unidad física desde **Unidades Individuales**, asignándole ubicación inicial y (opcional) nº de serie.
4. A medida que los bienes se mueven, se registran **salidas**, **transferencias** o **asignaciones**, según corresponda.
5. Periódicamente se hace un **ajuste** (conteo físico) para corregir diferencias entre lo registrado y lo real.
6. Unidades individuales rotas o perdidas se marcan **en reparación** o se **dan de baja** definitivamente.
