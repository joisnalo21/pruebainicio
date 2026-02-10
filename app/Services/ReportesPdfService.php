<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;

class ReportesPdfService
{
    public function render(array $report, array $filters): string
    {
        $orientation = $report['orientation'] ?? 'P';
        $columns = $report['columns'] ?? [];
        $rows    = $report['rows'] ?? [];
        $totals  = $report['totals'] ?? null;

        // Si no especificaron orientación, decide según ancho total
        $sumW = 0;
        foreach ($columns as $c) $sumW += (float)($c['w'] ?? 0);
        if (!isset($report['orientation'])) {
            $orientation = $sumW > 190 ? 'L' : 'P';
        }

        $pdf = new Fpdi($orientation, 'mm', 'A4');
        $pdf->SetMargins(12, 10, 12);
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 12);

        // ===== HEADER PRO (logo + caja) =====
        $pageW = $pdf->GetPageWidth();
        $left  = 12;
        $top   = 10;
        $right = 12;

        // Logo MSP (arriba izquierda)
        $logo = public_path('img/msp.png');
        if (is_file($logo)) {
            // Ajusta tamaño si quieres (20–26 queda bien)
            $pdf->Image($logo, $left, $top, 22);
        }

        // Textos (área/sistema)
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(30, 30, 30);

        // Deja espacio para el logo (22mm) + un gap
        $textX = $left + 26;
        $pdf->SetXY($textX, $top + 2);
        $pdf->Cell(0, 5, $this->enc('Área de Emergencia / Estadística'), 0, 1, 'L');
        $pdf->SetX($textX);
        $pdf->Cell(0, 5, $this->enc('Sistema: Emergencia008'), 0, 1, 'L');

        // Caja top-right (Documento/Generado/Tipo)
        $boxW = 64;
        $boxH = 18;
        $boxX = $pageW - $right - $boxW;
        $boxY = $top;

        $tipo = strtoupper($filters['tipo'] ?? 'GENERAL');
        $docCode = match ($filters['tipo'] ?? 'general') {
            'prod' => 'REP-008-PROD',
            'prod_prof' => 'REP-008-PROF',
            'demo' => 'REP-008-DEMO',
            'dx_ingreso' => 'REP-008-DX-ING',
            'dx_alta' => 'REP-008-DX-ALT',
            'tiempos' => 'REP-008-TIEM',
            default => 'REP-008-GEN',
        };

        $pdf->SetDrawColor(0, 0, 0);
        $pdf->Rect($boxX, $boxY, $boxW, $boxH);

        $pdf->SetFont('Arial', '', 8);
        $pdf->SetXY($boxX + 2, $boxY + 3);
        $pdf->Cell($boxW - 4, 4, $this->enc("Documento: {$docCode}"), 0, 1, 'L');
        $pdf->SetX($boxX + 2);
        $pdf->Cell($boxW - 4, 4, $this->enc('Generado: ' . now()->format('Y-m-d H:i')), 0, 1, 'L');
        $pdf->SetX($boxX + 2);
        $pdf->Cell($boxW - 4, 4, $this->enc('Tipo: ' . $tipo), 0, 1, 'L');

        // Línea separadora
        $pdf->Line($left, $top + 22, $pageW - $right, $top + 22);

        // ===== TÍTULO + META =====
        $pdf->SetY($top + 26);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 8, $this->enc($report['title'] ?? 'Reporte'), 0, 1, 'L');

        $pdf->SetFont('Arial', '', 9);

        $usuario = '—';
        if (!empty($filters['user_name'])) {
            $usuario = $filters['user_name'];
        } elseif (!empty($filters['user_id'])) {
            $usuario = 'ID ' . $filters['user_id'];
        } elseif (auth()->check() && !empty(auth()->user()->name)) {
            $usuario = auth()->user()->name;
        }

        $meta = sprintf(
            "Rango: %s a %s      Estado: %s      Usuario: %s",
            $filters['desde'] ?? '—',
            $filters['hasta'] ?? '—',
            strtoupper($filters['estado'] ?? '—'),
            $usuario
        );
        $pdf->MultiCell(0, 5, $this->enc($meta));

        if (!empty($report['note'])) {
            $pdf->Ln(1);
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->MultiCell(0, 4, $this->enc($report['note']));
        }

        $pdf->Ln(4);

        // Tabla header
        $this->drawTableHeader($pdf, $columns);

        // Rows (zebra)
        $pdf->SetFont('Arial', '', 9);
        $fill = false;

        foreach ($rows as $r) {
            if ($pdf->GetY() > 270) {
                $pdf->AddPage();
                $this->drawTableHeader($pdf, $columns);
            }

            $pdf->SetFillColor(248, 248, 248);

            foreach ($columns as $i => $c) {
                $w = (float)($c['w'] ?? 30);
                $val = $r[$i] ?? '';
                $align = is_numeric($val) ? 'R' : 'L';

                $pdf->Cell($w, 6, $this->enc((string)$val), 1, 0, $align, $fill);
            }
            $pdf->Ln();
            $fill = !$fill;
        }

        // Totals
        if (is_array($totals)) {
            if ($pdf->GetY() > 270) {
                $pdf->AddPage();
                $this->drawTableHeader($pdf, $columns);
            }

            $pdf->SetFont('Arial', 'B', 9);
            foreach ($columns as $i => $c) {
                $w = (float)($c['w'] ?? 30);
                $val = $totals[$i] ?? '';
                $align = ($i === 0) ? 'L' : 'R';
                $pdf->Cell($w, 7, $this->enc((string)$val), 1, 0, $align, false);
            }
            $pdf->Ln();
        }

        // Footer
        $pdf->SetY(-10);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 6, $this->enc('Emergencia008 · Reporte generado automáticamente · Página ' . $pdf->PageNo()), 0, 0, 'C');

        return $pdf->Output('S');
    }

    private function drawTableHeader(Fpdi $pdf, array $columns): void
    {
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(240, 240, 240);

        foreach ($columns as $c) {
            $w = (float)($c['w'] ?? 30);
            $pdf->Cell($w, 7, $this->enc((string)$c['label']), 1, 0, 'C', true);
        }
        $pdf->Ln();
    }

    private function enc(string $txt): string
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT', $txt);
    }
}
