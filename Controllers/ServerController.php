<?php

namespace Flute\Modules\API\Controllers;

use Exception;
use Flute\Core\Router\Annotations\Route;
use Flute\Core\Support\FluteRequest;
use Flute\Modules\API\Services\ServerService;

#[Route("/servers", name: "api.servers")]
class ServerController extends APIController
{
    private ServerService $serverService;

    public function __construct(ServerService $serverService)
    {
        $this->serverService = $serverService;
    }

    #[Route("", name: ".list", methods: ["GET"])]
    public function list(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.servers')) {
            return $this->forbiddenResponse();
        }

        $servers = $this->serverService->getAllServers();

        return $this->json([
            'servers' => array_map(
                fn ($server) => $this->serverService->formatServerData($server),
                $servers
            ),
        ]);
    }

    #[Route("/{id}", name: ".get", methods: ["GET"], where: ["id" => "\d+"])]
    public function get(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.servers')) {
            return $this->forbiddenResponse();
        }

        $server = $this->serverService->getServerById($id);

        if (!$server) {
            return $this->notFoundResponse('Server');
        }

        return $this->json([
            'server' => $this->serverService->formatServerData($server, true),
        ]);
    }

    #[Route("", name: ".create", methods: ["POST"])]
    public function create(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.servers')) {
            return $this->forbiddenResponse();
        }

        $data = $request->input();

        try {
            $server = $this->serverService->createServer($data);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Server created successfully',
            'server' => $this->serverService->formatServerData($server),
        ], 201);
    }

    #[Route("/{id}", name: ".update", methods: ["PUT"], where: ["id" => "\d+"])]
    public function update(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.servers')) {
            return $this->forbiddenResponse();
        }

        $server = $this->serverService->getServerById($id);

        if (!$server) {
            return $this->notFoundResponse('Server');
        }

        $data = $request->input();

        try {
            $this->serverService->updateServer($server, $data);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Server updated successfully',
            'server' => $this->serverService->formatServerData($server),
        ]);
    }

    #[Route("/{id}", name: ".delete", methods: ["DELETE"], where: ["id" => "\d+"])]
    public function delete(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.servers')) {
            return $this->forbiddenResponse();
        }

        $server = $this->serverService->getServerById($id);

        if (!$server) {
            return $this->notFoundResponse('Server');
        }

        $this->serverService->deleteServer($server);

        return $this->json([
            'message' => 'Server deleted successfully',
        ]);
    }

    #[Route("/{id}/toggle", name: ".toggle", methods: ["POST"], where: ["id" => "\d+"])]
    public function toggle(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.servers')) {
            return $this->forbiddenResponse();
        }

        $server = $this->serverService->getServerById($id);

        if (!$server) {
            return $this->notFoundResponse('Server');
        }

        $this->serverService->toggleServer($server);

        return $this->json([
            'message' => 'Server ' . ($server->enabled ? 'enabled' : 'disabled') . ' successfully',
            'enabled' => $server->enabled,
        ]);
    }
}
