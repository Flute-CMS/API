<?php

namespace Flute\Modules\API\Services;

use Flute\Core\Database\Entities\Permission;
use Flute\Core\Database\Entities\Role;
use Flute\Core\Validator\FluteValidator;
use InvalidArgumentException;

class RoleService
{
    private FluteValidator $validator;

    public function __construct(FluteValidator $validator)
    {
        $this->validator = $validator;
    }

    public function validateRoleData(array $data): bool
    {
        $rules = [
            'name' => 'required|string|min-str-len:3|max-str-len:100',
            'color' => 'string|regex:/^#[0-9a-fA-F]{6}$/|max-str-len:7',
            'priority' => 'integer|min:0',
        ];

        return $this->validator->validate($data, $rules);
    }

    public function getAllRoles(): array
    {
        return Role::findAll();
    }

    public function getRoleById(int $id): ?Role
    {
        return Role::findByPK($id);
    }

    public function createRole(array $data): Role
    {
        if (!$this->validateRoleData($data)) {
            throw new InvalidArgumentException('Invalid role data: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        $role = new Role();
        $role->name = $data['name'];
        if (isset($data['color'])) {
            $role->color = $data['color'];
        }
        if (isset($data['priority'])) {
            $role->priority = (int)$data['priority'];
        }

        $role->saveOrFail();

        return $role;
    }

    public function updateRole(Role $role, array $data): void
    {
        if (!$this->validateRoleData($data)) {
            throw new InvalidArgumentException('Invalid role data: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        $role->name = $data['name'];
        if (isset($data['color'])) {
            $role->color = $data['color'];
        }
        if (isset($data['priority'])) {
            $role->priority = (int)$data['priority'];
        }

        $role->saveOrFail();
    }

    public function deleteRole(Role $role): void
    {
        $role->delete();
    }

    public function updateRolePermissions(Role $role, array $permissionIds): void
    {
        $role->clearPermissions();

        foreach ($permissionIds as $id) {
            $permission = Permission::findByPK($id);
            if (!$permission) {
                throw new InvalidArgumentException("Permission with ID {$id} not found");
            }
            $role->addPermission($permission);
        }

        $role->saveOrFail();
    }

    public function formatRoleData(Role $role, bool $detailed = false): array
    {
        $data = [
            'id' => $role->id,
            'name' => $role->name,
            'color' => $role->color,
            'priority' => $role->priority,
        ];

        if ($detailed) {
            $data['permissions'] = array_map(static fn ($permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
            ], $role->permissions);
        }

        return $data;
    }
}
