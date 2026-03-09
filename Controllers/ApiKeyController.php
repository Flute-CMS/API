<?php

namespace Flute\Modules\API\Controllers;

use Exception;
use Flute\Core\Router\Annotations\Route;
use Flute\Core\Support\FluteRequest;
use Flute\Modules\API\Services\ApiKeyService;

#[Route("/api-keys", name: "api.api-keys")]
class ApiKeyController extends APIController
{
    private ApiKeyService $apiKeyService;

    public function __construct(ApiKeyService $apiKeyService)
    {
        $this->apiKeyService = $apiKeyService;
    }

    #[Route("", name: ".list", methods: ["GET"])]
    public function list(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.api-keys')) {
            return $this->forbiddenResponse();
        }

        $apiKeys = $this->apiKeyService->getAllApiKeys();

        return $this->json([
            'api_keys' => array_map(
                fn ($apiKey) => $this->apiKeyService->formatApiKeyData($apiKey),
                $apiKeys
            ),
        ]);
    }

    #[Route("/{id}", name: ".get", methods: ["GET"], where: ["id" => "\d+"])]
    public function get(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.api-keys')) {
            return $this->forbiddenResponse();
        }

        $apiKey = $this->apiKeyService->getApiKeyById($id);

        if (!$apiKey) {
            return $this->notFoundResponse('API Key');
        }

        return $this->json([
            'api_key' => $this->apiKeyService->formatApiKeyData($apiKey, true),
        ]);
    }

    #[Route("", name: ".create", methods: ["POST"])]
    public function create(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.api-keys')) {
            return $this->forbiddenResponse();
        }

        $data = $request->input();

        try {
            $apiKey = $this->apiKeyService->createApiKey($data);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'API Key создан успешно',
            'api_key' => $this->apiKeyService->formatApiKeyData($apiKey),
        ], 201);
    }

    #[Route("/{id}", name: ".update", methods: ["PUT"], where: ["id" => "\d+"])]
    public function update(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.api-keys')) {
            return $this->forbiddenResponse();
        }

        $apiKey = $this->apiKeyService->getApiKeyById($id);

        if (!$apiKey) {
            return $this->notFoundResponse('API Key');
        }

        $data = $request->input();

        try {
            $this->apiKeyService->updateApiKey($apiKey, $data);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'API Key обновлен успешно',
            'api_key' => $this->apiKeyService->formatApiKeyData($apiKey),
        ]);
    }

    #[Route("/{id}", name: ".delete", methods: ["DELETE"], where: ["id" => "\d+"])]
    public function delete(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.api-keys')) {
            return $this->forbiddenResponse();
        }

        $apiKey = $this->apiKeyService->getApiKeyById($id);

        if (!$apiKey) {
            return $this->notFoundResponse('API Key');
        }

        try {
            $this->apiKeyService->deleteApiKey($apiKey);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'API Key удален успешно',
        ]);
    }

    #[Route("/{id}/regenerate", name: ".regenerate", methods: ["POST"], where: ["id" => "\d+"])]
    public function regenerate(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.api-keys')) {
            return $this->forbiddenResponse();
        }

        $apiKey = $this->apiKeyService->getApiKeyById($id);

        if (!$apiKey) {
            return $this->notFoundResponse('API Key');
        }

        try {
            $this->apiKeyService->regenerateKey($apiKey);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'API Key перегенерирован успешно',
            'api_key' => $this->apiKeyService->formatApiKeyData($apiKey),
        ]);
    }

    #[Route("/{id}/permissions", name: ".permissions", methods: ["PUT"], where: ["id" => "\d+"])]
    public function updatePermissions(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.api-keys')) {
            return $this->forbiddenResponse();
        }

        $apiKey = $this->apiKeyService->getApiKeyById($id);

        if (!$apiKey) {
            return $this->notFoundResponse('API Key');
        }

        $data = $request->input();
        $permissionIds = $data['permission_ids'] ?? [];

        try {
            $this->apiKeyService->updateApiKeyPermissions($apiKey, $permissionIds);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Разрешения API Key обновлены успешно',
            'api_key' => $this->apiKeyService->formatApiKeyData($apiKey, true),
        ]);
    }
}
