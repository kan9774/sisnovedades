/**
 * Helper global de confirmación con SweetAlert2.
 * Replica el estilo que ya usás en confirmarCierre()/confirmarCierreForzado()
 * (show.blade.php) para que todos los modales de confirmación del sistema
 * se vean iguales, sin depender de clases CSS custom (btn-ops, etc.)
 *
 * Cargar este script UNA vez, globalmente (layout principal o app.js),
 * antes de cualquier @push('js') que lo use.
 */
window.confirmarAccion = function ({
    title,
    text = '',
    icon = 'warning',
    confirmButtonText = 'Sí, confirmar',
    confirmButtonColor = '#dc3545', // rojo, para acciones destructivas
    onConfirm,
}) {
    return Swal.fire({
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonText,
        cancelButtonText: 'Cancelar',
        confirmButtonColor,
        reverseButtons: true,
        allowOutsideClick: false,
        allowEscapeKey: false,
    }).then((result) => {
        if (result.isConfirmed) {
            onConfirm?.();
        }
        return result;
    });
};