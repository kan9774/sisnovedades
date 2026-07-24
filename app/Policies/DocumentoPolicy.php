<?php

namespace App\Policies;

use App\Models\Documento;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DocumentoPolicy
{
    public function viewAny(User $user): Response
    {
        return ($user->isAdmin() || $user->HasPermisos('ver_documento'))
            ? Response::allow()
            : Response::deny('No tienes permisos suficientes para listar el catálogo de documentos.');
    }

    public function view(User $user, Documento $documento): Response
    {
        return ($user->isAdmin() || $user->HasPermisos('ver_documento'))
            ? Response::allow()
            : Response::deny('No estás autorizado para visualizar o descargar este documento.');
    }

    public function create(User $user): Response
    {
        return ($user->isAdmin() || $user->HasPermisos('crear_documento'))
            ? Response::allow()
            : Response::deny('No cuentas con la autorización requerida para registrar o subir nuevos documentos.');
    }

    public function update(User $user, Documento $documento): Response
    {
        return ($user->isAdmin() || $user->HasPermisos('editar_documento'))
            ? Response::allow()
            : Response::deny('No tienes permisos para modificar este documento.');
    }

    public function delete(User $user, Documento $documento): Response
    {
        return ($user->isAdmin() || $user->HasPermisos('eliminar_documento'))
            ? Response::allow()
            : Response::deny('Acceso denegado: No puedes enviar este documento a la papelera.');
    }

    public function restore(User $user, Documento $documento): Response
    {
        return ($user->isAdmin() || $user->HasPermisos('eliminar_documento'))
            ? Response::allow()
            : Response::deny('No estás autorizado para restaurar documentos eliminados.');
    }

    public function forceDelete(User $user, Documento $documento): Response
    {
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('Esta acción requiere privilegios de administrador para eliminar permanentemente el archivo.');
    }
}