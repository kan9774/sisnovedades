<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

class StoreDocumentoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
           'categoria_documento_id' => 'required|exists:categorias_documentos,id',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'archivo' => 'required|file|mimes:pdf,docx,doc|max:20480', // 20MB máx
        ];
    }
    #[Override]
    public function messages()
    {
        return [
            'archivo.mimes' => 'El archivo debe ser PDF o Word (.docx/ .doc).',
            'archivo.max' => 'el archivo no puede superar los 20 MB.'
        ];
    }
}
