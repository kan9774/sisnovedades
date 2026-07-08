<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Attach extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'attaches';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->useLogName('Adjuntos'); // 'novedad', 'adjunto', 'salida_vehiculo' según el modelo
    }

    protected $fillable = [
        'news_id',
        'user_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    public function novedad(): BelongsTo
    {
        return $this->belongsTo(News::class, 'news_id');
    }

    public function subidoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function url(): string
    {
        return Storage::disk('guardias')->url($this->file_path);
    }

    public function tamanoLegible(): string
    {
        $kb = $this->file_size / 1024;
        if ($kb < 1024) {
            return round($kb, 1) . ' KB';
        }
        return round($kb / 1024, 1) . ' MB';
    }

    public function esImagen(): bool
    {
        return in_array($this->file_type, ['image/jpeg', 'image/png', 'image/jpg']);
    }

    public function esPdf(): bool
    {
        return $this->file_type === 'application/pdf';
    }
}
