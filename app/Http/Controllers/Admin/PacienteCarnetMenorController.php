<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Paciente;
use App\Services\WhatsAppService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class PacienteCarnetMenorController extends Controller
{
    public function show(Paciente $paciente)
    {
        Gate::authorize('access pacientes');

        $data = $this->buildViewData($paciente, false);

        return view('admin.pacientes.carnet-menor', $data);
    }

    public function png(Paciente $paciente)
    {
        Gate::authorize('access pacientes');

        $data = $this->buildViewData($paciente, true);

        if (empty($data['esMenor'])) {
            abort(404);
        }

        $png = $this->renderPng($data);
        $filename = 'carnet_menor_'.$paciente->id.'.png';

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        ]);
    }

    public function pdf(Paciente $paciente)
    {
        Gate::authorize('access pacientes');

        $data = $this->buildViewData($paciente, true);

        $html = view('admin.pacientes.carnet-menor-pdf', $data)->render();

        $options = new Options;
        $options->setIsRemoteEnabled(true);
        $options->setDefaultFont('DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');

        $width = $this->mmToPt(85);
        $height = $this->mmToPt(55);
        $dompdf->setPaper([0, 0, $width, $height]);
        $dompdf->render();

        $filename = 'carnet_menor_'.$paciente->id.'.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }

    public function sendWhatsApp(Request $request, Paciente $paciente)
    {
        Gate::authorize('edit pacientes');

        $data = $this->buildViewData($paciente, true);

        if (empty($data['esMenor'])) {
            return back()->with('error', 'El carnet aplica solo para pacientes menores de edad.');
        }

        $tutorTelefono = $paciente->tutor->telefono ?? null;
        if (! $tutorTelefono) {
            return back()->with('error', 'El tutor no tiene teléfono registrado.');
        }

        $to = $this->formatWhatsAppJid($tutorTelefono, $data['empresaCodigoPais']);

        $dir = storage_path('app/tmp/carnets');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $safeName = Str::slug(trim($paciente->nombres.' '.$paciente->apellidos));
        $baseName = 'carnet_menor_'.$paciente->id.'_'.($safeName ?: 'paciente');
        $pngPath = $dir.DIRECTORY_SEPARATOR.$baseName.'.png';

        file_put_contents($pngPath, $this->renderPng($data));

        try {
            $service = WhatsAppService::forCompany($paciente->empresa_id);
            $caption = 'Carnet de paciente menor: '.trim($paciente->nombres.' '.$paciente->apellidos);

            $result = $service->sendImage($to, $pngPath, $caption);

            if (! $result) {
                $pdfPath = $dir.DIRECTORY_SEPARATOR.$baseName.'.pdf';
                file_put_contents($pdfPath, $this->renderPdfBytes($data));
                try {
                    $result = $service->sendDocument($to, $pdfPath, $caption);
                } finally {
                    if (is_file($pdfPath)) {
                        @unlink($pdfPath);
                    }
                }
            }

            if (! $result) {
                return back()->with('error', 'No se pudo enviar el carnet por WhatsApp.');
            }

            return back()->with('success', 'Carnet enviado por WhatsApp al tutor.');
        } finally {
            if (is_file($pngPath)) {
                @unlink($pngPath);
            }
        }
    }

    private function buildViewData(Paciente $paciente, bool $pdfMode): array
    {
        $paciente->loadMissing(['tutor', 'empresa.pais', 'sucursal']);

        if (! auth()->check()) {
            abort(403);
        }

        if (! auth()->user()->hasRole('Super Administrador')) {
            if (auth()->user()->empresa_id && $paciente->empresa_id !== auth()->user()->empresa_id) {
                abort(403);
            }
            if (auth()->user()->sucursal_id && $paciente->sucursal_id !== auth()->user()->sucursal_id) {
                abort(403);
            }
        }

        $edadFormateada = $paciente->edad_formateada;
        $esMenor = $paciente->es_menor;

        $empresa = $paciente->empresa ?? auth()->user()->empresa;
        $sucursal = $paciente->sucursal ?? auth()->user()->sucursal;

        $empresaNombre = $empresa->razon_social ?? config('app.name', 'Medical System');
        $sucursalNombre = $sucursal->nombre ?? null;

        $codigoPais = '58';
        if ($empresa && $empresa->pais && $empresa->pais->codigo_telefonico) {
            $codigoPais = ltrim((string) $empresa->pais->codigo_telefonico, '+');
        }

        $logoUrl = $this->fileToDataUri(public_path('logo/logo.png'), 'image/png') ?? url('/logo/logo.png');

        $fotoDataUri = null;
        if (! empty($paciente->foto) && ! filter_var($paciente->foto, FILTER_VALIDATE_URL)) {
            $fotoDataUri = $this->fileToDataUri(storage_path('app/public/'.$paciente->foto), 'image/jpeg');
        }

        $contactTel = $sucursal->telefono ?? $empresa->telefono ?? null;
        $contactEmail = $empresa->email ?? null;
        $contactDireccion = $sucursal->direccion ?? $empresa->direccion ?? null;

        $qrText = trim(implode("\n", array_filter([
            config('app.name', 'Medical System'),
            'Empresa: '.$empresaNombre,
            $sucursalNombre ? ('Sucursal: '.$sucursalNombre) : null,
            $contactTel ? ('Tel: '.$contactTel) : null,
            $contactEmail ? ('Email: '.$contactEmail) : null,
            $contactDireccion ? ('Dirección: '.$contactDireccion) : null,
            'Paciente: '.trim($paciente->nombres.' '.$paciente->apellidos),
            'ID: '.$paciente->id,
            $paciente->documento_identidad ? ('Documento: '.$paciente->documento_identidad) : null,
            'En caso de extravío, por favor contactar a la clínica.',
            'URL: '.config('app.url'),
        ])));

        $qr = new QrCode(data: $qrText, size: 220, margin: 0);
        $qrDataUri = (new PngWriter)->write($qr)->getDataUri();

        return [
            'paciente' => $paciente,
            'empresaNombre' => $empresaNombre,
            'sucursalNombre' => $sucursalNombre,
            'logoUrl' => $logoUrl,
            'qrDataUri' => $qrDataUri,
            'contactTel' => $contactTel,
            'contactEmail' => $contactEmail,
            'pdfMode' => $pdfMode,
            'esMenor' => $esMenor,
            'empresaCodigoPais' => $codigoPais,
            'fotoDataUri' => $fotoDataUri,
        ];
    }

    private function renderPdfBytes(array $data): string
    {
        $html = view('admin.pacientes.carnet-menor-pdf', $data)->render();

        $options = new Options;
        $options->setIsRemoteEnabled(true);
        $options->setDefaultFont('DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $width = $this->mmToPt(85);
        $height = $this->mmToPt(55);
        $dompdf->setPaper([0, 0, $width, $height]);
        $dompdf->render();

        return $dompdf->output();
    }

    private function renderPng(array $data): string
    {
        // Usar el método GD mejorado para generar PNG con mejor calidad y diseño
        return $this->renderPngEnhanced($data);
    }

    private function renderPngEnhanced(array $data): string
    {
        // Generar PNG que se parezca mucho más al HTML real
        $paciente = $data['paciente'];

        // Dimensiones del carnet: 85mm x 55mm a alta resolución (300 DPI)
        $width = 1004; // 85mm * 300 DPI / 25.4 mm/inch
        $height = 650; // 55mm * 300 DPI / 25.4 mm/inch

        $img = imagecreatetruecolor($width, $height);
        imagesavealpha($img, true);
        imagealphablending($img, true);

        // Colores exactos del CSS
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 17, 24, 39); // #111827
        $muted = imagecolorallocate($img, 107, 114, 128); // #6b7280
        $border = imagecolorallocate($img, 229, 231, 235); // #e5e7eb
        $headerBg = imagecolorallocate($img, 243, 244, 246); // #f3f4f6
        $bandBg = imagecolorallocate($img, 37, 99, 235); // #2563eb
        $bandText = imagecolorallocate($img, 255, 255, 255);
        $badgeBg = imagecolorallocate($img, 220, 38, 38); // #dc2626
        $photoBg = imagecolorallocate($img, 243, 244, 246); // #f3f4f6
        $photoBorder = imagecolorallocate($img, 229, 231, 235); // #e5e7eb
        $qrBg = imagecolorallocate($img, 255, 255, 255);
        $qrBorder = imagecolorallocate($img, 229, 231, 235); // #e5e7eb

        // Fondo blanco con bordes redondeados
        imagefilledrectangle($img, 0, 0, $width, $height, $white);

        // Crear bordes redondeados
        $radius = 48; // 12px * escala
        $this->drawRoundedRectangle($img, 0, 0, $width - 1, $height - 1, $radius, $border);
        imagefilledrectangle($img, $radius, 0, $width - $radius - 1, $height - 1, $white);
        imagefilledrectangle($img, 0, $radius, $width - 1, $height - $radius - 1, $white);

        // Fuentes
        $regularFont = $this->resolveFontPath(false);
        $boldFont = $this->resolveFontPath(true);

        // Header con diseño exacto al HTML
        $headerHeight = 144; // 12mm * escala
        imagefilledrectangle($img, $radius, $radius, $width - $radius - 1, $headerHeight, $headerBg);

        // Logo
        $logo = $this->imageFromDataUri($data['logoUrl'] ?? null);
        $logoSize = 108; // 18mm * escala
        $logoX = 42; // 10px * escala
        $logoY = 42;
        if ($logo) {
            $this->copyResampled($img, $logo, $logoX, $logoY, $logoSize, 60); // Logo más ancho que alto
            imagedestroy($logo);
        }

        // Información de la empresa
        $empresaNombre = (string) ($data['empresaNombre'] ?? '');
        $sucursalNombre = (string) ($data['sucursalNombre'] ?? '');

        $headerTextX = $logoX + $logoSize + 42; // 8px * escala de espacio
        $this->drawText($img, $boldFont, 32, $headerTextX, 78, $black, $empresaNombre, $width - $headerTextX - 84);
        if ($sucursalNombre !== '') {
            $this->drawText($img, $regularFont, 26, $headerTextX, 120, $muted, $sucursalNombre, $width - $headerTextX - 84);
        }

        // Texto "Carnet" a la derecha
        $this->drawText($img, $boldFont, 28, $width - 168, 78, $muted, 'CARNET', 168);

        // Cuerpo principal
        $bodyY = $headerHeight + 42;

        // Foto del paciente
        $photoSize = 264; // 22mm * escala
        $photoX = 42;
        $photoY = $bodyY;

        // Foto con bordes redondeados
        $this->drawRoundedRectangle($img, $photoX, $photoY, $photoX + $photoSize, $photoY + $photoSize, 40, $photoBorder);
        imagefilledellipse($img, $photoX + $photoSize / 2, $photoY + $photoSize / 2, $photoSize - 2, $photoSize - 2, $photoBg);

        $photo = $this->imageFromDataUri($data['fotoDataUri'] ?? null);
        if ($photo) {
            $this->copyResampled($img, $photo, $photoX + 1, $photoY + 1, $photoSize - 2, $photoSize - 2);
            imagedestroy($photo);
        } else {
            // Iniciales centradas
            $initials = $this->initials(trim((string) $paciente->nombres), trim((string) $paciente->apellidos));
            $this->drawTextCentered($img, $boldFont, 72, $photoX + 1, $photoY + 1, $photoSize - 2, $photoSize - 2, $bandBg, $initials);
        }

        // Información del paciente
        $infoX = $photoX + $photoSize + 42;
        $infoY = $bodyY + 12;
        $infoWidth = $width - $infoX - 210; // Dejar espacio para el QR

        // Documento
        if (! empty($paciente->documento_identidad)) {
            $this->drawText($img, $regularFont, 28, $infoX, $infoY, $muted, strtoupper($paciente->documento_identidad), $infoWidth);
            $infoY += 36;
        }

        // Nombre completo
        $pacienteNombre = trim((string) $paciente->nombres.' '.(string) $paciente->apellidos);
        $this->drawText($img, $boldFont, 38, $infoX, $infoY, $black, $pacienteNombre, $infoWidth);
        $infoY += 48;

        // Nickname o subtítulo
        $nickname = $paciente->nickname ?: 'Registro de paciente';
        $this->drawText($img, $regularFont, 26, $infoX, $infoY, $muted, $nickname, $infoWidth);
        $infoY += 36;

        // Edad
        $edadFormateada = $paciente->edad_formateada;
        if ($edadFormateada) {
            $nacimiento = $paciente->fecha_nacimiento->format('d/m/Y');
            $this->drawText($img, $regularFont, 26, $infoX, $infoY, $muted, "Nac: $nacimiento ($edadFormateada)", $infoWidth);
            $infoY += 36;
        }

        // Tutor
        $tutorNombre = trim((string) ($paciente->tutor->nombres ?? '').' '.(string) ($paciente->tutor->apellidos ?? ''));
        if ($tutorNombre !== '') {
            $this->drawText($img, $regularFont, 26, $infoX, $infoY, $muted, "Tutor: $tutorNombre", $infoWidth);
        }

        // Código QR
        $qrSize = 192; // 16mm * escala
        $qrX = $width - $qrSize - 42;
        $qrY = $bodyY + 24;

        $this->drawRoundedRectangle($img, $qrX, $qrY, $qrX + $qrSize, $qrY + $qrSize, 32, $qrBorder);
        imagefilledrectangle($img, $qrX + 1, $qrY + 1, $qrX + $qrSize - 1, $qrY + $qrSize - 1, $qrBg);

        $qr = $this->imageFromDataUri($data['qrDataUri'] ?? null);
        if ($qr) {
            $this->copyResampled($img, $qr, $qrX + 8, $qrY + 8, $qrSize - 16, $qrSize - 16);
            imagedestroy($qr);
        }

        // Etiquetas debajo del QR
        $this->drawTextCentered($img, $regularFont, 24, $qrX, $qrY + $qrSize + 12, $qrSize, 24, $muted, 'ID');
        $this->drawTextCentered($img, $boldFont, 26, $qrX, $qrY + $qrSize + 36, $qrSize, 24, $black, $paciente->id);

        // Banda inferior con gradiente
        $bandTop = $height - 144; // 12mm * escala
        $this->drawGradientRectangle($img, $radius, $bandTop, $width - $radius - 1, $height - $radius - 1, $bandBg);

        // Texto en la banda
        $bandTextY = $bandTop + 54;
        $this->drawText($img, $boldFont, 26, 84, $bandTextY, $bandText, 'En caso de extravío, escanee el QR', $width - 168);

        // Contacto
        $tel = $data['contactTel'] ?? null;
        $email = $data['contactEmail'] ?? null;

        $contactParts = [];
        if ($tel) {
            $contactParts[] = "Tel: $tel";
        }
        if ($email) {
            $contactParts[] = $email;
        }
        $contactText = implode('  |  ', $contactParts);
        if ($contactText !== '') {
            $this->drawText($img, $regularFont, 24, 84, $bandTextY + 42, $bandText, $contactText, $width - 168);
        }

        // Generar PNG de alta calidad
        ob_start();
        imagepng($img, null, 9); // Máxima compresión
        $png = (string) ob_get_clean();
        imagedestroy($img);

        return $png;
    }

    private function drawRoundedRectangle($img, $x1, $y1, $x2, $y2, $radius, $color)
    {
        // Dibujar rectángulo con esquinas redondeadas
        imagefilledellipse($img, $x1 + $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x2 - $radius, $y1 + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x1 + $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($img, $x2 - $radius, $y2 - $radius, $radius * 2, $radius * 2, $color);

        imagefilledrectangle($img, $x1 + $radius, $y1, $x2 - $radius, $y2, $color);
        imagefilledrectangle($img, $x1, $y1 + $radius, $x2, $y2 - $radius, $color);
    }

    private function drawGradientRectangle($img, $x1, $y1, $x2, $y2, $baseColor)
    {
        // Crear efecto de gradiente suave
        $height = $y2 - $y1;
        for ($y = 0; $y < $height; $y++) {
            $factor = 1 - ($y / $height) * 0.3; // Gradiente suave
            $color = imagecolorallocate($img,
                (int) (37 * $factor),
                (int) (99 * $factor),
                (int) (235 * $factor)
            );
            imageline($img, $x1, $y1 + $y, $x2, $y1 + $y, $color);
        }
    }

    private function renderPngFallback(array $data): string
    {
        // Método GD original como fallback
        $paciente = $data['paciente'];

        $width = 1000;
        $height = 650;

        $img = imagecreatetruecolor($width, $height);
        imagesavealpha($img, true);
        imagealphablending($img, true);

        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 12, 18, 28);
        $muted = imagecolorallocate($img, 90, 100, 115);
        $border = imagecolorallocate($img, 220, 225, 235);
        $headerBg = imagecolorallocate($img, 244, 246, 251);
        $bandBg = imagecolorallocate($img, 19, 109, 151);
        $bandText = imagecolorallocate($img, 255, 255, 255);
        $badgeBg = imagecolorallocate($img, 220, 38, 38);

        imagefilledrectangle($img, 0, 0, $width, $height, $white);
        imagerectangle($img, 0, 0, $width - 1, $height - 1, $border);

        $padding = 28;
        $headerHeight = 110;
        $bandHeight = 135;

        imagefilledrectangle($img, 1, 1, $width - 2, $headerHeight, $headerBg);
        imagefilledrectangle($img, 1, $height - $bandHeight, $width - 2, $height - 2, $bandBg);

        $regularFont = $this->resolveFontPath(false);
        $boldFont = $this->resolveFontPath(true);

        $logo = $this->imageFromDataUri($data['logoUrl'] ?? null);
        if ($logo) {
            $logoSize = 86;
            $this->copyResampled($img, $logo, $padding, 12, $logoSize, $logoSize);
            imagedestroy($logo);
        }

        $empresaNombre = (string) ($data['empresaNombre'] ?? '');
        $sucursalNombre = (string) ($data['sucursalNombre'] ?? '');

        $headerTextX = $padding + 100;
        $this->drawText($img, $boldFont, 22, $headerTextX, 48, $black, $empresaNombre, $width - $headerTextX - $padding);
        if ($sucursalNombre !== '') {
            $this->drawText($img, $regularFont, 16, $headerTextX, 82, $muted, $sucursalNombre, $width - $headerTextX - $padding);
        }

        $rightColWidth = 260;
        $rightX = $width - $padding - $rightColWidth;

        $photoSize = 220;
        $photoX = $width - $padding - $photoSize;
        $photoY = $headerHeight + 22;

        $photo = $this->imageFromDataUri($data['fotoDataUri'] ?? null);
        if ($photo) {
            $this->copyResampled($img, $photo, $photoX, $photoY, $photoSize, $photoSize);
            imagedestroy($photo);
        } else {
            $circleBg = imagecolorallocate($img, 233, 238, 247);
            imagefilledellipse($img, $photoX + (int) ($photoSize / 2), $photoY + (int) ($photoSize / 2), $photoSize, $photoSize, $circleBg);

            $initials = $this->initials(trim((string) $paciente->nombres), trim((string) $paciente->apellidos));
            $this->drawTextCentered($img, $boldFont, 56, $photoX, $photoY, $photoSize, $photoSize, $black, $initials);
        }

        $qr = $this->imageFromDataUri($data['qrDataUri'] ?? null);
        if ($qr) {
            $qrSize = 200;
            $qrX = $width - $padding - $qrSize;
            $qrY = $photoY + $photoSize + 18;
            $this->copyResampled($img, $qr, $qrX, $qrY, $qrSize, $qrSize);
            imagedestroy($qr);
        }

        $leftX = $padding;
        $leftY = $headerHeight + 55;
        $leftWidth = $rightX - $leftX - 20;

        $pacienteNombre = trim((string) $paciente->nombres.' '.(string) $paciente->apellidos);
        $this->drawText($img, $boldFont, 28, $leftX, $leftY, $black, $pacienteNombre, $leftWidth - 140);

        $badgeX = $leftX + $this->measureTextWidth($boldFont, 28, $pacienteNombre);
        $badgeX = min($badgeX + 16, $leftX + $leftWidth - 120);
        $badgeYTop = $leftY - 30;
        imagefilledrectangle($img, $badgeX, $badgeYTop, $badgeX + 110, $badgeYTop + 36, $badgeBg);
        $this->drawText($img, $boldFont, 16, $badgeX + 14, $badgeYTop + 25, $bandText, 'MENOR', 110);

        $infoY = $leftY + 48;
        $edad = $paciente->fecha_nacimiento ? $paciente->fecha_nacimiento->age : null;
        if ($edad !== null) {
            $this->drawText($img, $regularFont, 18, $leftX, $infoY, $muted, 'Edad: '.$edad, $leftWidth);
            $infoY += 30;
        }

        if (! empty($paciente->documento_identidad)) {
            $this->drawText($img, $regularFont, 18, $leftX, $infoY, $muted, 'Documento: '.(string) $paciente->documento_identidad, $leftWidth);
            $infoY += 30;
        }

        $tutorNombre = trim((string) ($paciente->tutor->nombres ?? '').' '.(string) ($paciente->tutor->apellidos ?? ''));
        if ($tutorNombre !== '') {
            $this->drawText($img, $regularFont, 18, $leftX, $infoY, $muted, 'Tutor: '.$tutorNombre, $leftWidth);
            $infoY += 30;
        }

        $bandTop = $height - $bandHeight;
        $line1 = 'En caso de extravío, escanee el QR';
        $this->drawText($img, $boldFont, 16, $padding, $bandTop + 44, $bandText, $line1, $width - $padding * 2);

        $tel = $data['contactTel'] ?? null;
        $email = $data['contactEmail'] ?? null;

        $line2Parts = [];
        if ($tel) {
            $line2Parts[] = 'Tel: '.(string) $tel;
        }
        if ($email) {
            $line2Parts[] = (string) $email;
        }
        $line2 = implode('  |  ', $line2Parts);
        if ($line2 !== '') {
            $this->drawText($img, $regularFont, 14, $padding, $bandTop + 76, $bandText, $line2, $width - $padding * 2);
        }

        $line3 = 'ID: '.$paciente->id.'  •  '.config('app.url');
        $this->drawText($img, $regularFont, 12, $padding, $bandTop + 106, $bandText, $line3, $width - $padding * 2);

        ob_start();
        imagepng($img);
        $png = (string) ob_get_clean();
        imagedestroy($img);

        return $png;
    }

    private function resolveFontPath(bool $bold): ?string
    {
        $path = base_path('vendor/dompdf/dompdf/lib/fonts/'.($bold ? 'DejaVuSans-Bold.ttf' : 'DejaVuSans.ttf'));

        return is_file($path) ? $path : null;
    }

    private function imageFromDataUri(?string $dataUri): \GdImage|false|null
    {
        if (! $dataUri) {
            return null;
        }

        if (str_starts_with($dataUri, 'data:')) {
            $parts = explode(',', $dataUri, 2);
            if (count($parts) !== 2) {
                return null;
            }
            $raw = base64_decode($parts[1], true);
            if ($raw === false) {
                return null;
            }

            return @imagecreatefromstring($raw) ?: null;
        }

        return null;
    }

    private function copyResampled(\GdImage $dst, \GdImage $src, int $dstX, int $dstY, int $dstW, int $dstH): void
    {
        $srcW = imagesx($src);
        $srcH = imagesy($src);
        imagealphablending($dst, true);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, $dstX, $dstY, 0, 0, $dstW, $dstH, $srcW, $srcH);
    }

    private function drawText(\GdImage $img, ?string $font, int $size, int $x, int $y, int $color, string $text, int $maxWidth): void
    {
        $text = trim($text);
        if ($text === '') {
            return;
        }

        if (! $font || ! is_file($font)) {
            imagestring($img, 3, $x, $y - 14, mb_substr($text, 0, 40), $color);

            return;
        }

        $text = $this->truncateText($font, $size, $text, $maxWidth);
        imagettftext($img, $size, 0, $x, $y, $color, $font, $text);
    }

    private function drawTextCentered(\GdImage $img, ?string $font, int $size, int $x, int $y, int $w, int $h, int $color, string $text): void
    {
        $text = trim($text);
        if ($text === '') {
            return;
        }

        if (! $font || ! is_file($font)) {
            $tx = $x + (int) ($w / 2) - 10;
            $ty = $y + (int) ($h / 2) - 6;
            imagestring($img, 5, $tx, $ty, mb_substr($text, 0, 3), $color);

            return;
        }

        $bbox = imagettfbbox($size, 0, $font, $text);
        $textW = abs($bbox[2] - $bbox[0]);
        $textH = abs($bbox[7] - $bbox[1]);
        $tx = $x + (int) (($w - $textW) / 2);
        $ty = $y + (int) (($h + $textH) / 2);
        imagettftext($img, $size, 0, $tx, $ty, $color, $font, $text);
    }

    private function measureTextWidth(?string $font, int $size, string $text): int
    {
        if (! $font || ! is_file($font)) {
            return 0;
        }
        $bbox = imagettfbbox($size, 0, $font, $text);

        return (int) abs($bbox[2] - $bbox[0]);
    }

    private function truncateText(string $font, int $size, string $text, int $maxWidth): string
    {
        if ($maxWidth <= 0) {
            return '';
        }

        $text = str_replace(["\r", "\n"], ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text) ?? $text;

        if ($this->measureTextWidth($font, $size, $text) <= $maxWidth) {
            return $text;
        }

        $ellipsis = '…';
        $trimmed = $text;
        while (mb_strlen($trimmed) > 0) {
            $trimmed = mb_substr($trimmed, 0, mb_strlen($trimmed) - 1);
            $candidate = rtrim($trimmed).$ellipsis;
            if ($this->measureTextWidth($font, $size, $candidate) <= $maxWidth) {
                return $candidate;
            }
        }

        return $ellipsis;
    }

    private function initials(string $nombres, string $apellidos): string
    {
        $first = $this->firstLetter($nombres);
        $last = $this->firstLetter($apellidos);
        $value = strtoupper(trim($first.$last));

        return $value !== '' ? $value : 'P';
    }

    private function firstLetter(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }
        $parts = preg_split('/\s+/', $value) ?: [];
        $part = $parts[0] ?? $value;

        return mb_substr($part, 0, 1);
    }

    private function fileToDataUri(string $path, string $fallbackMime): ?string
    {
        if (! is_file($path)) {
            return null;
        }

        $data = file_get_contents($path);
        if ($data === false) {
            return null;
        }

        $mime = $fallbackMime;
        $base64 = base64_encode($data);

        return 'data:'.$mime.';base64,'.$base64;
    }

    private function formatWhatsAppJid(string $phone, string $codigoPais): string
    {
        $clean = preg_replace('/\D/', '', $phone) ?? '';

        if (str_starts_with($clean, '0')) {
            $clean = substr($clean, 1);
        }

        if (! str_starts_with($clean, $codigoPais) && strlen($clean) === 10) {
            $clean = $codigoPais.$clean;
        }

        return $clean.'@s.whatsapp.net';
    }

    private function mmToPt(float $mm): float
    {
        return $mm * 72 / 25.4;
    }
}
