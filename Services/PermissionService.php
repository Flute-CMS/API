<?php

namespace Flute\Modules\API\Services;

use Flute\Core\Database\Entities\Permission;
use Flute\Core\Validator\FluteValidator;
use InvalidArgumentException;

class PermissionService
{
    private FluteValidator $validator;

    public function __construct(FluteValidator $validator)
    {
        $this->validator = $validator;
    }

    public function validatePermissionData(array $data): bool
    {
        $rules = [
            'name' => 'required|string|min-str-len:3|max-str-len:100',
            'desc' => 'required|string|min-str-len:3|max-str-len:255',
        ];

        return $this->validator->validate($data, $rules);
    }

    public function getAllPermissions(): array
    {
        return Permission::findAll();
    }

    public function getPermissionById(int $id): ?Permission
    {
        return Permission::findByPK($id);
    }

    public function createPermission(array $data): Permission
    {
        if (!$this->validatePermissionData($data)) {
            throw new InvalidArgumentException('Invalid permission data: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        $permission = new Permission();
        $permission->name = $data['name'];
        $permission->desc = $data['desc'];

        $permission->saveOrFail();

        return $permission;
    }

    public function updatePermission(Permission $permission, array $data): void
    {
        if (!$this->validatePermissionData($data)) {
            throw new InvalidArgumentException('Invalid permission data: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        $permission->name = $data['name'];
        $permission->desc = $data['desc'];

        $permission->saveOrFail();
    }

    public function deletePermission(Permission $permission): void
    {
        $permission->delete();
    }

    public function formatPermissionData(Permission $permission): array
    {
        return [
            'id' => $permission->id,
            'name' => $permission->name,
            'description' => $permission->desc,
        ];
    }
}
