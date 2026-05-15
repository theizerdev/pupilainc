<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Traits\HasDynamicLayout;

class DatabaseExport extends Component
{
    use HasDynamicLayout, WithFileUploads;

    // Tab and wizard state
    public $activeTab = 'export';
    public $exportStep = 1;
    public $importStep = 1;
    public $password = '';
    public $uploadedFile = null;
    public $importFileName = '';
    public $importFileSize = 0;
    public $importValidationResults = [];
    public $importProgress = 0;
    public $totalTables = 0;
    public $estimatedFileSize = '';
    public $showPasswordModal = false;
    public $pendingAction = '';
    public $isImporting = false;

    // Export progress
    public $exportProgress = 0;
    public $isExporting = false;
    public $exportFileName = '';

    // Messages
    public $successMessage = '';
    public $errorMessage = '';
    public $downloadUrl = '';

    // Export options
    public $exportOptions = [
        'include_structure' => true,
        'include_data' => true,
        'include_views' => true,
        'include_procedures' => true,
        'include_triggers' => true,
        'include_functions' => true,
        'exclude_system_tables' => true,
        'compress' => false,
        'add_drop_table' => true,
        'add_if_not_exists' => true,
    ];

    // Import options
    public $importOptions = [
        'drop_existing' => false,
        'ignore_errors' => false,
        'skip_foreign_key_checks' => true,
    ];

    // Available tables
    public $availableTables = [];

    protected $rules = [
        'password' => 'required',
        'uploadedFile' => 'nullable|file|mimes:sql,gz|max:102400',
    ];

    public function mount()
    {
        $this->loadAvailableTables();
        $this->calculateEstimatedSize();
    }

    public function render()
    {
        return view('livewire.admin.database-export')
            ->layout($this->getLayout(), [
                'title' => 'Exportar e Importar Base de Datos',
                'breadcrumb' => [
                    ['name' => 'Dashboard', 'route' => 'admin.dashboard'],
                    ['name' => 'Base de Datos', 'active' => true]
                ]
            ]);
    }

    // ==================== TAB NAVIGATION ====================

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetExport();
        $this->resetImport();
    }

    // ==================== WIZARD NAVIGATION ====================

    public function nextExportStep()
    {
        if ($this->exportStep < 3) {
            $this->exportStep++;
        }
    }

    public function previousExportStep()
    {
        if ($this->exportStep > 1) {
            $this->exportStep--;
        }
    }

    public function resetExport()
    {
        $this->exportStep = 1;
        $this->exportProgress = 0;
        $this->isExporting = false;
        $this->exportFileName = '';
    }

    public function nextImportStep()
    {
        if ($this->importStep < 3) {
            $this->importStep++;
        }
    }

    public function previousImportStep()
    {
        if ($this->importStep > 1) {
            $this->importStep--;
        }
    }

    public function resetImport()
    {
        $this->importStep = 1;
        $this->uploadedFile = null;
        $this->importValidationResults = [];
        $this->importProgress = 0;
        $this->isImporting = false;
        $this->importFileName = '';
        $this->importFileSize = 0;
    }

    // ==================== PASSWORD VERIFICATION ====================

    public function requestPasswordVerification($action)
    {
        $this->pendingAction = $action;
        $this->password = '';
        $this->showPasswordModal = true;
        $this->dispatch('show-password-modal');
    }

    public function verifyPassword()
    {
        $this->validate([
            'password' => 'required'
        ]);

        if (!Hash::check($this->password, auth()->user()->password)) {
            $this->addError('password', 'La contraseña es incorrecta');
            return false;
        }

        // Close modal FIRST before executing action
        $this->showPasswordModal = false;
        $this->password = '';
        $this->dispatch('hide-password-modal');

        if ($this->pendingAction === 'export') {
            try {
                $this->executeExport();
            } catch (\Exception $e) {
                $this->errorMessage = 'Error al exportar: ' . $e->getMessage();
                Log::error('Database export error: ' . $e->getMessage());
            }
        } elseif ($this->pendingAction === 'import') {
            try {
                $this->executeImport();
            } catch (\Exception $e) {
                $this->errorMessage = 'Error en importación: ' . $e->getMessage();
                Log::error('Database import error: ' . $e->getMessage());
            }
        }

        return true;
    }

    // ==================== EXPORT FUNCTIONS ====================

    public function executeExport()
    {
        $this->exportStep = 4;
        $this->isExporting = true;
        $this->exportProgress = 10;
        $this->successMessage = '';
        $this->errorMessage = '';
        $this->downloadUrl = '';

        try {
            $databaseName = DB::getDatabaseName();
            $fileName = 'backup_' . str_replace('_', '-', $databaseName) . '_' . now()->format('Y-m-d_His');

            $this->exportProgress = 30;

            $sqlContent = $this->generateCompleteSQLDump();

            $this->exportProgress = 70;

            // Compress if requested
            if ($this->exportOptions['compress']) {
                $fileName .= '.sql.gz';
                $sqlContent = gzencode($sqlContent);
            } else {
                $fileName .= '.sql';
            }

            $this->exportProgress = 90;

            // Save to storage temporarily
            $filePath = 'backups/' . $fileName;
            Storage::disk('local')->put($filePath, $sqlContent);

            // Generate download URL
            $this->downloadUrl = route('admin.database-download', ['file' => $fileName]);

            $this->exportProgress = 100;

            // Log the export action
            activity()
                ->causedBy(auth()->user())
                ->log('Exportación de base de datos: ' . $fileName);

            $this->exportFileName = $fileName;
            $this->isExporting = false;
            $this->successMessage = 'Exportación completada. La descarga comenzará automáticamente.';

            // Trigger download via JavaScript
            $this->dispatch('trigger-download', url: $this->downloadUrl);

        } catch (\Exception $e) {
            $this->errorMessage = 'Error al exportar: ' . $e->getMessage();
            Log::error('Database export error: ' . $e->getMessage());
            $this->isExporting = false;
            throw $e;
        }
    }

    private function generateCompleteSQLDump()
    {
        $output = [];

        // Header
        $output[] = "-- ============================================";
        $output[] = "-- Exportación de Base de Datos Completa";
        $output[] = "-- ============================================";
        $output[] = "-- Fecha: " . now()->format('Y-m-d H:i:s');
        $output[] = "-- Sistema: Solumed - Sistema de Gestión";
        $output[] = "-- Usuario: " . (auth()->check() ? auth()->user()->name : 'Sistema');
        $output[] = "-- Empresa: " . (auth()->check() && auth()->user()->empresa ? auth()->user()->empresa->nombre : 'N/A');
        $output[] = "-- Base de datos: " . DB::getDatabaseName();
        $output[] = "-- ============================================";
        $output[] = "";
        $output[] = "SET FOREIGN_KEY_CHECKS = 0;";
        $output[] = "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';";
        $output[] = "START TRANSACTION;";
        $output[] = "SET time_zone = '+00:00';";
        $output[] = "";

        // Tables
        $tablesToExport = $this->availableTables;
        $totalTables = count($tablesToExport);
        $processed = 0;

        foreach ($tablesToExport as $table => $label) {
            $processed++;
            $this->exportProgress = 30 + ($processed / $totalTables * 40);

            $output = array_merge($output, $this->getTableSQL($table));
        }

        // Commit
        $output[] = "";
        $output[] = "COMMIT;";
        $output[] = "";
        $output[] = "SET FOREIGN_KEY_CHECKS = 1;";

        return implode("\n", $output);
    }

    private function getTableSQL($tableName)
    {
        $output = [];

        try {
            // Table structure
            if ($this->exportOptions['include_structure']) {
                $output[] = "--";
                $output[] = "-- Estructura de tabla para `{$tableName}`";
                $output[] = "--";

                if ($this->exportOptions['add_drop_table']) {
                    $output[] = "DROP TABLE IF EXISTS `{$tableName}`;";
                }

                $createTable = DB::selectOne("SHOW CREATE TABLE `{$tableName}`");
                $createStatement = $createTable->{'Create Table'};

                if ($this->exportOptions['add_if_not_exists']) {
                    $createStatement = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $createStatement);
                }

                $output[] = $createStatement . ";";
                $output[] = "";
            }

            // Table data
            if ($this->exportOptions['include_data']) {
                $output[] = "--";
                $output[] = "-- Volcado de datos para la tabla `{$tableName}`";
                $output[] = "--";

                $query = DB::table($tableName);
                $records = $query->get();

                if ($records->isNotEmpty()) {
                    foreach ($records as $record) {
                        $data = (array) $record;
                        $columns = array_keys($data);
                        $values = array_map(function($value) {
                            if ($value === null) {
                                return 'NULL';
                            } elseif (is_numeric($value)) {
                                return $value;
                            } elseif (is_bool($value)) {
                                return $value ? 1 : 0;
                            } else {
                                return "'" . addslashes($value) . "'";
                            }
                        }, array_values($data));

                        $sql = "INSERT INTO `{$tableName}` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");";
                        $output[] = $sql;
                    }
                }

                $output[] = "";
            }

        } catch (\Exception $e) {
            $output[] = "-- Error al procesar la tabla {$tableName}: " . $e->getMessage();
            $output[] = "";
            Log::warning("Error exporting table {$tableName}: " . $e->getMessage());
        }

        return $output;
    }

    // ==================== IMPORT FUNCTIONS ====================

    public function updatedUploadedFile()
    {
        if ($this->uploadedFile) {
            $this->importFileName = $this->uploadedFile->getClientOriginalName();
            $this->importFileSize = $this->uploadedFile->getSize();
        }
    }

    public function validateImportFile()
    {
        $this->importStep = 2;

        if (!$this->uploadedFile) {
            $this->addError('uploadedFile', 'Por favor selecciona un archivo');
            return;
        }

        // Validate file
        $validator = Validator::make([
            'file' => $this->uploadedFile
        ], [
            'file' => 'required|file|mimes:sql,gz|max:102400'
        ]);

        if ($validator->fails()) {
            $this->addError('uploadedFile', $validator->errors()->first('file'));
            $this->importStep = 1;
            return;
        }

        // Read and validate content
        try {
            $content = file_get_contents($this->uploadedFile->getRealPath());

            // Handle gzip
            if ($this->uploadedFile->getClientOriginalExtension() === 'gz') {
                $content = gzdecode($content);
                if ($content === false) {
                    $this->addError('uploadedFile', 'El archivo gzip está corrupto');
                    $this->importStep = 1;
                    return;
                }
            }

            // Check if valid SQL
            if (stripos($content, 'CREATE TABLE') === false && stripos($content, 'INSERT INTO') === false) {
                $this->addError('uploadedFile', 'El archivo no parece ser un archivo SQL válido');
                $this->importStep = 1;
                return;
            }

            // Analyze file
            $this->importValidationResults = $this->analyzeSQLFile($content);

        } catch (\Exception $e) {
            $this->addError('uploadedFile', 'Error al leer el archivo: ' . $e->getMessage());
            $this->importStep = 1;
        }
    }

    private function analyzeSQLFile($content)
    {
        $results = [
            'total_tables' => 0,
            'total_statements' => 0,
            'has_structure' => false,
            'has_data' => false,
            'tables' => [],
            'warnings' => []
        ];

        // Count CREATE TABLE statements
        $createMatches = [];
        preg_match_all('/CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?(\w+)`?/i', $content, $createMatches);
        $results['total_tables'] = count(array_unique($createMatches[1]));
        $results['tables'] = array_unique($createMatches[1]);
        $results['has_structure'] = $results['total_tables'] > 0;

        // Count INSERT statements
        $insertMatches = [];
        preg_match_all('/INSERT\s+INTO\s+`?(\w+)`?/i', $content, $insertMatches);
        $results['has_data'] = count($insertMatches[1]) > 0;

        // Total statements (approximate)
        $results['total_statements'] = substr_count($content, ';');

        // Warnings
        if (stripos($content, 'DROP TABLE') !== false) {
            $results['warnings'][] = 'El archivo contiene sentencias DROP TABLE - Esto eliminará tablas existentes';
        }

        if (stripos($content, 'TRUNCATE') !== false) {
            $results['warnings'][] = 'El archivo contiene sentencias TRUNCATE - Esto vaciará tablas';
        }

        return $results;
    }

    public function executeImport()
    {
        $this->importStep = 4;
        $this->isImporting = true;
        $this->importProgress = 10;
        $this->successMessage = '';
        $this->errorMessage = '';

        try {
            // Disable foreign key checks first (before any transaction)
            if ($this->importOptions['skip_foreign_key_checks']) {
                DB::statement('SET FOREIGN_KEY_CHECKS = 0');
            }

            // Disable unique checks and autocommit for better performance
            DB::statement('SET UNIQUE_CHECKS = 0');
            DB::statement('SET AUTOCOMMIT = 0');

            $content = file_get_contents($this->uploadedFile->getRealPath());

            // Handle gzip
            if ($this->uploadedFile->getClientOriginalExtension() === 'gz') {
                $content = gzdecode($content);
            }

            // Parse and execute statements
            $statements = $this->parseSQLStatements($content);
            $executed = 0;
            $total = count($statements);
            $errors = [];

            $this->importProgress = 30;

            // Execute statements without wrapping in a single transaction
            // DDL statements (DROP, CREATE) cannot be in transactions in MySQL
            foreach ($statements as $index => $statement) {
                if (empty(trim($statement))) continue;

                try {
                    DB::statement($statement);
                    $executed++;
                } catch (\Exception $e) {
                    if (!$this->importOptions['ignore_errors']) {
                        // Re-enable settings before throwing
                        if ($this->importOptions['skip_foreign_key_checks']) {
                            DB::statement('SET FOREIGN_KEY_CHECKS = 1');
                        }
                        DB::statement('SET UNIQUE_CHECKS = 1');
                        DB::statement('SET AUTOCOMMIT = 1');
                        throw $e;
                    }
                    $errors[] = "Statement {$index}: " . $e->getMessage();
                }

                $this->importProgress = 30 + ($executed / max(1, $total) * 70);
            }

            // Commit any pending changes
            DB::statement('COMMIT');

            // Re-enable settings
            if ($this->importOptions['skip_foreign_key_checks']) {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            }
            DB::statement('SET UNIQUE_CHECKS = 1');
            DB::statement('SET AUTOCOMMIT = 1');

            $this->importProgress = 100;

            // Log the import action
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'file' => $this->importFileName,
                    'statements_executed' => $executed,
                    'total_statements' => $total,
                    'errors_count' => count($errors)
                ])
                ->log('Importación de base de datos: ' . $this->importFileName);

            if (count($errors) > 0) {
                $this->successMessage = "Importación completada con advertencias: {$executed} sentencias ejecutadas, " . count($errors) . " errores ignorados";
            } else {
                $this->successMessage = "Importación exitosa: {$executed} sentencias ejecutadas";
            }

        } catch (\Exception $e) {
            // Ensure settings are re-enabled on error
            try {
                if ($this->importOptions['skip_foreign_key_checks']) {
                    DB::statement('SET FOREIGN_KEY_CHECKS = 1');
                }
                DB::statement('SET UNIQUE_CHECKS = 1');
                DB::statement('SET AUTOCOMMIT = 1');
            } catch (\Exception $cleanupError) {
                Log::error('Error cleaning up import settings: ' . $cleanupError->getMessage());
            }

            $this->errorMessage = 'Error en importación: ' . $e->getMessage();
            Log::error('Database import error: ' . $e->getMessage());
            throw $e;
        } finally {
            $this->isImporting = false;
        }
    }

    private function parseSQLStatements($content)
    {
        $statements = [];
        $current = '';
        $delimiter = ';';

        $lines = explode("\n", $content);

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines
            if (empty($line)) {
                continue;
            }

            // Skip comments
            if (strpos($line, '--') === 0 || strpos($line, '/*') === 0) {
                continue;
            }

            // Check for DELIMITER change
            if (stripos($line, 'DELIMITER') === 0) {
                $delimiter = trim(str_ireplace('DELIMITER', '', $line));
                continue;
            }

            $current .= $line . "\n";

            // Check if statement is complete
            if (substr(trim($line), -strlen($delimiter)) === $delimiter) {
                $statement = trim(str_replace($delimiter, '', $current));
                if (!empty($statement)) {
                    $statements[] = $statement;
                }
                $current = '';
            }
        }

        return $statements;
    }

    // ==================== UTILITY FUNCTIONS ====================

    public function loadAvailableTables()
    {
        $this->availableTables = [];
        $tables = DB::select('SHOW TABLES');
        $databaseName = DB::getDatabaseName();
        $key = 'Tables_in_' . $databaseName;

        $excludedTables = [
            'migrations', 'password_resets', 'password_reset_tokens',
            'personal_access_tokens', 'cache', 'cache_locks', 'jobs',
            'job_batches', 'failed_jobs', 'sessions', 'activity_log'
        ];

        foreach ($tables as $table) {
            $tableName = $table->$key;
            if (!in_array($tableName, $excludedTables)) {
                $this->availableTables[$tableName] = $this->formatTableName($tableName);
            }
        }

        asort($this->availableTables);
    }

    public function calculateEstimatedSize()
    {
        $totalSize = 0;

        foreach ($this->availableTables as $table => $label) {
            try {
                $stats = DB::selectOne("SELECT
                    data_length + index_length as total_size,
                    table_rows
                FROM information_schema.TABLES
                WHERE table_schema = '" . DB::getDatabaseName() . "'
                AND table_name = '{$table}'");

                $totalSize += $stats->total_size ?? 0;
            } catch (\Exception $e) {
                // Skip if error
            }
        }

        $this->estimatedFileSize = $this->formatBytes($totalSize);
        $this->totalTables = count($this->availableTables);
    }

    private function formatTableName($tableName)
    {
        return ucwords(str_replace('_', ' ', $tableName));
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
