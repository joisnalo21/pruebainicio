<?php

namespace App\Services;

use App\Models\Formulario008;
use setasign\Fpdi\Fpdi;

class Formulario008PdfService
{
    // app/Services/Formulario008PdfService.php

    private static ?array $ecuadorUbicaciones = null;

    private function ubicacionesEcuador(): array
    {
        if (self::$ecuadorUbicaciones !== null) {
            return self::$ecuadorUbicaciones;
        }

        $path = base_path('public/provincias.json'); // <- en tu ZIP está ahí

        if (!is_file($path)) {
            return self::$ecuadorUbicaciones = [];
        }

        $raw = file_get_contents($path);
        $json = json_decode($raw, true);

        return self::$ecuadorUbicaciones = is_array($json) ? $json : [];
    }

    private function resolverUbicacionPaciente(?\App\Models\Paciente $p): array
    {
        if (!$p) {
            return ['provincia' => '', 'canton' => '', 'parroquia' => '', 'barrio' => ''];
        }

        // En tu app Paciente guarda CÓDIGOS en provincia/canton/parroquia
        $provCode = (string)($p->provincia ?? '');
        $cantCode = (string)($p->canton ?? '');
        $parrCode = (string)($p->parroquia ?? '');

        $map = $this->ubicacionesEcuador();

        $provNombre = $map[$provCode]['provincia'] ?? (string)($p->provincia ?? '');
        $cantNombre = $map[$provCode]['cantones'][$cantCode]['canton'] ?? (string)($p->canton ?? '');
        $parrNombre = $map[$provCode]['cantones'][$cantCode]['parroquias'][$parrCode] ?? (string)($p->parroquia ?? '');

        return [
            'provincia' => $provNombre,
            'canton'    => $cantNombre,
            'parroquia' => $parrNombre,
            'barrio'    => (string)($p->barrio ?? ''),
        ];
    }


    /**
     * Parte un texto en líneas que NO excedan un ancho máximo en mm (FPDF).
     * Respeta palabras (no corta a mitad) y normaliza espacios.
     */
    private function wrapTextToWidth(Fpdi $pdf, string $text, float $maxW): array
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text));
        if ($text === '') return [];

        $words = preg_split('/\s+/u', $text) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $w) {
            $try = $current === '' ? $w : ($current . ' ' . $w);

            if ($pdf->GetStringWidth($try) <= $maxW) {
                $current = $try;
            } else {
                if ($current !== '') {
                    $lines[] = $current;
                    $current = $w;
                } else {
                    // palabra larguísima: cortamos brutal
                    $lines[] = $w;
                    $current = '';
                }
            }
        }

        if ($current !== '') $lines[] = $current;

        return $lines;
    }

    /**
     * Dibuja texto dentro de "líneas" con anchos distintos.
     * - $linesSpec: array de líneas, cada una: ['x'=>, 'y'=>, 'w'=>]
     * - Toma el texto, lo va acomodando en cada línea según ancho.
     * - Si sobra texto, lo recorta (o puedes guardarlo en logs).
     */
    private function drawMultiLineVariableWidth(Fpdi $pdf, string $text, array $linesSpec, float $fontSize = 8.5): void
    {
        $text = trim((string)$text);
        if ($text === '') return;

        // Si tu txt() ya fija fuente, puedes quitar esto
        $pdf->SetFont('Arial', '', $fontSize);

        $remaining = $text;

        foreach ($linesSpec as $spec) {
            $x = (float)$spec['x'];
            $y = (float)$spec['y'];
            $w = (float)$spec['w'];

            if ($remaining === '') break;

            // Genera posibles líneas con el ancho de ESTA fila
            $wrapped = $this->wrapTextToWidth($pdf, $remaining, $w);
            if (empty($wrapped)) break;

            // Tomamos solo la primera línea para esta fila
            $line = $wrapped[0];

            // Pintamos usando tu helper txt()
            $this->txt($pdf, $x, $y, $line);

            // Quitamos lo ya pintado del texto restante
            $remaining = trim(mb_substr($remaining, mb_strlen($line)));
            // Si quedó iniciando con espacio, lo limpiamos
            $remaining = ltrim($remaining);
        }

        // Si quieres saber si se recortó:
        // if ($remaining !== '') { logger()->warning('Observaciones recortadas en PDF', ['resto' => $remaining]); }
    }











    public function render(Formulario008 $f, bool $grid = false): string
    {
        $template = resource_path('pdf-templates/formulario008_msp.pdf');


        $pdf = new Fpdi('P', 'mm');
        $pageCount = $pdf->setSourceFile($template);

        for ($page = 1; $page <= $pageCount; $page++) {
            $tplId = $pdf->importPage($page);
            $size  = $pdf->getTemplateSize($tplId); // usa el tamaño real del PDF plantilla

            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);

            $pdf->SetAutoPageBreak(false);

            if ($grid) {
                $this->drawGrid($pdf, $size['width'], $size['height']);
                continue;
            }

            if ($page === 1) $this->fillPage1($pdf, $f);
            if ($page === 2) $this->fillPage2($pdf, $f);
        }

        return $pdf->Output('S'); // retorna bytes
    }

    /**
     * ============== Helpers ==============
     */
    private function enc(?string $s): string
    {
        $s = trim((string) $s);
        // FPDF trabaja mejor con Win-1252 para tildes
        $out = @iconv('UTF-8', 'windows-1252//TRANSLIT', $s);
        return $out !== false ? $out : $s;
    }

    private function txt(Fpdi $pdf, float $x, float $y, ?string $text, int $size = 8, float $w = 0, string $align = 'L'): void
    {
        $pdf->SetFont('Helvetica', '', $size);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetXY($x, $y);

        $t = $this->enc($text);

        if ($w > 0) {
            $pdf->Cell($w, 4, $t, 0, 0, $align);
        } else {
            $pdf->Write(4, $t);
        }
    }

    private function multi(Fpdi $pdf, float $x, float $y, float $w, float $h, ?string $text, int $size = 8): void
    {
        $pdf->SetFont('Helvetica', '', $size);
        $pdf->SetTextColor(0, 0, 0);

        $pdf->SetXY($x, $y);
        $pdf->MultiCell($w, $h, $this->enc($text), 0, 'L');
    }

    private function check(Fpdi $pdf, float $x, float $y, bool $on): void
    {
        if (!$on) return;
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Text($x, $y, 'X');
    }

    private function drawGrid($pdf, $step = 10)
    {
        $w = $pdf->GetPageWidth();
        $h = $pdf->GetPageHeight();

        $pdf->SetDrawColor(200, 200, 200);
        $pdf->SetTextColor(120, 120, 120);
        $pdf->SetFont('Helvetica', '', 7);

        // Verticales
        for ($x = 0; $x <= $w; $x += $step) {
            $pdf->Line($x, 0, $x, $h);
            $pdf->Text($x + 1, 3, (string)$x);
        }

        // Horizontales
        for ($y = 0; $y <= $h; $y += $step) {
            $pdf->Line(0, $y, $w, $y);
            $pdf->Text(1, $y + 3, (string)$y);
        }
    }


    /**
     * ============== Relleno páginas ==============
     * OJO: Estas coordenadas son EJEMPLO.
     * Usa ?grid=1 y ajusta hasta que quede perfecto.
     */
    private function fillPage1(Fpdi $pdf, Formulario008 $f): void
    {
        $p = $f->paciente;

        // ===== Sección 1 (Admisión) - EJEMPLOS =====
        $this->txt($pdf, 18, 39, $p->apellido_paterno ?? '');
        $this->txt($pdf, 66, 39, $p->apellido_materno ?? '');
        $this->txt($pdf, 115, 39, $p->primer_nombre ?? '');
        $this->txt($pdf, 160, 39, $p->segundo_nombre ?? '');
        $this->txt($pdf, 208, 39, $p->cedula ?? '', 8, 35);

        // Dirección / teléfono (ajusta coords)
        $this->txt($pdf, 18, 51, $p->direccion ?? '', 8, 150);

        $ubi = $this->resolverUbicacionPaciente($p);

        $this->txt($pdf, 175, 51, $ubi['provincia']);
        $this->txt($pdf, 152, 51, $ubi['canton']);
        $this->txt($pdf, 131, 51, $ubi['parroquia']);
        $this->txt($pdf, 109, 51, $ubi['barrio']);

        $zonaRaw = strtoupper(trim((string) ($p->zona ?? '')));

        $zona = match ($zonaRaw) {
            'URBANA', 'U' => 'U',
            'RURAL',  'R' => 'R',
            default => '',
        };
        $this->txt($pdf, 199, 51, $zona);
        $this->txt($pdf, 208, 51, $p->telefono ?? '', 8, 40);
        $this->txt($pdf, 18, 68, $p->fecha_nacimiento ? $p->fecha_nacimiento : '');
        $this->txt($pdf, 50, 68, $p->lugar_nacimiento ?? '');
        $this->txt($pdf, 92, 68, $p->nacionalidad ?? '');
        $this->txt($pdf, 120, 68, $p->grupo_cultural ?? '');
        $this->txt($pdf, 155, 68, $p->edad ?? '');

        // Sexo con punto según valor
        if (strtolower($p->sexo ?? '') === 'masculino' || strtolower($p->sexo ?? '') === 'm') {
            $this->check($pdf, 173, 71, true); // Punto para Masculino
        } elseif (strtolower($p->sexo ?? '') === 'femenino' || strtolower($p->sexo ?? '') === 'f') {
            $this->check($pdf, 180, 71, true); // Punto para Femenino
        }


        // Estado civil con punto según valor
        $estadoCivil = strtolower($p->estado_civil ?? '');
        $this->check($pdf, 187, 71, $estadoCivil === 'soltero/a');
        $this->check($pdf, 195, 71, $estadoCivil === 'casado/a');
        $this->check($pdf, 202, 71, $estadoCivil === 'divorciado/a');
        $this->check($pdf, 210, 71, $estadoCivil === 'viudo/a');
        $this->check($pdf, 217, 71, $estadoCivil === 'unión libre');

        $this->txt($pdf, 223, 68, $p->instruccion ?? '');
        $this->txt($pdf, 50, 80, $p->ocupacion ?? '');
        $this->txt($pdf, 93, 80, $p->empresa ?? '');
        $this->txt($pdf, 146, 80, $p->seguro_salud ?? '');

        $this->txt($pdf, 17, 80, $f->fecha_admision ? $f->fecha_admision : '');
        $this->txt($pdf, 208, 80, $f->referido_de ?? '');
        $this->txt($pdf, 18, 92, $f->avisar_nombre ?? '');
        $this->txt($pdf, 96, 92, $f->avisar_parentesco ?? '');
        $this->txt($pdf, 128, 92, $f->avisar_direccion ?? '', 8, 60);
        $this->txt($pdf, 208, 92, $f->avisar_telefono ?? '', 8, 40);
        $formaLlegada = strtolower($f->forma_llegada ?? '');
        $this->check($pdf, 38, 107, $formaLlegada === 'ambulatorio');
        $this->check($pdf, 64, 107, $formaLlegada === 'ambulancia');
        $this->check($pdf, 89, 107, $formaLlegada === 'otro');
        $this->txt($pdf, 95, 105, $f->fuente_informacion ?? '', 8, 60);
        $this->txt($pdf, 128, 105, $f->entrega_institucion_persona ?? '', 8, 60);
        $this->txt($pdf, 208, 105, $f->entrega_telefono ?? '', 8, 40);
        $this->txt($pdf, 36, 122, $f->hora_inicio_atencion ?? '');


        // ===== Sección 2 (Inicio atención y motivo) - EJEMPLOS =====


        // Motivo causa (pon X donde corresponda; ajusta coords a los circulitos)
        $this->check($pdf, 86, 125, $f->motivo_causa === 'trauma');
        $this->check($pdf, 122, 125, $f->motivo_causa === 'clinica');
        $this->check($pdf, 159, 125, $f->motivo_causa === 'obstetrica');
        $this->check($pdf, 195, 125, $f->motivo_causa === 'quirurgica');
        $this->check($pdf, 86, 131, $f->motivo_causa === 'otro');
        $this->txt($pdf, 92, 128, $f->otro_motivo_detalle ?? '', 7, 180);
        $this->txt($pdf, 232, 125, $f->grupo_sanguineo ?? '', 9, 20, 'C');
        $this->check($pdf, 49, 131, $f->notificacion_policia == 1);

        $this->txt($pdf, 39, 145, $f->evento_fecha_hora ?? '', 8, 50);
        $this->txt($pdf, 88, 145, $f->evento_lugar ?? '', 8, 80);
        $this->txt($pdf, 135, 145, $f->evento_direccion ?? '', 8, 70);

        $this->check($pdf, 246, 148, $f->custodia_policial == 1);
        $this->check($pdf, 246, 142, $f->no_aplica_apartado_3 == 1);


        // Evento tipos (checkboxes para cada tipo de evento)
        $eventoTipos = is_string($f->evento_tipos) ? json_decode($f->evento_tipos, true) : (is_array($f->evento_tipos) ? $f->evento_tipos : []);
        if (!is_array($eventoTipos)) $eventoTipos = [];

        $this->check($pdf, 41, 156, in_array('accidente_transito', $eventoTipos));
        $this->check($pdf, 71, 156, in_array('caida', $eventoTipos));
        $this->check($pdf, 100, 156, in_array('quemadura', $eventoTipos));
        $this->check($pdf, 129, 156, in_array('mordedura', $eventoTipos));
        $this->check($pdf, 158, 156, in_array('ahogamiento', $eventoTipos));
        $this->check($pdf, 187, 156, in_array('cuerpo_extrano', $eventoTipos));
        $this->check($pdf, 217, 156, in_array('aplastamiento', $eventoTipos));
        $this->check($pdf, 246, 156, in_array('otro_accidente', $eventoTipos));
        $this->check($pdf, 41, 163, in_array('violencia_arma_fuego', $eventoTipos));
        $this->check($pdf, 71, 163, in_array('violencia_arma_punzante', $eventoTipos));
        $this->check($pdf, 100, 163, in_array('violencia_rina', $eventoTipos));
        $this->check($pdf, 129, 163, in_array('violencia_familiar', $eventoTipos));
        $this->check($pdf, 158, 163, in_array('abuso_fisico', $eventoTipos));
        $this->check($pdf, 187, 163, in_array('abuso_psicologico', $eventoTipos));
        $this->check($pdf, 217, 163, in_array('abuso_sexual', $eventoTipos));
        $this->check($pdf, 246, 163, in_array('otra_violencia', $eventoTipos));
        $this->check($pdf, 41, 171, in_array('intoxicacion_alcoholica', $eventoTipos));
        $this->check($pdf, 71, 171, in_array('intoxicacion_alimentaria', $eventoTipos));
        $this->check($pdf, 100, 171, in_array('intoxicacion_drogas', $eventoTipos));
        $this->check($pdf, 129, 171, in_array('inhalacion_gases', $eventoTipos));
        $this->check($pdf, 158, 171, in_array('otra_intoxicacion', $eventoTipos));
        $this->check($pdf, 187, 171, in_array('envenenamiento', $eventoTipos));
        $this->check($pdf, 217, 171, in_array('picadura', $eventoTipos));
        $this->check($pdf, 246, 171, in_array('anafilaxia', $eventoTipos));

        $this->check($pdf, 213, 190, $f->aliento_etilico == 1);
        $this->txt($pdf, 236, 188, $f->valor_alcochek ?? '', 8, 30);

        // ✅ OBSERVACIONES (3 líneas, anchos distintos)
        $obs = (string)($f->evento_observaciones ?? '');
        $linesSpec = [
            // Línea 1 (más ancha)
            ['x' => 43, 'y' => 175, 'w' => 217],
            // Línea 2 (más ancha)
            ['x' => 18, 'y' => 182, 'w' => 237],
            // Línea 3 (más corta porque a la derecha hay otros campos)
            ['x' => 18, 'y' => 188, 'w' => 180],
        ];
        $this->drawMultiLineVariableWidth($pdf, $obs, $linesSpec, 8.5);



        if (is_string($f->antecedentes_tipos)) {
            $antecedentesTipos = json_decode($f->antecedentes_tipos, true);
        } elseif (is_array($f->antecedentes_tipos)) {
            $antecedentesTipos = $f->antecedentes_tipos;
        } else {
            $antecedentesTipos = [];
        }
        if (!is_array($antecedentesTipos)) {
            $antecedentesTipos = [];
        }

        $this->check($pdf, 41, 207, in_array('alergico', $antecedentesTipos));
        $this->check($pdf, 71, 207, in_array('clinico', $antecedentesTipos));
        $this->check($pdf, 100, 207, in_array('ginecologico', $antecedentesTipos));
        $this->check($pdf, 129, 207, in_array('traumatologico', $antecedentesTipos));
        $this->check($pdf, 158, 207, in_array('quirurgico', $antecedentesTipos));
        $this->check($pdf, 187, 207, in_array('farmacologico', $antecedentesTipos));
        $this->check($pdf, 217, 207, in_array('otro', $antecedentesTipos));
        $this->txt($pdf, 223, 204, $f->antecedentes_otro_texto ?? '', 8, 50);
        // ===== Antecedentes detalle (textarea con 4 límites) =====
        $antecedentesDetalle = (string)($f->antecedentes_detalle ?? '');
        $linesSpec = [
            ['x' => 18, 'y' => 213, 'w' => 230],
            ['x' => 18, 'y' => 219, 'w' => 230],
            ['x' => 18, 'y' => 225, 'w' => 230],
            ['x' => 18, 'y' => 231, 'w' => 230],
        ];
        $this->drawMultiLineVariableWidth($pdf, $antecedentesDetalle, $linesSpec, 8);
        $this->check($pdf, 246, 201, $f->antecedentes_no_aplica == 1);

        $this->check($pdf, 246, 244, $f->no_aplica_enfermedad_actual == 1);

        $this->check($pdf, 49, 250, $f->via_aerea === 'libre');
        $this->check($pdf, 86, 250, $f->via_aerea === 'obstruida');
        $this->check($pdf, 122, 250, $f->condicion === 'estable');
        $this->check($pdf, 159, 250, $f->condicion === 'inestable');

        $enfermedad_actual_revision = (string)($f->enfermedad_actual_revision ?? '');
        $linesSpec = [
            ['x' => 18, 'y' => 255, 'w' => 230],
            ['x' => 18, 'y' => 261, 'w' => 230],
            ['x' => 18, 'y' => 267, 'w' => 230],
            ['x' => 18, 'y' => 273, 'w' => 230],
        ];
        $this->drawMultiLineVariableWidth($pdf, $enfermedad_actual_revision, $linesSpec, 8);
        $this->check($pdf, 246, 294, $f->no_aplica_dolor == 1);


        // ===== Sección Dolor (hasta 3 dolores) =====
        $dolores = is_string($f->dolor_items) ? json_decode($f->dolor_items, true) : (is_array($f->dolor_items) ? $f->dolor_items : []);
        if (!is_array($dolores)) $dolores = [];

        // Coordenadas base para cada dolor (ajusta según tu PDF)
        $dolorYBase = 320; // Y inicial del primer dolor
        $dolorYStep = 6;  // Espaciado vertical entre dolores

        foreach ($dolores as $idx => $dolor) {
            if ($idx >= 3) break; // máximo 3 dolores

            $y = $dolorYBase + ($idx * $dolorYStep);

            // Región y Punto doloroso
            $this->txt($pdf, 18, $y, $dolor['region'] ?? '', 8, 50);
            $this->txt($pdf, 58, $y, $dolor['punto'] ?? '', 8, 50);

            // Situación (localizado, difuso, irradiado, referido)
            $situacion = strtolower($dolor['situacion'] ?? '');
            $this->check($pdf, 97,  $y + 2, $situacion === 'localizado');
            $this->check($pdf, 104, $y + 2, $situacion === 'difuso');
            $this->check($pdf, 111, $y + 2, $situacion === 'irradiado');
            $this->check($pdf, 119, $y + 2, $situacion === 'referido');

            // Evolución (agudo, subagudo, crónico)
            $evolucion = strtolower($dolor['evolucion'] ?? '');
            $this->check($pdf, 126, $y + 2, $evolucion === 'agudo');
            $this->check($pdf, 133, $y + 2, $evolucion === 'subagudo');
            $this->check($pdf, 140, $y + 2, $evolucion === 'cronico');

            // Tipo (episódico, continuo, cólico)
            $tipo = strtolower($dolor['tipo'] ?? '');
            $this->check($pdf, 148, $y + 2, $tipo === 'episodico');
            $this->check($pdf, 155, $y + 2, $tipo === 'continuo');
            $this->check($pdf, 162, $y + 2, $tipo === 'colico');

            // Se modifica con (posición, ingesta, esfuerzo, dígito presión)
            $modificaCon = is_array($dolor['se_modifica_con'] ?? []) ? $dolor['se_modifica_con'] : [];
            $this->check($pdf, 170, $y + 2, in_array('posicion', $modificaCon));
            $this->check($pdf, 176, $y + 2, in_array('ingesta', $modificaCon));
            $this->check($pdf, 183, $y + 2, in_array('esfuerzo', $modificaCon));
            $this->check($pdf, 190, $y + 2, in_array('digito_presion', $modificaCon));

            // Alivia con (analgésico, antiespasmódico, opiáceo, no alivia)
            $aliviaCon = is_array($dolor['alivia_con'] ?? []) ? $dolor['alivia_con'] : [];
            $this->check($pdf, 198, $y + 2, in_array('analgesico', $aliviaCon));
            $this->check($pdf, 205, $y + 2, in_array('anti_espasmodico', $aliviaCon));
            $this->check($pdf, 212, $y + 2, in_array('opiaceo', $aliviaCon));
            $this->check($pdf, 219, $y + 2, in_array('no_alivia', $aliviaCon));

            // Intensidad (escala numérica)
            $intensidad = $dolor['intensidad'] ?? '';
            $this->txt($pdf, 208, $y, (string)$intensidad, 8, 40, 'C');
        }
    }


    private function fillPage2(Fpdi $pdf, Formulario008 $f): void
    {
        // ===== Sección Antecedentes =====

        // EJEMPLO: Diagnóstico de ingreso 1 (texto + CIE + PRE/DEF)
        // $this->txt($pdf, x, y, $f->dx_ingreso_1 ?? '');
        // $this->txt($pdf, x, y, $f->dx_ingreso_1_cie ?? '');
        // $this->check($pdf, x, y, $f->dx_ingreso_1_tipo === 'pre');
        // $this->check($pdf, x, y, $f->dx_ingreso_1_tipo === 'def');

        $yVitals = 16;
        $paS = $f->pa_sistolica;
        $paD = $f->pa_diastolica;
        $pa = '';
        if ($paS !== null || $paD !== null) {
            $parts = [];
            if ($paS !== null && $paS !== '') $parts[] = $paS;
            if ($paD !== null && $paD !== '') $parts[] = $paD;
            $pa = implode('/', $parts);
        }

        $this->txt($pdf, 20.5, $yVitals, $pa, 8, 40, 'C'); // Presion arterial
        $this->txt($pdf, 63.5, $yVitals, (string)($f->frecuencia_cardiaca ?? ''), 8, 32, 'C');
        $this->txt($pdf, 98.5, $yVitals, (string)($f->frecuencia_respiratoria ?? ''), 8, 32, 'C');
        $this->txt($pdf, 132,  $yVitals, (string)($f->temp_bucal ?? ''), 8, 32, 'C');
        $this->txt($pdf, 162,  $yVitals, (string)($f->temp_axilar ?? ''), 8, 36, 'C');
        $this->txt($pdf, 198, $yVitals, (string)($f->peso ?? ''), 8, 32, 'C');
        $this->txt($pdf, 230, $yVitals, (string)($f->talla ?? ''), 8, 32, 'C');

        $this->txt($pdf, 42, 23, (string)($f->glasgow_ocular ?? ''), 8, 32, 'C');
        $this->txt($pdf, 68, 23, (string)($f->glasgow_verbal ?? ''), 8, 32, 'C');
        $this->txt($pdf, 95, 23, (string)($f->glasgow_motora ?? ''), 8, 32, 'C');
        $this->txt($pdf, 121, 23, (string)($f->glasgow_total ?? ''), 8, 32, 'C');
        $this->txt($pdf, 161.5, 23, (string)($f->reaccion_pupila_der ?? ''), 5, 10, 'C');
        $this->txt($pdf, 191.5, 23, (string)($f->reaccion_pupila_izq ?? ''), 5, 10, 'C');
        $this->txt($pdf, 230, 23, (string)($f->saturacion_oxigeno ?? ''), 8, 32, 'C');
        $this->txt($pdf, 205, 23, (string)($f->tiempo_llenado_capilar ?? ''), 8, 32, 'C');

        // ===== Sección 8: Examen físico (CP/SP) =====
        $checks = $f->examen_fisico_checks ?? [];
        if (!is_array($checks)) $checks = [];

        $placeCheck = function (string $key, float $xCp, float $xSp, float $y) use ($pdf, $checks): void {
            $val = $checks[$key] ?? null;
            $this->check($pdf, $xCp, $y, $val === 'CP');
            $this->check($pdf, $xSp, $y, $val === 'SP');
        };

        // Regional (1-15R)
        $rowsR = [46, 52, 57.5, 64, 71];
        $xRGroups = [
            [48, 55.5], // 1-5
            [96, 103], // 6-10
            [145.5, 153], // 11-15
        ];

        for ($i = 1; $i <= 15; $i++) {
            $group = intdiv($i - 1, 5);
            $row = ($i - 1) % 5;
            $coords = $xRGroups[$group];
            $placeCheck($i . '-R', $coords[0], $coords[1], $rowsR[$row]);
        }

        // Sistémico (1-10S)
        $rowsS = [46, 52, 57.5, 64, 71];
        $xSGroups = [
            [193, 200.5], // 1-5
            [241.1, 248.5], // 6-10
        ];

        for ($i = 1; $i <= 10; $i++) {
            $group = intdiv($i - 1, 5);
            $row = ($i - 1) % 5;
            $coords = $xSGroups[$group];
            $placeCheck($i . '-S', $coords[0], $coords[1], $rowsS[$row]);
        }
        // ✅ Examen físico descripción (4 líneas, ancho fijo)
        $examen_fisico_descripcion = (string)($f->examen_fisico_descripcion ?? '');
        $linesSpec = [
            ['x' => 18, 'y' => 75, 'w' => 230],
            ['x' => 18, 'y' => 81, 'w' => 230],
            ['x' => 18, 'y' => 87, 'w' => 230],
            ['x' => 18, 'y' => 93, 'w' => 230],
            ['x' => 18, 'y' => 99, 'w' => 230],
        ];
        $this->drawMultiLineVariableWidth($pdf, $examen_fisico_descripcion, $linesSpec, 8);

        $this->check($pdf, 164, 114, $f->no_aplica_lesiones == 1);

        // ===== Sección 9: Localización de lesiones =====

        if (!($f->no_aplica_lesiones ?? false)) {
            $lesiones = $f->lesiones ?? [];
            if (!is_array($lesiones)) $lesiones = [];


            $frontBox = ['x0' => 18, 'x1' => 66, 'y0' => 110.5, 'y1' => 216];
            $backBox  = ['x0' => 72.5, 'x1' => 122.5, 'y0' => 113.5, 'y1' => 216];

            $frontMargins = ['left' => 0.0430, 'right' => 0.0039, 'top' => 0.0098, 'bottom' => 0.0156];
            $backMargins  = ['left' => 0.0000, 'right' => 0.0508, 'top' => 0.0137, 'bottom' => 0.0176];

            $mapPoint = function (float $x, float $y, array $box, array $margins): array {
                $w = $box['x1'] - $box['x0'];
                $h = $box['y1'] - $box['y0'];
                $wRatio = max(0.0001, 1 - $margins['left'] - $margins['right']);
                $hRatio = max(0.0001, 1 - $margins['top'] - $margins['bottom']);

                $xIn = ($x - $margins['left']) / $wRatio;
                $yIn = ($y - $margins['top']) / $hRatio;

                $xIn = max(0.0, min(1.0, $xIn));
                $yIn = max(0.0, min(1.0, $yIn));

                return [
                    $box['x0'] + ($xIn * $w),
                    $box['y0'] + ($yIn * $h),
                ];
            };

            $drawMarker = function (float $x, float $y, int $tipo) use ($pdf): void {
                $size = 4.5;
                $pdf->SetFillColor(30, 30, 30);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetFont('Helvetica', 'B', 7);
                $pdf->SetXY($x - ($size / 2), $y - ($size / 2));
                $pdf->Cell($size, $size, (string)$tipo, 0, 0, 'C', true);
            };

            foreach ($lesiones as $p) {
                $view = $p['view'] ?? null;
                $x = isset($p['x']) ? (float)$p['x'] : null;
                $y = isset($p['y']) ? (float)$p['y'] : null;
                $tipo = isset($p['tipo']) ? (int)$p['tipo'] : null;

                if (!in_array($view, ['front', 'back'], true) || $x === null || $y === null || !$tipo) {
                    continue;
                }

                if ($view === 'front') {
                    [$px, $py] = $mapPoint($x, $y, $frontBox, $frontMargins);
                } else {
                    [$px, $py] = $mapPoint($x, $y, $backBox, $backMargins);
                }

                $drawMarker($px, $py, $tipo);
            }

            $pdf->SetTextColor(0, 0, 0);
        }

        // ===== Sección 10: Emergencia obstétrica =====
        $this->check($pdf, 248.5, 114, $f->no_aplica_obstetrica == 1);

        if (!($f->no_aplica_obstetrica ?? false)) {
            $fum = '';
            if (!empty($f->obst_fum)) {
                try {
                    $fum = $f->obst_fum->format('d/m/Y');
                } catch (\Throwable $e) {
                    $fum = (string)$f->obst_fum;
                }
            }

            // Valores
            $this->txt($pdf, 181, 116.4, (string)($f->obst_gestas ?? ''), 8, 12, 'C');
            $this->txt($pdf, 200, 116.4, (string)($f->obst_partos ?? ''), 8, 12, 'C');
            $this->txt($pdf, 221, 116.4, (string)($f->obst_abortos ?? ''), 8, 12, 'C');
            $this->txt($pdf, 243.5, 116.4, (string)($f->obst_cesareas ?? ''), 8, 12, 'C');

            $this->txt($pdf, 186.9, 123.5, $fum, 8, 24, 'C');
            $this->txt($pdf, 219, 123.5, (string)($f->obst_semanas_gestacion ?? ''), 8, 16, 'C');
            $this->txt($pdf, 182, 130.3, (string)($f->obst_frecuencia_fetal ?? ''), 8, 18, 'C');
            $this->txt($pdf, 235, 130.3, (string)($f->obst_tiempo_membranas_rotas ?? ''), 8, 22, 'C');
            $this->txt($pdf, 182, 137, (string)($f->obst_altura_uterina ?? ''), 8, 18, 'C');
            $this->txt($pdf, 214, 137, (string)($f->obst_presentacion ?? ''), 8, 22, 'C');
            $this->txt($pdf, 182, 143, (string)($f->obst_dilatacion_cm ?? ''), 8, 18, 'C');
            $this->txt($pdf, 214, 143, (string)($f->obst_borramiento_pct ?? ''), 8, 18, 'C');
            $this->txt($pdf, 237, 143, (string)($f->obst_plano ?? ''), 8, 18, 'C');

            // Checks
            $this->check($pdf, 249, 127, ($f->obst_movimiento_fetal ?? '') === 'presente');
            $this->check($pdf, 218.5, 133.5, $f->obst_membranas_rotas == 1);
            $this->check($pdf, 189.5, 153, ($f->obst_pelvis_util ?? '') === 'si');
            $this->check($pdf, 211.5, 153, $f->obst_sangrado_vaginal == 1);
            $this->check($pdf, 241, 153, $f->obst_contracciones == 1);

            // Texto inferior
            $obst_texto = (string)($f->obst_texto ?? '');
            $linesSpecObst = [
                ['x' => 173.5, 'y' => 156, 'w' => 76],
                ['x' => 173.5, 'y' => 162, 'w' => 76],
                ['x' => 173.5, 'y' => 168, 'w' => 76],
                ['x' => 173.5, 'y' => 174, 'w' => 76],
                ['x' => 173.5, 'y' => 180, 'w' => 76],
                ['x' => 173.5, 'y' => 186, 'w' => 76],
                ['x' => 173.5, 'y' => 192, 'w' => 76],
            ];
            $this->drawMultiLineVariableWidth($pdf, $obst_texto, $linesSpecObst, 8);
        }

        // ===== Sección 12: Diagnóstico de ingreso =====
        $diagIngreso = $f->diagnosticos_ingreso ?? [];
        if (!is_array($diagIngreso)) $diagIngreso = [];

        $diagIngresoRowsY = [254, 260, 266];
        foreach ([1, 2, 3] as $idx => $n) {
            $row = $diagIngreso[$n] ?? [];
            $dx = (string)($row['dx'] ?? '');
            $cie = (string)($row['cie'] ?? '');
            $tipo = $row['tipo'] ?? null;

            $y = $diagIngresoRowsY[$idx];
            $this->txt($pdf, 25, $y, $dx, 8, 58, 'L');
            $this->txt($pdf, 105, $y, $cie, 8, 10, 'C');
            $this->check($pdf, 122, $y + 2, $tipo === 'pre');
            $this->check($pdf, 130, $y + 2, $tipo === 'def');
        }

        // ===== Sección 13: Diagnóstico de alta =====
        $diagAlta = $f->diagnosticos_alta ?? [];
        if (!is_array($diagAlta)) $diagAlta = [];

        $diagAltaRowsY = [254, 260, 266];
        foreach ([1, 2, 3] as $idx => $n) {
            $row = $diagAlta[$n] ?? [];
            $dx = (string)($row['dx'] ?? '');
            $cie = (string)($row['cie'] ?? '');
            $tipo = $row['tipo'] ?? null;

            $y = $diagAltaRowsY[$idx];
            $this->txt($pdf, 147, $y, $dx, 8, 60, 'L');
            $this->txt($pdf, 224, $y, $cie, 8, 10, 'C');
            $this->check($pdf, 241, $y + 2, $tipo === 'pre');
            $this->check($pdf, 248.6, $y + 2, $tipo === 'def');
        }

        // ===== Sección 14: Plan de tratamiento =====
        $plan = $f->plan_tratamiento ?? [];
        if (!is_array($plan)) $plan = [];

        $planRowsY = [289, 295.5, 301, 306.92];
        foreach ([1, 2, 3, 4] as $idx => $n) {
            $row = $plan[$n] ?? [];
            $indic = (string)($row['indicaciones'] ?? '');
            $med = (string)($row['medicamento'] ?? '');
            $pos = (string)($row['posologia'] ?? '');

            $y = $planRowsY[$idx];
            $this->txt($pdf, 17, $y, $indic, 8, 135, 'L');
            $this->txt($pdf, 143, $y, $med, 8, 55, 'L');
            $this->txt($pdf, 216, $y, $pos, 8, 22, 'L');
        }
        $this->check($pdf, 248.5, 222, $f->no_aplica_examenes == 1);
        $this->txt($pdf, 16, 237.5, (string)($f->examenes_comentarios ?? ''), 8, 50);


        $examenes = is_string($f->examenes_solicitados)
            ? json_decode($f->examenes_solicitados, true)
            : (is_array($f->examenes_solicitados) ? $f->examenes_solicitados : []);
        if (!is_array($examenes)) $examenes = [];

        $this->check($pdf, 40.5, 228.5, in_array('1_biometria', $examenes));
        $this->check($pdf, 40.5, 235, in_array('2_uroanalisis', $examenes));
        $this->check($pdf, 70, 228.5, in_array('3_quimica_sanguinea', $examenes));
        $this->check($pdf, 70, 235, in_array('4_electrolitos', $examenes));
        $this->check($pdf, 100, 228.5, in_array('5_gasometria', $examenes));
        $this->check($pdf, 100, 235, in_array('6_electrocardiograma', $examenes));
        $this->check($pdf, 130, 228.5, in_array('7_endoscopia', $examenes));
        $this->check($pdf, 130, 235, in_array('8_rx_torax', $examenes));
        $this->check($pdf, 160, 228.5, in_array('9_rx_abdomen', $examenes));
        $this->check($pdf, 160, 235, in_array('10_rx_osea', $examenes));
        $this->check($pdf, 190, 228.5, in_array('11_tomografia', $examenes));
        $this->check($pdf, 190, 235, in_array('12_resonancia', $examenes));
        $this->check($pdf, 219, 228.5, in_array('13_ecografia_pelvica', $examenes));
        $this->check($pdf, 219, 235, in_array('14_ecografia_abdomen', $examenes));
        $this->check($pdf, 248.5, 228.5, in_array('15_interconsulta', $examenes));
        $this->check($pdf, 248.5, 235, in_array('16_otros', $examenes));

        // ===== Sección 15: Notas médicas =====
         $examenes = is_string($f->examenes_solicitados)
            ? json_decode($f->examenes_solicitados, true)
            : (is_array($f->examenes_solicitados) ? $f->examenes_solicitados : []);
        if (!is_array($examenes)) $examenes = [];


        // ===== Sección 16: Alta =====
            $this->check($pdf, 33, 325, $f->alta_destino === 'domicilio');
            $this->check($pdf, 55, 325, $f->alta_destino === 'consulta_externa');
            $this->check($pdf, 81, 325, $f->alta_destino === 'observacion');
            $this->check($pdf, 107.5, 325, $f->alta_destino === 'internacion');
            $this->check($pdf, 130.5, 325, $f->alta_destino === 'referencia');

            $this->txt($pdf, 32, 328, $f->alta_servicio_referencia ?? '', 8, 80);
            $this->txt($pdf, 100.7, 328, $f->alta_establecimiento_referencia ?? '', 8, 85);

            $this->check($pdf, 159.5, 325, $f->alta_resultado === 'vivo');
            $this->check($pdf, 159.5, 331.3, $f->alta_resultado === 'muerto_emergencia');

            $this->check($pdf, 189, 325, $f->alta_condicion === 'estable');
            $this->check($pdf, 105, 341, $f->alta_condicion === 'inestable');

            $this->txt($pdf, 185, 328, $f->alta_causa ?? '', 8, 150);
            $this->txt($pdf, 229, 322, (string)($f->alta_dias_incapacidad ?? ''), 8, 35, 'C');

            $fechaControl = '';
            if (!empty($f->alta_fecha_control)) {
                try {
                $fechaControl = $f->alta_fecha_control->format('d/m/Y');
                } catch (\Throwable $e) {
                $fechaControl = (string)$f->alta_fecha_control;
                }
            }
            $this->txt($pdf, 38, 338  , $fechaControl, 8, 40);
            $this->txt($pdf, 81, 338, $f->alta_hora_finalizacion ?? '', 8, 40);
            $this->txt($pdf, 117, 338, $f->alta_profesional_codigo ?? '', 8, 80);
            $this->txt($pdf, 229, 338, (string)($f->alta_numero_hoja ?? ''), 8, 35, 'C');




    }


    

    





}
