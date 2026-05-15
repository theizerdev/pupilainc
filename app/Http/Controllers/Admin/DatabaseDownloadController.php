<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseDownloadController extends Controller
{
    public function download($file)
    {
        // Validate file name to prevent directory traversal
        if (!preg_match('/^backup_[a-zA-Z0-9_-]+_\d{4}-\d{2}-\d{2}_\d{6}\.(sql|sql\.gz)$/', $file)) {
            abort(404, 'Archivo no válido');
        }

        $filePath = 'backups/' . $file;

        // Check if file exists
        if (!Storage::disk('local')->exists($filePath)) {
            abort(404, 'Archivo no encontrado');
        }

        // Get file content
        $content = Storage::disk('local')->get($filePath);

        // Determine content type
        $contentType = str_ends_with($file, '.gz') ? 'application/gzip' : 'application/sql';

        // Create streamed response for download
        return new StreamedResponse(function () use ($content) {
            echo $content;
        }, 200, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'attachment; filename="' . $file . '"',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
