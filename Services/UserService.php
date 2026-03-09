<?php

namespace Flute\Modules\API\Services;

use DateTimeImmutable;
use Flute\Core\Database\Entities\Role;
use Flute\Core\Database\Entities\SocialNetwork;
use Flute\Core\Database\Entities\User;
use Flute\Core\Database\Entities\UserBlock;
use Flute\Core\Database\Entities\UserSocialNetwork;
use Flute\Core\Validator\FluteValidator;
use InvalidArgumentException;

class UserService
{
    private FluteValidator $validator;

    public function __construct(FluteValidator $validator)
    {
        $this->validator = $validator;
    }

    public function validateBlockData(array $data): bool
    {
        $rules = [
            'reason' => 'required|string|min-str-len:5|max-str-len:1000',
            'until' => 'nullable|datetime:Y-m-d H:i:s|after:now',
        ];

        return $this->validator->validate($data, $rules);
    }

    public function validateUserData(array $data): bool
    {
        $rules = [
            'name' => 'required|string|min-str-len:3|max-str-len:100',
            'login' => 'required|string|min-str-len:3|max-str-len:50',
            'email' => 'required|email',
            'password' => 'required-if:id,null|string|min-str-len:8',
            'avatar' => 'nullable|string|max-str-len:500',
            'banner' => 'nullable|string|max-str-len:500',
        ];

        return $this->validator->validate($data, $rules);
    }

    public function validateSocialNetworkData(array $data): bool
    {
        $rules = [
            'social_network_id' => 'required|integer|min:1',
            'value' => 'required|string|max-str-len:500',
            'url' => 'nullable|string|max-str-len:500',
            'name' => 'nullable|string|max-str-len:255',
            'hidden' => 'boolean',
        ];

        return $this->validator->validate($data, $rules);
    }

    public function getAllUsers(): array
    {
        return User::findAll();
    }

    public function getUserById(int $id): ?User
    {
        return User::findByPK($id);
    }

    public function createUser(array $data): User
    {
        if (!$this->validateUserData($data)) {
            throw new InvalidArgumentException('Invalid user data: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        $user = new User();
        $user->name = $data['name'];
        $user->login = $data['login'];
        $user->email = $data['email'];
        $user->setPassword($data['password']);

        if (isset($data['avatar'])) {
            $user->avatar = $data['avatar'];
        }

        if (isset($data['banner'])) {
            $user->banner = $data['banner'];
        }

        $user->saveOrFail();

        return $user;
    }

    public function updateUser(User $user, array $data): void
    {
        $data['password'] ??= null;

        if (!$this->validateUserData($data)) {
            throw new InvalidArgumentException('Invalid user data: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        $user->name = $data['name'];
        $user->login = $data['login'];
        $user->email = $data['email'];

        if (!empty($data['password'])) {
            $user->setPassword($data['password']);
        }

        if (isset($data['avatar'])) {
            $user->avatar = $data['avatar'];
        }

        if (isset($data['banner'])) {
            $user->banner = $data['banner'];
        }

        $user->saveOrFail();
    }

    public function blockUser(User $user, string $reason, ?DateTimeImmutable $until = null): void
    {
        if (!$this->validateBlockData(['reason' => $reason, 'until' => $until?->format('Y-m-d H:i:s')])) {
            throw new InvalidArgumentException('Invalid block data: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        $block = new UserBlock();
        $block->reason = $reason;
        $block->blockedUntil = $until;
        $block->isActive = true;

        $user->blocksReceived[] = $block;
        $block->saveOrFail();
        $user->saveOrFail();
    }

    public function unblockUser(User $user): void
    {
        foreach ($user->blocksReceived as $block) {
            $block->isActive = false;
            $block->saveOrFail();
        }
        $user->saveOrFail();
    }

    public function updateUserRoles(User $user, array $roleIds): void
    {
        $user->roles = [];

        foreach ($roleIds as $id) {
            $role = Role::findByPK($id);
            if (!$role) {
                throw new InvalidArgumentException("Role with ID {$id} not found");
            }
            $user->roles[] = $role;
        }

        $user->saveOrFail();
    }

    public function getUserSocialNetworks(User $user): array
    {
        return array_map(static fn ($social) => [
            'id' => $social->id,
            'network' => $social->socialNetwork->key,
            'value' => $social->value,
            'url' => $social->url,
            'name' => $social->name,
            'hidden' => $social->hidden,
            'linked_at' => $social->linkedAt?->format('c'),
        ], $user->socialNetworks);
    }

    public function addUserSocialNetwork(User $user, array $data): UserSocialNetwork
    {
        if (!$this->validateSocialNetworkData($data)) {
            throw new InvalidArgumentException('Invalid social network data: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        if (!isset($data['social_network_id'])) {
            throw new InvalidArgumentException('social_network_id is required');
        }

        $socialNetworkId = $data['social_network_id'];
        $socialNetwork = SocialNetwork::findByPK($socialNetworkId);

        if (!$socialNetwork) {
            throw new InvalidArgumentException("Social network with ID {$socialNetworkId} not found");
        }

        foreach ($user->socialNetworks as $existing) {
            if ($existing->socialNetwork->id === $socialNetworkId) {
                throw new InvalidArgumentException("User already has this social network connected");
            }
        }

        $userSocialNetwork = new UserSocialNetwork();
        $userSocialNetwork->socialNetwork = $socialNetwork;
        $userSocialNetwork->value = $data['value'];
        $userSocialNetwork->url = $data['url'] ?? null;
        $userSocialNetwork->name = $data['name'] ?? null;
        $userSocialNetwork->hidden = $data['hidden'] ?? false;
        $userSocialNetwork->user = $user;

        $userSocialNetwork->saveOrFail();
        $user->socialNetworks[] = $userSocialNetwork;
        $user->saveOrFail();

        return $userSocialNetwork;
    }

    public function removeUserSocialNetwork(User $user, int $networkId): void
    {
        $found = false;

        foreach ($user->socialNetworks as $index => $social) {
            if ($social->id === $networkId) {
                unset($user->socialNetworks[$index]);
                $social->delete();
                $found = true;

                break;
            }
        }

        if (!$found) {
            throw new InvalidArgumentException("Social network with ID {$networkId} not found for this user");
        }

        $user->saveOrFail();
    }

    public function getUserDevices(User $user): array
    {
        return array_map(static fn ($device) => [
            'id' => $device->id,
            'ip' => $device->ip,
            'device_details' => $device->deviceDetails,
        ], $user->userDevices);
    }

    public function removeUserDevice(User $user, int $deviceId): void
    {
        $found = false;

        foreach ($user->userDevices as $index => $device) {
            if ($device->id === $deviceId) {
                unset($user->userDevices[$index]);
                $device->delete();
                $found = true;

                break;
            }
        }

        if (!$found) {
            throw new InvalidArgumentException("Device with ID {$deviceId} not found for this user");
        }

        $user->saveOrFail();
    }

    public function searchUsers(string $query): array
    {
        $allUsers = User::query()->where('name', 'like', "%{$query}%")->orWhere('login', 'like', "%{$query}%")->orWhere('email', 'like', "%{$query}%")->fetchAll();

        return $allUsers;
    }

    public function deleteUser(User $user): void
    {
        $user->deletedAt = new DateTimeImmutable();
        $user->saveOrFail();
    }

    public function formatUserData(User $user, bool $detailed = false): array
    {
        $data = [
            'id' => $user->id,
            'name' => $user->name,
            'login' => $user->login,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'created_at' => $user->createdAt->format('c'),
            'is_online' => $user->isOnline(),
            'is_blocked' => $user->isBlocked(),
            'roles' => array_map(static fn ($role) => $role->name, $user->roles),
        ];

        if ($detailed) {
            $data += [
                'banner' => $user->banner,
                'last_logged' => $user->last_logged?->format('c'),
                'block_info' => $user->getBlockInfo(),
                'roles' => array_map(static fn ($role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'color' => $role->color,
                    'permissions' => array_map(static fn ($perm) => $perm->name, $role->permissions),
                ], $user->roles),
                'social_networks' => $this->getUserSocialNetworks($user),
                'devices' => $this->getUserDevices($user),
            ];
        }

        return $data;
    }
}
