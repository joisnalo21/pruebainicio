<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formulario008 extends Model
{
    use HasFactory;

    protected $table = 'formularios008';

    protected $fillable = [
        'paciente_id',
        'created_by',
        'estado',
        'paso_actual',

        // Paso 1 (Admisión / Encabezado)
        'institucion_sistema',
        'unidad_operativa',
        'cod_uo',
        'cod_provincia',
        'cod_canton',
        'cod_parroquia',
        'numero_historia_clinica',
        'fecha_admision',
        'referido_de',
        'avisar_nombre',
        'avisar_parentesco',
        'avisar_direccion',
        'avisar_telefono',
        'forma_llegada',
        'fuente_informacion',
        'entrega_institucion_persona',
        'entrega_telefono',

        // Paso 2 (Motivo / Evento)
        'hora_inicio_atencion',
        'motivo_causa',
        'notificacion_policia',
        'otro_motivo_detalle',
        'grupo_sanguineo',

        // Paso 2 - Apartado 3 (Accidente/violencia/intoxicación/otros)
        'no_aplica_apartado_3',
        'evento_fecha_hora',
        'evento_lugar',
        'evento_direccion',
        'evento_tipos',
        'custodia_policial',
        'evento_observaciones',
        'aliento_etilico',
        'valor_alcochek',
    ];

    /**
     * Relaciones
     */
    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Helpers
     */
    public function esBorrador(): bool
    {
        return $this->estado === 'borrador';
    }

    public function esCompleto(): bool
    {
        return $this->estado === 'completo';
    }

    /**
     * Casts
     */
    protected $casts = [
        'evento_tipos' => 'array',

        'notificacion_policia' => 'boolean',
        'no_aplica_apartado_3' => 'boolean',
        'custodia_policial' => 'boolean',



        'aliento_etilico' => 'boolean',
        'evento_fecha_hora' => 'datetime',
    ];
}
