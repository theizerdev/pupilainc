<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Consulta;
use Codedge\Fpdf\Fpdf\Fpdf;

class JustificativoController extends Controller
{
    public function generar($id)
    {
        $consulta = Consulta::with(['paciente', 'medico', 'especialidad', 'diagnosticos'])->findOrFail($id);
        $empresa = auth()->user()->empresa;
        
        // Obtener ciudad usando Nominatim con las coordenadas de la empresa
        $ciudad = 'Ciudad';
        
        // Primero intentar con capital del país
        if ($empresa->pais && $empresa->pais->capital) {
            $ciudad = $empresa->pais->capital;
        }
        
        // Si tiene coordenadas, intentar obtener ciudad específica con Nominatim
        if ($empresa->latitud && $empresa->longitud) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(5)
                    ->withHeaders(['User-Agent' => 'MedicalApp/1.0'])
                    ->get('https://nominatim.openstreetmap.org/reverse', [
                        'format' => 'json',
                        'lat' => $empresa->latitud,
                        'lon' => $empresa->longitud,
                        'zoom' => 12,
                        'addressdetails' => 1
                    ]);
                
                if ($response->successful()) {
                    $data = $response->json();
                    // Si encuentra ciudad específica, usarla
                    $ciudadNominatim = $data['address']['city'] ?? 
                                      $data['address']['town'] ?? 
                                      $data['address']['municipality'] ?? 
                                      $data['address']['village'] ?? 
                                      $data['address']['county'] ?? 
                                      null;
                    
                    if ($ciudadNominatim) {
                        $ciudad = $ciudadNominatim;
                    }
                }
            } catch (\Exception $e) {
                // Mantener ciudad del país si falla
            }
        }
        
        $fpdf = new Fpdf('P', 'mm', 'A4');
        $fpdf->AddPage();
        $fpdf->SetMargins(20, 20, 20);

        // Logo
        $logoPath = public_path('logo/app.png');
        if (file_exists($logoPath)) {
            $fpdf->Image($logoPath, 20, 15, 40);
        }

        // Encabezado empresa
        $fpdf->SetFont('Arial', 'B', 12);
        $fpdf->SetXY(70, 15);
        $fpdf->Cell(0, 6, utf8_decode($empresa->nombre), 0, 1);
        $fpdf->SetFont('Arial', '', 9);
        $fpdf->SetX(70);
        $fpdf->Cell(0, 5, utf8_decode('RIF: ' . $empresa->rif), 0, 1);
        if ($empresa->telefono) {
            $fpdf->SetX(70);
            $fpdf->Cell(0, 5, utf8_decode('Tel: ' . $empresa->telefono), 0, 1);
        }
        if ($empresa->direccion) {
            $fpdf->SetX(70);
            $fpdf->SetFont('Arial', '', 8);
            $fpdf->MultiCell(120, 4, utf8_decode($empresa->direccion), 0);
        }
        
        $fpdf->Ln(5);
        $fpdf->SetDrawColor(41, 128, 185);
        $fpdf->SetLineWidth(0.5);
        $fpdf->Line(20, $fpdf->GetY(), 190, $fpdf->GetY());
        $fpdf->SetDrawColor(0, 0, 0);
        $fpdf->SetLineWidth(0.2);
        $fpdf->Ln(10);

        // Título
        $fpdf->SetFont('Arial', 'B', 16);
        $fpdf->SetTextColor(41, 128, 185);
        $fpdf->Cell(0, 10, utf8_decode('CONSTANCIA DE ASISTENCIA MÉDICA'), 0, 1, 'C');
        $fpdf->SetTextColor(0, 0, 0);
        $fpdf->Ln(5);

        // Número de constancia
        $fpdf->SetFont('Arial', '', 9);
        $fpdf->SetTextColor(100, 100, 100);
        $fpdf->Cell(0, 5, utf8_decode('Constancia N°: CONST-' . str_pad($consulta->codigo, 6, '0', STR_PAD_LEFT)), 0, 1, 'R');
        $fpdf->SetTextColor(0, 0, 0);
        $fpdf->Ln(5);

        // Contenido
        $fpdf->SetFont('Arial', '', 11);
        $fpdf->MultiCell(0, 6, utf8_decode('Por medio de la presente, se hace constar que:'), 0, 'J');
        $fpdf->Ln(5);

        // Datos del paciente en recuadro
        $fpdf->SetFillColor(234, 231, 230);
        $fpdf->SetFont('Arial', 'B', 10);
        $fpdf->Cell(0, 7, utf8_decode('DATOS DEL PACIENTE'), 1, 1, 'C', true);
        
        $fpdf->SetFont('Arial', 'B', 11);
        $fpdf->Cell(50, 7, utf8_decode('Nombre:'), 1, 0);
        $fpdf->SetFont('Arial', '', 11);
        $fpdf->Cell(120, 7, utf8_decode($consulta->paciente->nombre_completo), 1, 1);
        
        $fpdf->SetFont('Arial', 'B', 11);
        $fpdf->Cell(50, 7, utf8_decode('Documento:'), 1, 0);
        $fpdf->SetFont('Arial', '', 11);
        $fpdf->Cell(120, 7, utf8_decode($consulta->paciente->documento_identidad), 1, 1);
        
        $fpdf->Ln(5);

        // Texto de asistencia
        $fpdf->SetFont('Arial', '', 11);
        $texto = 'Acudió a consulta médica de ' . ($consulta->especialidad->nombre ?? 'Medicina General') . 
                 ' el día ' . $consulta->created_at->format('d/m/Y') . 
                 ' a las ' . $consulta->created_at->format('H:i') . ' horas, ' .
                 'siendo atendido(a) por el Dr(a). ' . $consulta->medico->nombre_completo . '.';
        $fpdf->MultiCell(0, 6, utf8_decode($texto), 0, 'J');
        
        // Diagnóstico si existe
        if ($consulta->diagnosticos && $consulta->diagnosticos->count() > 0) {
            $fpdf->Ln(3);
            $diagnosticoPrincipal = $consulta->diagnosticos->where('pivot.tipo', 'principal')->first();
            if ($diagnosticoPrincipal) {
                $fpdf->SetFont('Arial', 'B', 11);
                $fpdf->Cell(0, 6, utf8_decode('Diagnóstico: '), 0, 0);
                $fpdf->SetFont('Arial', '', 11);
                $fpdf->MultiCell(0, 6, utf8_decode($diagnosticoPrincipal->nombre), 0, 'J');
            }
        }
        
        $fpdf->Ln(5);
        $fpdf->SetFont('Arial', 'I', 10);
        $fpdf->MultiCell(0, 6, utf8_decode('Constancia que se expide a petición del interesado para los fines que estime conveniente.'), 0, 'J');

        // Fecha y lugar
        $fpdf->Ln(10);
        $fpdf->SetFont('Arial', '', 10);
        $fpdf->Cell(0, 6, utf8_decode($ciudad . ', ' . now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY')), 0, 1, 'R');

        // Firma
        $fpdf->Ln(20);
        $fpdf->SetFont('Arial', '', 10);
        $fpdf->Cell(0, 6, utf8_decode('_________________________________'), 0, 1, 'C');
        $fpdf->SetFont('Arial', 'B', 11);
        $fpdf->Cell(0, 6, utf8_decode('Dr(a). ' . $consulta->medico->nombre_completo), 0, 1, 'C');
        $fpdf->SetFont('Arial', '', 10);
        $fpdf->Cell(0, 5, utf8_decode('Reg. Médico: ' . ($consulta->medico->numero_colegiatura ?? 'N/A')), 0, 1, 'C');
        if ($consulta->especialidad) {
            $fpdf->SetFont('Arial', 'I', 9);
            $fpdf->Cell(0, 5, utf8_decode($consulta->especialidad->nombre), 0, 1, 'C');
        }
        
        // Pie de página
        $fpdf->SetY(-20);
        $fpdf->SetFont('Arial', 'I', 8);
        $fpdf->SetTextColor(150, 150, 150);
        $fpdf->Cell(0, 5, utf8_decode('Documento médico oficial - Verificable'), 0, 1, 'C');

        return response($fpdf->Output('S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Justificativo_' . $consulta->codigo . '.pdf"'
        ]);
    }
}
