<?php

namespace Flute\Modules\API\Services;

use Flute\Core\Database\Entities\SocialNetwork;
use Flute\Core\Database\Entities\UserSocialNetwork;
use Flute\Core\Validator\FluteValidator;
use InvalidArgumentException;

class SocialNetworkService
{
    private FluteValidator $validator;

    public function __construct(FluteValidator $validator)
    {
        $this->validator = $validator;
    }

    public function validateSocialNetworkData(array $data): bool
    {
        $rules = [
            'key' => 'required|string|min-str-len:2|max-str-len:50',
            'settings' => 'required|string',
            'cooldown_time' => 'integer|min:0',
            'allow_to_register' => 'boolean',
            'icon' => 'required|string',
            'enabled' => 'boolean',
        ];

        return $this->validator->validate($data, $rules);
    }

    public function getAllSocialNetworks(): array
    {
        return SocialNetwork::findAll();
    }

    public function getSocialNetworkById(int $id): ?SocialNetwork
    {
        return SocialNetwork::findByPK($id);
    }

    public function createSocialNetwork(array $data): SocialNetwork
    {
        if (!$this->validateSocialNetworkData($data)) {
            throw new InvalidArgumentException('Invalid social network data: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        $socialNetwork = new SocialNetwork();
        $socialNetwork->key = $data['key'];
        $socialNetwork->settings = $data['settings'];
        $socialNetwork->cooldownTime = $data['cooldown_time'] ?? 0;
        $socialNetwork->allowToRegister = $data['allow_to_register'] ?? true;
        $socialNetwork->icon = $data['icon'];
        $socialNetwork->enabled = $data['enabled'] ?? false;

        $socialNetwork->saveOrFail();

        return $socialNetwork;
    }

    public function updateSocialNetwork(SocialNetwork $socialNetwork, array $data): void
    {
        if (!$this->validateSocialNetworkData($data)) {
            throw new InvalidArgumentException('Invalid social network data: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        $socialNetwork->key = $data['key'];
        $socialNetwork->settings = $data['settings'];
        $socialNetwork->cooldownTime = $data['cooldown_time'] ?? $socialNetwork->cooldownTime;
        $socialNetwork->allowToRegister = $data['allow_to_register'] ?? $socialNetwork->allowToRegister;
        $socialNetwork->icon = $data['icon'];
        $socialNetwork->enabled = $data['enabled'] ?? $socialNetwork->enabled;

        $socialNetwork->saveOrFail();
    }

    public function deleteSocialNetwork(SocialNetwork $socialNetwork): void
    {
        $usersCount = UserSocialNetwork::query()
            ->where('socialNetwork_id', $socialNetwork->id)
            ->count();

        if ($usersCount > 0) {
            throw new InvalidArgumentException('Cannot delete social network as it is being used by users');
        }

        $socialNetwork->delete();
    }

    public function getUsersByNetwork(SocialNetwork $socialNetwork): array
    {
        $userSocialNetworks = UserSocialNetwork::query()
            ->where('socialNetwork_id', $socialNetwork->id)
            ->load(['user'])
            ->fetchAll();

        $users = [];
        foreach ($userSocialNetworks as $userSocialNetwork) {
            $user = $userSocialNetwork->user;

            if (!$user) {
                continue;
            }

            $users[] = [
                'id' => $user->id,
                'name' => $user->name,
                'login' => $user->login,
                'value' => $userSocialNetwork->value,
                'url' => $userSocialNetwork->url,
                'linked_at' => $userSocialNetwork->linkedAt?->format('c'),
            ];
        }

        return $users;
    }

    public function formatSocialNetworkData(SocialNetwork $socialNetwork, bool $detailed = false): array
    {
        $data = [
            'id' => $socialNetwork->id,
            'key' => $socialNetwork->key,
            'cooldown_time' => $socialNetwork->cooldownTime,
            'allow_to_register' => $socialNetwork->allowToRegister,
            'icon' => $socialNetwork->icon,
            'enabled' => $socialNetwork->enabled,
        ];

        if ($detailed) {
            $data['settings'] = $socialNetwork->getSettings();

            $usersCount = UserSocialNetwork::query()
                ->where('socialNetwork_id', $socialNetwork->id)
                ->count();

            $data['users_count'] = $usersCount;
        }

        return $data;
    }
}
