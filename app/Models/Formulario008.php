<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formulario008 extends Model
{
    use HasFactory;

    protected $table = 'formularios008';

    protected $fillable = [
        'archivado_en' => 'datetime',
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


        //Paso 3 (Antecedentes)
        'antecedentes_no_aplica',
        'antecedentes_tipos',
        'antecedentes_otro_texto',
        'antecedentes_detalle',

        // Paso 4 (Enfermedad actual)
        'no_aplica_enfermedad_actual',
        'via_aerea',
        'condicion',
        'enfermedad_actual_revision',

        // Paso 5 Dolor
        'no_aplica_dolor',
        'dolor_items',

        // Paso 6  signos vitales
        // Paso 6 - Signos vitales
        'pa_sistolica',
        'pa_diastolica',
        'frecuencia_cardiaca',
        'frecuencia_respiratoria',
        'temp_bucal',
        'temp_axilar',
        'peso',
        'talla',
        'saturacion_oxigeno',
        'tiempo_llenado_capilar',
        'glasgow_ocular',
        'glasgow_verbal',
        'glasgow_motora',
        'glasgow_total',
        'reaccion_pupila_der',
        'reaccion_pupila_izq',

        // Paso 7 Examen físico
        'examen_fisico_checks',
        'examen_fisico_descripcion',

        // Paso 8 Lesiones
        'no_aplica_lesiones',
        'lesiones',

        // Paso 9 (Sección 10 PDF): Emergencia obstétrica
        'no_aplica_obstetrica',
        'obst_gestas',
        'obst_partos',
        'obst_abortos',
        'obst_cesareas',
        'obst_fum',
        'obst_semanas_gestacion',
        'obst_movimiento_fetal',
        'obst_frecuencia_fetal',
        'obst_altura_uterina',
        'obst_membranas_rotas',
        'obst_tiempo_membranas_rotas',
        'obst_presentacion',
        'obst_dilatacion_cm',
        'obst_borramiento_pct',
        'obst_plano',
        'obst_pelvis_util',
        'obst_sangrado_vaginal',
        'obst_contracciones',
        'obst_texto',

        // Solicitud de exámenes (Sección 11 PDF)
        'no_aplica_examenes',
        'examenes_solicitados',
        'examenes_comentarios',

        'diagnosticos_ingreso',
        'diagnosticos_alta',
        'plan_tratamiento',

        'alta_destino',
        'alta_servicio_referencia',
        'alta_establecimiento_referencia',
        'alta_resultado',
        'alta_condicion',
        'alta_causa',
        'alta_dias_incapacidad',
        'alta_fecha_control',
        'alta_hora_finalizacion',
        'alta_profesional_codigo',
        'alta_numero_hoja',



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

    public function getNumeroAttribute(): string
    {
        return '008-' . str_pad((string)$this->id, 6, '0', STR_PAD_LEFT);
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

    public function esArchivado(): bool
    {
        return $this->estado === 'archivado';
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
        'antecedentes_no_aplica' => 'boolean',
        'antecedentes_tipos' => 'array',
        'no_aplica_enfermedad_actual' => 'boolean',
        'no_aplica_dolor' => 'boolean',
        'dolor_items' => 'array',
        'temp_bucal' => 'decimal:1',
        'temp_axilar' => 'decimal:1',
        'peso' => 'decimal:2',
        'talla' => 'decimal:2',
        'tiempo_llenado_capilar' => 'decimal:1',
        'examen_fisico_checks' => 'array',
        'no_aplica_lesiones' => 'boolean',
        'lesiones' => 'array',
        // Obstétrica
        'no_aplica_obstetrica' => 'boolean',
        'obst_membranas_rotas' => 'boolean',
        'obst_sangrado_vaginal' => 'boolean',
        'obst_contracciones' => 'boolean',
        'obst_fum' => 'date',

        'no_aplica_examenes' => 'boolean',
        'examenes_solicitados' => 'array',

        'diagnosticos_ingreso' => 'array',
        'diagnosticos_alta' => 'array',
        'plan_tratamiento' => 'array',
        'alta_fecha_control' => 'date',


    ];
}
