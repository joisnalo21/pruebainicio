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
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 12);

        // Header
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 8, $this->enc($report['title'] ?? 'Reporte'), 0, 1, 'L');

        $pdf->SetFont('Arial', '', 9);
        $meta = sprintf(
            "Rango: %s a %s | Estado: %s | Generado: %s",
            $filters['desde'] ?? '—',
            $filters['hasta'] ?? '—',
            strtoupper($filters['estado'] ?? '—'),
            now()->format('Y-m-d H:i')
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
