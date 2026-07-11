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
            'archivo' => 'required|file|mimes:pdf,docx,doc,txt|max:10485760', // 10MB máx
        ];
    }
    #[Override]
    public function messages()
    {
        return [
            'archivo.mimes' => 'El archivo debe ser PDF, Word (.docx/ .doc) o TXT.',
            'archivo.max' => 'El archivo no puede superar los 10 MB.'
        ];
    }
}
