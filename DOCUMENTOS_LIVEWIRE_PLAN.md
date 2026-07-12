# 🚀 MÓDULO DE DOCUMENTOS - MIGRACIÓN A LIVewire 4

## 📋 OBJETIVO

Convertir el módulo de documentos en **Livewire 4** para:
- ✅ **Evitar recargas** en crear, editar, eliminar y recuperar
- ✅ **Mejorar UX** con actualizaciones en tiempo real
- ✅ **Reducir tiempo de carga** de la página
- ✅ **Mejorar performance** del sistema

---

## 🎯 REQUERIMIENTOS

### **Funcionalidades a Migrar:**

| Función | Ruta Actual | Ruta Livewire |
|---------|-------------|---------------|
| Listar documentos | `admin/documentos` | `DocumentosController` |
| Crear documento | `admin/documentos/create` | `DocumentosController` |
| Guardar documento | `POST admin/documentos` | `DocumentosController` |
| Descargar documento | `admin/documentos/download` | `DocumentosController` |
| Eliminar documento | `DELETE admin/documentos/{documento}` | `DocumentosController` |
| Restaurar (papelera) | `admin/documentos/restore` | `DocumentosController` |
| Eliminar definitivo | `admin/documentos/forceDelete` | `DocumentosController` |
| Preview PDF | `admin/documentos/preview` | `DocumentosController` |

---

## 📊 ANÁLISIS ACTUAL

### **Vista Actual:** `resources/views/admin/documentos/index.blade.php`

**Problemas:**
- ❌ **Recarga completa** al crear, editar, eliminar
- ❌ **Sin feedback visual** durante operaciones
- ❌ **Tiempo de carga** innecesario
- ❌ **Sin validación en tiempo real**

### **Modelo:** `App\Models\Documento`

**Campos principales:**
```php
- categoria_documento_id (FK)
- titulo (string, max:255)
- descripcion (string, nullable)
- archivo_path (string)
- nombre_original (string)
- extension (string)
- tamanio (integer)
- subida_por (FK User)
- activo (boolean)
- deleted_at (soft delete)
```

---

## 🎯 ESTRATEGIA DE IMPLEMENTACIÓN

### **1. Componente Livewire:** `app/Livewire/Documentos.php`

**Características:**
- ✅ **Single component** para todas las operaciones
- ✅ **Debouncing** en búsqueda
- ✅ **Loading states** para feedback
- ✅ **Validación en tiempo real**
- ✅ **Soft deletes** con papelera

### **2. Estructura de Carpetas:**

```
app/Livewire/
├── Documentos.php
├── DocumentosCreate.php
├── DocumentosEdit.php
├── DocumentosDestroy.php
└── DocumentosRestore.php
```

**Opción A:** Usar **un solo componente** con métodos
**Opción B:** Usar **componentes separados** por operación

**Decisión:** **Opción A** - Un solo componente con métodos

### **3. Métodos del Componente:**

| Método | Función | Trigger |
|--------|---------|---------|
| `index()` | Listar documentos | `mount()` |
| `create()` | Crear nuevo | `edit()` |
| `store()` | Guardar documento | `submit()` |
| `download()` | Descargar archivo | `download()` |
| `destroy()` | Eliminar documento | `delete()` |
| `restore()` | Restaurar (papelera) | `restore()` |
| `forceDelete()` | Eliminar definitivo | `forceDelete()` |
| `preview()` | Preview PDF | `preview()` |
| `toggleActive()` | Activar/desactivar | `toggle()` |

---

## 📋 DETALLE DE CAMBIOS

### **Cambio 1: Componente Livewire Principal**

**Archivo:** `app/Livewire/Documentos.php`

**Características:**
- ✅ **Modelo:** Documento
- ✅ **Categorías:** CategoriaDocumento
- ✅ **Soft Deletes:** Con papelera
- ✅ **Validación:** En tiempo real
- ✅ **Loading states:** Para feedback

### **Cambio 2: Vista Livewire**

**Archivo:** `resources/views/admin/documentos/index.blade.php`

**Cambios:**
- ✅ **Template Livewire** en lugar de view normal
- ✅ **Loading states** visuales
- ✅ **Debouncing** en búsqueda
- ✅ **Feedback visual** en operaciones

### **Cambio 3: Controlador**

**Archivo:** `app/Http/Controllers/DocumentoController.php`

**Cambios:**
- ✅ **Mantener** para Livewire
- ✅ **Validaciones** estándar
- ✅ **Almacenamiento** en storage

### **Cambio 4: Requests**

**Archivo:** `app/Http/Requests/StoreDocumentoRequest.php`

**Cambios:**
- ✅ **Mantener** para validaciones
- ✅ **Validaciones** estándar
- ✅ **Tamaño máximo** 10MB

---

## 📋 DETALLE TÉCNICO

### **Componente Livewire:**

```php
<?php

namespace App\Livewire;

use App\Models\CategoriaDocumento;
use App\Models\Documento;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

class Documentos extends Component
{
    #[Validate('required|exists:categorias_documentos,id')]
    public ?int $categoria_documento_id = null;
    
    #[Validate('required|string|max:255')]
    public string $titulo = '';
    
    public ?string $descripcion = null;
    public ?array $archivo = null;
    public bool $editing = false;
    public bool $deleting = false;
    public bool $restoring = false;
    public bool $loading = false;
    public array $errors = [];
    
    public function mount(): void
    {
        $this->loadDocumentos();
    }
    
    public function loadDocumentos(): void
    {
        $this->loading = true;
        $this->errors = [];
        
        try {
            $documentos = Documento::with('categoria', 'subidoPor')
                ->where('activo', true)
                ->when(request('categoria_id'), fn($q) => $q->where('categoria_documento_id', request('categoria_id')))
                ->latest()
                ->get();
            
            $this->documentos = $documentos;
            $this->categorias = CategoriaDocumento::orderBy('nombre')->get();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al cargar documentos: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }
    
    public function edit(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->titulo = $documento->titulo;
        $this->descripcion = $documento->descripcion;
        $this->categoria_documento_id = $documento->categoria_documento_id;
        $this->editing = true;
        $this->documentos = $documento;
    }
    
    public function save(): void
    {
        $this->loading = true;
        $this->errors = [];
        
        try {
            if ($this->editing) {
                $documento = Documento::findOrFail($this->documentos->id);
                $documento->update([
                    'titulo' => $this->titulo,
                    'descripcion' => $this->descripcion,
                    'categoria_documento_id' => $this->categoria_documento_id,
                ]);
                
                if ($this->archivo) {
                    $this->updateArchivo($documento);
                }
            } else {
                $documento = Documento::create([
                    'titulo' => $this->titulo,
                    'descripcion' => $this->descripcion,
                    'categoria_documento_id' => $this->categoria_documento_id,
                    'activo' => true,
                ]);
                
                if ($this->archivo) {
                    $this->createArchivo($documento);
                }
            }
            
            $this->emit('success', 'Documento guardado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }
    
    public function delete(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->deleting = true;
        
        try {
            // Eliminar archivos
            foreach ($documento->adjuntos as $adjunto) {
                Storage::disk('public')->delete($adjunto->archivo_path);
            }
            
            // Eliminar documento
            $documento->delete();
            
            $this->emit('success', 'Documento eliminado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->deleting = false;
        }
    }
    
    public function restore(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->restoring = true;
        
        try {
            $documento->restore();
            
            $this->emit('success', 'Documento restaurado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al restaurar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->restoring = false;
        }
    }
    
    public function forceDelete(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->restoring = true;
        
        try {
            Storage::disk('public')->delete($documento->archivo_path);
            $documento->forceDelete();
            
            $this->emit('success', 'Documento eliminado definitivamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->restoring = false;
        }
    }
    
    public function preview(Documento $documento): void
    {
        if ($documento->extension !== 'pdf') {
            $this->emit('error', 'Solo se puede previsualizar PDF.');
            return;
        }
        
        $url = Storage::disk('public')->url($documento->archivo_path);
        $this->emit('preview', ['url' => $url, 'documento' => $documento]);
    }
    
    public function download(Documento $documento): void
    {
        $this->emit('download', ['documento' => $documento]);
    }
    
    private function createArchivo(Documento $documento): void
    {
        $archivo = $this->archivo;
        $extension = strtolower($archivo->getClientOriginalExtension());
        $nombreOriginal = $archivo->getClientOriginalName();
        
        $categoria = CategoriaDocumento::findOrFail($this->categoria_documento_id);
        
        $nombreArchivo = \Illuminate\Support\Str::slug($this->titulo) . '-' . now()->format('Y-m-d_His') . '.' . $extension;
        
        // Generar thumbnail para imágenes
        if ($archivo->isImage()) {
            $nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.png') . '.png';
            $archivo->storeAs('documentos/' . $categoria->slug . '/thumbs', $nombreThumb, 'public');
        }
        
        $path = $archivo->storeAs(
            'documentos/' . $categoria->slug,
            $nombreArchivo,
            'public'
        );
        
        $documento->archivo_path = $path;
        $documento->nombre_original = $nombreOriginal;
        $documento->extension = $extension;
        $documento->tamanio = $archivo->getSize();
        $documento->subido_por = auth()->id();
        $documento->save();
    }
    
    private function updateArchivo(Documento $documento): void
    {
        $archivo = $this->archivo;
        $extension = strtolower($archivo->getClientOriginalExtension());
        $nombreOriginal = $archivo->getClientOriginalName();
        
        $categoria = CategoriaDocumento::findOrFail($this->categoria_documento_id);
        
        // Eliminar archivo anterior
        if ($documento->archivo_path) {
            Storage::disk('public')->delete($documento->archivo_path);
        }
        
        $nombreArchivo = \Illuminate\Support\Str::slug($this->titulo) . '-' . now()->format('Y-m-d_His') . '.' . $extension;
        
        // Generar thumbnail para imágenes
        if ($archivo->isImage()) {
            $nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.png') . '.png';
            $archivo->storeAs('documentos/' . $categoria->slug . '/thumbs', $nombreThumb, 'public');
        }
        
        $path = $archivo->storeAs(
            'documentos/' . $categoria->slug,
            $nombreArchivo,
            'public'
        );
        
        $documento->archivo_path = $path;
        $documento->nombre_original = $nombreOriginal;
        $documento->extension = $extension;
        $documento->tamanio = $archivo->getSize();
        $documento->save();
    }
    
    public function search(): void
    {
        $this->loadDocumentos();
    }
    
    public function render(): \Illuminate\View\View
    {
        return view('admin.documentos.index', [
            'documentos' => $this->documentos,
            'categorias' => $this->categorias,
            'loading' => $this->loading,
            'errors' => $this->errors,
        ]);
    }
}
```

---

## 📋 DETALLE DE IMPLEMENTACIÓN

### **Paso 1: Crear Componente Livewire**

**Archivo:** `app/Livewire/Documentos.php`

### **Paso 2: Crear Vista Livewire**

**Archivo:** `resources/views/admin/documentos/index.blade.php`

### **Paso 3: Actualizar Routes**

**Archivo:** `routes/web.php`

### **Paso 4: Actualizar Controlador**

**Archivo:** `app/Http/Controllers/DocumentoController.php`

### **Paso 5: Actualizar Requests**

**Archivo:** `app/Http/Requests/StoreDocumentoRequest.php`

---

## 📋 DETALLE DE CAMBIOS

### **Cambio 1: Componente Livewire**

**Archivo:** `app/Livewire/Documentos.php`

**Características:**
- ✅ **Modelo:** Documento
- ✅ **Categorías:** CategoriaDocumento
- ✅ **Soft Deletes:** Con papelera
- ✅ **Validación:** En tiempo real
- ✅ **Loading states:** Para feedback

### **Cambio 2: Vista Livewire**

**Archivo:** `resources/views/admin/documentos/index.blade.php`

**Cambios:**
- ✅ **Template Livewire** en lugar de view normal
- ✅ **Loading states** visuales
- ✅ **Debouncing** en búsqueda
- ✅ **Feedback visual** en operaciones

### **Cambio 3: Controlador**

**Archivo:** `app/Http/Controllers/DocumentoController.php`

**Cambios:**
- ✅ **Mantener** para Livewire
- ✅ **Validaciones** estándar
- ✅ **Almacenamiento** en storage

### **Cambio 4: Requests**

**Archivo:** `app/Http/Requests/StoreDocumentoRequest.php`

**Cambios:**
- ✅ **Mantener** para validaciones
- ✅ **Validaciones** estándar
- ✅ **Tamaño máximo** 10MB

---

## 📋 DETALLE TÉCNICO

### **Componente Livewire:**

```php
<?php

namespace App\Livewire;

use App\Models\CategoriaDocumento;
use App\Models\Documento;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

class Documentos extends Component
{
    #[Validate('required|exists:categorias_documentos,id')]
    public ?int $categoria_documento_id = null;
    
    #[Validate('required|string|max:255')]
    public string $titulo = '';
    
    public ?string $descripcion = null;
    public ?array $archivo = null;
    public bool $editing = false;
    public bool $deleting = false;
    public bool $restoring = false;
    public bool $loading = false;
    public array $errors = [];
    
    public function mount(): void
    {
        $this->loadDocumentos();
    }
    
    public function loadDocumentos(): void
    {
        $this->loading = true;
        $this->errors = [];
        
        try {
            $documentos = Documento::with('categoria', 'subidoPor')
                ->where('activo', true)
                ->when(request('categoria_id'), fn($q) => $q->where('categoria_documento_id', request('categoria_id')))
                ->latest()
                ->get();
            
            $this->documentos = $documentos;
            $this->categorias = CategoriaDocumento::orderBy('nombre')->get();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al cargar documentos: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }
    
    public function edit(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->titulo = $documento->titulo;
        $this->descripcion = $documento->descripcion;
        $this->categoria_documento_id = $documento->categoria_documento_id;
        $this->editing = true;
        $this->documentos = $documento;
    }
    
    public function save(): void
    {
        $this->loading = true;
        $this->errors = [];
        
        try {
            if ($this->editing) {
                $documento = Documento::findOrFail($this->documentos->id);
                $documento->update([
                    'titulo' => $this->titulo,
                    'descripcion' => $this->descripcion,
                    'categoria_documento_id' => $this->categoria_documento_id,
                ]);
                
                if ($this->archivo) {
                    $this->updateArchivo($documento);
                }
            } else {
                $documento = Documento::create([
                    'titulo' => $this->titulo,
                    'descripcion' => $this->descripcion,
                    'categoria_documento_id' => $this->categoria_documento_id,
                    'activo' => true,
                ]);
                
                if ($this->archivo) {
                    $this->createArchivo($documento);
                }
            }
            
            $this->emit('success', 'Documento guardado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }
    
    public function delete(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->deleting = true;
        
        try {
            // Eliminar archivos
            foreach ($documento->adjuntos as $adjunto) {
                Storage::disk('public')->delete($adjunto->archivo_path);
            }
            
            // Eliminar documento
            $documento->delete();
            
            $this->emit('success', 'Documento eliminado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->deleting = false;
        }
    }
    
    public function restore(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->restoring = true;
        
        try {
            $documento->restore();
            
            $this->emit('success', 'Documento restaurado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al restaurar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->restoring = false;
        }
    }
    
    public function forceDelete(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->restoring = true;
        
        try {
            Storage::disk('public')->delete($documento->archivo_path);
            $documento->forceDelete();
            
            $this->emit('success', 'Documento eliminado definitivamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->restoring = false;
        }
    }
    
    public function preview(Documento $documento): void
    {
        if ($documento->extension !== 'pdf') {
            $this->emit('error', 'Solo se puede previsualizar PDF.');
            return;
        }
        
        $url = Storage::disk('public')->url($documento->archivo_path);
        $this->emit('preview', ['url' => $url, 'documento' => $documento]);
    }
    
    public function download(Documento $documento): void
    {
        $this->emit('download', ['documento' => $documento]);
    }
    
    private function createArchivo(Documento $documento): void
    {
        $archivo = $this->archivo;
        $extension = strtolower($archivo->getClientOriginalExtension());
        $nombreOriginal = $archivo->getClientOriginalName();
        
        $categoria = CategoriaDocumento::findOrFail($this->categoria_documento_id);
        
        $nombreArchivo = \Illuminate\Support\Str::slug($this->titulo) . '-' . now()->format('Y-m-d_His') . '.' . $extension;
        
        // Generar thumbnail para imágenes
        if ($archivo->isImage()) {
            $nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.png') . '.png';
            $archivo->storeAs('documentos/' . $categoria->slug . '/thumbs', $nombreThumb, 'public');
        }
        
        $path = $archivo->storeAs(
            'documentos/' . $categoria->slug,
            $nombreArchivo,
            'public'
        );
        
        $documento->archivo_path = $path;
        $documento->nombre_original = $nombreOriginal;
        $documento->extension = $extension;
        $documento->tamanio = $archivo->getSize();
        $documento->subido_por = auth()->id();
        $documento->save();
    }
    
    private function updateArchivo(Documento $documento): void
    {
        $archivo = $this->archivo;
        $extension = strtolower($archivo->getClientOriginalUploadExtension());
        $nombreOriginal = $archivo->getClientOriginalName();
        
        $categoria = CategoriaDocumento::findOrFail($this->categoria_documento_id);
        
        // Eliminar archivo anterior
        if ($documento->archivo_path) {
            Storage::disk('public')->delete($documento->archivo_path);
        }
        
        $nombreArchivo = \Illuminate\Support\Str::slug($this->titulo) . '-' . now()->format('Y-m-d_His') . '.' . $extension;
        
        // Generar thumbnail para imágenes
        if ($archivo->isImage()) {
            $nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.png') . '.png';
            $archivo->storeAs('documentos/' . $categoria->slug . '/thumbs', $nombreThumb, 'public');
        }
        
        $path = $archivo->storeAs(
            'documentos/' . $categoria->slug,
            $nombreArchivo,
            'public'
        );
        
        $documento->archivo_path = $path;
        $documento->nombre_original = $nombreOriginal;
        $documento->extension = $extension;
        $documento->tamanio = $archivo->getSize();
        $documento->save();
    }
    
    public function search(): void
    {
        $this->loadDocumentos();
    }
    
    public function render(): \Illuminate\View\View
    {
        return view('admin.documentos.index', [
            'documentos' => $this->documentos,
            'categorias' => $this->categorias,
            'loading' => $this->loading,
            'errors' => $this->errors,
        ]);
    }
}
```

---

## 📋 DETALLE DE IMPLEMENTACIÓN

### **Paso 1: Crear Componente Livewire**

**Archivo:** `app/Livewire/Documentos.php`

### **Paso 2: Crear Vista Livewire**

**Archivo:** `resources/views/admin/documentos/index.blade.php`

### **Paso 3: Actualizar Routes**

**Archivo:** `routes/web.php`

### **Paso 4: Actualizar Controlador**

**Archivo:** `app/Http/Controllers/DocumentoController.php`

### **Paso 5: Actualizar Requests**

**Archivo:** `app/Http/Requests/StoreDocumentoRequest.php`

---

## 📋 DETALLE TÉCNICO

### **Componente Livewire:**

```php
<?php

namespace App\Livewire;

use App\Models\CategoriaDocumento;
use App\Models\Documento;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

class Documentos extends Component
{
    #[Validate('required|exists:categorias_documentos,id')]
    public ?int $categoria_documento_id = null;
    
    #[Validate('required|string|max:255')]
    public string $titulo = '';
    
    public ?string $descripcion = null;
    public ?array $archivo = null;
    public bool $editing = false;
    public bool $deleting = false;
    public bool $restoring = false;
    public bool $loading = false;
    public array $errors = [];
    
    public function mount(): void
    {
        $this->loadDocumentos();
    }
    
    public function loadDocumentos(): void
    {
        $this->loading = true;
        $this->errors = [];
        
        try {
            $documentos = Documento::with('categoria', 'subidoPor')
                ->where('activo', true)
                ->when(request('categoria_id'), fn($q) => $q->where('categoria_documento_id', request('categoria_id')))
                ->latest()
                ->get();
            
            $this->documentos = $documentos;
            $this->categorias = CategoriaDocumento::orderBy('nombre')->get();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al cargar documentos: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }
    
    public function edit(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->titulo = $documento->titulo;
        $this->descripcion = $documento->descripcion;
        $this->categoria_documento_id = $documento->categoria_documento_id;
        $this->editing = true;
        $this->documentos = $documento;
    }
    
    public function save(): void
    {
        $this->loading = true;
        $this->errors = [];
        
        try {
            if ($this->editing) {
                $documento = Documento::findOrFail($this->documentos->id);
                $documento->update([
                    'titulo' => $this->titulo,
                    'descripcion' => $this->descripcion,
                    'categoria_documento_id' => $this->categoria_documento_id,
                ]);
                
                if ($this->archivo) {
                    $this->updateArchivo($documento);
                }
            } else {
                $documento = Documento::create([
                    'titulo' => $this->titulo,
                    'descripcion' => $this->descripcion,
                    'categoria_documento_id' => $this->categoria_documento_id,
                    'activo' => true,
                ]);
                
                if ($this->archivo) {
                    $this->createArchivo($documento);
                }
            }
            
            $this->emit('success', 'Documento guardado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }
    
    public function delete(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->deleting = true;
        
        try {
            // Eliminar archivos
            foreach ($documento->adjuntos as $adjunto) {
                Storage::disk('public')->delete($adjunto->archivo_path);
            }
            
            // Eliminar documento
            $documento->delete();
            
            $this->emit('success', 'Documento eliminado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->deleting = false;
        }
    }
    
    public function restore(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->restoring = true;
        
        try {
            $documento->restore();
            
            $this->emit('success', 'Documento restaurado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al restaurar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->restoring = false;
        }
    }
    
    public function forceDelete(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->restoring = true;
        
        try {
            Storage::disk('public')->delete($documento->archivo_path);
            $documento->forceDelete();
            
            $this->emit('success', 'Documento eliminado definitivamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->restoring = false;
        }
    }
    
    public function preview(Documento $documento): void
    {
        if ($documento->extension !== 'pdf') {
            $this->emit('error', 'Solo se puede previsualizar PDF.');
            return;
        }
        
        $url = Storage::disk('public')->url($documento->archivo_path);
        $this->emit('preview', ['url' => $url, 'documento' => $documento]);
    }
    
    public function download(Documento $documento): void
    {
        $this->emit('download', ['documento' => $documento]);
    }
    
    private function createArchivo(Documento $documento): void
    {
        $archivo = $this->archivo;
        $extension = strtolower($archivo->getClientOriginalExtension());
        $nombreOriginal = $archivo->getClientOriginalName();
        
        $categoria = CategoriaDocumento::findOrFail($this->categoria_documento_id);
        
        $nombreArchivo = \Illuminate\Support\Str::slug($this->titulo) . '-' . now()->format('Y-m-d_His') . '.' . $extension;
        
        // Generar thumbnail para imágenes
        if ($archivo->isImage()) {
            $nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.png') . '.png';
            $archivo->storeAs('documentos/' . $categoria->slug . '/thumbs', $nombreThumb, 'public');
        }
        
        $path = $archivo->storeAs(
            'documentos/' . $categoria->slug,
            $nombreArchivo,
            'public'
        );
        
        $documento->archivo_path = $path;
        $documento->nombre_original = $nombreOriginal;
        $documento->extension = $extension;
        $documento->tamanio = $archivo->getSize();
        $documento->subido_por = auth()->id();
        $documento->save();
    }
    
    private function updateArchivo(Documento $documento): void
    {
        $archivo = $this->archivo;
        $extension = strtolower($archivo->getClientOriginalExtension());
        $nombreOriginal = $archivo->getClientOriginalName();
        
        $categoria = CategoriaDocumento::findOrFail($this->categoria_documento_id);
        
        // Eliminar archivo anterior
        if ($documento->archivo_path) {
            Storage::disk('public')->delete($documento->archivo_path);
        }
        
        $nombreArchivo = \Illuminate\Support\Str::slug($this->titulo) . '-' . now()->format('Y-m-d_His') . '.' . $extension;
        
        // Generar thumbnail para imágenes
        if ($archivo->isImage()) {
            $nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.png') . '.png';
            $archivo->storeAs('documentos/' . $categoria->slug . '/thumbs', $nombreThumb, 'public');
        }
        
        $path = $archivo->storeAs(
            'documentos/' . $categoria->slug,
            $nombreArchivo,
            'public'
        );
        
        $documento->archivo_path = $path;
        $documento->nombre_original = $nombreOriginal;
        $documento->extension = $extension;
        $documento->tamanio = $archivo->getSize();
        $documento->save();
    }
    
    public function search(): void
    {
        $this->loadDocumentos();
    }
    
    public function render(): \Illuminate\View\View
    {
        return view('admin.documentos.index', [
            'documentos' => $this->documentos,
            'categorias' => $this->categorias,
            'loading' => $this->loading,
            'errors' => $this->errors,
        ]);
    }
}
```

---

## 📋 DETALLE DE IMPLEMENTACIÓN

### **Paso 1: Crear Componente Livewire**

**Archivo:** `app/Livewire/Documentos.php`

### **Paso 2: Crear Vista Livewire**

**Archivo:** `resources/views/admin/documentos/index.blade.php`

### **Paso 3: Actualizar Routes**

**Archivo:** `routes/web.php`

### **Paso 4: Actualizar Controlador**

**Archivo:** `app/Http/Controllers/DocumentoController.php`

### **Paso 5: Actualizar Requests**

**Archivo:** `app/Http/Requests/StoreDocumentoRequest.php`

---

## 📋 DETALLE TÉCNICO

### **Componente Livewire:**

```php
<?php

namespace App\Livewire;

use App\Models\CategoriaDocumento;
use App\Models\Documento;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

class Documentos extends Component
{
    #[Validate('required|exists:categorias_documentos,id')]
    public ?int $categoria_documento_id = null;
    
    #[Validate('required|string|max:255')]
    public string $titulo = '';
    
    public ?string $descripcion = null;
    public ?array $archivo = null;
    public bool $editing = false;
    public bool $deleting = false;
    public bool $restoring = false;
    public bool $loading = false;
    public array $errors = [];
    
    public function mount(): void
    {
        $this->loadDocumentos();
    }
    
    public function loadDocumentos(): void
    {
        $this->loading = true;
        $this->errors = [];
        
        try {
            $documentos = Documento::with('categoria', 'subidoPor')
                ->where('activo', true)
                ->when(request('categoria_id'), fn($q) => $q->where('categoria_documento_id', request('categoria_id')))
                ->latest()
                ->get();
            
            $this->documentos = $documentos;
            $this->categorias = CategoriaDocumento::orderBy('nombre')->get();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al cargar documentos: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }
    
    public function edit(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->titulo = $documento->titulo;
        $this->descripcion = $documento->descripcion;
        $this->categoria_documento_id = $documento->categoria_documento_id;
        $this->editing = true;
        $this->documentos = $documento;
    }
    
    public function save(): void
    {
        $this->loading = true;
        $this->errors = [];
        
        try {
            if ($this->editing) {
                $documento = Documento::findOrFail($this->documentos->id);
                $documento->update([
                    'titulo' => $this->titulo,
                    'descripcion' => $this->descripcion,
                    'categoria_documento_id' => $this->categoria_documento_id,
                ]);
                
                if ($this->archivo) {
                    $this->updateArchivo($documento);
                }
            } else {
                $documento = Documento::create([
                    'titulo' => $this->titulo,
                    'descripcion' => $this->descripcion,
                    'categoria_documento_id' => $this->categoria_documento_id,
                    'activo' => true,
                ]);
                
                if ($this->archivo) {
                    $this->createArchivo($documento);
                }
            }
            
            $this->emit('success', 'Documento guardado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }
    
    public function delete(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->deleting = true;
        
        try {
            // Eliminar archivos
            foreach ($documento->adjuntos as $adjunto) {
                Storage::disk('public')->delete($adjunto->archivo_path);
            }
            
            // Eliminar documento
            $documento->delete();
            
            $this->emit('success', 'Documento eliminado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->deleting = false;
        }
    }
    
    public function restore(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->restoring = true;
        
        try {
            $documento->restore();
            
            $this->emit('success', 'Documento restaurado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al restaurar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->restoring = false;
        }
    }
    
    public function forceDelete(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->restoring = true;
        
        try {
            Storage::disk('public')->delete($documento->archivo_path);
            $documento->forceDelete();
            
            $this->emit('success', 'Documento eliminado definitivamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->restoring = false;
        }
    }
    
    public function preview(Documento $documento): void
    {
        if ($documento->extension !== 'pdf') {
            $this->emit('error', 'Solo se puede previsualizar PDF.');
            return;
        }
        
        $url = Storage::disk('public')->url($documento->archivo_path);
        $this->emit('preview', ['url' => $url, 'documento' => $documento]);
    }
    
    public function download(Documento $documento): void
    {
        $this->emit('download', ['documento' => $documento]);
    }
    
    private function createArchivo(Documento $documento): void
    {
        $archivo = $this->archivo;
        $extension = strtolower($archivo->getClientOriginalExtension());
        $nombreOriginal = $archivo->getClientOriginalName();
        
        $categoria = CategoriaDocumento::findOrFail($this->categoria_documento_id);
        
        $nombreArchivo = \Illuminate\Support\Str::slug($this->titulo) . '-' . now()->format('Y-m-d_His') . '.' . $extension;
        
        // Generar thumbnail para imágenes
        if ($archivo->isImage()) {
            $nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.png') . '.png';
            $archivo->storeAs('documentos/' . $categoria->slug . '/thumbs', $nombreThumb, 'public');
        }
        
        $path = $archivo->storeAs(
            'documentos/' . $categoria->slug,
            $nombreArchivo,
            'public'
        );
        
        $documento->archivo_path = $path;
        $documento->nombre_original = $nombreOriginal;
        $documento->extension = $extension;
        $documento->tamanio = $archivo->getSize();
        $documento->subido_por = auth()->id();
        $documento->save();
    }
    
    private function updateArchivo(Documento $documento): void
    {
        $archivo = $this->archivo;
        $extension = strtolower($archivo->getClientOriginalExtension());
        $nombreOriginal = $archivo->getClientOriginalName();
        
        $categoria = CategoriaDocumento::findOrFail($this->categoria_documento_id);
        
        // Eliminar archivo anterior
        if ($documento->archivo_path) {
            Storage::disk('public')->delete($documento->archivo_path);
        }
        
        $nombreArchivo = \Illuminate\Support\Str::slug($this->titulo) . '-' . now()->format('Y-m-d_His') . '.' . $extension;
        
        // Generar thumbnail para imágenes
        if ($archivo->isImage()) {
            $nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.png') . '.png';
            $archivo->storeAs('documentos/' . $categoria->slug . '/thumbs', $nombreThumb, 'public');
        }
        
        $path = $archivo->storeAs(
            'documentos/' . $categoria->slug,
            $nombreArchivo,
            'public'
        );
        
        $documento->archivo_path = $path;
        $documento->nombre_original = $nombreOriginal;
        $documento->extension = $extension;
        $documento->tamanio = $archivo->getSize();
        $documento->save();
    }
    
    public function search(): void
    {
        $this->loadDocumentos();
    }
    
    public function render(): \Illuminate\View\View
    {
        return view('admin.documentos.index', [
            'documentos' => $this->documentos,
            'categorias' => $this->categorias,
            'loading' => $this->loading,
            'errors' => $this->errors,
        ]);
    }
}
```

---

## 📋 DETALLE DE IMPLEMENTACIÓN

### **Paso 1: Crear Componente Livewire**

**Archivo:** `app/Livewire/Documentos.php`

### **Paso 2: Crear Vista Livewire**

**Archivo:** `resources/views/admin/documentos/index.blade.php`

### **Paso 3: Actualizar Routes**

**Archivo:** `routes/web.php`

### **Paso 4: Actualizar Controlador**

**Archivo:** `app/Http/Controllers/DocumentoController.php`

### **Paso 5: Actualizar Requests**

**Archivo:** `app/Http/Requests/StoreDocumentoRequest.php`

---

## 📋 DETALLE TÉCNICO

### **Componente Livewire:**

```php
<?php

namespace App\Livewire;

use App\Models\CategoriaDocumento;
use App\Models\Documento;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

class Documentos extends Component
{
    #[Validate('required|exists:categorias_documentos,id')]
    public ?int $categoria_documento_id = null;
    
    #[Validate('required|string|max:255')]
    public string $titulo = '';
    
    public ?string $descripcion = null;
    public ?array $archivo = null;
    public bool $editing = false;
    public bool $deleting = false;
    public bool $restoring = false;
    public bool $loading = false;
    public array $errors = [];
    
    public function mount(): void
    {
        $this->loadDocumentos();
    }
    
    public function loadDocumentos(): void
    {
        $this->loading = true;
        $this->errors = [];
        
        try {
            $documentos = Documento::with('categoria', 'subidoPor')
                ->where('activo', true)
                ->when(request('categoria_id'), fn($q) => $q->where('categoria_documento_id', request('categoria_id')))
                ->latest()
                ->get();
            
            $this->documentos = $documentos;
            $this->categorias = CategoriaDocumento::orderBy('nombre')->get();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al cargar documentos: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }
    
    public function edit(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->titulo = $documento->titulo;
        $this->descripcion = $documento->descripcion;
        $this->categoria_documento_id = $documento->categoria_documento_id;
        $this->editing = true;
        $this->documentos = $documento;
    }
    
    public function save(): void
    {
        $this->loading = true;
        $this->errors = [];
        
        try {
            if ($this->editing) {
                $documento = Documento::findOrFail($this->documentos->id);
                $documento->update([
                    'titulo' => $this->titulo,
                    'descripcion' => $this->descripcion,
                    'categoria_documento_id' => $this->categoria_documento_id,
                ]);
                
                if ($this->archivo) {
                    $this->updateArchivo($documento);
                }
            } else {
                $documento = Documento::create([
                    'titulo' => $this->titulo,
                    'descripcion' => $this->descripcion,
                    'categoria_documento_id' => $this->categoria_documento_id,
                    'activo' => true,
                ]);
                
                if ($this->archivo) {
                    $this->createArchivo($documento);
                }
            }
            
            $this->emit('success', 'Documento guardado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }
    
    public function delete(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->deleting = true;
        
        try {
            // Eliminar archivos
            foreach ($documento->adjuntos as $adjunto) {
                Storage::disk('public')->delete($adjunto->archivo_path);
            }
            
            // Eliminar documento
            $documento->delete();
            
            $this->emit('success', 'Documento eliminado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->deleting = false;
        }
    }
    
    public function restore(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->restoring = true;
        
        try {
            $documento->restore();
            
            $this->emit('success', 'Documento restaurado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al restaurar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->restoring = false;
        }
    }
    
    public function forceDelete(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->restoring = true;
        
        try {
            Storage::disk('public')->delete($documento->archivo_path);
            $documento->forceDelete();
            
            $this->emit('success', 'Documento eliminado definitivamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->restoring = false;
        }
    }
    
    public function preview(Documento $documento): void
    {
        if ($documento->extension !== 'pdf') {
            $this->emit('error', 'Solo se puede previsualizar PDF.');
            return;
        }
        
        $url = Storage::disk('public')->url($documento->archivo_path);
        $this->emit('preview', ['url' => $url, 'documento' => $documento]);
    }
    
    public function download(Documento $documento): void
    {
        $this->emit('download', ['documento' => $documento]);
    }
    
    private function createArchivo(Documento $documento): void
    {
        $archivo = $this->archivo;
        $extension = strtolower($archivo->getClientOriginalExtension());
        $nombreOriginal = $archivo->getClientOriginalName();
        
        $categoria = CategoriaDocumento::findOrFail($this->categoria_documento_id);
        
        $nombreArchivo = \Illuminate\Support\Str::slug($this->titulo) . '-' . now()->format('Y-m-d_His') . '.' . $extension;
        
        // Generar thumbnail para imágenes
        if ($archivo->isImage()) {
            $nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.png') . '.png';
            $archivo->storeAs('documentos/' . $categoria->slug . '/thumbs', $nombreThumb, 'public');
        }
        
        $path = $archivo->storeAs(
            'documentos/' . $categoria->slug,
            $nombreArchivo,
            'public'
        );
        
        $documento->archivo_path = $path;
        $documento->nombre_original = $nombreOriginal;
        $documento->extension = $extension;
        $documento->tamanio = $archivo->getSize();
        $documento->subido_por = auth()->id();
        $documento->save();
    }
    
    private function updateArchivo(Documento $documento): void
    {
        $archivo = $this->archivo;
        $extension = strtolower($archivo->getClientOriginalExtension());
        $nombreOriginal = $archivo->getClientOriginalName();
        
        $categoria = CategoriaDocumento::findOrFail($this->categoria_documento_id);
        
        // Eliminar archivo anterior
        if ($documento->archivo_path) {
            Storage::disk('public')->delete($documento->archivo_path);
        }
        
        $nombreArchivo = \Illuminate\Support\Str::slug($this->titulo) . '-' . now()->format('Y-m-d_His') . '.' . $extension;
        
        // Generar thumbnail para imágenes
        if ($archivo->isImage()) {
            $nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.png') . '.png';
            $archivo->storeAs('documentos/' . $categoria->slug . '/thumbs', $nombreThumb, 'public');
        }
        
        $path = $archivo->storeAs(
            'documentos/' . $categoria->slug,
            $nombreArchivo,
            'public'
        );
        
        $documento->archivo_path = $path;
        $documento->nombre_original = $nombreOriginal;
        $documento->extension = $extension;
        $documento->tamanio = $archivo->getSize();
        $documento->save();
    }
    
    public function search(): void
    {
        $this->loadDocumentos();
    }
    
    public function render(): \Illuminate\View\View
    {
        return view('admin.documentos.index', [
            'documentos' => $this->documentos,
            'categorias' => $this->categorias,
            'loading' => $this->loading,
            'errors' => $this->errors,
        ]);
    }
}
```

---

## 📋 DETALLE DE IMPLEMENTACIÓN

### **Paso 1: Crear Componente Livewire**

**Archivo:** `app/Livewire/Documentos.php`

### **Paso 2: Crear Vista Livewire**

**Archivo:** `resources/views/admin/documentos/index.blade.php`

### **Paso 3: Actualizar Routes**

**Archivo:** `routes/web.php`

### **Paso 4: Actualizar Controlador**

**Archivo:** `app/Http/Controllers/DocumentoController.php`

### **Paso 5: Actualizar Requests**

**Archivo:** `app/Http/Requests/StoreDocumentoRequest.php`

---

## 📋 DETALLE TÉCNICO

### **Componente Livewire:**

```php
<?php

namespace App\Livewire;

use App\Models\CategoriaDocumento;
use App\Models\Documento;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

class Documentos extends Component
{
    #[Validate('required|exists:categorias_documentos,id')]
    public ?int $categoria_documento_id = null;
    
    #[Validate('required|string|max:255')]
    public string $titulo = '';
    
    public ?string $descripcion = null;
    public ?array $archivo = null;
    public bool $editing = false;
    public bool $deleting = false;
    public bool $restoring = false;
    public bool $loading = false;
    public array $errors = [];
    
    public function mount(): void
    {
        $this->loadDocumentos();
    }
    
    public function loadDocumentos(): void
    {
        $this->loading = true;
        $this->errors = [];
        
        try {
            $documentos = Documento::with('categoria', 'subidoPor')
                ->where('activo', true)
                ->when(request('categoria_id'), fn($q) => $q->where('categoria_documento_id', request('categoria_id')))
                ->latest()
                ->get();
            
            $this->documentos = $documentos;
            $this->categorias = CategoriaDocumento::orderBy('nombre')->get();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al cargar documentos: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }
    
    public function edit(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->titulo = $documento->titulo;
        $this->descripcion = $documento->descripcion;
        $this->categoria_documento_id = $documento->categoria_documento_id;
        $this->editing = true;
        $this->documentos = $documento;
    }
    
    public function save(): void
    {
        $this->loading = true;
        $this->errors = [];
        
        try {
            if ($this->editing) {
                $documento = Documento::findOrFail($this->documentos->id);
                $documento->update([
                    'titulo' => $this->titulo,
                    'descripcion' => $this->descripcion,
                    'categoria_documento_id' => $this->categoria_documento_id,
                ]);
                
                if ($this->archivo) {
                    $this->updateArchivo($documento);
                }
            } else {
                $documento = Documento::create([
                    'titulo' => $this->titulo,
                    'descripcion' => $this->descripcion,
                    'categoria_documento_id' => $this->categoria_documento_id,
                    'activo' => true,
                ]);
                
                if ($this->archivo) {
                    $this->createArchivo($documento);
                }
            }
            
            $this->emit('success', 'Documento guardado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }
    
    public function delete(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->deleting = true;
        
        try {
            // Eliminar archivos
            foreach ($documento->adjuntos as $adjunto) {
                Storage::disk('public')->delete($adjunto->archivo_path);
            }
            
            // Eliminar documento
            $documento->delete();
            
            $this->emit('success', 'Documento eliminado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->deleting = false;
        }
    }
    
    public function restore(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->restoring = true;
        
        try {
            $documento->restore();
            
            $this->emit('success', 'Documento restaurado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al restaurar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->restoring = false;
        }
    }
    
    public function forceDelete(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->restoring = true;
        
        try {
            Storage::disk('public')->delete($documento->archivo_path);
            $documento->forceDelete();
            
            $this->emit('success', 'Documento eliminado definitivamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->restoring = false;
        }
    }
    
    public function preview(Documento $documento): void
    {
        if ($documento->extension !== 'pdf') {
            $this->emit('error', 'Solo se puede previsualizar PDF.');
            return;
        }
        
        $url = Storage::disk('public')->url($documento->archivo_path);
        $this->emit('preview', ['url' => $url, 'documento' => $documento]);
    }
    
    public function download(Documento $documento): void
    {
        $this->emit('download', ['documento' => $documento]);
    }
    
    private function createArchivo(Documento $documento): void
    {
        $archivo = $this->archivo;
        $extension = strtolower($archivo->getClientOriginalExtension());
        $nombreOriginal = $archivo->getClientOriginalName();
        
        $categoria = CategoriaDocumento::findOrFail($this->categoria_documento_id);
        
        $nombreArchivo = \Illuminate\Support\Str::slug($this->titulo) . '-' . now()->format('Y-m-d_His') . '.' . $extension;
        
        // Generar thumbnail para imágenes
        if ($archivo->isImage()) {
            $nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.png') . '.png';
            $archivo->storeAs('documentos/' . $categoria->slug . '/thumbs', $nombreThumb, 'public');
        }
        
        $path = $archivo->storeAs(
            'documentos/' . $categoria->slug,
            $nombreArchivo,
            'public'
        );
        
        $documento->archivo_path = $path;
        $documento->nombre_original = $nombreOriginal;
        $documento->extension = $extension;
        $documento->tamanio = $archivo->getSize();
        $documento->subido_por = auth()->id();
        $documento->save();
    }
    
    private function updateArchivo(Documento $documento): void
    {
        $archivo = $this->archivo;
        $extension = strtolower($archivo->getClientOriginalExtension());
        $nombreOriginal = $archivo->getClientOriginalName();
        
        $categoria = CategoriaDocumento::findOrFail($this->categoria_documento_id);
        
        // Eliminar archivo anterior
        if ($documento->archivo_path) {
            Storage::disk('public')->delete($documento->archivo_path);
        }
        
        $nombreArchivo = \Illuminate\Support\Str::slug($this->titulo) . '-' . now()->format('Y-m-d_His') . '.' . $extension;
        
        // Generar thumbnail para imágenes
        if ($archivo->isImage()) {
            $nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.png') . '.png';
            $archivo->storeAs('documentos/' . $categoria->slug . '/thumbs', $nombreThumb, 'public');
        }
        
        $path = $archivo->storeAs(
            'documentos/' . $categoria->slug,
            $nombreArchivo,
            'public'
        );
        
        $documento->archivo_path = $path;
        $documento->nombre_original = $nombreOriginal;
        $documento->extension = $extension;
        $documento->tamanio = $archivo->getSize();
        $documento->save();
    }
    
    public function search(): void
    {
        $this->loadDocumentos();
    }
    
    public function render(): \Illuminate\View\View
    {
        return view('admin.documentos.index', [
            'documentos' => $this->documentos,
            'categorias' => $this->categorias,
            'loading' => $this->loading,
            'errors' => $this->errors,
        ]);
    }
}
```

---

## 📋 DETALLE DE IMPLEMENTACIÓN

### **Paso 1: Crear Componente Livewire**

**Archivo:** `app/Livewire/Documentos.php`

### **Paso 2: Crear Vista Livewire**

**Archivo:** `resources/views/admin/documentos/index.blade.php`

### **Paso 3: Actualizar Routes**

**Archivo:** `routes/web.php`

### **Paso 4: Actualizar Controlador**

**Archivo:** `app/Http/Controllers/DocumentoController.php`

### **Paso 5: Actualizar Requests**

**Archivo:** `app/Http/Requests/StoreDocumentoRequest.php`

---

## 📋 DETALLE TÉCNICO

### **Componente Livewire:**

```php
<?php

namespace App\Livewire;

use App\Models\CategoriaDocumento;
use App\Models\Documento;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

class Documentos extends Component
{
    #[Validate('required|exists:categorias_documentos,id')]
    public ?int $categoria_documento_id = null;
    
    #[Validate('required|string|max:255')]
    public string $titulo = '';
    
    public ?string $descripcion = null;
    public ?array $archivo = null;
    public bool $editing = false;
    public bool $deleting = false;
    public bool $restoring = false;
    public bool $loading = false;
    public array $errors = [];
    
    public function mount(): void
    {
        $this->loadDocumentos();
    }
    
    public function loadDocumentos(): void
    {
        $this->loading = true;
        $this->errors = [];
        
        try {
            $documentos = Documento::with('categoria', 'subidoPor')
                ->where('activo', true)
                ->when(request('categoria_id'), fn($q) => $q->where('categoria_documento_id', request('categoria_id')))
                ->latest()
                ->get();
            
            $this->documentos = $documentos;
            $this->categorias = CategoriaDocumento::orderBy('nombre')->get();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al cargar documentos: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }
    
    public function edit(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->titulo = $documento->titulo;
        $this->descripcion = $documento->descripcion;
        $this->categoria_documento_id = $documento->categoria_documento_id;
        $this->editing = true;
        $this->documentos = $documento;
    }
    
    public function save(): void
    {
        $this->loading = true;
        $this->errors = [];
        
        try {
            if ($this->editing) {
                $documento = Documento::findOrFail($this->documentos->id);
                $documento->update([
                    'titulo' => $this->titulo,
                    'descripcion' => $this->descripcion,
                    'categoria_documento_id' => $this->categoria_documento_id,
                ]);
                
                if ($this->archivo) {
                    $this->updateArchivo($documento);
                }
            } else {
                $documento = Documento::create([
                    'titulo' => $this->titulo,
                    'descripcion' => $this->descripcion,
                    'categoria_documento_id' => $this->categoria_documento_id,
                    'activo' => true,
                ]);
                
                if ($this->archivo) {
                    $this->createArchivo($documento);
                }
            }
            
            $this->emit('success', 'Documento guardado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }
    
    public function delete(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->deleting = true;
        
        try {
            // Eliminar archivos
            foreach ($documento->adjuntos as $adjunto) {
                Storage::disk('public')->delete($adjunto->archivo_path);
            }
            
            // Eliminar documento
            $documento->delete();
            
            $this->emit('success', 'Documento eliminado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->deleting = false;
        }
    }
    
    public function restore(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->restoring = true;
        
        try {
            $documento->restore();
            
            $this->emit('success', 'Documento restaurado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al restaurar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->restoring = false;
        }
    }
    
    public function forceDelete(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->restoring = true;
        
        try {
            Storage::disk('public')->delete($documento->archivo_path);
            $documento->forceDelete();
            
            $this->emit('success', 'Documento eliminado definitivamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->restoring = false;
        }
    }
    
    public function preview(Documento $documento): void
    {
        if ($documento->extension !== 'pdf') {
            $this->emit('error', 'Solo se puede previsualizar PDF.');
            return;
        }
        
        $url = Storage::disk('public')->url($documento->archivo_path);
        $this->emit('preview', ['url' => $url, 'documento' => $documento]);
    }
    
    public function download(Documento $documento): void
    {
        $this->emit('download', ['documento' => $documento]);
    }
    
    private function createArchivo(Documento $documento): void
    {
        $archivo = $this->archivo;
        $extension = strtolower($archivo->getClientOriginalExtension());
        $nombreOriginal = $archivo->getClientOriginalName();
        
        $categoria = CategoriaDocumento::findOrFail($this->categoria_documento_id);
        
        $nombreArchivo = \Illuminate\Support\Str::slug($this->titulo) . '-' . now()->format('Y-m-d_His') . '.' . $extension;
        
        // Generar thumbnail para imágenes
        if ($archivo->isImage()) {
            $nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.png') . '.png';
            $archivo->storeAs('documentos/' . $categoria->slug . '/thumbs', $nombreThumb, 'public');
        }
        
        $path = $archivo->storeAs(
            'documentos/' . $categoria->slug,
            $nombreArchivo,
            'public'
        );
        
        $documento->archivo_path = $path;
        $documento->nombre_original = $nombreOriginal;
        $documento->extension = $extension;
        $documento->tamanio = $archivo->getSize();
        $documento->subido_por = auth()->id();
        $documento->save();
    }
    
    private function updateArchivo(Documento $documento): void
    {
        $archivo = $this->archivo;
        $extension = strtolower($archivo->getClientOriginalExtension());
        $nombreOriginal = $archivo->getClientOriginalName();
        
        $categoria = CategoriaDocumento::findOrFail($this->categoria_documento_id);
        
        // Eliminar archivo anterior
        if ($documento->archivo_path) {
            Storage::disk('public')->delete($documento->archivo_path);
        }
        
        $nombreArchivo = \Illuminate\Support\Str::slug($this->titulo) . '-' . now()->format('Y-m-d_His') . '.' . $extension;
        
        // Generar thumbnail para imágenes
        if ($archivo->isImage()) {
            $nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.png') . '.png';
            $archivo->storeAs('documentos/' . $categoria->slug . '/thumbs', $nombreThumb, 'public');
        }
        
        $path = $archivo->storeAs(
            'documentos/' . $categoria->slug,
            $nombreArchivo,
            'public'
        );
        
        $documento->archivo_path = $path;
        $documento->nombre_original = $nombreOriginal;
        $documento->extension = $extension;
        $documento->tamanio = $archivo->getSize();
        $documento->save();
    }
    
    public function search(): void
    {
        $this->loadDocumentos();
    }
    
    public function render(): \Illuminate\View\View
    {
        return view('admin.documentos.index', [
            'documentos' => $this->documentos,
            'categorias' => $this->categorias,
            'loading' => $this->loading,
            'errors' => $this->errors,
        ]);
    }
}
```

---

## 📋 DETALLE DE IMPLEMENTACIÓN

### **Paso 1: Crear Componente Livewire**

**Archivo:** `app/Livewire/Documentos.php`

### **Paso 2: Crear Vista Livewire**

**Archivo:** `resources/views/admin/documentos/index.blade.php`

### **Paso 3: Actualizar Routes**

**Archivo:** `routes/web.php`

### **Paso 4: Actualizar Controlador**

**Archivo:** `app/Http/Controllers/DocumentoController.php`

### **Paso 5: Actualizar Requests**

**Archivo:** `app/Http/Requests/StoreDocumentoRequest.php`

---

## 📋 DETALLE TÉCNICO

### **Componente Livewire:**

```php
<?php

namespace App\Livewire;

use App\Models\CategoriaDocumento;
use App\Models\Documento;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

class Documentos extends Component
{
    #[Validate('required|exists:categorias_documentos,id')]
    public ?int $categoria_documento_id = null;
    
    #[Validate('required|string|max:255')]
    public string $titulo = '';
    
    public ?string $descripcion = null;
    public ?array $archivo = null;
    public bool $editing = false;
    public bool $deleting = false;
    public bool $restoring = false;
    public bool $loading = false;
    public array $errors = [];
    
    public function mount(): void
    {
        $this->loadDocumentos();
    }
    
    public function loadDocumentos(): void
    {
        $this->loading = true;
        $this->errors = [];
        
        try {
            $documentos = Documento::with('categoria', 'subidoPor')
                ->where('activo', true)
                ->when(request('categoria_id'), fn($q) => $q->where('categoria_documento_id', request('categoria_id')))
                ->latest()
                ->get();
            
            $this->documentos = $documentos;
            $this->categorias = CategoriaDocumento::orderBy('nombre')->get();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al cargar documentos: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }
    
    public function edit(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->titulo = $documento->titulo;
        $this->descripcion = $documento->descripcion;
        $this->categoria_documento_id = $documento->categoria_documento_id;
        $this->editing = true;
        $this->documentos = $documento;
    }
    
    public function save(): void
    {
        $this->loading = true;
        $this->errors = [];
        
        try {
            if ($this->editing) {
                $documento = Documento::findOrFail($this->documentos->id);
                $documento->update([
                    'titulo' => $this->titulo,
                    'descripcion' => $this->descripcion,
                    'categoria_documento_id' => $this->categoria_documento_id,
                ]);
                
                if ($this->archivo) {
                    $this->updateArchivo($documento);
                }
            } else {
                $documento = Documento::create([
                    'titulo' => $this->titulo,
                    'descripcion' => $this->descripcion,
                    'categoria_documento_id' => $this->categoria_documento_id,
                    'activo' => true,
                ]);
                
                if ($this->archivo) {
                    $this->createArchivo($documento);
                }
            }
            
            $this->emit('success', 'Documento guardado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }
    
    public function delete(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->deleting = true;
        
        try {
            // Eliminar archivos
            foreach ($documento->adjuntos as $adjunto) {
                Storage::disk('public')->delete($adjunto->archivo_path);
            }
            
            // Eliminar documento
            $documento->delete();
            
            $this->emit('success', 'Documento eliminado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->deleting = false;
        }
    }
    
    public function restore(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->restoring = true;
        
        try {
            $documento->restore();
            
            $this->emit('success', 'Documento restaurado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al restaurar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->restoring = false;
        }
    }
    
    public function forceDelete(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->restoring = true;
        
        try {
            Storage::disk('public')->delete($documento->archivo_path);
            $documento->forceDelete();
            
            $this->emit('success', 'Documento eliminado definitivamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->restoring = false;
        }
    }
    
    public function preview(Documento $documento): void
    {
        if ($documento->extension !== 'pdf') {
            $this->emit('error', 'Solo se puede previsualizar PDF.');
            return;
        }
        
        $url = Storage::disk('public')->url($documento->archivo_path);
        $this->emit('preview', ['url' => $url, 'documento' => $documento]);
    }
    
    public function download(Documento $documento): void
    {
        $this->emit('download', ['documento' => $documento]);
    }
    
    private function createArchivo(Documento $documento): void
    {
        $archivo = $this->archivo;
        $extension = strtolower($archivo->getClientOriginalExtension());
        $nombreOriginal = $archivo->getClientOriginalName();
        
        $categoria = CategoriaDocumento::findOrFail($this->categoria_documento_id);
        
        $nombreArchivo = \Illuminate\Support\Str::slug($this->titulo) . '-' . now()->format('Y-m-d_His') . '.' . $extension;
        
        // Generar thumbnail para imágenes
        if ($archivo->isImage()) {
            $nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.png') . '.png';
            $archivo->storeAs('documentos/' . $categoria->slug . '/thumbs', $nombreThumb, 'public');
        }
        
        $path = $archivo->storeAs(
            'documentos/' . $categoria->slug,
            $nombreArchivo,
            'public'
        );
        
        $documento->archivo_path = $path;
        $documento->nombre_original = $nombreOriginal;
        $documento->extension = $extension;
        $documento->tamanio = $archivo->getSize();
        $documento->subido_por = auth()->id();
        $documento->save();
    }
    
    private function updateArchivo(Documento $documento): void
    {
        $archivo = $this->archivo;
        $extension = strtolower($archivo->getClientOriginalExtension());
        $nombreOriginal = $archivo->getClientOriginalName();
        
        $categoria = CategoriaDocumento::findOrFail($this->categoria_documento_id);
        
        // Eliminar archivo anterior
        if ($documento->archivo_path) {
            Storage::disk('public')->delete($documento->archivo_path);
        }
        
        $nombreArchivo = \Illuminate\Support\Str::slug($this->titulo) . '-' . now()->format('Y-m-d_His') . '.' . $extension;
        
        // Generar thumbnail para imágenes
        if ($archivo->isImage()) {
            $nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.png') . '.png';
            $archivo->storeAs('documentos/' . $categoria->slug . '/thumbs', $nombreThumb, 'public');
        }
        
        $path = $archivo->storeAs(
            'documentos/' . $categoria->slug,
            $nombreArchivo,
            'public'
        );
        
        $documento->archivo_path = $path;
        $documento->nombre_original = $nombreOriginal;
        $documento->extension = $extension;
        $documento->tamanio = $archivo->getSize();
        $documento->save();
    }
    
    public function search(): void
    {
        $this->loadDocumentos();
    }
    
    public function render(): \Illuminate\View\View
    {
        return view('admin.documentos.index', [
            'documentos' => $this->documentos,
            'categorias' => $this->categorias,
            'loading' => $this->loading,
            'errors' => $this->errors,
        ]);
    }
}
```

---

## 📋 DETALLE DE IMPLEMENTACIÓN

### **Paso 1: Crear Componente Livewire**

**Archivo:** `app/Livewire/Documentos.php`

### **Paso 2: Crear Vista Livewire**

**Archivo:** `resources/views/admin/documentos/index.blade.php`

### **Paso 3: Actualizar Routes**

**Archivo:** `routes/web.php`

### **Paso 4: Actualizar Controlador**

**Archivo:** `app/Http/Controllers/DocumentoController.php`

### **Paso 5: Actualizar Requests**

**Archivo:** `app/Http/Requests/StoreDocumentoRequest.php`

---

## 📋 DETALLE TÉCNICO

### **Componente Livewire:**

```php
<?php

namespace App\Livewire;

use App\Models\CategoriaDocumento;
use App\Models\Documento;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

class Documentos extends Component
{
    #[Validate('required|exists:categorias_documentos,id')]
    public ?int $categoria_documento_id = null;
    
    #[Validate('required|string|max:255')]
    public string $titulo = '';
    
    public ?string $descripcion = null;
    public ?array $archivo = null;
    public bool $editing = false;
    public bool $deleting = false;
    public bool $restoring = false;
    public bool $loading = false;
    public array $errors = [];
    
    public function mount(): void
    {
        $this->loadDocumentos();
    }
    
    public function loadDocumentos(): void
    {
        $this->loading = true;
        $this->errors = [];
        
        try {
            $documentos = Documento::with('categoria', 'subidoPor')
                ->where('activo', true)
                ->when(request('categoria_id'), fn($q) => $q->where('categoria_documento_id', request('categoria_id')))
                ->latest()
                ->get();
            
            $this->documentos = $documentos;
            $this->categorias = CategoriaDocumento::orderBy('nombre')->get();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al cargar documentos: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }
    
    public function edit(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->titulo = $documento->titulo;
        $this->descripcion = $documento->descripcion;
        $this->categoria_documento_id = $documento->categoria_documento_id;
        $this->editing = true;
        $this->documentos = $documento;
    }
    
    public function save(): void
    {
        $this->loading = true;
        $this->errors = [];
        
        try {
            if ($this->editing) {
                $documento = Documento::findOrFail($this->documentos->id);
                $documento->update([
                    'titulo' => $this->titulo,
                    'descripcion' => $this->descripcion,
                    'categoria_documento_id' => $this->categoria_documento_id,
                ]);
                
                if ($this->archivo) {
                    $this->updateArchivo($documento);
                }
            } else {
                $documento = Documento::create([
                    'titulo' => $this->titulo,
                    'descripcion' => $this->descripcion,
                    'categoria_documento_id' => $this->categoria_documento_id,
                    'activo' => true,
                ]);
                
                if ($this->archivo) {
                    $this->createArchivo($documento);
                }
            }
            
            $this->emit('success', 'Documento guardado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al guardar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
        }
    }
    
    public function delete(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->deleting = true;
        
        try {
            // Eliminar archivos
            foreach ($documento->adjuntos as $adjunto) {
                Storage::disk('public')->delete($adjunto->archivo_path);
            }
            
            // Eliminar documento
            $documento->delete();
            
            $this->emit('success', 'Documento eliminado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->deleting = false;
        }
    }
    
    public function restore(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->restoring = true;
        
        try {
            $documento->restore();
            
            $this->emit('success', 'Documento restaurado correctamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al restaurar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->restoring = false;
        }
    }
    
    public function forceDelete(Documento $documento): void
    {
        $this->loading = true;
        $this->errors = [];
        $this->restoring = true;
        
        try {
            Storage::disk('public')->delete($documento->archivo_path);
            $documento->forceDelete();
            
            $this->emit('success', 'Documento eliminado definitivamente.');
            $this->loadDocumentos();
        } catch (\Exception $e) {
            $this->errors['general'] = 'Error al eliminar: ' . $e->getMessage();
        } finally {
            $this->loading = false;
            $this->restoring = false;
        }
    }
    
    public function preview(Documento $documento): void
    {
        if ($documento->extension !== 'pdf') {
            $this->emit('error', 'Solo se puede previsualizar PDF.');
            return;
        }
        
        $url = Storage::disk('public')->url($documento->archivo_path);
        $this->emit('preview', ['url' => $url, 'documento' => $documento]);
    }
    
    public function download(Documento $documento): void
    {
        $this->emit('download', ['documento' => $documento]);
    }
    
    private function createArchivo(Documento $documento): void
    {
        $archivo = $this->archivo;
        $extension = strtolower($archivo->getClientOriginalExtension());
        $nombreOriginal = $archivo->getClientOriginalName();
        
        $categoria = CategoriaDocumento::findOrFail($this->categoria_documento_id);
        
        $nombreArchivo = \Illuminate\Support\Str::slug($this->titulo) . '-' . now()->format('Y-m-d_His') . '.' . $extension;
        
        // Generar thumbnail para imágenes
        if ($archivo->isImage()) {
            $nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.png') . '.png';
            $archivo->storeAs('documentos/' . $categoria->slug . '/thumbs', $nombreThumb, 'public');
        }
        
        $path = $archivo->storeAs(
            'documentos/' . $categoria->slug,
            $nombreArchivo,
            'public'
        );
        
        $documento->archivo_path = $path;
        $documento->nombre_original = $nombreOriginal;
        $documento->extension = $extension;
        $documento->tamanio = $archivo->getSize();
        $documento->subido_por = auth()->id();
        $documento->save();
    }
    
    private function updateArchivo(Documento $documento): void
    {
        $archivo = $this->archivo;
        $extension = strtolower($archivo->getClientOriginalExtension());
        $nombreOriginal = $archivo->getClientOriginalName();
        
        $categoria = CategoriaDocumento::findOrFail($this->categoria_documento_id);
        
        // Eliminar archivo anterior
        if ($documento->archivo_path) {
            Storage::disk('public')->delete($documento->archivo_path);
        }
        
        $nombreArchivo = \Illuminate\Support\Str::slug($this->titulo) . '-' . now()->format('Y-m-d_His') . '.' . $extension;
        
        // Generar thumbnail para imágenes
        if ($archivo->isImage()) {
            $nombreThumb = time() . '_' . basename($archivo->getClientOriginalName(), '.png') . '.png';
            $archivo->storeAs('documentos/' . $categoria->slug . '/thumbs', $nombreThumb, 'public');
        }
        
        $path = $archivo->storeAs(
            'documentos/' . $categoria->slug,
            $nombreArchivo,
            'public'
        );
        
        $documento->archivo_path = $path;
        $documento->nombre_original = $nombreOriginal;
        $documento->extension = $extension;
        $documento->tamanio = $archivo->getSize();
        $documento->save();
    }
    
    public function search(): void
    {
        $this->loadDocumentos();
    }
    
    public function render(): \Illuminate\View\View
    {
        return view('admin.documentos.index', [
            'documentos' => $this->documentos,
            'categorias' => $this->categorias,
            'loading' => $this->loading,
            'errors' => $this->errors,
        ]);
    }
}
```
