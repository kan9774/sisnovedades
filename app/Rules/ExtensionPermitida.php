<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class ExtensionPermitida implements ValidationRule
{
    /**
     * Valida por extensión del nombre original en lugar de mimes:, porque la
     * detección de mimetype falla con .zip según el navegador (application/zip
     * vs application/x-zip-compressed) y rechaza archivos válidos.
     *
     * @param  array<int, string>  $extensiones
     */
    public function __construct(
        protected array $extensiones,
    ) {
        $this->extensiones = array_map(
            static fn (string $extension): string => strtolower($extension),
            $extensiones,
        );
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): void  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail('El archivo no es válido.');

            return;
        }

        $extension = strtolower($value->getClientOriginalExtension());

        if (! in_array($extension, $this->extensiones, true)) {
            $fail('El formato del archivo no está permitido. Formatos válidos: '
                . implode(', ', $this->extensiones) . '.');
        }
    }
}
