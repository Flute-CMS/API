<?php

namespace Flute\Modules\API\Controllers;

use Flute\Core\App;
use Flute\Core\Database\Entities\ApiKey;
use Flute\Core\ModulesManager\ModuleManager;
use Flute\Core\Router\Annotations\Middleware;
use Flute\Core\Router\Annotations\Route;
use Flute\Core\Support\BaseController;
use Flute\Modules\API\Middleware\ApiAuthMiddleware;
use Symfony\Component\HttpFoundation\Request;

#[Route("/api", name: "api.index", methods: ["GET"])]
#[Middleware(ApiAuthMiddleware::class)]
class APIController extends BaseController
{
    #[Route("/status", name: "api.status", methods: ["GET"])]
    public function status()
    {
        /**
         * @var ModuleManager
         */
        $moduleManager = app(ModuleManager::class);

        return $this->json([
            'message' => 'Welcome to Flute CMS API',
            'flute_version' => App::VERSION,
            'api_version' => $moduleManager->getModule('API')->installedVersion,
        ]);
    }

    #[Route("/permissions", name: "api.permissions", methods: ["GET"])]
    public function permissions(Request $request)
    {
        /** @var ApiKey $apiKey */
        $apiKey = $request->attributes->get('api_key');

        return $this->json([
            'permissions' => $apiKey->getPermissions(),
        ]);
    }

    protected function requirePermission(Request $request, string $permission): bool
    {
        /** @var ApiKey $apiKey */
        $apiKey = $request->attributes->get('api_key');

        return $apiKey->hasPermissionByName($permission);
    }

    protected function forbiddenResponse()
    {
        return $this->json(['message' => 'Forbidden'], 403);
    }

    protected function notFoundResponse(string $entity = 'Resource')
    {
        return $this->json(['message' => $entity . ' not found'], 404);
    }
}
