<?php

namespace Flute\Modules\API\Controllers;

use Exception;
use Flute\Core\Router\Annotations\Route;
use Flute\Core\Support\FluteRequest;
use Flute\Modules\API\Services\SocialNetworkService;

#[Route("/social-networks", name: "api.social-networks")]
class SocialNetworkController extends APIController
{
    private SocialNetworkService $socialNetworkService;

    public function __construct(SocialNetworkService $socialNetworkService)
    {
        $this->socialNetworkService = $socialNetworkService;
    }

    #[Route("", name: ".list", methods: ["GET"])]
    public function list(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.social-networks')) {
            return $this->forbiddenResponse();
        }

        $socialNetworks = $this->socialNetworkService->getAllSocialNetworks();

        return $this->json([
            'social_networks' => array_map(
                fn ($network) => $this->socialNetworkService->formatSocialNetworkData($network),
                $socialNetworks
            ),
        ]);
    }

    #[Route("/{networkId}/users", name: ".users", methods: ["GET"], where: ["networkId" => "\d+"])]
    public function getUsers(FluteRequest $request, int $networkId)
    {
        if (!$this->requirePermission($request, 'admin.social-networks')) {
            return $this->forbiddenResponse();
        }

        $socialNetwork = $this->socialNetworkService->getSocialNetworkById($networkId);

        if (!$socialNetwork) {
            return $this->notFoundResponse('Social Network');
        }

        $users = $this->socialNetworkService->getUsersByNetwork($socialNetwork);

        return $this->json([
            'users' => $users,
        ]);
    }

    #[Route("/{id}", name: ".get", methods: ["GET"], where: ["id" => "\d+"])]
    public function get(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.social-networks')) {
            return $this->forbiddenResponse();
        }

        $socialNetwork = $this->socialNetworkService->getSocialNetworkById($id);

        if (!$socialNetwork) {
            return $this->notFoundResponse('Social Network');
        }

        return $this->json([
            'social_network' => $this->socialNetworkService->formatSocialNetworkData($socialNetwork, true),
        ]);
    }

    #[Route("", name: ".create", methods: ["POST"])]
    public function create(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.social-networks')) {
            return $this->forbiddenResponse();
        }

        $data = $request->input();

        try {
            $socialNetwork = $this->socialNetworkService->createSocialNetwork($data);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Social Network created successfully',
            'social_network' => $this->socialNetworkService->formatSocialNetworkData($socialNetwork),
        ], 201);
    }

    #[Route("/{id}", name: ".update", methods: ["PUT"], where: ["id" => "\d+"])]
    public function update(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.social-networks')) {
            return $this->forbiddenResponse();
        }

        $socialNetwork = $this->socialNetworkService->getSocialNetworkById($id);

        if (!$socialNetwork) {
            return $this->notFoundResponse('Social Network');
        }

        $data = $request->input();

        try {
            $this->socialNetworkService->updateSocialNetwork($socialNetwork, $data);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Social Network updated successfully',
            'social_network' => $this->socialNetworkService->formatSocialNetworkData($socialNetwork),
        ]);
    }

    #[Route("/{id}", name: ".delete", methods: ["DELETE"], where: ["id" => "\d+"])]
    public function delete(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.social-networks')) {
            return $this->forbiddenResponse();
        }

        $socialNetwork = $this->socialNetworkService->getSocialNetworkById($id);

        if (!$socialNetwork) {
            return $this->notFoundResponse('Social Network');
        }

        try {
            $this->socialNetworkService->deleteSocialNetwork($socialNetwork);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Social Network deleted successfully',
        ]);
    }
}
