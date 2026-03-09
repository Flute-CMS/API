<?php

namespace Flute\Modules\API\Middleware;

use Closure;
use Flute\Core\Database\Entities\ApiKey;
use Flute\Core\Support\BaseMiddleware;
use Flute\Core\Support\FluteRequest;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthMiddleware extends BaseMiddleware
{
    public function handle(FluteRequest $request, Closure $next, ...$args): Response
    {
        $apiKey = $request->headers->get('X-API-Key');

        if (!$apiKey) {
            return response()->json(['message' => 'API key is required'], 401);
        }

        $keys = ApiKey::findAll();
        $key = null;

        foreach ($keys as $apiKeyEntity) {
            if ($apiKeyEntity->key === $apiKey) {
                $key = $apiKeyEntity;

                break;
            }
        }

        if (!$key) {
            return response()->json(['message' => 'Invalid API key'], 401);
        }

        $key->updateLastUsed();

        $request->attributes->set('api_key', $key);

        return $next($request);
    }
}
