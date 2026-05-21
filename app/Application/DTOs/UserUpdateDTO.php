<?php

declare(strict_types=1);

namespace App\Application\DTOs;

class UserUpdateDTO
{
    public ?string $name = null;
    public ?string $username = null;
    public ?string $email = null;
    public ?string $password = null;
    public ?string $status = null;
    public ?int $empresaId = null;
    public ?int $sucursalId = null;
    public ?array $roleIds = null;
    public ?array $permissionIds = null;

    public function __construct(
        ?string $name = null,
        ?string $username = null,
        ?string $email = null,
        ?string $password = null,
        ?string $status = null,
        ?int $empresaId = null,
        ?int $sucursalId = null,
        ?array $roleIds = null,
        ?array $permissionIds = null
    ) {
        $this->name = $name;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->status = $status;
        $this->empresaId = $empresaId;
        $this->sucursalId = $sucursalId;
        $this->roleIds = $roleIds;
        $this->permissionIds = $permissionIds;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'] ?? null,
            $data['username'] ?? null,
            $data['email'] ?? null,
            $data['password'] ?? null,
            $data['status'] ?? null,
            $data['empresa_id'] ?? null,
            $data['sucursal_id'] ?? null,
            $data['role_ids'] ?? null,
            $data['permission_ids'] ?? null
        );
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }

        if ($this->username !== null) {
            $data['username'] = $this->username;
        }

        if ($this->email !== null) {
            $data['email'] = $this->email;
        }

        if ($this->password !== null) {
            $data['password'] = $this->password;
        }

        if ($this->status !== null) {
            $data['status'] = $this->status;
        }

        if ($this->empresaId !== null) {
            $data['empresa_id'] = $this->empresaId;
        }

        if ($this->sucursalId !== null) {
            $data['sucursal_id'] = $this->sucursalId;
        }

        if ($this->roleIds !== null) {
            $data['role_ids'] = $this->roleIds;
        }

        if ($this->permissionIds !== null) {
            $data['permission_ids'] = $this->permissionIds;
        }

        return $data;
    }

    public function hasChanges(): bool
    {
        return $this->name !== null ||
               $this->username !== null ||
               $this->email !== null ||
               $this->password !== null ||
               $this->status !== null ||
               $this->empresaId !== null ||
               $this->sucursalId !== null ||
               $this->roleIds !== null ||
               $this->permissionIds !== null;
    }

    public function getChangedFields(): array
    {
        $fields = [];

        if ($this->name !== null) {
            $fields[] = 'name';
        }

        if ($this->username !== null) {
            $fields[] = 'username';
        }

        if ($this->email !== null) {
            $fields[] = 'email';
        }

        if ($this->password !== null) {
            $fields[] = 'password';
        }

        if ($this->status !== null) {
            $fields[] = 'status';
        }

        if ($this->empresaId !== null) {
            $fields[] = 'empresa_id';
        }

        if ($this->sucursalId !== null) {
            $fields[] = 'sucursal_id';
        }

        if ($this->roleIds !== null) {
            $fields[] = 'roles';
        }

        if ($this->permissionIds !== null) {
            $fields[] = 'permissions';
        }

        return $fields;
    }

    public function shouldSendNotification(): bool
    {
        return $this->status !== null || $this->password !== null;
    }

    public function getNotificationType(): string
    {
        if ($this->password !== null) {
            return 'password_changed';
        }

        if ($this->status !== null) {
            return 'status_changed';
        }

        return 'profile_updated';
    }

    public function shouldCreateAuditLog(): bool
    {
        return $this->hasChanges();
    }

    public function getAuditContext(): array
    {
        $context = [];

        if ($this->name !== null) {
            $context['name'] = $this->name;
        }

        if ($this->username !== null) {
            $context['username'] = $this->username;
        }

        if ($this->email !== null) {
            $context['email'] = $this->email;
        }

        if ($this->status !== null) {
            $context['status'] = $this->status;
        }

        if ($this->empresaId !== null) {
            $context['empresa_id'] = $this->empresaId;
        }

        if ($this->sucursalId !== null) {
            $context['sucursal_id'] = $this->sucursalId;
        }

        if ($this->roleIds !== null) {
            $context['role_ids'] = $this->roleIds;
            $context['role_count'] = count($this->roleIds);
        }

        if ($this->permissionIds !== null) {
            $context['permission_ids'] = $this->permissionIds;
            $context['permission_count'] = count($this->permissionIds);
        }

        return $context;
    }

    public function getMultitenancyContext(): array
    {
        return [
            'empresa_id' => $this->empresaId,
            'sucursal_id' => $this->sucursalId,
        ];
    }

    public function hasMultitenancyChanges(): bool
    {
        return $this->empresaId !== null || $this->sucursalId !== null;
    }

    public function shouldInvalidateCache(): bool
    {
        return $this->hasChanges();
    }

    public function shouldDispatchEvent(): bool
    {
        return $this->hasChanges();
    }
}
