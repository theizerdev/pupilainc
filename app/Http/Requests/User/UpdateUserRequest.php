<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        $user = $this->route('user');
        return $this->user()->can('update', $user);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $user = $this->route('user');
        $userId = $user ? $user->id : null;

        return [
            'name' => [
                'sometimes',
                'string',
                'min:2',
                'max:255',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u',
            ],
            'username' => [
                'sometimes',
                'string',
                'min:3',
                'max:255',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'email' => [
                'sometimes',
                'string',
                'email:rfc,dns,spoof,filter',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
                'lowercase',
            ],
            'password' => [
                'sometimes',
                'string',
                'confirmed',
                'nullable',
                Password::min(12)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
            ],
            'password_confirmation' => [
                'required_with:password',
                'string',
                'same:password',
            ],
            'current_password' => [
                'required_with:password',
                'string',
                'current_password',
            ],
            'status' => [
                'sometimes',
                'string',
                Rule::in(['active', 'inactive', 'pending', 'suspended']),
            ],
            'empresa_id' => [
                'sometimes',
                'integer',
                'exists:empresas,id',
                Rule::when(!$this->user()->hasRole('super-admin'), [
                    'in:' . $this->user()->empresa_id,
                ]),
            ],
            'sucursal_id' => [
                'sometimes',
                'integer',
                'exists:sucursales,id',
                Rule::when($this->has('empresa_id'), [
                    'exists:sucursales,id,empresa_id,' . $this->empresa_id,
                ]),
                Rule::when(!$this->user()->hasRole('super-admin'), [
                    function ($attribute, $value, $fail) {
                        if ($this->user()->sucursal_id && $value !== $this->user()->sucursal_id) {
                            $fail('You can only assign users to your own branch.');
                        }
                    },
                ]),
            ],
            'roles' => [
                'sometimes',
                'array',
                'max:10',
            ],
            'roles.*' => [
                'integer',
                'exists:roles,id',
                Rule::when(!$this->user()->hasRole('super-admin'), [
                    'not_in:1', // Prevent non-super-admins from assigning super-admin role
                ]),
            ],
            'permissions' => [
                'sometimes',
                'array',
                'max:50',
            ],
            'permissions.*' => [
                'integer',
                'exists:permissions,id',
            ],
            'sync_roles' => [
                'sometimes',
                'boolean',
            ],
            'sync_permissions' => [
                'sometimes',
                'boolean',
            ],
            'phone' => [
                'sometimes',
                'string',
                'max:20',
                'regex:/^[+]?[0-9\s\-\(\)]+$/',
                'nullable',
            ],
            'address' => [
                'sometimes',
                'string',
                'max:255',
                'nullable',
            ],
            'city' => [
                'sometimes',
                'string',
                'max:100',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u',
                'nullable',
            ],
            'country' => [
                'sometimes',
                'string',
                'size:2',
                'exists:countries,iso2',
                'nullable',
            ],
            'timezone' => [
                'sometimes',
                'string',
                'timezone',
                'nullable',
            ],
            'locale' => [
                'sometimes',
                'string',
                'in:es,en,pt',
                'nullable',
            ],
            'notifications_enabled' => [
                'sometimes',
                'boolean',
            ],
            'two_factor_enabled' => [
                'sometimes',
                'boolean',
            ],
            'theme' => [
                'sometimes',
                'string',
                'in:light,dark,auto',
                'nullable',
            ],
            'force_logout' => [
                'sometimes',
                'boolean',
            ],
            'send_notification' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            'name.regex' => 'El nombre solo puede contener letras y espacios.',
            'username.min' => 'El nombre de usuario debe tener al menos 3 caracteres.',
            'username.max' => 'El nombre de usuario no puede exceder 255 caracteres.',
            'username.alpha_dash' => 'El nombre de usuario solo puede contener letras, números, guiones y guiones bajos.',
            'username.unique' => 'Este nombre de usuario ya está registrado.',
            'email.email' => 'El correo electrónico debe ser una dirección válida.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 12 caracteres.',
            'password.letters' => 'La contraseña debe contener letras.',
            'password.mixed' => 'La contraseña debe contener mayúsculas y minúsculas.',
            'password.numbers' => 'La contraseña debe contener números.',
            'password.symbols' => 'La contraseña debe contener símbolos.',
            'password.uncompromised' => 'Esta contraseña ha sido comprometida en filtraciones de datos. Por favor elige otra.',
            'password_confirmation.required_with' => 'La confirmación de contraseña es obligatoria cuando se cambia la contraseña.',
            'password_confirmation.same' => 'Las contraseñas no coinciden.',
            'current_password.required_with' => 'La contraseña actual es obligatoria para cambiar la contraseña.',
            'current_password.current_password' => 'La contraseña actual es incorrecta.',
            'status.in' => 'El estado debe ser uno de: active, inactive, pending, suspended.',
            'empresa_id.exists' => 'La empresa seleccionada no existe.',
            'empresa_id.in' => 'No tienes permiso para asignar usuarios a esta empresa.',
            'sucursal_id.exists' => 'La sucursal seleccionada no existe.',
            'roles.*.exists' => 'Uno o más roles seleccionados no existen.',
            'roles.*.not_in' => 'No tienes permiso para asignar el rol de super administrador.',
            'permissions.*.exists' => 'Uno o más permisos seleccionados no existen.',
            'phone.regex' => 'El teléfono debe contener solo números, espacios, guiones y paréntesis.',
            'city.regex' => 'La ciudad solo puede contener letras y espacios.',
            'country.size' => 'El país debe ser un código ISO de 2 letras.',
            'country.exists' => 'El país seleccionado no es válido.',
            'timezone.timezone' => 'La zona horaria debe ser válida.',
            'locale.in' => 'El idioma debe ser uno de: es, en, pt.',
            'theme.in' => 'El tema debe ser uno de: light, dark, auto.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'username' => 'nombre de usuario',
            'email' => 'correo electrónico',
            'password' => 'contraseña',
            'password_confirmation' => 'confirmación de contraseña',
            'current_password' => 'contraseña actual',
            'status' => 'estado',
            'empresa_id' => 'empresa',
            'sucursal_id' => 'sucursal',
            'roles' => 'roles',
            'roles.*' => 'rol',
            'permissions' => 'permisos',
            'permissions.*' => 'permiso',
            'sync_roles' => 'sincronizar roles',
            'sync_permissions' => 'sincronizar permisos',
            'phone' => 'teléfono',
            'address' => 'dirección',
            'city' => 'ciudad',
            'country' => 'país',
            'timezone' => 'zona horaria',
            'locale' => 'idioma',
            'notifications_enabled' => 'notificaciones habilitadas',
            'two_factor_enabled' => 'autenticación de dos factores',
            'theme' => 'tema',
            'force_logout' => 'forzar cierre de sesión',
            'send_notification' => 'enviar notificación',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            // Additional validation logic
            $this->validateMultitenancy($validator);
            $this->validateRolePermissions($validator);
            $this->validateBusinessRules($validator);
            $this->validateStatusTransitions($validator);
        });
    }

    /**
     * Validate multitenancy constraints.
     *
     * @param Validator $validator
     * @return void
     */
    protected function validateMultitenancy(Validator $validator): void
    {
        // Validate empresa and sucursal relationship
        if ($this->has('empresa_id') && $this->has('sucursal_id')) {
            $empresaId = $this->empresa_id;
            $sucursalId = $this->sucursal_id;

            // Check if sucursal belongs to empresa
            $sucursal = \App\Models\Sucursal::where('id', $sucursalId)
                ->where('empresa_id', $empresaId)
                ->first();

            if (!$sucursal) {
                $validator->errors()->add('sucursal_id', 'La sucursal no pertenece a la empresa seleccionada.');
            }
        }

        // Validate user permissions for multitenancy
        if (!$this->user()->hasRole('super-admin')) {
            if ($this->has('empresa_id') && $this->empresa_id !== $this->user()->empresa_id) {
                $validator->errors()->add('empresa_id', 'No tienes permiso para asignar usuarios a esta empresa.');
            }

            if ($this->has('sucursal_id') && $this->user()->sucursal_id && $this->sucursal_id !== $this->user()->sucursal_id) {
                $validator->errors()->add('sucursal_id', 'No tienes permiso para asignar usuarios a esta sucursal.');
            }
        }
    }

    /**
     * Validate role and permission assignments.
     *
     * @param Validator $validator
     * @return void
     */
    protected function validateRolePermissions(Validator $validator): void
    {
        if ($this->has('roles')) {
            // Check if user has permission to assign these roles
            $roles = \App\Models\Role::whereIn('id', $this->roles)->get();

            foreach ($roles as $role) {
                if (!$this->user()->can('assign role ' . $role->name)) {
                    $validator->errors()->add('roles', "No tienes permiso para asignar el rol: {$role->display_name}");
                }
            }
        }

        if ($this->has('permissions')) {
            // Check if user has permission to assign these permissions
            $permissions = \App\Models\Permission::whereIn('id', $this->permissions)->get();

            foreach ($permissions as $permission) {
                if (!$this->user()->can('assign permission ' . $permission->name)) {
                    $validator->errors()->add('permissions', "No tienes permiso para asignar el permiso: {$permission->display_name}");
                }
            }
        }
    }

    /**
     * Validate business rules.
     *
     * @param Validator $validator
     * @return void
     */
    protected function validateBusinessRules(Validator $validator): void
    {
        // Validate email domain if configured
        $allowedDomains = config('app.allowed_email_domains', []);
        if (!empty($allowedDomains) && $this->has('email')) {
            $domain = substr(strrchr($this->email, "@"), 1);
            if (!in_array($domain, $allowedDomains)) {
                $validator->errors()->add('email', 'El dominio del correo electrónico no está permitido.');
            }
        }

        // Validate password strength requirements
        if ($this->has('password')) {
            $password = $this->password;

            // Check for common passwords
            $commonPasswords = [
                'password', '123456', '12345678', 'qwerty', 'abc123',
                'password123', 'admin', 'letmein', 'welcome', 'monkey'
            ];

            if (in_array(strtolower($password), $commonPasswords)) {
                $validator->errors()->add('password', 'Esta contraseña es muy común. Por favor elige una más segura.');
            }

            // Check for personal information in password
            if ($this->has('name') && stripos($password, $this->name) !== false) {
                $validator->errors()->add('password', 'La contraseña no debe contener tu nombre.');
            }

            if ($this->has('email') && stripos($password, explode('@', $this->email)[0]) !== false) {
                $validator->errors()->add('password', 'La contraseña no debe contener tu correo electrónico.');
            }
        }

        // Validate user cannot modify their own roles/permissions without proper authorization
        if ($this->user()->id === $this->route('user')?->id) {
            if ($this->has('roles') && !$this->user()->can('manage own roles')) {
                $validator->errors()->add('roles', 'No tienes permiso para modificar tus propios roles.');
            }

            if ($this->has('permissions') && !$this->user()->can('manage own permissions')) {
                $validator->errors()->add('permissions', 'No tienes permiso para modificar tus propios permisos.');
            }
        }
    }

    /**
     * Validate status transitions.
     *
     * @param Validator $validator
     * @return void
     */
    protected function validateStatusTransitions(Validator $validator): void
    {
        if (!$this->has('status')) {
            return;
        }

        $user = $this->route('user');
        $currentStatus = $user->status;
        $newStatus = $this->status;

        // Define allowed status transitions
        $allowedTransitions = [
            'pending' => ['active', 'inactive', 'suspended'],
            'active' => ['inactive', 'suspended'],
            'inactive' => ['active', 'suspended'],
            'suspended' => ['active', 'inactive'],
        ];

        if (!isset($allowedTransitions[$currentStatus]) ||
            !in_array($newStatus, $allowedTransitions[$currentStatus])) {
            $validator->errors()->add(
                'status',
                "No se puede cambiar el estado de '{$currentStatus}' a '{$newStatus}'. Transiciones permitidas: " .
                implode(', ', $allowedTransitions[$currentStatus] ?? [])
            );
        }

        // Check if user has permission to change to specific status
        $statusPermissions = [
            'active' => 'activate users',
            'inactive' => 'deactivate users',
            'suspended' => 'suspend users',
        ];

        if (isset($statusPermissions[$newStatus]) && !$this->user()->can($statusPermissions[$newStatus])) {
            $validator->errors()->add('status', "No tienes permiso para cambiar el estado a '{$newStatus}'.");
        }
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @return void
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Los datos proporcionados no son válidos.',
            'errors' => $validator->errors(),
            'timestamp' => now()->toIso8601String(),
            'request_id' => $this->header('X-Request-ID'),
        ], 422));
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Clean and normalize input data
        $this->merge([
            'email' => $this->filled('email') ? strtolower(trim($this->email)) : null,
            'name' => $this->filled('name') ? trim(preg_replace('/\s+/', ' ', $this->name)) : null,
            'phone' => $this->filled('phone') ? preg_replace('/[^0-9+\-\s\(\)]/', '', $this->phone) : null,
            'notifications_enabled' => $this->boolean('notifications_enabled'),
            'two_factor_enabled' => $this->boolean('two_factor_enabled'),
        ]);
    }
}
