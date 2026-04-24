<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consulta;
use Codedge\Fpdf\Fpdf\Fpdf;

class InformeMedicoController extends Controller
{
    private $fpdf;
    private $consulta;
    private $empresa;

    public function generar($id)
    {
        $this->consulta = Consulta::with([
            'paciente',
            'medico',
            'signosVitales',
            'evaluacion',
            'diagnosticos',
            'tratamientos',
            'estudios',
            'cita'
        ])->findOrFail($id);

        $this->empresa = auth()->user()->empresa;
        $this->fpdf = new Fpdf('P', 'mm', 'A4');
        $this->fpdf->SetAutoPageBreak(true, 25);

        // Informe completo
        $this->fpdf->AddPage();
        $this->encabezado('INFORME MÉDICO');
        $this->datosPaciente();
        $this->datosConsulta();
        $this->signosVitales();
        $this->evaluacion();
        $this->diagnosticos();
        $this->estudios();
        $this->tratamientos();
        //$this->firma();

        // Orden de estudios (página separada)
        if ($this->consulta->estudios && $this->consulta->estudios->count() > 0) {
            $this->fpdf->AddPage();
            $this->encabezado('ORDEN DE ESTUDIOS');
            $this->datosPacienteResumido();
            $this->estudiosDetallado();
            //$this->firmaSimple();
        }

        // Recipe médico (página separada)
        if ($this->consulta->tratamientos && $this->consulta->tratamientos->count() > 0) {
            $this->fpdf->AddPage();
            $this->encabezado('RECIPE MÉDICO');
            $this->datosPacienteResumido();
            $this->tratamientosDetallado();
            //$this->firmaSimple();
        }

        return response($this->fpdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Informe_Medico_' . $this->consulta->id . '.pdf"'
        ]);
    }

    private function encabezado($titulo = 'INFORME MÉDICO')
    {
        $logoPath = public_path('logo/app.png');

        if (file_exists($logoPath)) {
            $this->fpdf->Image($logoPath, 12, 5, 50, 30);
        }

        $this->fpdf->SetFont('Arial', 'B', 16);
        $this->fpdf->SetTextColor(0, 0, 0);
        $this->fpdf->Cell(0, 8, utf8_decode($titulo), 0, 1, 'C');

        $this->fpdf->SetFont('Arial', 'B', 11);
        $this->fpdf->SetTextColor(0, 0, 0);
        $this->fpdf->Cell(0, 6, utf8_decode($this->empresa->nombre), 0, 1, 'C');

        $this->fpdf->SetFont('Arial', '', 9);
        $this->fpdf->SetTextColor(100, 100, 100);
        $this->fpdf->Cell(0, 5, utf8_decode('RIF: ' . $this->empresa->rif), 0, 1, 'C');

        if ($this->empresa->telefono) {
            $this->fpdf->Cell(0, 5, utf8_decode('Tel: ' . $this->empresa->telefono), 0, 1, 'C');
        }

        if ($this->empresa->direccion) {
            $this->fpdf->SetFont('Arial', '', 8);
            $this->fpdf->MultiCell(0, 4, utf8_decode($this->empresa->direccion), 0, 'C');
        }

        $this->fpdf->SetTextColor(0, 0, 0);
        $this->fpdf->Ln(3);

        // Número de informe y fecha
        $this->fpdf->SetFont('Arial', '', 8);
        $this->fpdf->SetTextColor(100, 100, 100);
        $this->fpdf->Cell(95, 4, utf8_decode('Informe N°: INF - ' . str_pad($this->consulta->codigo, 6, '0', STR_PAD_LEFT)), 0, 0, 'L');
        $this->fpdf->Cell(95, 4, utf8_decode('Generado: ' . now()->format('d/m/Y H:i')), 0, 1, 'R');
        $this->fpdf->SetTextColor(0, 0, 0);

        $this->fpdf->Ln(2);
        $this->fpdf->SetDrawColor(41, 128, 185);
        $this->fpdf->SetLineWidth(0.5);
        $this->fpdf->Line(15, $this->fpdf->GetY(), 195, $this->fpdf->GetY());
        $this->fpdf->SetDrawColor(0, 0, 0);
        $this->fpdf->SetLineWidth(0.2);
        $this->fpdf->Ln(5);
    }

    private function datosPaciente()
    {
        $this->seccionTitulo('DATOS DEL PACIENTE');

        $paciente = $this->consulta->paciente;
        $this->fpdf->SetFont('Arial', '', 9);

        $this->campoValor('Nombre Completo:', $paciente->nombre_completo);
        $this->campoValor('Documento:',  $paciente->documento_identidad);

        // Calcular edad con años y meses
        $fechaNac = \Carbon\Carbon::parse($paciente->fecha_nacimiento);
        $hoy = \Carbon\Carbon::now();
        $años = (int) $fechaNac->diffInYears($hoy);
        $meses = (int) $fechaNac->copy()->addYears($años)->diffInMonths($hoy);
        $edadTexto = $años . ' años' . ($meses > 0 ? ' y ' . $meses . ' meses' : '');

        $this->campoValor('Fecha de Nacimiento:', $fechaNac->format('d/m/Y') . ' (' . $edadTexto . ')');
        $this->campoValor('Sexo:', ucfirst($paciente->genero ?? 'No especificado'));

        if ($paciente->telefono) {
            $this->campoValor('Teléfono:', $paciente->telefono);
        }

        // Alergias (crítico)
        if ($paciente->alergias) {
            $this->fpdf->SetFillColor(234, 231, 230);
            $this->fpdf->SetFont('Arial', 'B', 9);
            $this->fpdf->Cell(60, 6, utf8_decode('⚠ ALERGIAS:'), 1, 0, 'L', true);
            $this->fpdf->SetFont('Arial', 'B', 9);
            $this->fpdf->SetTextColor(204, 0, 0);
            $this->fpdf->Cell(130, 6, utf8_decode(strtoupper($paciente->alergias)), 1, 1, 'L', true);
            $this->fpdf->SetTextColor(0, 0, 0);
        }

        // Antecedentes médicos
        if ($paciente->antecedentes_medicos) {
            $this->fpdf->SetFont('Arial', 'B', 9);
            $this->fpdf->Cell(60, 6, utf8_decode('Antecedentes Médicos:'), 1, 0);
            $this->fpdf->SetFont('Arial', '', 9);
            $this->fpdf->MultiCell(130, 6, utf8_decode($paciente->antecedentes_medicos), 1);
        }

        $this->fpdf->Ln(3);
    }

    private function datosConsulta()
    {
        $this->seccionTitulo('DATOS DE LA CONSULTA');

        $this->fpdf->SetFont('Arial', '', 9);
        $this->campoValor('Fecha y Hora:', $this->consulta->created_at->format('d/m/Y H:i'));
        $this->campoValor('Médico Tratante:', 'Dr(a). ' . $this->consulta->medico->nombre_completo);


        $this->campoValor('Especialidad:', $this->consulta->medico->especialidades()->take(1)->pluck('nombre')->first() ?? 'No especificada');



            $this->fpdf->SetFont('Arial', 'B', 9);
            $this->fpdf->Cell(60, 6, utf8_decode('Motivo de Consulta:'), 1, 0);
            $this->fpdf->SetFont('Arial', '', 9);
            $this->fpdf->MultiCell(130, 6, utf8_decode($this->consulta->motivo_consulta), 1);


        $this->fpdf->Ln(3);
    }

    private function signosVitales()
    {
        $sv = $this->consulta->signosVitales()->latest()->first();
        if (!$sv) return;

        $this->seccionTitulo('SIGNOS VITALES');

        $this->fpdf->SetFont('Arial', '', 9);

        // Presión Arterial
        $pas = $sv->presion_arterial_sistolica ?? null;
        $pad = $sv->presion_arterial_diastolica ?? null;
        $paValor = $pas && $pad ? "$pas/$pad" : '--';

        $this->fpdf->Cell(45, 6, utf8_decode('P.A. (mmHg):'), 1, 0);
        $this->fpdf->Cell(40, 6, $paValor, 1, 0);

        // Frecuencia Cardíaca
        $fc = $sv->frecuencia_cardiaca ?? null;
        $this->fpdf->Cell(45, 6, utf8_decode('F.C. (lpm):'), 1, 0);
        $this->fpdf->Cell(60, 6, $fc ?? '--', 1, 1);

        // Temperatura
        $temp = $sv->temperatura ?? null;
        $this->fpdf->Cell(45, 6, utf8_decode('Temperatura (°C):'), 1, 0);
        $this->fpdf->Cell(40, 6, $temp ?? '--', 1, 0);

        // Frecuencia Respiratoria
        $fr = $sv->frecuencia_respiratoria ?? null;
        $this->fpdf->Cell(45, 6, utf8_decode('F.R. (rpm):'), 1, 0);
        $this->fpdf->Cell(60, 6, $fr ?? '--', 1, 1);

        // Peso y Talla
        $peso = $sv->peso ?? null;
        $talla = $sv->talla ?? null;
        $this->fpdf->Cell(45, 6, utf8_decode('Peso (kg):'), 1, 0);
        $this->fpdf->Cell(40, 6, $peso ?? '--', 1, 0);
        $this->fpdf->Cell(45, 6, utf8_decode('Talla (cm):'), 1, 0);
        $this->fpdf->Cell(60, 6, $talla ?? '--', 1, 1);

        // IMC
        $imc = $sv->imc ?? null;
        $imcClasif = $this->clasificacionIMC($imc);
        $this->fpdf->Cell(45, 6, utf8_decode('IMC:'), 1, 0);
        $this->fpdf->Cell(40, 6, $imc ?? '--', 1, 0);
        $this->fpdf->Cell(45, 6, utf8_decode('Clasificación:'), 1, 0);
        $this->fpdf->Cell(60, 6, utf8_decode($imcClasif), 1, 1);

        if ($sv->saturacion_oxigeno) {
            $this->fpdf->Cell(45, 6, utf8_decode('Sat. O₂ (%):'), 1, 0);
            $this->fpdf->Cell(145, 6, $sv->saturacion_oxigeno, 1, 1);
        }

        if ($sv->observaciones) {
            $this->fpdf->SetFont('Arial', 'I', 8);
            $this->fpdf->MultiCell(0, 5, utf8_decode('Observaciones: ' . $sv->observaciones), 1);
        }

        $this->fpdf->Ln(3);
    }

    private function evaluacion()
    {
        if (!$this->consulta->evaluacion) return;

        $eval = $this->consulta->evaluacion;

        if ($eval->enfermedad_actual) {
            $this->seccionTitulo('ENFERMEDAD ACTUAL');
            $this->fpdf->SetFont('Arial', '', 9);
            $this->fpdf->MultiCell(0, 5, utf8_decode($eval->enfermedad_actual), 1);
            $this->fpdf->Ln(2);
        }

        if ($eval->examen_fisico) {
            $this->seccionTitulo('EXAMEN FÍSICO');
            $this->fpdf->SetFont('Arial', '', 9);
            $this->fpdf->MultiCell(0, 5, utf8_decode($eval->examen_fisico), 1);
            $this->fpdf->Ln(2);
        }

        if ($eval->conclusion) {
            $this->seccionTitulo('CONCLUSIÓN MÉDICA');
            $this->fpdf->SetFont('Arial', '', 9);
            $this->fpdf->MultiCell(0, 5, utf8_decode($eval->conclusion), 1);
            $this->fpdf->Ln(2);
        }
    }

    private function diagnosticos()
    {
        if (!$this->consulta->diagnosticos || $this->consulta->diagnosticos->count() == 0) return;

        $this->seccionTitulo('DIAGNÓSTICOS CIE-10');

        $this->fpdf->SetFont('Arial', '', 9);

        foreach ($this->consulta->diagnosticos as $diag) {
            $esPrincipal = $diag->pivot->tipo == 'principal';

            if ($esPrincipal) {
                $this->fpdf->SetFillColor(234, 231, 230);
                $this->fpdf->SetFont('Arial', 'B', 9);
                $this->fpdf->Cell(35, 6, utf8_decode('★ PRINCIPAL'), 1, 0, 'C', true);
            } else {
                $this->fpdf->SetFillColor(234, 231, 230);
                $this->fpdf->SetFont('Arial', '', 9);
                $this->fpdf->Cell(35, 6, utf8_decode('Secundario'), 1, 0, 'C', true);
            }

            $this->fpdf->SetFont('Arial', 'B', 9);
            $this->fpdf->Cell(30, 6, utf8_decode($diag->codigo), 1, 0, 'C');
            $this->fpdf->SetFont('Arial', '', 9);
            $this->fpdf->Cell(115, 6, utf8_decode($diag->nombre), 1, 1);
        }

        $this->fpdf->Ln(3);
    }

    private function estudios()
    {
        if (!$this->consulta->estudios || $this->consulta->estudios->count() == 0) return;

        $this->seccionTitulo('ESTUDIOS SOLICITADOS');

        $this->fpdf->SetFont('Arial', '', 9);

        foreach ($this->consulta->estudios as $index => $estudio) {
            $this->fpdf->SetFont('Arial', 'B', 9);
            $this->fpdf->Cell(10, 6, ($index + 1) . '.', 1, 0);
            $this->fpdf->Cell(40, 6, utf8_decode(ucfirst($estudio->tipo_estudio)), 1, 0);
            $this->fpdf->SetFont('Arial', '', 9);
            $this->fpdf->Cell(140, 6, utf8_decode($estudio->nombre_estudio), 1, 1);

            if ($estudio->indicaciones) {
                $this->fpdf->Cell(10, 5, '', 0, 0);
                $this->fpdf->SetFont('Arial', 'I', 8);
                $this->fpdf->MultiCell(180, 5, utf8_decode('Indicaciones: ' . $estudio->indicaciones), 1);
            }
        }

        $this->fpdf->Ln(3);
    }

    private function tratamientos()
    {
        if (!$this->consulta->tratamientos || $this->consulta->tratamientos->count() == 0) return;

        $this->seccionTitulo('TRATAMIENTO PRESCRITO');

        foreach ($this->consulta->tratamientos as $index => $trat) {
            $this->fpdf->SetFont('Arial', 'B', 9);
            $this->fpdf->Cell(10, 6, ($index + 1) . '.', 1, 0);
            $this->fpdf->Cell(180, 6, utf8_decode($trat->medicamento), 1, 1);

            $this->fpdf->Cell(10, 5, '', 0, 0);
            $this->fpdf->SetFont('Arial', '', 9);
            $this->fpdf->MultiCell(180, 5, utf8_decode($trat->indicaciones), 1);
        }

        $this->fpdf->Ln(3);
    }

    private function firma()
    {
        $this->fpdf->Ln(10);
        $this->fpdf->SetFont('Arial', 'I', 8);
        $this->fpdf->SetTextColor(150, 150, 150);
        $this->fpdf->Cell(0, 5, utf8_decode('Documento confidencial - Uso exclusivo médico'), 0, 1, 'C');
        $this->fpdf->SetTextColor(0, 0, 0);

        $this->fpdf->Ln(5);
        $this->fpdf->SetFont('Arial', '', 9);
        $this->fpdf->Cell(0, 6, utf8_decode('_________________________________'), 0, 1, 'C');
        $this->fpdf->SetFont('Arial', 'B', 10);
        $this->fpdf->Cell(0, 5, utf8_decode('Dr(a). ' . $this->consulta->medico->nombre_completo), 0, 1, 'C');
        $this->fpdf->SetFont('Arial', '', 9);
        $this->fpdf->Cell(0, 5, utf8_decode('Reg. Médico: ' . ($this->consulta->medico->numero_colegiatura ?? 'N/A')), 0, 1, 'C');
    }

    private function datosPacienteResumido()
    {
        $paciente = $this->consulta->paciente;
        $this->fpdf->SetFont('Arial', 'B', 9);
        $this->fpdf->Cell(40, 6, utf8_decode('Paciente:'), 0, 0);
        $this->fpdf->SetFont('Arial', '', 9);
        $this->fpdf->Cell(0, 6, utf8_decode($paciente->nombre_completo), 0, 1);

        $this->fpdf->SetFont('Arial', 'B', 9);
        $this->fpdf->Cell(40, 6, utf8_decode('Documento:'), 0, 0);
        $this->fpdf->SetFont('Arial', '', 9);
        $this->fpdf->Cell(60, 6, utf8_decode($paciente->documento_identidad), 0, 0);

        $this->fpdf->SetFont('Arial', 'B', 9);
        $this->fpdf->Cell(30, 6, utf8_decode('Fecha:'), 0, 0);
        $this->fpdf->SetFont('Arial', '', 9);
        $this->fpdf->Cell(0, 6, $this->consulta->created_at->format('d/m/Y'), 0, 1);

        $this->fpdf->Ln(3);
    }

    private function estudiosDetallado()
    {
        $this->fpdf->SetFont('Arial', 'B', 11);
        $this->fpdf->Cell(0, 8, utf8_decode('ESTUDIOS SOLICITADOS'), 0, 1, 'L');
        $this->fpdf->Ln(2);

        foreach ($this->consulta->estudios as $index => $estudio) {
            $this->fpdf->SetFont('Arial', 'B', 10);
            $this->fpdf->Cell(0, 7, utf8_decode(($index + 1) . '. ' . strtoupper($estudio->tipo_estudio)), 0, 1);

            $this->fpdf->SetFont('Arial', '', 10);
            $this->fpdf->MultiCell(0, 6, utf8_decode($estudio->nombre_estudio), 0);

            if ($estudio->indicaciones) {
                $this->fpdf->SetFont('Arial', 'I', 9);
                $this->fpdf->MultiCell(0, 5, utf8_decode('Indicaciones: ' . $estudio->indicaciones), 0);
            }

            $this->fpdf->Ln(3);
        }
    }

    private function tratamientosDetallado()
    {
        $this->fpdf->SetFont('Arial', 'B', 11);
        $this->fpdf->Cell(0, 8, utf8_decode('TRATAMIENTO PRESCRITO'), 0, 1, 'L');
        $this->fpdf->Ln(2);

        foreach ($this->consulta->tratamientos as $index => $trat) {
            $this->fpdf->SetFont('Arial', 'B', 10);
            $this->fpdf->Cell(10, 7, ($index + 1) . '.', 0, 0);
            $this->fpdf->Cell(0, 7, utf8_decode($trat->medicamento), 0, 1);

            $this->fpdf->SetFont('Arial', '', 10);
            $this->fpdf->SetX(25);
            $this->fpdf->MultiCell(0, 6, utf8_decode($trat->indicaciones), 0);

            $this->fpdf->Ln(3);
        }
    }

    private function firmaSimple()
    {
        $this->fpdf->Ln(15);
        $this->fpdf->SetFont('Arial', '', 9);
        $this->fpdf->Cell(0, 6, utf8_decode('_________________________________'), 0, 1, 'C');
        $this->fpdf->SetFont('Arial', 'B', 10);
        $this->fpdf->Cell(0, 5, utf8_decode('Dr(a). ' . $this->consulta->medico->nombre_completo), 0, 1, 'C');
        $this->fpdf->SetFont('Arial', '', 9);
        $this->fpdf->Cell(0, 5, utf8_decode('Reg. Médico: ' . ($this->consulta->medico->numero_colegiatura ?? 'N/A')), 0, 1, 'C');
    }

    // Métodos auxiliares
    private function seccionTitulo($titulo)
    {
        $this->fpdf->SetFillColor(234, 231, 230);
        $this->fpdf->SetTextColor(0, 0, 0);
        $this->fpdf->SetFont('Arial', 'B', 10);
        $this->fpdf->Cell(0, 7, utf8_decode($titulo), 1, 1, 'C', true);
        $this->fpdf->SetTextColor(0, 0, 0);
    }

    private function campoValor($campo, $valor)
    {
        $this->fpdf->SetFont('Arial', 'B', 9);
        $this->fpdf->Cell(60, 6, utf8_decode($campo), 1, 0);
        $this->fpdf->SetFont('Arial', '', 9);
        $this->fpdf->Cell(130, 6, utf8_decode($valor), 1, 1);
    }



    private function clasificacionIMC($imc)
    {
        if (!$imc) return 'No calculado';
        if ($imc < 18.5) return 'Bajo peso';
        if ($imc < 25) return 'Normal';
        if ($imc < 30) return 'Sobrepeso';
        if ($imc < 35) return 'Obesidad I';
        if ($imc < 40) return 'Obesidad II';
        return 'Obesidad III';
    }
}
