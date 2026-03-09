<?php

namespace Flute\Modules\API\Controllers;

use Exception;
use Flute\Core\Router\Annotations\Route;
use Flute\Core\Support\FluteRequest;
use Flute\Modules\API\Services\NavbarItemService;

#[Route("/navbar-items", name: "api.navbar-items")]
class NavbarItemController extends APIController
{
    private NavbarItemService $navbarItemService;

    public function __construct(NavbarItemService $navbarItemService)
    {
        $this->navbarItemService = $navbarItemService;
    }

    #[Route("", name: ".list", methods: ["GET"])]
    public function list(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.navbar')) {
            return $this->forbiddenResponse();
        }

        $navbarItems = $this->navbarItemService->getAllNavbarItems();

        return $this->json([
            'navbar_items' => array_map(
                fn ($item) => $this->navbarItemService->formatNavbarItemData($item),
                $navbarItems
            ),
        ]);
    }

    #[Route("/{id}", name: ".get", methods: ["GET"], where: ["id" => "\d+"])]
    public function get(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.navbar')) {
            return $this->forbiddenResponse();
        }

        $navbarItem = $this->navbarItemService->getNavbarItemById($id);

        if (!$navbarItem) {
            return $this->notFoundResponse('Navbar Item');
        }

        return $this->json([
            'navbar_item' => $this->navbarItemService->formatNavbarItemData($navbarItem, true),
        ]);
    }

    #[Route("", name: ".create", methods: ["POST"])]
    public function create(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.navbar')) {
            return $this->forbiddenResponse();
        }

        $data = $request->input();

        try {
            $navbarItem = $this->navbarItemService->createNavbarItem($data);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Navbar Item created successfully',
            'navbar_item' => $this->navbarItemService->formatNavbarItemData($navbarItem),
        ], 201);
    }

    #[Route("/{id}", name: ".update", methods: ["PUT"], where: ["id" => "\d+"])]
    public function update(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.navbar')) {
            return $this->forbiddenResponse();
        }

        $navbarItem = $this->navbarItemService->getNavbarItemById($id);

        if (!$navbarItem) {
            return $this->notFoundResponse('Navbar Item');
        }

        $data = $request->input();

        try {
            $this->navbarItemService->updateNavbarItem($navbarItem, $data);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Navbar Item updated successfully',
            'navbar_item' => $this->navbarItemService->formatNavbarItemData($navbarItem),
        ]);
    }

    #[Route("/{id}", name: ".delete", methods: ["DELETE"], where: ["id" => "\d+"])]
    public function delete(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.navbar')) {
            return $this->forbiddenResponse();
        }

        $navbarItem = $this->navbarItemService->getNavbarItemById($id);

        if (!$navbarItem) {
            return $this->notFoundResponse('Navbar Item');
        }

        try {
            $this->navbarItemService->deleteNavbarItem($navbarItem);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Navbar Item deleted successfully',
        ]);
    }

    #[Route("/{id}/roles", name: ".roles", methods: ["PUT"], where: ["id" => "\d+"])]
    public function updateRoles(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.navbar')) {
            return $this->forbiddenResponse();
        }

        $navbarItem = $this->navbarItemService->getNavbarItemById($id);

        if (!$navbarItem) {
            return $this->notFoundResponse('Navbar Item');
        }

        $data = $request->input();
        $roleIds = $data['role_ids'] ?? [];

        try {
            $this->navbarItemService->updateNavbarItemRoles($navbarItem, $roleIds);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Navbar Item roles updated successfully',
            'navbar_item' => $this->navbarItemService->formatNavbarItemData($navbarItem, true),
        ]);
    }

    #[Route("/reorder", name: ".reorder", methods: ["POST"])]
    public function reorder(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.navbar')) {
            return $this->forbiddenResponse();
        }

        $data = $request->input();
        $orderData = $data['order'] ?? [];

        if (empty($orderData)) {
            return $this->json([
                'error' => 'No order data provided',
            ], 422);
        }

        try {
            $this->navbarItemService->reorderNavbarItems($orderData);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Navbar Items reordered successfully',
        ]);
    }
}
