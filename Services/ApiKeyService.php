<?php

namespace Flute\Modules\API\Services;

use Flute\Core\Database\Entities\ApiKey;
use Flute\Core\Database\Entities\Permission;
use Flute\Core\Validator\FluteValidator;
use InvalidArgumentException;

class ApiKeyService
{
    private FluteValidator $validator;

    public function __construct(FluteValidator $validator)
    {
        $this->validator = $validator;
    }

    public function validateApiKeyData(array $data): bool
    {
        $rules = [
            'name' => 'required|string|min-str-len:3|max-str-len:100',
        ];

        return $this->validator->validate($data, $rules);
    }

    public function getAllApiKeys(): array
    {
        return ApiKey::findAll();
    }

    public function getApiKeyById(int $id): ?ApiKey
    {
        return ApiKey::findByPK($id);
    }

    public function getApiKeyByKey(string $key): ?ApiKey
    {
        $apiKeys = ApiKey::findAll();
        foreach ($apiKeys as $apiKey) {
            if ($apiKey->key === $key) {
                return $apiKey;
            }
        }

        return null;
    }

    public function createApiKey(array $data): ApiKey
    {
        if (!$this->validateApiKeyData($data)) {
            throw new InvalidArgumentException('Неверные данные API ключа: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        $apiKey = new ApiKey();
        $apiKey->name = $data['name'];
        $apiKey->key = $this->generateUniqueKey();

        $apiKey->saveOrFail();

        return $apiKey;
    }

    public function updateApiKey(ApiKey $apiKey, array $data): void
    {
        if (!$this->validateApiKeyData($data)) {
            throw new InvalidArgumentException('Неверные данные API ключа: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        $apiKey->name = $data['name'];
        $apiKey->saveOrFail();
    }

    public function deleteApiKey(ApiKey $apiKey): void
    {
        $apiKey->delete();
    }

    public function regenerateKey(ApiKey $apiKey): void
    {
        $apiKey->key = $this->generateUniqueKey();
        $apiKey->saveOrFail();
    }

    public function updateApiKeyPermissions(ApiKey $apiKey, array $permissionIds): void
    {
        $apiKey->permissions = [];

        foreach ($permissionIds as $id) {
            $permission = Permission::findByPK($id);
            if (!$permission) {
                throw new InvalidArgumentException("Разрешение с ID {$id} не найдено");
            }
            $apiKey->addPermission($permission);
        }

        $apiKey->saveOrFail();
    }

    public function formatApiKeyData(ApiKey $apiKey, bool $detailed = false): array
    {
        $data = [
            'id' => $apiKey->id,
            'name' => $apiKey->name,
            'key' => $apiKey->key,
            'created_at' => $apiKey->createdAt->format('Y-m-d H:i:s'),
            'last_used_at' => $apiKey->lastUsedAt ? $apiKey->lastUsedAt->format('Y-m-d H:i:s') : null,
        ];

        if ($detailed) {
            $data['permissions'] = $apiKey->getPermissions();
        }

        return $data;
    }

    private function generateUniqueKey(): string
    {
        $key = bin2hex(random_bytes(16));

        // Проверяем, что ключ уникальный
        while ($this->getApiKeyByKey($key) !== null) {
            $key = bin2hex(random_bytes(16));
        }

        return $key;
    }
}
