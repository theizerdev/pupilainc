<?php

namespace App\Livewire\Admin\Medicos;

use App\Models\Medico;
use App\Models\Especialidad;
use App\Models\Subespecialidad;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Services\WhatsAppNotificationService;
use Livewire\Component;
use Livewire\WithPagination;
use App\Traits\HasDynamicLayout;

class Index extends Component
{
    use WithPagination, HasDynamicLayout;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $status = '';
    public $empresa_id = '';
    public $sucursal_id = '';
    public $especialidad_id = '';
    public $nivel_experiencia = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
        'status' => ['except' => ''],
        'empresa_id' => ['except' => ''],
        'sucursal_id' => ['except' => ''],
        'especialidad_id' => ['except' => ''],
        'nivel_experiencia' => ['except' => '']
    ];

    protected $paginationTheme = 'bootstrap';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEmpresaId()
    {
        $this->resetPage();
        $this->sucursal_id = '';
    }

    public function updatingSucursalId()
    {
        $this->resetPage();
    }

    public function updatingStatus()
    {
        $this->resetPage();
    }

    public function updatingEspecialidadId()
    {
        $this->resetPage();
    }

    public function updatingNivelExperiencia()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function resetFilters()
    {
        $this->reset(['search', 'status', 'empresa_id', 'sucursal_id', 'especialidad_id', 'nivel_experiencia']);
        $this->resetPage();
    }

    public function delete($id)
    {
        $this->authorize('admin.medicos.destroy');
        
        try {
            $medico = Medico::findOrFail($id);
            $medico->delete();
            
            session()->flash('success', 'Médico eliminado exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el médico: ' . $e->getMessage());
        }
    }

    public function toggleStatus($id)
    {
        $this->authorize('edit medicos');
        
        try {
            $medico = Medico::findOrFail($id);
            $medico->status = !$medico->status;
            $medico->save();
            
            session()->flash('success', 'Estado actualizado exitosamente.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al actualizar el estado: ' . $e->getMessage());
        }
    }

    public function sendWelcomeMessage($id)
    {
        $this->authorize('edit medicos');
        
        try {
            $medico = Medico::findOrFail($id);
            
            // Verificar que el médico tenga teléfono
            if (empty($medico->telefono)) {
                session()->flash('error', 'El médico no tiene número de teléfono registrado.');
                return;
            }
            
            // Obtener la empresa del médico
            $empresa = $medico->empresa;
            if (!$empresa || !$empresa->whatsapp_api_key) {
                session()->flash('error', 'La empresa no tiene configurado el servicio de WhatsApp.');
                return;
            }
            
             $mensaje = $this->generateDoctorWelcomeMessage($medico);
            $telefonoFormateado = $this->formatPhoneNumber($medico->telefono);
            
            $whatsappService = new \App\Services\WhatsAppService($empresa);
            $whatsappResult = $whatsappService->sendMessage($telefonoFormateado, $mensaje, true);
            $result['sent'] = $whatsappResult && ($whatsappResult['success'] ?? false);
       } catch (\Exception $e) {
             \Log::error('Error enviando notificación WhatsApp de bienvenida: ' . $e->getMessage());
            $result['attempted'] = true;
        }
        
        return $result;
    }

     /**
     * Generar mensaje de bienvenida para médico
     */
    public function generateDoctorWelcomeMessage($medico)
    {
        $user = $medico->user;
        $empresa = $medico->empresa;
        $sucursal = $medico->sucursal ?? $user->sucursal ?? null;
        $especialidades = $medico->especialidades->pluck('nombre')->implode(', ') ?: 'No asignada';
        $empresaNombre = $empresa ? $empresa->razon_social : 'la empresa';

        $mensaje = "🩺 ¡Bienvenido/a Dr./Dra. {$medico->nombres} {$medico->apellidos}!\n\n";
        $mensaje .= "✅ Su cuenta ha sido creada exitosamente en nuestro sistema médico.\n\n";
        $mensaje .= "📋 *Datos de acceso:*\n";
        $mensaje .= "• Usuario: {$user->username}\n";
        $mensaje .= "• Email: {$user->email}\n";
        $mensaje .= "• Contraseña temporal: {$medico->documento_identidad}\n\n";
        $mensaje .= "🏥 *Información institucional:*\n";
        $mensaje .= "• Empresa: {$empresaNombre}\n";
        if ($sucursal) {
            $mensaje .= "• Sucursal: {$sucursal->nombre}\n";
        }
        $mensaje .= "• Especialidad: {$especialidades}\n\n";
        $mensaje .= "🔐 *Importante:* Por seguridad, le recomendamos cambiar su contraseña al iniciar sesión.\n\n";
        if ($empresa && $empresa->telefono) {
            $mensaje .= "📱 ¿Preguntas? Contáctenos al {$empresa->telefono}\n\n";
        }
        $mensaje .= "¡Gracias por formar parte de nuestro equipo médico! 🏥✨";

        return $mensaje;
    }
    


    public function getMedicosProperty()
    {
        return Medico::with(['user', 'empresa', 'sucursal', 'especialidades'])
            ->forUser()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nombres', 'like', '%' . $this->search . '%')
                      ->orWhere('apellidos', 'like', '%' . $this->search . '%')
                      ->orWhere('documento_identidad', 'like', '%' . $this->search . '%')
                      ->orWhere('licencia_medica', 'like', '%' . $this->search . '%')
                      ->orWhereHas('user', function ($q) {
                          $q->where('email', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->status !== '', function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->empresa_id, function ($query) {
                $query->where('empresa_id', $this->empresa_id);
            })
            ->when($this->sucursal_id, function ($query) {
                $query->where('sucursal_id', $this->sucursal_id);
            })
            ->when($this->especialidad_id, function ($query) {
                $query->whereHas('especialidades', function ($q) {
                    $q->where('especialidad_id', $this->especialidad_id);
                });
            })
            ->when($this->nivel_experiencia, function ($query) {
                $query->where('nivel_experiencia', $this->nivel_experiencia);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    public function getStatsProperty()
    {
        $query = Medico::forUser();
        
        return [
            'total' => $query->count(),
            'activos' => $query->where('status', true)->count(),
            'inactivos' => $query->where('status', false)->count(),
            'promedio_experiencia' => round($query->avg('anios_experiencia') ?? 0, 1),
        ];
    }

    public function getEmpresasProperty()
    {
        return Empresa::forUser()->get();
    }

    public function getSucursalesProperty()
    {
        return Sucursal::when($this->empresa_id, function ($query) {
                $query->where('empresa_id', $this->empresa_id);
            })
            ->forUser()
            ->get();
    }

    public function getEspecialidadesProperty()
    {
        return Especialidad::forUser()->where('status', true)->orderBy('nombre')->get();
    }


   private function formatPhoneNumber($number)
    {
        $empresa = \DB::table('empresas')->where('id', 1)->first();
        $pais = $empresa ? \DB::table('pais')->where('id', $empresa->pais_id)->first() : null;
        $codigoPais = $pais ? $pais->codigo_telefonico : '58';
        
        $cleaned = preg_replace('/[^0-9]/', '', $number);
        
        if (strlen($cleaned) > 10 && str_starts_with($cleaned, $codigoPais)) {
            return $cleaned;
        }
        
        if (str_starts_with($cleaned, '0')) {
            $cleaned = substr($cleaned, 1);
        }
        
        return $codigoPais . $cleaned;
    }

    public function render()
    {
        return view('livewire.admin.medicos.index', [
            'medicos' => $this->medicos,
            'stats' => $this->stats,
            'empresas' => $this->empresas,
            'sucursales' => $this->sucursales,
            'especialidades' => $this->especialidades,
        ])->layout($this->getLayout());
    }
}