<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Paciente extends Model
{
    use HasFactory;

    protected $fillable = [
        'cedula',
        'primer_nombre',
        'segundo_nombre',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'edad',
        'direccion',
        'sexo',
        'provincia',
        'canton',
        'parroquia',
        'telefono',
        'ocupacion',

        // NUEVOS CAMPOS
        'zona',
        'barrio',
        'lugar_nacimiento',
        'nacionalidad',
        'grupo_cultural',
        'estado_civil',
        'instruccion',
        'empresa',
        'seguro_salud',
    ];

    // ✅ Calcula la edad automáticamente cuando se asigna fecha_nacimiento
    public function setFechaNacimientoAttribute($value)
    {
        $this->attributes['fecha_nacimiento'] = $value;

        if ($value) {
            $this->attributes['edad'] = Carbon::parse($value)->age;
        }
    }

    // ✅ Accesor para mostrar nombre completo
    public function getNombreCompletoAttribute()
    {
        return trim("{$this->primer_nombre} {$this->segundo_nombre} {$this->apellido_paterno} {$this->apellido_materno}");
    }
}
