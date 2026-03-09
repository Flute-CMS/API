<?php

namespace Flute\Modules\API\Controllers;

use Exception;
use Flute\Core\Router\Annotations\Route;
use Flute\Core\Support\FluteRequest;
use Flute\Modules\API\Services\PermissionService;

#[Route("/permissions", name: "api.permissions")]
class PermissionController extends APIController
{
    private PermissionService $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    #[Route("", name: ".list", methods: ["GET"])]
    public function list(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.permissions')) {
            return $this->forbiddenResponse();
        }

        $permissions = $this->permissionService->getAllPermissions();

        return $this->json([
            'permissions' => array_map(
                fn ($permission) => $this->permissionService->formatPermissionData($permission),
                $permissions
            ),
        ]);
    }

    #[Route("/{id}", name: ".get", methods: ["GET"], where: ["id" => "\d+"])]
    public function get(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.permissions')) {
            return $this->forbiddenResponse();
        }

        $permission = $this->permissionService->getPermissionById($id);

        if (!$permission) {
            return $this->notFoundResponse('Permission');
        }

        return $this->json([
            'permission' => $this->permissionService->formatPermissionData($permission),
        ]);
    }

    #[Route("", name: ".create", methods: ["POST"])]
    public function create(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.permissions')) {
            return $this->forbiddenResponse();
        }

        $data = $request->input();

        try {
            $permission = $this->permissionService->createPermission($data);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Permission created successfully',
            'permission' => $this->permissionService->formatPermissionData($permission),
        ], 201);
    }

    #[Route("/{id}", name: ".update", methods: ["PUT"], where: ["id" => "\d+"])]
    public function update(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.permissions')) {
            return $this->forbiddenResponse();
        }

        $permission = $this->permissionService->getPermissionById($id);

        if (!$permission) {
            return $this->notFoundResponse('Permission');
        }

        $data = $request->input();
        $this->permissionService->updatePermission($permission, $data);

        return $this->json([
            'message' => 'Permission updated successfully',
            'permission' => $this->permissionService->formatPermissionData($permission),
        ]);
    }

    #[Route("/{id}", name: ".delete", methods: ["DELETE"], where: ["id" => "\d+"])]
    public function delete(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.permissions')) {
            return $this->forbiddenResponse();
        }

        $permission = $this->permissionService->getPermissionById($id);

        if (!$permission) {
            return $this->notFoundResponse('Permission');
        }

        $this->permissionService->deletePermission($permission);

        return $this->json([
            'message' => 'Permission deleted successfully',
        ]);
    }
}
