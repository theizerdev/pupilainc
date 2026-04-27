<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consulta;
use App\Models\EspecialidadPlantilla;
use Codedge\Fpdf\Fpdf\Fpdf;

class InformeMedicoController extends Controller
{
    private Fpdf $pdf;
    private $consulta;
    private $empresa;

    public function generar($id)
    {
        $this->consulta = Consulta::with([
            'paciente',
            'medico.especialidades',
            'especialidad',
            'signosVitales',
            'evaluacion',
            'tratamientos',
            'estudios',
            'reposo',
            'estadoDatos',
        ])->findOrFail($id);

        // Cargar diagnósticos explícitamente para evitar conflicto con cast
        $this->consulta->setRelation(
            'diagnosticos',
            $this->consulta->diagnosticos()->get()
        );

        $this->empresa = auth()->user()->empresa;

        $this->pdf = new Fpdf('P', 'mm', 'A4');
        $this->pdf->SetMargins(20, 15, 20);
        $this->pdf->SetAutoPageBreak(true, 28);

        // ── Página 1: Informe completo ────────────────────────────────────────
        $this->pdf->AddPage();
        $this->encabezado('INFORME MÉDICO');
        $this->datosPaciente();
        $this->datosConsulta();
        $this->signosVitales();
        $this->datosEstados();
        $this->evaluacion();
        $this->diagnosticos();
        $this->estudios();
        $this->tratamientos();
        //$this->firma();
        //$this->piePagina();

        // ── Página 2: Orden de estudios (si aplica) ───────────────────────────
        if ($this->consulta->estudios->count() > 0) {
            $this->pdf->AddPage();
            $this->encabezado('ORDEN DE ESTUDIOS');
            $this->datosPacienteResumido();
            $this->estudiosDetallado();
            //$this->firma();
            //$this->piePagina();
        }

        // ── Página 3: Recipe médico (si aplica) ───────────────────────────────
        if ($this->consulta->tratamientos->count() > 0) {
            $this->pdf->AddPage();
            $this->encabezado('RECIPE MÉDICO');
            $this->datosPacienteResumido();
            $this->tratamientosDetallado();
            //$this->firma();
            //$this->piePagina();
        }

        return response($this->pdf->Output('S'), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Informe_Medico_' . $this->consulta->codigo . '.pdf"',
        ]);
    }

    // ── ENCABEZADO ────────────────────────────────────────────────────────────

    private function encabezado(string $titulo): void
    {
        $logoPath  = public_path('logo/app.png');
        $tieneLogo = file_exists($logoPath);

        if ($tieneLogo) {
            $this->pdf->Image($logoPath, 20, 12, 35);
        }

        // Datos empresa a la derecha del logo
        $xTexto = $tieneLogo ? 60 : 20;
        $this->pdf->SetXY($xTexto, 12);
        $this->pdf->SetFont('Arial', 'B', 13);
        $this->pdf->SetTextColor(41, 128, 185);
        $nombre = $this->empresa->razon_social ?? $this->empresa->nombre ?? '';
        $this->pdf->Cell(0, 7, utf8_decode($nombre), 0, 1, 'L');

        $this->pdf->SetXY($xTexto, $this->pdf->GetY());
        $this->pdf->SetFont('Arial', '', 9);
        $this->pdf->SetTextColor(100, 100, 100);

        if (!empty($this->empresa->rif)) {
            $this->pdf->Cell(0, 5, utf8_decode('RIF: ' . $this->empresa->rif), 0, 1, 'L');
            $this->pdf->SetX($xTexto);
        }
        if ($this->empresa->telefono) {
            $this->pdf->Cell(0, 5, utf8_decode('Tel: ' . $this->empresa->telefono), 0, 1, 'L');
            $this->pdf->SetX($xTexto);
        }
        if ($this->empresa->direccion) {
            $this->pdf->SetFont('Arial', '', 8);
            $this->pdf->MultiCell(130, 4, utf8_decode($this->empresa->direccion), 0, 'L');
        }

        // Número y fecha alineados a la derecha
        $this->pdf->SetXY(130, 12);
        $this->pdf->SetFont('Arial', '', 8);
        $this->pdf->SetTextColor(130, 130, 130);
        $this->pdf->Cell(70, 5, utf8_decode('N°: ' . str_pad($this->consulta->codigo, 8, '0', STR_PAD_LEFT)), 0, 1, 'R');
        $this->pdf->SetX(130);
        $this->pdf->Cell(70, 5, utf8_decode('Generado: ' . now()->format('d/m/Y H:i')), 0, 1, 'R');

        // Línea separadora
        $yLinea = max($this->pdf->GetY(), 48) + 3;
        $this->pdf->SetDrawColor(41, 128, 185);
        $this->pdf->SetLineWidth(0.8);
        $this->pdf->Line(20, $yLinea, 190, $yLinea);
        $this->pdf->SetLineWidth(0.2);
        $this->pdf->SetDrawColor(0, 0, 0);
        $this->pdf->SetY($yLinea + 4);

        // Barra de título
        $this->pdf->SetFillColor(41, 128, 185);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->Cell(0, 9, utf8_decode($titulo), 0, 1, 'C', true);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->Ln(5);
    }

    // ── DATOS PACIENTE ────────────────────────────────────────────────────────

    private function datosPaciente(): void
    {
        $this->seccion('DATOS DEL PACIENTE');
        $paciente = $this->consulta->paciente;

        $fechaNac = \Carbon\Carbon::parse($paciente->fecha_nacimiento);
        $años     = (int) $fechaNac->diffInYears(now());
        $meses    = (int) $fechaNac->copy()->addYears($años)->diffInMonths(now());
        $edad     = $años . ' años' . ($meses > 0 ? ' y ' . $meses . ' meses' : '');

        // Fila 1: nombre y documento
        $this->fila2col('Nombre completo', $paciente->nombre_completo, 'Documento', $paciente->documento_identidad ?? 'N/A');
        // Fila 2: fecha nacimiento y sexo
        $this->fila2col('F. nacimiento', $fechaNac->format('d/m/Y') . ' (' . $edad . ')', 'Sexo', ucfirst($paciente->genero ?? 'No especificado'));
        // Fila 3: teléfono
        if ($paciente->telefono) {
            $this->fila1col('Teléfono', $paciente->telefono);
        }

        // Alergias — bloque rojo si existe
        if (!empty($paciente->alergias)) {
            $this->pdf->SetFillColor(255, 235, 235);
            $this->pdf->SetDrawColor(220, 53, 69);
            $this->pdf->SetLineWidth(0.4);
            $this->pdf->SetFont('Arial', 'B', 9);
            $this->pdf->SetTextColor(220, 53, 69);
            $this->pdf->Cell(40, 7, utf8_decode('ALERGIAS:'), 1, 0, 'L', true);
            $this->pdf->SetFont('Arial', 'B', 9);
            $this->pdf->Cell(130, 7, utf8_decode(strtoupper($paciente->alergias)), 1, 1, 'L', true);
            $this->pdf->SetLineWidth(0.2);
            $this->pdf->SetDrawColor(0, 0, 0);
            $this->pdf->SetTextColor(0, 0, 0);
        }

        $this->pdf->Ln(4);
    }

    // ── DATOS CONSULTA ────────────────────────────────────────────────────────

    private function datosConsulta(): void
    {
        $this->seccion('DATOS DE LA CONSULTA');

        $especialidad = $this->consulta->especialidad->nombre
            ?? $this->consulta->medico->especialidades()->take(1)->pluck('nombre')->first()
            ?? 'No especificada';

        $this->fila2col(
            'Fecha y hora',
            $this->consulta->fecha_consulta->format('d/m/Y H:i'),
            'Especialidad',
            $especialidad
        );
        $this->fila1col('Médico tratante', 'Dr(a). ' . $this->consulta->medico->nombre_completo);

        if ($this->consulta->motivo_consulta) {
            $this->pdf->SetFont('Arial', 'B', 9);
            $this->pdf->SetFillColor(245, 248, 252);
            $this->pdf->Cell(40, 6, utf8_decode('Motivo de consulta:'), 1, 0, 'L', true);
            $this->pdf->SetFont('Arial', '', 9);
            $this->pdf->MultiCell(130, 6, utf8_decode($this->consulta->motivo_consulta), 1);
        }

        $this->pdf->Ln(4);
    }

    // ── SIGNOS VITALES ────────────────────────────────────────────────────────

    private function signosVitales(): void
    {
        $sv = $this->consulta->signosVitales()->latest()->first();
        if (!$sv) return;

        $this->seccion('SIGNOS VITALES');

        // 4 tarjetas en una fila
        $tarjetas = [
            ['P.A.', ($sv->presion_arterial_sistolica ?? '--') . '/' . ($sv->presion_arterial_diastolica ?? '--'), 'mmHg'],
            ['F.C.', $sv->frecuencia_cardiaca ?? '--', 'lpm'],
            ['Temp.', $sv->temperatura ?? '--', '°C'],
            ['Sat. O₂', $sv->saturacion_oxigeno ?? '--', '%'],
        ];

        $xInicio = 20;
        $yInicio = $this->pdf->GetY();
        $ancho   = 42;
        $alto    = 18;

        foreach ($tarjetas as $i => $t) {
            $x = $xInicio + ($i * ($ancho + 2));
            $this->pdf->SetFillColor(245, 248, 252);
            $this->pdf->SetDrawColor(41, 128, 185);
            $this->pdf->SetLineWidth(0.3);
            $this->pdf->Rect($x, $yInicio, $ancho, $alto, 'DF');

            $this->pdf->SetXY($x, $yInicio + 1);
            $this->pdf->SetFont('Arial', '', 7);
            $this->pdf->SetTextColor(100, 100, 100);
            $this->pdf->Cell($ancho, 5, utf8_decode($t[0]), 0, 1, 'C');

            $this->pdf->SetX($x);
            $this->pdf->SetFont('Arial', 'B', 12);
            $this->pdf->SetTextColor(41, 128, 185);
            $this->pdf->Cell($ancho, 7, utf8_decode((string)$t[1]), 0, 1, 'C');

            $this->pdf->SetX($x);
            $this->pdf->SetFont('Arial', '', 7);
            $this->pdf->SetTextColor(130, 130, 130);
            $this->pdf->Cell($ancho, 4, utf8_decode($t[2]), 0, 1, 'C');
        }

        $this->pdf->SetLineWidth(0.2);
        $this->pdf->SetDrawColor(0, 0, 0);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->SetY($yInicio + $alto + 3);

        // Segunda fila: peso, talla, IMC, FR
        $imc      = $sv->imc ?? null;
        $tarjetas2 = [
            ['Peso', $sv->peso ?? '--', 'kg'],
            ['Talla', $sv->talla ?? '--', 'cm'],
            ['IMC', $imc ?? '--', $this->clasificacionIMC($imc)],
            ['F.R.', $sv->frecuencia_respiratoria ?? '--', 'rpm'],
        ];

        $yInicio2 = $this->pdf->GetY();
        foreach ($tarjetas2 as $i => $t) {
            $x = $xInicio + ($i * ($ancho + 2));
            $this->pdf->SetFillColor(250, 250, 250);
            $this->pdf->SetDrawColor(200, 200, 200);
            $this->pdf->SetLineWidth(0.2);
            $this->pdf->Rect($x, $yInicio2, $ancho, $alto, 'DF');

            $this->pdf->SetXY($x, $yInicio2 + 1);
            $this->pdf->SetFont('Arial', '', 7);
            $this->pdf->SetTextColor(100, 100, 100);
            $this->pdf->Cell($ancho, 5, utf8_decode($t[0]), 0, 1, 'C');

            $this->pdf->SetX($x);
            $this->pdf->SetFont('Arial', 'B', 11);
            $this->pdf->SetTextColor(50, 50, 50);
            $this->pdf->Cell($ancho, 7, utf8_decode((string)$t[1]), 0, 1, 'C');

            $this->pdf->SetX($x);
            $this->pdf->SetFont('Arial', '', 7);
            $this->pdf->SetTextColor(130, 130, 130);
            $this->pdf->Cell($ancho, 4, utf8_decode($t[2]), 0, 1, 'C');
        }

        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->SetY($yInicio2 + $alto + 3);

        if ($sv->observaciones) {
            $this->pdf->SetFont('Arial', 'I', 8);
            $this->pdf->SetTextColor(100, 100, 100);
            $this->pdf->MultiCell(0, 5, utf8_decode('Obs: ' . $sv->observaciones), 0);
            $this->pdf->SetTextColor(0, 0, 0);
        }

        $this->pdf->Ln(3);
    }

    // ── EVALUACIÓN DINÁMICA ───────────────────────────────────────────────────


    private function datosEstados(): void
    {
        $estadoDatos = $this->consulta->estadoDatos;
        if (!$estadoDatos || $estadoDatos->count() === 0) return;

        $plantilla = null;
        if ($this->consulta->especialidad_id) {
            $plantilla = EspecialidadPlantilla::with(['estadoFormularios.secciones.campos'])
                ->where('especialidad_id', $this->consulta->especialidad_id)
                ->where('activo', true)
                ->latest()
                ->first();
        }

        foreach ($estadoDatos as $estadoDato) {
            $datos = $estadoDato->datos ?? [];
            if (empty($datos)) continue;

            $tituloEstado = 'DATOS DEL ESTADO';
            if ($plantilla) {
                $ef = $plantilla->estadoFormularios->where('estado', $estadoDato->estado)->first();
                if ($ef) {
                    $tituloEstado = strtoupper($ef->titulo);
                }
            }

            $this->seccion($tituloEstado);

            if ($plantilla) {
                $ef = $plantilla->estadoFormularios->where('estado', $estadoDato->estado)->first();
                if ($ef) {
                    foreach ($ef->secciones as $seccion) {
                        $hayDatos = collect($seccion->campos)->contains(function ($campo) use ($datos) {
                            $v = $datos[$campo->nombre_campo] ?? null;
                            return $v !== null && $v !== '' && $v !== [];
                        });

                        if (!$hayDatos) continue;

                        $this->pdf->SetFont('Arial', 'B', 9);
                        $this->pdf->SetTextColor(41, 128, 185);
                        $this->pdf->Cell(0, 6, utf8_decode($seccion->nombre), 0, 1);
                        $this->pdf->SetTextColor(0, 0, 0);

                        foreach ($seccion->campos as $campo) {
                            $valor = $datos[$campo->nombre_campo] ?? null;
                            if ($valor === null || $valor === '' || $valor === []) continue;

                            $valorTexto = is_array($valor) ? implode(', ', $valor) : (string) $valor;
                            if ($campo->unidad) $valorTexto .= ' ' . $campo->unidad;

                            $this->pdf->SetFillColor(248, 249, 250);
                            $this->pdf->SetFont('Arial', 'B', 9);
                            $this->pdf->Cell(65, 6, utf8_decode($campo->etiqueta . ':'), 0, 0, 'L');
                            $this->pdf->SetFont('Arial', '', 9);
                            $this->pdf->MultiCell(105, 6, utf8_decode($valorTexto), 0);
                        }
                        $this->pdf->Ln(2);
                    }
                }
            } else {
                foreach ($datos as $clave => $valor) {
                    if ($valor === null || $valor === '' || $valor === []) continue;
                    $valorTexto = is_array($valor) ? implode(', ', $valor) : (string) $valor;
                    $this->pdf->SetFont('Arial', 'B', 9);
                    $this->pdf->Cell(65, 6, utf8_decode(ucwords(str_replace('_', ' ', $clave)) . ':'), 0, 0);
                    $this->pdf->SetFont('Arial', '', 9);
                    $this->pdf->MultiCell(105, 6, utf8_decode($valorTexto), 0);
                }
            }

            $this->pdf->Ln(3);
        }
    }

    private function evaluacion(): void
    {
        if (!$this->consulta->evaluacion) return;

        $eval  = $this->consulta->evaluacion;
        $datos = $eval->datos_dinamicos ?? [];

        // Campos legacy
        $legacy = [
            'enfermedad_actual'        => 'ENFERMEDAD ACTUAL',
            'examen_fisico'            => 'EXAMEN FÍSICO',
            'conclusion'               => 'CONCLUSIÓN MÉDICA',
            'observaciones_adicionales'=> 'OBSERVACIONES',
        ];

        foreach ($legacy as $campo => $titulo) {
            $valor = $eval->$campo ?? ($datos[$campo] ?? null);
            if ($valor) {
                $this->seccion($titulo);
                $this->pdf->SetFont('Arial', '', 9);
                $this->pdf->SetTextColor(30, 30, 30);
                $this->pdf->MultiCell(0, 5, utf8_decode($valor), 0);
                $this->pdf->Ln(3);
            }
        }

        // Campos dinámicos de plantilla
        $excluidos      = array_keys($legacy);
        $datosDinamicos = array_diff_key($datos, array_flip($excluidos));

        if (empty($datosDinamicos)) return;

        $plantilla = null;
        if ($this->consulta->especialidad_id) {
            $plantilla = EspecialidadPlantilla::with(['secciones.campos'])
                ->where('especialidad_id', $this->consulta->especialidad_id)
                ->where('activo', true)
                ->latest()
                ->first();
        }

        if ($plantilla) {
            foreach ($plantilla->secciones as $seccion) {
                $hayDatos = collect($seccion->campos)->contains(function ($campo) use ($datos) {
                    $v = $datos[$campo->nombre_campo] ?? null;
                    return $v !== null && $v !== '' && $v !== [];
                });

                if (!$hayDatos) continue;

                $this->seccion(strtoupper($seccion->nombre));

                foreach ($seccion->campos as $campo) {
                    $valor = $datos[$campo->nombre_campo] ?? null;
                    if ($valor === null || $valor === '' || $valor === []) continue;

                    $valorTexto = is_array($valor) ? implode(', ', $valor) : (string) $valor;
                    if ($campo->unidad) $valorTexto .= ' ' . $campo->unidad;

                    $this->pdf->SetFillColor(248, 249, 250);
                    $this->pdf->SetFont('Arial', 'B', 9);
                    $this->pdf->Cell(65, 6, utf8_decode($campo->etiqueta . ':'), 0, 0, 'L');
                    $this->pdf->SetFont('Arial', '', 9);
                    $this->pdf->MultiCell(105, 6, utf8_decode($valorTexto), 0);
                }
                $this->pdf->Ln(2);
            }
        } else {
            $this->seccion('EVALUACIÓN CLÍNICA');
            foreach ($datosDinamicos as $clave => $valor) {
                if ($valor === null || $valor === '' || $valor === []) continue;
                $valorTexto = is_array($valor) ? implode(', ', $valor) : (string) $valor;
                $this->pdf->SetFont('Arial', 'B', 9);
                $this->pdf->Cell(65, 6, utf8_decode(ucwords(str_replace('_', ' ', $clave)) . ':'), 0, 0);
                $this->pdf->SetFont('Arial', '', 9);
                $this->pdf->MultiCell(105, 6, utf8_decode($valorTexto), 0);
            }
            $this->pdf->Ln(2);
        }
    }

    // ── DIAGNÓSTICOS ─────────────────────────────────────────────────────────

    private function diagnosticos(): void
    {
        $diags = $this->consulta->getRelation('diagnosticos');
        if (!$diags || $diags->count() === 0) return;

        $this->seccion('DIAGNÓSTICOS CIE-10');

        foreach ($diags as $diag) {
            $esPrincipal = $diag->pivot->tipo === 'principal';

            if ($esPrincipal) {
                $this->pdf->SetFillColor(41, 128, 185);
                $this->pdf->SetTextColor(255, 255, 255);
                $this->pdf->SetFont('Arial', 'B', 8);
                $this->pdf->Cell(28, 7, utf8_decode('PRINCIPAL'), 1, 0, 'C', true);
            } else {
                $this->pdf->SetFillColor(245, 248, 252);
                $this->pdf->SetTextColor(80, 80, 80);
                $this->pdf->SetFont('Arial', '', 8);
                $this->pdf->Cell(28, 7, utf8_decode('Secundario'), 1, 0, 'C', true);
            }

            $this->pdf->SetFillColor(245, 248, 252);
            $this->pdf->SetTextColor(0, 0, 0);
            $this->pdf->SetFont('Arial', 'B', 9);
            $this->pdf->Cell(25, 7, utf8_decode($diag->codigo), 1, 0, 'C', true);
            $this->pdf->SetFont('Arial', '', 9);
            $this->pdf->Cell(117, 7, utf8_decode($diag->nombre), 1, 1, 'L');
        }

        $this->pdf->Ln(3);
    }

    // ── ESTUDIOS ──────────────────────────────────────────────────────────────

    private function estudios(): void
    {
        if ($this->consulta->estudios->count() === 0) return;

        $this->seccion('ESTUDIOS SOLICITADOS');

        foreach ($this->consulta->estudios as $i => $est) {
            $this->pdf->SetFillColor(245, 248, 252);
            $this->pdf->SetFont('Arial', 'B', 9);
            $this->pdf->Cell(8, 6, ($i + 1) . '.', 0, 0);
            $this->pdf->SetFont('Arial', 'B', 9);
            $this->pdf->Cell(30, 6, utf8_decode(ucfirst($est->tipo_estudio)), 1, 0, 'C', true);
            $this->pdf->SetFont('Arial', '', 9);
            $this->pdf->Cell(132, 6, utf8_decode($est->nombre_estudio), 1, 1);

            if ($est->indicaciones) {
                $this->pdf->SetX(28);
                $this->pdf->SetFont('Arial', 'I', 8);
                $this->pdf->SetTextColor(80, 80, 80);
                $this->pdf->MultiCell(142, 5, utf8_decode('Indicaciones: ' . $est->indicaciones), 0);
                $this->pdf->SetTextColor(0, 0, 0);
            }
        }

        $this->pdf->Ln(3);
    }

    // ── TRATAMIENTOS ──────────────────────────────────────────────────────────

    private function tratamientos(): void
    {
        if ($this->consulta->tratamientos->count() === 0) return;

        $this->seccion('TRATAMIENTO PRESCRITO');

        foreach ($this->consulta->tratamientos as $i => $trat) {
            $this->pdf->SetFillColor(245, 248, 252);
            $this->pdf->SetFont('Arial', 'B', 9);
            $this->pdf->Cell(8, 6, ($i + 1) . '.', 0, 0);
            $this->pdf->Cell(162, 6, utf8_decode($trat->medicamento), 1, 1, 'L', true);

            $this->pdf->SetX(28);
            $this->pdf->SetFont('Arial', '', 9);
            $this->pdf->SetTextColor(50, 50, 50);
            $this->pdf->MultiCell(152, 5, utf8_decode($trat->indicaciones), 0);
            $this->pdf->SetTextColor(0, 0, 0);
            $this->pdf->Ln(2);
        }

        $this->pdf->Ln(2);
    }

    // ── PÁGINAS SECUNDARIAS ───────────────────────────────────────────────────

    private function datosPacienteResumido(): void
    {
        $p = $this->consulta->paciente;
        $this->pdf->SetFillColor(245, 248, 252);
        $this->pdf->SetDrawColor(41, 128, 185);
        $this->pdf->SetLineWidth(0.3);
        $this->pdf->Rect(20, $this->pdf->GetY(), 170, 14, 'DF');
        $this->pdf->SetLineWidth(0.2);
        $this->pdf->SetDrawColor(0, 0, 0);

        $y = $this->pdf->GetY() + 2;
        $this->pdf->SetXY(22, $y);
        $this->pdf->SetFont('Arial', 'B', 9);
        $this->pdf->Cell(30, 5, utf8_decode('Paciente:'), 0, 0);
        $this->pdf->SetFont('Arial', '', 9);
        $this->pdf->Cell(80, 5, utf8_decode($p->nombre_completo), 0, 0);
        $this->pdf->SetFont('Arial', 'B', 9);
        $this->pdf->Cell(20, 5, utf8_decode('Documento:'), 0, 0);
        $this->pdf->SetFont('Arial', '', 9);
        $this->pdf->Cell(0, 5, utf8_decode($p->documento_identidad ?? 'N/A'), 0, 1);

        $this->pdf->SetX(22);
        $this->pdf->SetFont('Arial', 'B', 9);
        $this->pdf->Cell(30, 5, utf8_decode('Fecha:'), 0, 0);
        $this->pdf->SetFont('Arial', '', 9);
        $this->pdf->Cell(0, 5, $this->consulta->fecha_consulta->format('d/m/Y H:i'), 0, 1);

        $this->pdf->Ln(5);
    }

    private function estudiosDetallado(): void
    {
        foreach ($this->consulta->estudios as $i => $est) {
            $this->pdf->SetFillColor(41, 128, 185);
            $this->pdf->SetTextColor(255, 255, 255);
            $this->pdf->SetFont('Arial', 'B', 9);
            $this->pdf->Cell(0, 7, utf8_decode(($i + 1) . '. ' . strtoupper($est->tipo_estudio) . ': ' . $est->nombre_estudio), 1, 1, 'L', true);
            $this->pdf->SetTextColor(0, 0, 0);

            if ($est->indicaciones) {
                $this->pdf->SetFont('Arial', '', 9);
                $this->pdf->SetX(22);
                $this->pdf->MultiCell(0, 6, utf8_decode($est->indicaciones), 0);
            }
            $this->pdf->Ln(3);
        }
    }

    private function tratamientosDetallado(): void
    {
        foreach ($this->consulta->tratamientos as $i => $trat) {
            $this->pdf->SetFillColor(41, 128, 185);
            $this->pdf->SetTextColor(255, 255, 255);
            $this->pdf->SetFont('Arial', 'B', 10);
            $this->pdf->Cell(0, 8, utf8_decode(($i + 1) . '. ' . $trat->medicamento), 1, 1, 'L', true);
            $this->pdf->SetTextColor(0, 0, 0);

            $this->pdf->SetFont('Arial', '', 9);
            $this->pdf->SetX(22);
            $this->pdf->MultiCell(0, 6, utf8_decode($trat->indicaciones), 0);
            $this->pdf->Ln(4);
        }
    }

    // ── FIRMA ─────────────────────────────────────────────────────────────────

    private function firma(): void
    {
        $this->pdf->Ln(12);
        $xFirma = 72;
        $this->pdf->SetDrawColor(80, 80, 80);
        $this->pdf->SetLineWidth(0.4);
        $this->pdf->Line($xFirma, $this->pdf->GetY(), $xFirma + 66, $this->pdf->GetY());
        $this->pdf->SetLineWidth(0.2);
        $this->pdf->SetDrawColor(0, 0, 0);
        $this->pdf->Ln(2);

        $this->pdf->SetFont('Arial', 'B', 10);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->Cell(0, 6, utf8_decode('Dr(a). ' . $this->consulta->medico->nombre_completo), 0, 1, 'C');

        $especialidad = $this->consulta->especialidad->nombre
            ?? $this->consulta->medico->especialidades()->take(1)->pluck('nombre')->first()
            ?? '';

        $this->pdf->SetFont('Arial', '', 9);
        $this->pdf->SetTextColor(80, 80, 80);
        $this->pdf->Cell(0, 5, utf8_decode($especialidad), 0, 1, 'C');

        if (!empty($this->consulta->medico->numero_colegiatura)) {
            $this->pdf->Cell(0, 5, utf8_decode('Reg. Médico: ' . $this->consulta->medico->numero_colegiatura), 0, 1, 'C');
        }

        $this->pdf->SetTextColor(0, 0, 0);
    }

    // ── PIE DE PÁGINA ─────────────────────────────────────────────────────────

    private function piePagina(): void
    {
        $this->pdf->SetY(-18);
        $this->pdf->SetDrawColor(200, 200, 200);
        $this->pdf->SetLineWidth(0.3);
        $this->pdf->Line(20, $this->pdf->GetY(), 190, $this->pdf->GetY());
        $this->pdf->Ln(2);
        $this->pdf->SetFont('Arial', 'I', 7);
        $this->pdf->SetTextColor(150, 150, 150);
        $this->pdf->Cell(0, 4,
            utf8_decode('Documento confidencial — Uso exclusivo médico — ' .
            ($this->empresa->razon_social ?? $this->empresa->nombre ?? '') .
            ' — ' . now()->format('d/m/Y H:i')),
            0, 0, 'C'
        );
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────

    private function seccion(string $titulo): void
    {
        $this->pdf->SetFillColor(41, 128, 185);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->SetFont('Arial', 'B', 9);
        $this->pdf->Cell(0, 7, utf8_decode(' ' . $titulo), 0, 1, 'L', true);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->Ln(2);
    }

    private function fila2col(string $label1, string $val1, string $label2, string $val2): void
    {
        $this->pdf->SetFillColor(245, 248, 252);
        $this->pdf->SetFont('Arial', 'B', 9);
        $this->pdf->Cell(30, 6, utf8_decode($label1 . ':'), 1, 0, 'L', true);
        $this->pdf->SetFont('Arial', '', 9);
        $this->pdf->Cell(55, 6, utf8_decode($val1), 1, 0);
        $this->pdf->SetFont('Arial', 'B', 9);
        $this->pdf->Cell(30, 6, utf8_decode($label2 . ':'), 1, 0, 'L', true);
        $this->pdf->SetFont('Arial', '', 9);
        $this->pdf->Cell(55, 6, utf8_decode($val2), 1, 1);
    }

    private function fila1col(string $label, string $val): void
    {
        $this->pdf->SetFillColor(245, 248, 252);
        $this->pdf->SetFont('Arial', 'B', 9);
        $this->pdf->Cell(30, 6, utf8_decode($label . ':'), 1, 0, 'L', true);
        $this->pdf->SetFont('Arial', '', 9);
        $this->pdf->Cell(140, 6, utf8_decode($val), 1, 1);
    }

    private function clasificacionIMC($imc): string
    {
        if (!$imc) return 'N/A';
        if ($imc < 18.5) return 'Bajo peso';
        if ($imc < 25)   return 'Normal';
        if ($imc < 30)   return 'Sobrepeso';
        if ($imc < 35)   return 'Obesidad I';
        if ($imc < 40)   return 'Obesidad II';
        return 'Obesidad III';
    }
}
