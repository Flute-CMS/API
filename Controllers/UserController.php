<?php

namespace Flute\Modules\API\Controllers;

use DateTimeImmutable;
use Exception;
use Flute\Core\Router\Annotations\Route;
use Flute\Core\Support\FluteRequest;
use Flute\Modules\API\Services\UserService;

#[Route("/users", name: "api.users")]
class UserController extends APIController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    #[Route("", name: ".list", methods: ["GET"])]
    public function list(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.users')) {
            return $this->forbiddenResponse();
        }

        $users = $this->userService->getAllUsers();

        return $this->json([
            'users' => array_map(
                fn ($user) => $this->userService->formatUserData($user),
                $users
            ),
        ]);
    }

    #[Route("/search", name: ".search", methods: ["GET"])]
    public function search(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.users')) {
            return $this->forbiddenResponse();
        }

        $query = $request->get('q', '');

        if (strlen($query) < 3) {
            return $this->json([
                'error' => 'Search query must be at least 3 characters long',
            ], 422);
        }

        $users = $this->userService->searchUsers($query);

        return $this->json([
            'users' => array_map(
                fn ($user) => $this->userService->formatUserData($user),
                $users
            ),
        ]);
    }

    #[Route("/{id}", name: ".get", methods: ["GET"], where: ["id" => "\d+"])]
    public function get(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.users')) {
            return $this->forbiddenResponse();
        }

        $user = $this->userService->getUserById($id);

        if (!$user) {
            return $this->notFoundResponse('User');
        }

        return $this->json([
            'user' => $this->userService->formatUserData($user, true),
        ]);
    }

    #[Route("", name: ".create", methods: ["POST"])]
    public function create(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.users')) {
            return $this->forbiddenResponse();
        }

        $data = $request->input();

        try {
            $user = $this->userService->createUser($data);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'User created successfully',
            'user' => $this->userService->formatUserData($user),
        ], 201);
    }

    #[Route("/{id}", name: ".update", methods: ["PUT"], where: ["id" => "\d+"])]
    public function update(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.users')) {
            return $this->forbiddenResponse();
        }

        $user = $this->userService->getUserById($id);

        if (!$user) {
            return $this->notFoundResponse('User');
        }

        $data = $request->input();

        try {
            $this->userService->updateUser($user, $data);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'User updated successfully',
            'user' => $this->userService->formatUserData($user),
        ]);
    }

    #[Route("/{id}/block", name: ".block", methods: ["POST"], where: ["id" => "\d+"])]
    public function block(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.users')) {
            return $this->forbiddenResponse();
        }

        $user = $this->userService->getUserById($id);

        if (!$user) {
            return $this->notFoundResponse('User');
        }

        $data = $request->input();
        $reason = $data['reason'] ?? 'No reason provided';
        $until = isset($data['until']) ? new DateTimeImmutable($data['until']) : null;

        $this->userService->blockUser($user, $reason, $until);

        return $this->json(['message' => 'User blocked successfully']);
    }

    #[Route("/{id}/unblock", name: ".unblock", methods: ["POST"], where: ["id" => "\d+"])]
    public function unblock(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.users')) {
            return $this->forbiddenResponse();
        }

        $user = $this->userService->getUserById($id);

        if (!$user) {
            return $this->notFoundResponse('User');
        }

        $this->userService->unblockUser($user);

        return $this->json(['message' => 'User unblocked successfully']);
    }

    #[Route("/{id}/roles", name: ".roles", methods: ["PUT"], where: ["id" => "\d+"])]
    public function updateRoles(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.users')) {
            return $this->forbiddenResponse();
        }

        $user = $this->userService->getUserById($id);

        if (!$user) {
            return $this->notFoundResponse('User');
        }

        $data = $request->input();
        $roleIds = $data['role_ids'] ?? [];

        try {
            $this->userService->updateUserRoles($user, $roleIds);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'User roles updated successfully',
            'user' => $this->userService->formatUserData($user, true),
        ]);
    }

    #[Route("/{id}/social-networks", name: ".social-networks.list", methods: ["GET"], where: ["id" => "\d+"])]
    public function getSocialNetworks(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.users')) {
            return $this->forbiddenResponse();
        }

        $user = $this->userService->getUserById($id);

        if (!$user) {
            return $this->notFoundResponse('User');
        }

        return $this->json([
            'social_networks' => $this->userService->getUserSocialNetworks($user),
        ]);
    }

    #[Route("/{id}/social-networks", name: ".social-networks.add", methods: ["POST"], where: ["id" => "\d+"])]
    public function addSocialNetwork(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.users')) {
            return $this->forbiddenResponse();
        }

        $user = $this->userService->getUserById($id);

        if (!$user) {
            return $this->notFoundResponse('User');
        }

        $data = $request->input();

        try {
            $socialNetwork = $this->userService->addUserSocialNetwork($user, $data);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Social network added successfully',
            'social_network' => [
                'id' => $socialNetwork->id,
                'network' => $socialNetwork->socialNetwork->key,
                'profile_url' => $socialNetwork->url,
            ],
        ], 201);
    }

    #[Route("/{userId}/social-networks/{networkId}", name: ".social-networks.remove", methods: ["DELETE"], where: ["userId" => "\d+", "networkId" => "\d+"])]
    public function removeSocialNetwork(FluteRequest $request, int $userId, int $networkId)
    {
        if (!$this->requirePermission($request, 'admin.users')) {
            return $this->forbiddenResponse();
        }

        $user = $this->userService->getUserById($userId);

        if (!$user) {
            return $this->notFoundResponse('User');
        }

        try {
            $this->userService->removeUserSocialNetwork($user, $networkId);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Social network removed successfully',
        ]);
    }

    #[Route("/{id}/devices", name: ".devices", methods: ["GET"], where: ["id" => "\d+"])]
    public function getDevices(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.users')) {
            return $this->forbiddenResponse();
        }

        $user = $this->userService->getUserById($id);

        if (!$user) {
            return $this->notFoundResponse('User');
        }

        return $this->json([
            'devices' => $this->userService->getUserDevices($user),
        ]);
    }

    #[Route("/{userId}/devices/{deviceId}", name: ".devices.remove", methods: ["DELETE"], where: ["userId" => "\d+", "deviceId" => "\d+"])]
    public function removeDevice(FluteRequest $request, int $userId, int $deviceId)
    {
        if (!$this->requirePermission($request, 'admin.users')) {
            return $this->forbiddenResponse();
        }

        $user = $this->userService->getUserById($userId);

        if (!$user) {
            return $this->notFoundResponse('User');
        }

        try {
            $this->userService->removeUserDevice($user, $deviceId);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Device removed successfully',
        ]);
    }

    #[Route("/{id}/delete", name: ".delete", methods: ["DELETE"], where: ["id" => "\d+"])]
    public function delete(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.users')) {
            return $this->forbiddenResponse();
        }

        $user = $this->userService->getUserById($id);

        if (!$user) {
            return $this->notFoundResponse('User');
        }

        try {
            $this->userService->deleteUser($user);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'User deleted successfully',
        ]);
    }
}
