<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Apicultor extends Model implements Auditable
{
    use SoftDeletes, AuditableTrait;
    protected $table = 'apicultores';
    protected $fillable = [
        'latitud', 'longitud', 'codigo_runsa', 'subcodigo', 'runsa', 'nombre_apellido',
        'ci', 'expedido', 'celular', 'lugar_apiario', 'n_colmenas_runsa', 'n_colmenas_produccion',
        'produccion_promedio', 'proyeccion_produccion_total', 'proyeccion_produccion_toneladas',
        'asociacion', 'fomento', 'fortalecimiento', 'total_beneficiarios', 'nativas',
        'fom', 'fort', 'suma_nuevos', 'n_acta', 'lote', 'estado',
    ];

//    protected $casts = [
//        'ultima_inspeccion' => 'date',
//        'apiarios' => 'integer',
//        'lat' => 'decimal:7',
//        'lng' => 'decimal:7',
//    ];

    // Evitar auditar datos sensibles si luego agregas más campos
    protected $auditExclude = [];
    const COD_PREFIX = 'COL-';
    const PAD_LEN    = 3;
    protected static function booted()
    {
//        static::creating(function ($model) {
//            if (empty($model->codigo)) {
//                $model->codigo = self::nextCodigo();
//            }
//        });
    }

    // Genera COL-001, COL-002, ...
    public static function nextCodigo(): string
    {
        // --- Intento 1: usar SQL para sacar el MAX del número (MySQL/MariaDB)
        try {
            $max = static::query()
                ->whereNull('deleted_at')
                ->selectRaw("MAX(CAST(SUBSTRING(codigo, ?) AS UNSIGNED)) AS maxnum", [strlen(self::COD_PREFIX) + 1])
                ->value('maxnum');

            $next = ((int) $max) + 1;

            return self::COD_PREFIX . str_pad((string)$next, self::PAD_LEN, '0', STR_PAD_LEFT);
        } catch (\Throwable $e) {
            // --- Fallback: calcular en PHP por si la BD no soporta el SQL anterior
            $max = 0;
            $codes = static::query()
                ->whereNull('deleted_at')
                ->pluck('codigo');

            foreach ($codes as $code) {
                if (preg_match('/^' . preg_quote(self::COD_PREFIX, '/') . '(\d+)$/', $code, $m)) {
                    $num = (int) $m[1];
                    if ($num > $max) $max = $num;
                }
            }

            $next = $max + 1;
            return self::COD_PREFIX . str_pad((string)$next, self::PAD_LEN, '0', STR_PAD_LEFT);
        }
    }
}
