<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermisoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Los permisos se agrupan por modelo/tabla (no por acción), para que
     * la UI de asignación de permisos por rol pueda agrupar correctamente.
     */
    public function run(): void
    {
        $modulos = [

            'Guardia' => [
                ['name' => 'ver_guardias', 'description' => 'Ver listado de guardias'],
                ['name' => 'ver_guardia', 'description' => 'Ver detalle de una guardia'],
                ['name' => 'crear_guardia', 'description' => 'Crear nuevas guardias'],
                ['name' => 'editar_guardia', 'description' => 'Editar guardias existentes'],
                ['name' => 'eliminar_guardia', 'description' => 'Eliminar guardias'],
                ['name' => 'cerrar_guardia', 'description' => 'Cerrar guardias activas'],
                ['name' => 'ver_pdf_guardia', 'description' => 'Ver PDF de la guardia'],
                ['name' => 'asignar_destinatarios_pdf', 'description' => 'Asignar destinatarios para PDF de guardia'],
            ],

            'Novedad' => [
                ['name' => 'ver_novedades', 'description' => 'Ver listado de novedades'],
                ['name' => 'ver_novedad', 'description' => 'Ver detalle de una novedad'],
                ['name' => 'crear_novedad', 'description' => 'Crear nuevas novedades'],
                ['name' => 'editar_novedad', 'description' => 'Editar novedades'],
                ['name' => 'eliminar_novedad', 'description' => 'Eliminar novedades'],
                ['name' => 'confirmar_novedad', 'description' => 'Confirmar novedades'],
                ['name' => 'tomar_novedad', 'description' => 'Tomar/Atender una novedad'],
            ],

            'NovedadPersonal' => [
                ['name' => 'ver_novedades_personal', 'description' => 'Ver listado de novedades de personal'],
                ['name' => 'crear_novedad_personal', 'description' => 'Crear novedades de personal'],
                ['name' => 'editar_novedad_personal', 'description' => 'Editar novedades de personal'],
                ['name' => 'eliminar_novedad_personal', 'description' => 'Eliminar novedades de personal'],
            ],

            'NovedadRancho' => [
                ['name' => 'ver_novedades_rancho', 'description' => 'Ver listado de novedades de rancho'],
                ['name' => 'crear_novedad_rancho', 'description' => 'Crear novedades de rancho'],
                ['name' => 'editar_novedad_rancho', 'description' => 'Editar novedades de rancho'],
                ['name' => 'eliminar_novedad_rancho', 'description' => 'Eliminar novedades de rancho'],
            ],

            'RanchoMenu' => [
                ['name' => 'ver_menus_rancho', 'description' => 'Ver menús de rancho'],
                ['name' => 'crear_menu_rancho', 'description' => 'Crear menús de rancho'],
                ['name' => 'editar_menu_rancho', 'description' => 'Editar menús de rancho'],
                ['name' => 'eliminar_menu_rancho', 'description' => 'Eliminar menús de rancho'],
            ],

            'Vehiculo' => [
                ['name' => 'ver_vehiculos', 'description' => 'Ver listado de vehículos'],
                ['name' => 'ver_vehiculo', 'description' => 'Ver detalle de un vehículo'],
                ['name' => 'crear_vehiculo', 'description' => 'Crear nuevos vehículos'],
                ['name' => 'editar_vehiculo', 'description' => 'Editar vehículos existentes'],
                ['name' => 'eliminar_vehiculo', 'description' => 'Eliminar vehículos'],
                ['name' => 'restaurar_vehiculo', 'description' => 'Restaurar vehículos eliminados'],
            ],

            'SalidaVehiculo' => [
                ['name' => 'ver_salida_vehiculo', 'description' => 'Ver listado/detalle de salidas de vehículos'],
                ['name' => 'crear_salida_vehiculo', 'description' => 'Crear salidas de vehículos'],
                ['name' => 'editar_salida_vehiculo', 'description' => 'Editar salidas de vehículos'],
                ['name' => 'eliminar_salida_vehiculo', 'description' => 'Eliminar salidas de vehículos'],
            ],

            'BoletaCierre' => [
                ['name' => 'ver_boletas_cierre', 'description' => 'Ver listado de boletas de cierre'],
                ['name' => 'ver_boleta_cierre', 'description' => 'Ver detalle de una boleta de cierre'],
                ['name' => 'crear_boleta_cierre', 'description' => 'Crear boletas de cierre'],
                ['name' => 'editar_boleta_cierre', 'description' => 'Editar boletas de cierre'],
                ['name' => 'eliminar_boleta_cierre', 'description' => 'Eliminar boletas de cierre'],
            ],

            'Conductor' => [
                ['name' => 'ver_conductores', 'description' => 'Ver listado de conductores'],
                ['name' => 'ver_conductor', 'description' => 'Ver detalle de un conductor'],
                ['name' => 'crear_conductor', 'description' => 'Crear nuevos conductores'],
                ['name' => 'editar_conductor', 'description' => 'Editar conductores existentes'],
                ['name' => 'eliminar_conductor', 'description' => 'Eliminar conductores'],
                ['name' => 'restaurar_conductor', 'description' => 'Restaurar conductores eliminados'],
                ['name' => 'exportar_conductores', 'description' => 'Exportar listado de conductores'],
            ],

            'MantenimientoVehiculo' => [
                ['name' => 'ver_mantenimientos', 'description' => 'Ver listado de mantenimientos'],
                ['name' => 'ver_mantenimiento', 'description' => 'Ver detalle de un mantenimiento'],
                ['name' => 'crear_mantenimiento', 'description' => 'Crear mantenimientos de vehículos'],
                ['name' => 'editar_mantenimiento', 'description' => 'Editar mantenimientos de vehículos'],
                ['name' => 'eliminar_mantenimiento', 'description' => 'Eliminar mantenimientos de vehículos'],
            ],

            'Resumen' => [
                ['name' => 'ver_resumenes_vehiculos', 'description' => 'Ver resúmenes diarios de vehículos'],
                ['name' => 'ver_resumenes_combustible', 'description' => 'Ver resúmenes diarios de combustible'],
                ['name' => 'exportar_resumenes', 'description' => 'Exportar resúmenes diarios'],
                ['name' => 'generar_resumenes', 'description' => 'Generar resúmenes diarios'],
            ],

            'Palomar' => [
                ['name' => 'ver_palomares', 'description' => 'Ver listado de palomares'],
                ['name' => 'ver_palomar', 'description' => 'Ver detalle de un palomar'],
                ['name' => 'crear_palomar', 'description' => 'Crear nuevos palomares'],
                ['name' => 'editar_palomar', 'description' => 'Editar palomares existentes'],
                ['name' => 'eliminar_palomar', 'description' => 'Eliminar palomares'],
            ],

            'Paloma' => [
                ['name' => 'ver_palomas', 'description' => 'Ver listado de palomas'],
                ['name' => 'ver_paloma', 'description' => 'Ver detalle de una paloma'],
                ['name' => 'crear_paloma', 'description' => 'Crear nuevas palomas'],
                ['name' => 'editar_paloma', 'description' => 'Editar palomas existentes'],
                ['name' => 'eliminar_paloma', 'description' => 'Eliminar palomas'],
                ['name' => 'ver_historial_paloma', 'description' => 'Ver historial de una paloma'],
                ['name' => 'registrar_cambio_estado', 'description' => 'Registrar cambio de estado de paloma'],
            ],

            'EstadoPaloma' => [
                ['name' => 'ver_estados_paloma', 'description' => 'Ver listado de estados de paloma'],
                ['name' => 'crear_estado_paloma', 'description' => 'Crear estados de paloma'],
                ['name' => 'editar_estado_paloma', 'description' => 'Editar estados de paloma'],
                ['name' => 'eliminar_estado_paloma', 'description' => 'Eliminar estados de paloma'],
            ],

            'Vuelo' => [
                ['name' => 'ver_vuelos', 'description' => 'Ver listado de vuelos'],
                ['name' => 'ver_vuelo', 'description' => 'Ver detalle de un vuelo'],
                ['name' => 'crear_vuelo', 'description' => 'Crear nuevos vuelos'],
                ['name' => 'editar_vuelo', 'description' => 'Editar vuelos existentes'],
                ['name' => 'eliminar_vuelo', 'description' => 'Eliminar vuelos'],
                ['name' => 'registrar_paloma_vuelo', 'description' => 'Registrar palomas en vuelos'],
                ['name' => 'ver_resultados_vuelo', 'description' => 'Ver resultados de vuelos'],
            ],

            'Documento' => [
                ['name' => 'ver_documentos', 'description' => 'Ver listado de documentos'],
                ['name' => 'ver_documento', 'description' => 'Ver detalle de un documento'],
                ['name' => 'crear_documento', 'description' => 'Subir y crear documentos'],
                ['name' => 'editar_documento', 'description' => 'Editar documentos existentes'],
                ['name' => 'eliminar_documento', 'description' => 'Eliminar documentos'],
                ['name' => 'descargar_documento', 'description' => 'Descargar documentos'],
                ['name' => 'ver_categorias_documentos', 'description' => 'Ver categorías de documentos'],
            ],

            'Adjunto' => [
                ['name' => 'ver_adjuntos', 'description' => 'Ver adjuntos de novedades'],
                ['name' => 'subir_adjunto', 'description' => 'Subir adjuntos a novedades'],
                ['name' => 'eliminar_adjunto', 'description' => 'Eliminar adjuntos de novedades'],
                ['name' => 'descargar_adjunto', 'description' => 'Descargar adjuntos'],
            ],

            'CategoriaDocumento' => [
                ['name' => 'crear_categoria_documento', 'description' => 'Crear categorías de documentos'],
                ['name' => 'editar_categoria_documento', 'description' => 'Editar categorías de documentos'],
                ['name' => 'eliminar_categoria_documento', 'description' => 'Eliminar categorías de documentos'],
            ],

            'Organismo' => [
                ['name' => 'ver_organismos', 'description' => 'Ver listado de organismos'],
                ['name' => 'crear_organismo', 'description' => 'Crear organismos'],
                ['name' => 'editar_organismo', 'description' => 'Editar organismos'],
                ['name' => 'eliminar_organismo', 'description' => 'Eliminar organismos'],
            ],

            'Oficina' => [
                ['name' => 'ver_oficinas', 'description' => 'Ver listado de oficinas'],
                ['name' => 'crear_oficina', 'description' => 'Crear oficinas'],
                ['name' => 'editar_oficina', 'description' => 'Editar oficinas'],
                ['name' => 'eliminar_oficina', 'description' => 'Eliminar oficinas'],
            ],

            'Unidad' => [
                ['name' => 'ver_unidades', 'description' => 'Ver listado de unidades'],
                ['name' => 'crear_unidad', 'description' => 'Crear unidades'],
                ['name' => 'editar_unidad', 'description' => 'Editar unidades'],
                ['name' => 'eliminar_unidad', 'description' => 'Eliminar unidades'],
            ],

            'TipoVehiculo' => [
                ['name' => 'ver_tipos_vehiculo', 'description' => 'Ver tipos de vehículo'],
                ['name' => 'crear_tipo_vehiculo', 'description' => 'Crear tipos de vehículo'],
                ['name' => 'editar_tipo_vehiculo', 'description' => 'Editar tipos de vehículo'],
                ['name' => 'eliminar_tipo_vehiculo', 'description' => 'Eliminar tipos de vehículo'],
            ],

            'TipoCombustible' => [
                ['name' => 'ver_tipos_combustible', 'description' => 'Ver tipos de combustible'],
                ['name' => 'crear_tipo_combustible', 'description' => 'Crear tipos de combustible'],
                ['name' => 'editar_tipo_combustible', 'description' => 'Editar tipos de combustible'],
                ['name' => 'eliminar_tipo_combustible', 'description' => 'Eliminar tipos de combustible'],
            ],

            'TipoLubricante' => [
                ['name' => 'ver_tipos_lubricante', 'description' => 'Ver tipos de lubricante'],
                ['name' => 'crear_tipo_lubricante', 'description' => 'Crear tipos de lubricante'],
                ['name' => 'editar_tipo_lubricante', 'description' => 'Editar tipos de lubricante'],
                ['name' => 'eliminar_tipo_lubricante', 'description' => 'Eliminar tipos de lubricante'],
            ],

            'TipoRodado' => [
                ['name' => 'ver_tipos_rodado', 'description' => 'Ver tipos de rodado'],
                ['name' => 'crear_tipo_rodado', 'description' => 'Crear tipos de rodado'],
                ['name' => 'editar_tipo_rodado', 'description' => 'Editar tipos de rodado'],
                ['name' => 'eliminar_tipo_rodado', 'description' => 'Eliminar tipos de rodado'],
            ],

            'User' => [
                ['name' => 'ver_usuarios', 'description' => 'Ver listado de usuarios'],
                ['name' => 'ver_usuario', 'description' => 'Ver detalle de un usuario'],
                ['name' => 'crear_usuario', 'description' => 'Crear nuevos usuarios'],
                ['name' => 'editar_usuario', 'description' => 'Editar usuarios existentes'],
                ['name' => 'eliminar_usuario', 'description' => 'Eliminar usuarios'],
                ['name' => 'restaurar_usuario', 'description' => 'Restaurar usuarios eliminados'],
                ['name' => 'asignar_rol_usuario', 'description' => 'Asignar roles a usuarios'],
            ],

            'Rol' => [
                ['name' => 'ver_roles', 'description' => 'Ver listado de roles'],
                ['name' => 'ver_rol', 'description' => 'Ver detalle de un rol'],
                ['name' => 'crear_rol', 'description' => 'Crear nuevos roles'],
                ['name' => 'editar_rol', 'description' => 'Editar roles existentes'],
                ['name' => 'eliminar_rol', 'description' => 'Eliminar roles'],
                ['name' => 'asignar_permisos_rol', 'description' => 'Asignar permisos a roles'],
            ],

            'Permission' => [
                ['name' => 'ver_permisos', 'description' => 'Ver listado de permisos'],
                ['name' => 'ver_permiso', 'description' => 'Ver detalle de un permiso'],
                ['name' => 'crear_permiso', 'description' => 'Crear nuevos permisos'],
                ['name' => 'editar_permiso', 'description' => 'Editar permisos existentes'],
                ['name' => 'eliminar_permiso', 'description' => 'Eliminar permisos'],
            ],

            'GuardiaPdfDestinatario' => [
                ['name' => 'ver_destinatarios_pdf', 'description' => 'Ver destinatarios de PDF de guardia'],
                ['name' => 'crear_destinatario_pdf', 'description' => 'Crear destinatarios de PDF de guardia'],
                ['name' => 'editar_destinatario_pdf', 'description' => 'Editar destinatarios de PDF de guardia'],
                ['name' => 'eliminar_destinatario_pdf', 'description' => 'Eliminar destinatarios de PDF de guardia'],
                ['name' => 'asignar_usuarios_destinatario', 'description' => 'Asignar usuarios a destinatarios de PDF'],
            ],

            'Inventario' => [
                ['name' => 'ver_item', 'description' => 'Ver catálogo de ítems, unidades, movimientos y entregas de inventario'],
                ['name' => 'crear_item', 'description' => 'Dar de alta ítems y unidades individuales en inventario'],
                ['name' => 'editar_item', 'description' => 'Editar ítems del catálogo de inventario'],
                ['name' => 'eliminar_item', 'description' => 'Eliminar ítems del catálogo de inventario'],
                ['name' => 'ver_categoria', 'description' => 'Ver categorías de ítems'],
                ['name' => 'crear_categoria', 'description' => 'Crear categorías de ítems'],
                ['name' => 'editar_categoria', 'description' => 'Editar categorías de ítems'],
                ['name' => 'eliminar_categoria', 'description' => 'Eliminar categorías de ítems'],
                ['name' => 'ver_talla', 'description' => 'Ver tallas de ítems'],
                ['name' => 'crear_talla', 'description' => 'Crear tallas de ítems'],
                ['name' => 'editar_talla', 'description' => 'Editar tallas de ítems'],
                ['name' => 'eliminar_talla', 'description' => 'Eliminar tallas de ítems'],
                ['name' => 'ver_ubicacion', 'description' => 'Ver ubicaciones de depósito'],
                ['name' => 'crear_ubicacion', 'description' => 'Crear ubicaciones de depósito'],
                ['name' => 'editar_ubicacion', 'description' => 'Editar ubicaciones de depósito'],
                ['name' => 'eliminar_ubicacion', 'description' => 'Eliminar ubicaciones de depósito'],
                ['name' => 'registrar_entrada_inventario', 'description' => 'Registrar entradas de stock (ítems por cantidad)'],
                ['name' => 'registrar_salida_inventario', 'description' => 'Registrar salidas de stock (ítems por cantidad)'],
                ['name' => 'registrar_transferencia_inventario', 'description' => 'Transferir stock entre ubicaciones'],
                ['name' => 'registrar_entrega_inventario', 'description' => 'Registrar entregas de inventario a personal'],
                ['name' => 'ajustar_stock_inventario', 'description' => 'Ajustar stock tras un conteo físico'],
                ['name' => 'asignar_item_unidad', 'description' => 'Asignar/transferir una unidad individual a otra ubicación'],
                ['name' => 'reparar_item_unidad', 'description' => 'Marcar una unidad individual como en reparación'],
                ['name' => 'dar_baja_item_unidad', 'description' => 'Dar de baja definitivamente una unidad individual'],
            ],

            'Proveedor' => [
                ['name' => 'ver_proveedores', 'description' => 'Ver listado de proveedores'],
                ['name' => 'crear_proveedor', 'description' => 'Crear proveedores'],
                ['name' => 'editar_proveedor', 'description' => 'Editar proveedores'],
                ['name' => 'eliminar_proveedor', 'description' => 'Eliminar proveedores'],
            ],

            // Acciones de sistema que no pertenecen a un único modelo/tabla.
            'Sistema' => [
                ['name' => 'ver_reportes', 'description' => 'Ver reportes del sistema'],
                ['name' => 'generar_reportes', 'description' => 'Generar reportes personalizados'],
                ['name' => 'exportar_reportes', 'description' => 'Exportar reportes en diferentes formatos'],
                ['name' => 'ver_estadisticas', 'description' => 'Ver estadísticas del sistema'],
                ['name' => 'ver_dashboard', 'description' => 'Ver dashboard principal'],
                ['name' => 'ver_configuracion', 'description' => 'Ver configuración del sistema'],
                ['name' => 'editar_configuracion', 'description' => 'Editar configuración del sistema'],
                ['name' => 'ver_logs', 'description' => 'Ver logs del sistema'],
                ['name' => 'ver_auditoria', 'description' => 'Ver auditoría del sistema'],
                ['name' => 'importar_datos', 'description' => 'Importar datos al sistema'],
                ['name' => 'exportar_datos', 'description' => 'Exportar datos del sistema'],
                ['name' => 'backup_datos', 'description' => 'Realizar backup de datos'],
                ['name' => 'restaurar_datos', 'description' => 'Restaurar datos del sistema'],
                ['name' => 'acceso_api', 'description' => 'Acceso a la API del sistema'],
            ],

        ];

        foreach ($modulos as $modelo => $permisos) {
            foreach ($permisos as $permiso) {
                // updateOrCreate (no firstOrCreate) para que el 'model' se
                // sincronice también en los permisos que ya existían.
                Permission::updateOrCreate(
                    ['name' => $permiso['name']],
                    [
                        'description' => $permiso['description'],
                        'model'       => $modelo,
                    ]
                );
            }
        }
    }
}