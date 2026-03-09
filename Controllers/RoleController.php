<?php

namespace Flute\Modules\API\Controllers;

use Exception;
use Flute\Core\Router\Annotations\Route;
use Flute\Core\Support\FluteRequest;
use Flute\Modules\API\Services\RoleService;

#[Route("/roles", name: "api.roles")]
class RoleController extends APIController
{
    private RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    #[Route("", name: ".list", methods: ["GET"])]
    public function list(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.roles')) {
            return $this->forbiddenResponse();
        }

        $roles = $this->roleService->getAllRoles();

        return $this->json([
            'roles' => array_map(
                fn ($role) => $this->roleService->formatRoleData($role),
                $roles
            ),
        ]);
    }

    #[Route("/{id}", name: ".get", methods: ["GET"], where: ["id" => "\d+"])]
    public function get(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.roles')) {
            return $this->forbiddenResponse();
        }

        $role = $this->roleService->getRoleById($id);

        if (!$role) {
            return $this->notFoundResponse('Role');
        }

        return $this->json([
            'role' => $this->roleService->formatRoleData($role, true),
        ]);
    }

    #[Route("", name: ".create", methods: ["POST"])]
    public function create(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.roles')) {
            return $this->forbiddenResponse();
        }

        $data = $request->input();

        try {
            $role = $this->roleService->createRole($data);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Role created successfully',
            'role' => $this->roleService->formatRoleData($role),
        ], 201);
    }

    #[Route("/{id}", name: ".update", methods: ["PUT"], where: ["id" => "\d+"])]
    public function update(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.roles')) {
            return $this->forbiddenResponse();
        }

        $role = $this->roleService->getRoleById($id);

        if (!$role) {
            return $this->notFoundResponse('Role');
        }

        $data = $request->input();
        $this->roleService->updateRole($role, $data);

        return $this->json([
            'message' => 'Role updated successfully',
            'role' => $this->roleService->formatRoleData($role),
        ]);
    }

    #[Route("/{id}", name: ".delete", methods: ["DELETE"], where: ["id" => "\d+"])]
    public function delete(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.roles')) {
            return $this->forbiddenResponse();
        }

        $role = $this->roleService->getRoleById($id);

        if (!$role) {
            return $this->notFoundResponse('Role');
        }

        $this->roleService->deleteRole($role);

        return $this->json([
            'message' => 'Role deleted successfully',
        ]);
    }

    #[Route("/{id}/permissions", name: ".permissions", methods: ["PUT"], where: ["id" => "\d+"])]
    public function updatePermissions(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.roles')) {
            return $this->forbiddenResponse();
        }

        $role = $this->roleService->getRoleById($id);

        if (!$role) {
            return $this->notFoundResponse('Role');
        }

        $data = $request->input();
        $permissionIds = $data['permission_ids'] ?? [];

        try {
            $this->roleService->updateRolePermissions($role, $permissionIds);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Role permissions updated successfully',
            'role' => $this->roleService->formatRoleData($role, true),
        ]);
    }
}
