<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\DTOs\UserDTO;
use App\Application\DTOs\UserCreateDTO;
use App\Application\DTOs\UserUpdateDTO;
use App\Application\Events\UserCreatedEvent;
use App\Application\Events\UserUpdatedEvent;
use App\Application\Events\UserDeletedEvent;
use App\Domain\Contracts\Repositories\UserRepositoryInterface;
use App\Domain\Entities\User;
use App\Domain\ValueObjects\Email;
use App\Domain\ValueObjects\UserId;
use App\Domain\ValueObjects\UserStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function findById(int $id): ?UserDTO
    {
        $userId = UserId::from($id);
        $user = $this->userRepository->findById($userId);

        return $user ? UserDTO::fromEntity($user) : null;
    }

    public function findByEmail(string $email): ?UserDTO
    {
        $user = $this->userRepository->findByEmail($email);

        return $user ? UserDTO::fromEntity($user) : null;
    }

    public function findByEmpresaId(int $empresaId): Collection
    {
        $users = $this->userRepository->findByEmpresaId($empresaId);

        return $users->map(fn($user) => UserDTO::fromEntity($user));
    }

    public function findBySucursalId(int $sucursalId): Collection
    {
        $users = $this->userRepository->findBySucursalId($sucursalId);

        return $users->map(fn($user) => UserDTO::fromEntity($user));
    }

    public function create(UserCreateDTO $userCreateDTO): UserDTO
    {
        $this->validateCreateData($userCreateDTO);

        $userData = [
            'name' => $userCreateDTO->name,
            'email' => $userCreateDTO->email,
            'password' => Hash::make($userCreateDTO->password),
            'status' => $userCreateDTO->status ?? 'active',
            'empresa_id' => $userCreateDTO->empresaId,
            'sucursal_id' => $userCreateDTO->sucursalId,
        ];

        $user = $this->userRepository->create($userData);

        // Assign roles if provided
        if (!empty($userCreateDTO->roleIds)) {
            $this->userRepository->syncRoles($user->getId(), $userCreateDTO->roleIds);
        }

        // Assign permissions if provided
        if (!empty($userCreateDTO->permissionIds)) {
            $this->userRepository->syncPermissions($user->getId(), $userCreateDTO->permissionIds);
        }

        // Reload user with roles and permissions
        $user = $this->userRepository->findById($user->getId());

        $userDTO = UserDTO::fromEntity($user);

        // Dispatch event
        Event::dispatch(new UserCreatedEvent($userDTO));

        Log::info('User created successfully', [
            'user_id' => $user->getId()->getValue(),
            'email' => $user->getEmail()->getValue(),
            'created_by' => auth()->id(),
        ]);

        return $userDTO;
    }

    public function update(int $id, UserUpdateDTO $userUpdateDTO): UserDTO
    {
        $userId = UserId::from($id);
        $existingUser = $this->userRepository->findById($userId);

        if (!$existingUser) {
            throw new \RuntimeException('User not found');
        }

        $this->validateUpdateData($userUpdateDTO, $id);

        $updateData = [];

        if ($userUpdateDTO->name !== null) {
            $updateData['name'] = $userUpdateDTO->name;
        }

        if ($userUpdateDTO->username !== null) {
            $updateData['username'] = $userUpdateDTO->username;
        }

        if ($userUpdateDTO->email !== null) {
            $updateData['email'] = $userUpdateDTO->email;
        }

        if ($userUpdateDTO->password !== null) {
            $updateData['password'] = Hash::make($userUpdateDTO->password);
        }

        if ($userUpdateDTO->status !== null) {
            $updateData['status'] = $userUpdateDTO->status;
        }

        if ($userUpdateDTO->empresaId !== null) {
            $updateData['empresa_id'] = $userUpdateDTO->empresaId;
        }

        if ($userUpdateDTO->sucursalId !== null) {
            $updateData['sucursal_id'] = $userUpdateDTO->sucursalId;
        }

        $this->userRepository->update($userId, $updateData);

        // Update roles if provided
        if ($userUpdateDTO->roleIds !== null) {
            $this->userRepository->syncRoles($userId, $userUpdateDTO->roleIds);
        }

        // Update permissions if provided
        if ($userUpdateDTO->permissionIds !== null) {
            $this->userRepository->syncPermissions($userId, $userUpdateDTO->permissionIds);
        }

        // Reload user
        $user = $this->userRepository->findById($userId);

        $userDTO = UserDTO::fromEntity($user);

        // Dispatch event
        Event::dispatch(new UserUpdatedEvent($userDTO));

        Log::info('User updated successfully', [
            'user_id' => $id,
            'updated_by' => auth()->id(),
            'changes' => array_keys($updateData),
        ]);

        return $userDTO;
    }

    public function delete(int $id): bool
    {
        $userId = UserId::from($id);
        $user = $this->userRepository->findById($userId);

        if (!$user) {
            throw new \RuntimeException('User not found');
        }

        $result = $this->userRepository->delete($userId);

        if ($result) {
            Event::dispatch(new UserDeletedEvent($id));

            Log::info('User deleted successfully', [
                'user_id' => $id,
                'deleted_by' => auth()->id(),
            ]);
        }

        return $result;
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $paginator = $this->userRepository->paginate($perPage, $filters);

        // Transform items to DTOs
        $paginator->getCollection()->transform(function ($user) {
            return UserDTO::fromEntity($user);
        });

        return $paginator;
    }

    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        $paginator = $this->userRepository->search($query, $perPage);

        // Transform items to DTOs
        $paginator->getCollection()->transform(function ($user) {
            return UserDTO::fromEntity($user);
        });

        return $paginator;
    }

    public function attachRole(int $userId, int $roleId): void
    {
        $this->userRepository->attachRole(UserId::from($userId), $roleId);

        Log::info('Role attached to user', [
            'user_id' => $userId,
            'role_id' => $roleId,
            'attached_by' => auth()->id(),
        ]);
    }

    public function detachRole(int $userId, int $roleId): void
    {
        $this->userRepository->detachRole(UserId::from($userId), $roleId);

        Log::info('Role detached from user', [
            'user_id' => $userId,
            'role_id' => $roleId,
            'detached_by' => auth()->id(),
        ]);
    }

    public function syncRoles(int $userId, array $roleIds): void
    {
        $this->userRepository->syncRoles(UserId::from($userId), $roleIds);

        Log::info('User roles synchronized', [
            'user_id' => $userId,
            'role_ids' => $roleIds,
            'synced_by' => auth()->id(),
        ]);
    }

    private function validateCreateData(UserCreateDTO $dto): void
    {
        $validator = Validator::make([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $dto->password,
            'status' => $dto->status,
            'empresa_id' => $dto->empresaId,
            'sucursal_id' => $dto->sucursalId,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'status' => 'sometimes|string|in:active,inactive,pending,suspended',
            'empresa_id' => 'nullable|integer|exists:empresas,id',
            'sucursal_id' => 'nullable|integer|exists:sucursales,id',
        ], [
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    private function validateUpdateData(UserUpdateDTO $dto, int $userId): void
    {
        $rules = [];
        $data = [];

        if ($dto->name !== null) {
            $data['name'] = $dto->name;
            $rules['name'] = 'string|max:255';
        }

        if ($dto->username !== null) {
            $data['username'] = $dto->username;
            $rules['username'] = 'string|max:255|unique:users,username,' . $userId;
        }

        if ($dto->email !== null) {
            $data['email'] = $dto->email;
            $rules['email'] = 'email|unique:users,email,' . $userId;
        }

        if ($dto->password !== null) {
            $data['password'] = $dto->password;
            $rules['password'] = 'string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/';
        }

        if ($dto->status !== null) {
            $data['status'] = $dto->status;
            $rules['status'] = 'string|in:active,inactive,pending,suspended';
        }

        if ($dto->empresaId !== null) {
            $data['empresa_id'] = $dto->empresaId;
            $rules['empresa_id'] = 'integer|exists:empresas,id';
        }

        if ($dto->sucursalId !== null) {
            $data['sucursal_id'] = $dto->sucursalId;
            $rules['sucursal_id'] = 'integer|exists:sucursales,id';
        }

        if (!empty($rules)) {
            $validator = Validator::make($data, $rules, [
                'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number',
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        }
    }
}
