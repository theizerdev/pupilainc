<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CuentaContable;
use App\Models\AsientoContable;
use App\Models\AsientoDetalle;
use App\Models\Empresa;
use Codedge\Fpdf\Fpdf\Fpdf;

class ContabilidadPdfController extends Controller
{
    private function u($text)
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT', $text ?? '');
    }

    private function header(Fpdf $pdf, string $titulo, ?string $periodo = null)
    {
        $empresa = Empresa::find(auth()->user()->empresa_id);

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 7, $this->u($empresa->razon_social ?? 'Empresa'), 0, 1, 'C');

        if ($empresa->rif_fiscal) {
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(0, 5, $this->u('RIF: ' . $empresa->rif_fiscal), 0, 1, 'C');
        }

        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 7, $this->u($titulo), 0, 1, 'C');

        if ($periodo) {
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(0, 5, $this->u($periodo), 0, 1, 'C');
        }

        $pdf->Ln(3);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(3);
    }

    private function numf($val): string
    {
        return number_format((float) $val, 2, ',', '.');
    }

    // ================================================
    // BALANCE DE COMPROBACION
    // ================================================
    public function balanceComprobacion()
    {
        $desde = request('desde', now()->startOfMonth()->format('Y-m-d'));
        $hasta = request('hasta', now()->endOfMonth()->format('Y-m-d'));
        $empresaId = auth()->user()->empresa_id;

        $pdf = new Fpdf('P', 'mm', 'A4');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        $this->header($pdf, 'Balance de Comprobación', "Desde: {$desde}  Hasta: {$hasta}");

        // Table header
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(50, 50, 50);
        $pdf->SetTextColor(255);
        $pdf->Cell(22, 6, $this->u('Código'), 1, 0, 'C', true);
        $pdf->Cell(60, 6, 'Cuenta', 1, 0, 'C', true);
        $pdf->Cell(27, 6, 'Debe', 1, 0, 'C', true);
        $pdf->Cell(27, 6, 'Haber', 1, 0, 'C', true);
        $pdf->Cell(27, 6, 'S.Deudor', 1, 0, 'C', true);
        $pdf->Cell(27, 6, 'S.Acreedor', 1, 1, 'C', true);
        $pdf->SetTextColor(0);

        $cuentas = CuentaContable::where('empresa_id', $empresaId)
            ->where('acepta_movimientos', true)->where('activo', true)
            ->orderBy('codigo')->get();

        $totDebe = $totHaber = $totSD = $totSA = 0;

        $pdf->SetFont('Arial', '', 7);
        foreach ($cuentas as $cuenta) {
            $detalles = AsientoDetalle::where('cuenta_id', $cuenta->id)
                ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado')
                    ->whereBetween('fecha', [$desde, $hasta]));
            $debe = (float) $detalles->sum('debe');
            $haber = (float) (clone $detalles)->sum('haber');

            if ($debe == 0 && $haber == 0) continue;

            $saldoDeudor = $saldoAcreedor = 0;
            if ($cuenta->naturaleza === 'deudora') {
                $s = $debe - $haber;
                $s >= 0 ? $saldoDeudor = $s : $saldoAcreedor = abs($s);
            } else {
                $s = $haber - $debe;
                $s >= 0 ? $saldoAcreedor = $s : $saldoDeudor = abs($s);
            }

            $totDebe += $debe; $totHaber += $haber;
            $totSD += $saldoDeudor; $totSA += $saldoAcreedor;

            $pdf->Cell(22, 5, $cuenta->codigo, 'LR', 0);
            $pdf->Cell(60, 5, $this->u(substr($cuenta->nombre, 0, 40)), 'LR', 0);
            $pdf->Cell(27, 5, $this->numf($debe), 'LR', 0, 'R');
            $pdf->Cell(27, 5, $this->numf($haber), 'LR', 0, 'R');
            $pdf->Cell(27, 5, $saldoDeudor > 0 ? $this->numf($saldoDeudor) : '', 'LR', 0, 'R');
            $pdf->Cell(27, 5, $saldoAcreedor > 0 ? $this->numf($saldoAcreedor) : '', 'LR', 1, 'R');
        }

        // Totals
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(50, 50, 50);
        $pdf->SetTextColor(255);
        $pdf->Cell(82, 6, 'TOTALES', 1, 0, 'R', true);
        $pdf->Cell(27, 6, $this->numf($totDebe), 1, 0, 'R', true);
        $pdf->Cell(27, 6, $this->numf($totHaber), 1, 0, 'R', true);
        $pdf->Cell(27, 6, $this->numf($totSD), 1, 0, 'R', true);
        $pdf->Cell(27, 6, $this->numf($totSA), 1, 1, 'R', true);
        $pdf->SetTextColor(0);

        // Footer
        $pdf->Ln(5);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 4, $this->u('Generado el: ' . now()->format('d/m/Y H:i:s')), 0, 1, 'R');

        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="balance-comprobacion.pdf"');
    }

    // ================================================
    // BALANCE GENERAL
    // ================================================
    public function balanceGeneral()
    {
        $fechaCorte = request('fecha', now()->format('Y-m-d'));
        $empresaId = auth()->user()->empresa_id;

        $pdf = new Fpdf('P', 'mm', 'A4');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        $this->header($pdf, 'Balance General', "Al: {$fechaCorte}");

        $getSaldo = function($cuenta) use ($fechaCorte) {
            $query = AsientoDetalle::where('cuenta_id', $cuenta->id)
                ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado')->whereDate('fecha', '<=', $fechaCorte));
            $d = (float) $query->sum('debe');
            $h = (float) (clone $query)->sum('haber');
            return $cuenta->naturaleza === 'deudora' ? ($d - $h) : ($h - $d);
        };

        $getCuentas = function($tipo) use ($empresaId, $getSaldo) {
            return CuentaContable::where('empresa_id', $empresaId)
                ->where('tipo', $tipo)->where('acepta_movimientos', true)->where('activo', true)
                ->orderBy('codigo')->get()
                ->map(fn($c) => (object)['codigo' => $c->codigo, 'nombre' => $c->nombre, 'saldo' => $getSaldo($c)])
                ->filter(fn($c) => abs($c->saldo) > 0.01);
        };

        $activos = $getCuentas('activo');
        $pasivos = $getCuentas('pasivo');
        $patrimonio = $getCuentas('patrimonio');
        $ingresos = $getCuentas('ingreso');
        $egresos = $getCuentas('egreso');
        $costos = $getCuentas('costo');

        $totalActivos = $activos->sum('saldo');
        $resultadoEjercicio = $ingresos->sum('saldo') - $egresos->sum('saldo') - $costos->sum('saldo');
        $totalPasivos = $pasivos->sum('saldo');
        $totalPatrimonio = $patrimonio->sum('saldo') + $resultadoEjercicio;

        // ACTIVOS section
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(220, 235, 255);
        $pdf->Cell(0, 6, 'ACTIVOS', 1, 1, 'L', true);

        $pdf->SetFont('Arial', '', 8);
        foreach ($activos as $c) {
            $pdf->Cell(25, 5, $c->codigo, 0, 0);
            $pdf->Cell(110, 5, $this->u($c->nombre), 0, 0);
            $pdf->Cell(0, 5, 'Bs ' . $this->numf($c->saldo), 0, 1, 'R');
        }

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(200, 220, 255);
        $pdf->Cell(135, 6, 'TOTAL ACTIVOS', 1, 0, 'R', true);
        $pdf->Cell(0, 6, 'Bs ' . $this->numf($totalActivos), 1, 1, 'R', true);
        $pdf->Ln(3);

        // PASIVOS section
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(255, 220, 220);
        $pdf->Cell(0, 6, 'PASIVOS', 1, 1, 'L', true);

        $pdf->SetFont('Arial', '', 8);
        foreach ($pasivos as $c) {
            $pdf->Cell(25, 5, $c->codigo, 0, 0);
            $pdf->Cell(110, 5, $this->u($c->nombre), 0, 0);
            $pdf->Cell(0, 5, 'Bs ' . $this->numf($c->saldo), 0, 1, 'R');
        }

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(255, 200, 200);
        $pdf->Cell(135, 6, 'TOTAL PASIVOS', 1, 0, 'R', true);
        $pdf->Cell(0, 6, 'Bs ' . $this->numf($totalPasivos), 1, 1, 'R', true);
        $pdf->Ln(3);

        // PATRIMONIO section
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(220, 255, 240);
        $pdf->Cell(0, 6, 'PATRIMONIO', 1, 1, 'L', true);

        $pdf->SetFont('Arial', '', 8);
        foreach ($patrimonio as $c) {
            $pdf->Cell(25, 5, $c->codigo, 0, 0);
            $pdf->Cell(110, 5, $this->u($c->nombre), 0, 0);
            $pdf->Cell(0, 5, 'Bs ' . $this->numf($c->saldo), 0, 1, 'R');
        }

        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(25, 5, '', 0, 0);
        $pdf->Cell(110, 5, $this->u('Resultado del Ejercicio'), 0, 0);
        $pdf->Cell(0, 5, 'Bs ' . $this->numf($resultadoEjercicio), 0, 1, 'R');

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(200, 245, 225);
        $pdf->Cell(135, 6, 'TOTAL PATRIMONIO', 1, 0, 'R', true);
        $pdf->Cell(0, 6, 'Bs ' . $this->numf($totalPatrimonio), 1, 1, 'R', true);
        $pdf->Ln(3);

        // PASIVO + PATRIMONIO
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(50, 50, 50);
        $pdf->SetTextColor(255);
        $pdf->Cell(135, 7, 'PASIVO + PATRIMONIO', 1, 0, 'R', true);
        $pdf->Cell(0, 7, 'Bs ' . $this->numf($totalPasivos + $totalPatrimonio), 1, 1, 'R', true);
        $pdf->SetTextColor(0);

        // Verificación ecuación patrimonial
        $pdf->Ln(5);
        $pdf->SetFont('Arial', 'B', 9);
        if (round($totalActivos, 2) === round($totalPasivos + $totalPatrimonio, 2)) {
            $pdf->Cell(0, 6, $this->u('✓ Ecuación Patrimonial Verificada: A = P + Pt'), 0, 1, 'C');
        } else {
            $pdf->Cell(0, 6, $this->u('✗ Descuadre: Bs ' . $this->numf(abs($totalActivos - $totalPasivos - $totalPatrimonio))), 0, 1, 'C');
        }

        $pdf->Ln(3);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 4, $this->u('Generado el: ' . now()->format('d/m/Y H:i:s')), 0, 1, 'R');

        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="balance-general.pdf"');
    }

    // ================================================
    // ESTADO DE RESULTADOS
    // ================================================
    public function estadoResultados()
    {
        $desde = request('desde', now()->startOfYear()->format('Y-m-d'));
        $hasta = request('hasta', now()->format('Y-m-d'));
        $empresaId = auth()->user()->empresa_id;

        $pdf = new Fpdf('P', 'mm', 'A4');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        $this->header($pdf, 'Estado de Resultados', "Desde: {$desde}  Hasta: {$hasta}");

        $getCuentas = function($tipo) use ($empresaId, $desde, $hasta) {
            return CuentaContable::where('empresa_id', $empresaId)
                ->where('tipo', $tipo)->where('acepta_movimientos', true)->where('activo', true)
                ->orderBy('codigo')->get()
                ->map(function($cuenta) use ($desde, $hasta) {
                    $query = AsientoDetalle::where('cuenta_id', $cuenta->id)
                        ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado')
                            ->whereBetween('fecha', [$desde, $hasta]));
                    $d = (float) $query->sum('debe');
                    $h = (float) (clone $query)->sum('haber');
                    $saldo = $cuenta->naturaleza === 'deudora' ? ($d - $h) : ($h - $d);
                    return (object)['codigo' => $cuenta->codigo, 'nombre' => $cuenta->nombre, 'saldo' => $saldo];
                })
                ->filter(fn($c) => abs($c->saldo) > 0.01);
        };

        $ingresos = $getCuentas('ingreso');
        $costos = $getCuentas('costo');
        $egresos = $getCuentas('egreso');

        $totalIngresos = $ingresos->sum('saldo');
        $totalCostos = $costos->sum('saldo');
        $utilidadBruta = $totalIngresos - $totalCostos;
        $totalEgresos = $egresos->sum('saldo');
        $utilidadNeta = $utilidadBruta - $totalEgresos;

        $pageW = 190;

        // INGRESOS
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(200, 240, 200);
        $pdf->Cell($pageW, 6, 'INGRESOS OPERACIONALES', 1, 1, 'L', true);

        $pdf->SetFont('Arial', '', 8);
        foreach ($ingresos as $c) {
            $pdf->Cell(25, 5, $c->codigo, 0, 0);
            $pdf->Cell($pageW - 80, 5, $this->u($c->nombre), 0, 0);
            $pdf->Cell(55, 5, 'Bs ' . $this->numf($c->saldo), 0, 1, 'R');
        }

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($pageW - 55, 6, 'Total Ingresos', 0, 0, 'R');
        $pdf->Cell(55, 6, 'Bs ' . $this->numf($totalIngresos), 0, 1, 'R');
        $pdf->Ln(2);

        // COSTOS
        if ($costos->count() > 0) {
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(255, 240, 200);
            $pdf->Cell($pageW, 6, 'COSTOS DE SERVICIO', 1, 1, 'L', true);

            $pdf->SetFont('Arial', '', 8);
            foreach ($costos as $c) {
                $pdf->Cell(25, 5, $c->codigo, 0, 0);
                $pdf->Cell($pageW - 80, 5, $this->u($c->nombre), 0, 0);
                $pdf->Cell(55, 5, 'Bs ' . $this->numf($c->saldo), 0, 1, 'R');
            }

            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell($pageW - 55, 6, 'Total Costos', 0, 0, 'R');
            $pdf->Cell(55, 6, 'Bs ' . $this->numf($totalCostos), 0, 1, 'R');
            $pdf->Ln(2);
        }

        // UTILIDAD BRUTA
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(230, 230, 230);
        $pdf->Cell($pageW - 55, 7, 'UTILIDAD BRUTA', 1, 0, 'R', true);
        $pdf->Cell(55, 7, 'Bs ' . $this->numf($utilidadBruta), 1, 1, 'R', true);
        $pdf->Ln(2);

        // GASTOS
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(255, 210, 210);
        $pdf->Cell($pageW, 6, 'GASTOS OPERACIONALES', 1, 1, 'L', true);

        $pdf->SetFont('Arial', '', 8);
        foreach ($egresos as $c) {
            $pdf->Cell(25, 5, $c->codigo, 0, 0);
            $pdf->Cell($pageW - 80, 5, $this->u($c->nombre), 0, 0);
            $pdf->Cell(55, 5, 'Bs ' . $this->numf($c->saldo), 0, 1, 'R');
        }

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell($pageW - 55, 6, 'Total Gastos', 0, 0, 'R');
        $pdf->Cell(55, 6, 'Bs ' . $this->numf($totalEgresos), 0, 1, 'R');
        $pdf->Ln(3);

        // UTILIDAD NETA
        $pdf->SetFont('Arial', 'B', 11);
        if ($utilidadNeta >= 0) {
            $pdf->SetFillColor(100, 180, 100);
        } else {
            $pdf->SetFillColor(220, 80, 80);
        }
        $pdf->SetTextColor(255);
        $label = $utilidadNeta >= 0 ? 'UTILIDAD NETA DEL EJERCICIO' : $this->u('PÉRDIDA NETA DEL EJERCICIO');
        $pdf->Cell($pageW - 55, 8, $label, 1, 0, 'R', true);
        $pdf->Cell(55, 8, 'Bs ' . $this->numf(abs($utilidadNeta)), 1, 1, 'R', true);
        $pdf->SetTextColor(0);

        $pdf->Ln(5);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 4, $this->u('Generado el: ' . now()->format('d/m/Y H:i:s')), 0, 1, 'R');

        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="estado-resultados.pdf"');
    }

    // ================================================
    // LIBRO MAYOR
    // ================================================
    public function libroMayor()
    {
        $cuentaId = request('cuenta_id');
        $desde = request('desde', now()->startOfMonth()->format('Y-m-d'));
        $hasta = request('hasta', now()->endOfMonth()->format('Y-m-d'));

        if (!$cuentaId) {
            abort(400, 'Debe seleccionar una cuenta');
        }

        $cuenta = CuentaContable::findOrFail($cuentaId);

        $pdf = new Fpdf('L', 'mm', 'A4');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        $this->header($pdf, 'Libro Mayor', "Cuenta: {$cuenta->codigo} - {$cuenta->nombre}  |  Desde: {$desde}  Hasta: {$hasta}");

        // Saldo anterior
        $queryAnterior = AsientoDetalle::where('cuenta_id', $cuentaId)
            ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado')->whereDate('fecha', '<', $desde));
        $di = (float) $queryAnterior->sum('debe');
        $hi = (float) (clone $queryAnterior)->sum('haber');
        $saldoAnterior = $cuenta->naturaleza === 'deudora' ? ($di - $hi) : ($hi - $di);
        $saldo = $saldoAnterior;

        // Table header
        $pageW = 277;
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(50, 50, 50);
        $pdf->SetTextColor(255);
        $pdf->Cell(22, 6, 'Fecha', 1, 0, 'C', true);
        $pdf->Cell(25, 6, 'Asiento', 1, 0, 'C', true);
        $pdf->Cell(120, 6, $this->u('Descripción'), 1, 0, 'C', true);
        $pdf->Cell(30, 6, 'Debe', 1, 0, 'C', true);
        $pdf->Cell(30, 6, 'Haber', 1, 0, 'C', true);
        $pdf->Cell(30, 6, 'Saldo', 1, 1, 'C', true);
        $pdf->SetTextColor(0);

        // Saldo anterior row
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->SetFillColor(240, 240, 240);
        $pdf->Cell(167, 5, 'Saldo Anterior', 1, 0, 'R', true);
        $pdf->Cell(30, 5, '-', 1, 0, 'C', true);
        $pdf->Cell(30, 5, '-', 1, 0, 'C', true);
        $pdf->Cell(30, 5, 'Bs ' . $this->numf($saldoAnterior), 1, 1, 'R', true);

        // Movimientos
        $movimientos = AsientoDetalle::where('cuenta_id', $cuentaId)
            ->whereHas('asiento', fn($q) => $q->where('estado', 'aprobado')
                ->whereBetween('fecha', [$desde, $hasta]))
            ->with('asiento')
            ->get()
            ->sortBy('asiento.fecha');

        $pdf->SetFont('Arial', '', 7);
        $totalDebe = $totalHaber = 0;
        foreach ($movimientos as $det) {
            $d = (float) $det->debe;
            $h = (float) $det->haber;
            $totalDebe += $d;
            $totalHaber += $h;

            if ($cuenta->naturaleza === 'deudora') {
                $saldo += ($d - $h);
            } else {
                $saldo += ($h - $d);
            }

            $pdf->Cell(22, 5, $det->asiento->fecha->format('d/m/Y'), 'LR', 0);
            $pdf->Cell(25, 5, $det->asiento->numero, 'LR', 0);
            $pdf->Cell(120, 5, $this->u(substr($det->descripcion ?: $det->asiento->descripcion, 0, 75)), 'LR', 0);
            $pdf->Cell(30, 5, $d > 0 ? $this->numf($d) : '', 'LR', 0, 'R');
            $pdf->Cell(30, 5, $h > 0 ? $this->numf($h) : '', 'LR', 0, 'R');
            $pdf->Cell(30, 5, 'Bs ' . $this->numf($saldo), 'LR', 1, 'R');
        }

        // Totals
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(50, 50, 50);
        $pdf->SetTextColor(255);
        $pdf->Cell(167, 6, $this->u('TOTALES PERÍODO'), 1, 0, 'R', true);
        $pdf->Cell(30, 6, $this->numf($totalDebe), 1, 0, 'R', true);
        $pdf->Cell(30, 6, $this->numf($totalHaber), 1, 0, 'R', true);
        $pdf->Cell(30, 6, 'Bs ' . $this->numf($saldo), 1, 1, 'R', true);
        $pdf->SetTextColor(0);

        $pdf->Ln(5);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 4, $this->u('Generado el: ' . now()->format('d/m/Y H:i:s')), 0, 1, 'R');

        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="libro-mayor.pdf"');
    }

    // ================================================
    // LIBRO DIARIO
    // ================================================
    public function libroDiario()
    {
        $desde = request('desde', now()->startOfMonth()->format('Y-m-d'));
        $hasta = request('hasta', now()->endOfMonth()->format('Y-m-d'));
        $empresaId = auth()->user()->empresa_id;

        $pdf = new Fpdf('L', 'mm', 'A4');
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        $this->header($pdf, 'Libro Diario', "Desde: {$desde}  Hasta: {$hasta}");

        $asientos = AsientoContable::with(['detalles.cuenta', 'user'])
            ->where('empresa_id', $empresaId)
            ->where('estado', 'aprobado')
            ->whereBetween('fecha', [$desde, $hasta])
            ->orderBy('fecha')
            ->orderBy('numero')
            ->get();

        $grandTotalDebe = 0;
        $grandTotalHaber = 0;
        $pageW = 277;

        foreach ($asientos as $asiento) {
            // Check if we need a new page (estimate: header + at least 3 detail rows)
            if ($pdf->GetY() > 170) {
                $pdf->AddPage();
            }

            // Asiento header
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetFillColor(230, 235, 245);
            $pdf->Cell(25, 5, $asiento->numero, 1, 0, 'L', true);
            $pdf->Cell(20, 5, $asiento->fecha->format('d/m/Y'), 1, 0, 'C', true);
            $pdf->SetFont('Arial', '', 7);
            $pdf->Cell($pageW - 45, 5, $this->u($asiento->descripcion), 1, 1, 'L', true);

            // Detail table header
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell(22, 5, $this->u('Código'), 1, 0, 'C', true);
            $pdf->Cell(70, 5, 'Cuenta', 1, 0, 'C', true);
            $pdf->Cell(100, 5, $this->u('Descripción'), 1, 0, 'C', true);
            $pdf->Cell(30, 5, 'Debe', 1, 0, 'C', true);
            $pdf->Cell(30, 5, 'Haber', 1, 1, 'C', true);

            // Detail rows
            $pdf->SetFont('Arial', '', 7);
            $asientoDebe = 0;
            $asientoHaber = 0;

            foreach ($asiento->detalles as $det) {
                $d = (float) $det->debe;
                $h = (float) $det->haber;
                $asientoDebe += $d;
                $asientoHaber += $h;

                $pdf->Cell(22, 4, $det->cuenta->codigo ?? '', 'LR', 0);
                $pdf->Cell(70, 4, $this->u(substr($det->cuenta->nombre ?? '', 0, 45)), 'LR', 0);
                $pdf->Cell(100, 4, $this->u(substr($det->descripcion ?? '', 0, 65)), 'LR', 0);
                $pdf->Cell(30, 4, $d > 0 ? $this->numf($d) : '', 'LR', 0, 'R');
                $pdf->Cell(30, 4, $h > 0 ? $this->numf($h) : '', 'LR', 1, 'R');
            }

            // Asiento totals
            $pdf->SetFont('Arial', 'B', 7);
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell(192, 5, 'Totales asiento:', 1, 0, 'R', true);
            $pdf->Cell(30, 5, $this->numf($asientoDebe), 1, 0, 'R', true);
            $pdf->Cell(30, 5, $this->numf($asientoHaber), 1, 1, 'R', true);

            $grandTotalDebe += $asientoDebe;
            $grandTotalHaber += $asientoHaber;

            $pdf->Ln(2);
        }

        // Grand totals
        $pdf->Ln(3);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetFillColor(50, 50, 50);
        $pdf->SetTextColor(255);
        $pdf->Cell(192, 7, $this->u('TOTALES GENERALES (' . $asientos->count() . ' asientos)'), 1, 0, 'R', true);
        $pdf->Cell(30, 7, $this->numf($grandTotalDebe), 1, 0, 'R', true);
        $pdf->Cell(30, 7, $this->numf($grandTotalHaber), 1, 1, 'R', true);
        $pdf->SetTextColor(0);

        $pdf->Ln(5);
        $pdf->SetFont('Arial', '', 7);
        $pdf->Cell(0, 4, $this->u('Generado el: ' . now()->format('d/m/Y H:i:s')), 0, 1, 'R');

        return response($pdf->Output('S'), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="libro-diario.pdf"');
    }
}
