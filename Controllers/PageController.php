<?php

namespace Flute\Modules\API\Controllers;

use Exception;
use Flute\Core\Router\Annotations\Route;
use Flute\Core\Support\FluteRequest;
use Flute\Modules\API\Services\PageService;

#[Route("/flute-pages", name: "api.pages")]
class PageController extends APIController
{
    private PageService $pageService;

    public function __construct(PageService $pageService)
    {
        $this->pageService = $pageService;
    }

    #[Route("", name: ".list", methods: ["GET"])]
    public function list(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.pages')) {
            return $this->forbiddenResponse();
        }

        $pages = $this->pageService->getAllPages();

        return $this->json([
            'pages' => array_map(
                fn ($page) => $this->pageService->formatPageData($page),
                $pages
            ),
        ]);
    }

    #[Route("/{id}", name: ".get", methods: ["GET"], where: ["id" => "\d+"])]
    public function get(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.pages')) {
            return $this->forbiddenResponse();
        }

        $page = $this->pageService->getPageById($id);

        if (!$page) {
            return $this->notFoundResponse('Page');
        }

        return $this->json([
            'page' => $this->pageService->formatPageData($page, true),
        ]);
    }

    #[Route("", name: ".create", methods: ["POST"])]
    public function create(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.pages')) {
            return $this->forbiddenResponse();
        }

        $data = $request->input();

        try {
            $page = $this->pageService->createPage($data);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Page created successfully',
            'page' => $this->pageService->formatPageData($page),
        ], 201);
    }

    #[Route("/{id}", name: ".update", methods: ["PUT"], where: ["id" => "\d+"])]
    public function update(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.pages')) {
            return $this->forbiddenResponse();
        }

        $page = $this->pageService->getPageById($id);

        if (!$page) {
            return $this->notFoundResponse('Page');
        }

        $data = $request->input();
        $this->pageService->updatePage($page, $data);

        return $this->json([
            'message' => 'Page updated successfully',
            'page' => $this->pageService->formatPageData($page),
        ]);
    }

    #[Route("/{id}", name: ".delete", methods: ["DELETE"], where: ["id" => "\d+"])]
    public function delete(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.pages')) {
            return $this->forbiddenResponse();
        }

        $page = $this->pageService->getPageById($id);

        if (!$page) {
            return $this->notFoundResponse('Page');
        }

        $this->pageService->deletePage($page);

        return $this->json([
            'message' => 'Page deleted successfully',
        ]);
    }

    #[Route("/{id}/blocks", name: ".blocks", methods: ["GET"], where: ["id" => "\d+"])]
    public function getBlocks(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.pages')) {
            return $this->forbiddenResponse();
        }

        $page = $this->pageService->getPageById($id);

        if (!$page) {
            return $this->notFoundResponse('Page');
        }

        return $this->json([
            'blocks' => $this->pageService->getPageBlocks($page),
        ]);
    }

    #[Route("/{id}/permissions", name: ".permissions", methods: ["PUT"], where: ["id" => "\d+"])]
    public function updatePermissions(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.pages')) {
            return $this->forbiddenResponse();
        }

        $page = $this->pageService->getPageById($id);

        if (!$page) {
            return $this->notFoundResponse('Page');
        }

        $data = $request->input();
        $permissionIds = $data['permission_ids'] ?? [];

        try {
            $this->pageService->updatePagePermissions($page, $permissionIds);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Page permissions updated successfully',
            'page' => $this->pageService->formatPageData($page, true),
        ]);
    }
}
