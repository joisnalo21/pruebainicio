<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formulario008 extends Model
{
    use HasFactory;

    // 👇 fuerza el nombre de tabla correcto
    protected $table = 'formularios008';

    protected $fillable = [
        'paciente_id',
        'motivo',
        'diagnostico',
        'tratamiento',
        'observaciones',
    ];

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }
}
