<?php

namespace Flute\Modules\API\Services;

use DateTimeImmutable;
use Flute\Core\Database\Entities\PromoCode;
use Flute\Core\Database\Entities\Role;
use Flute\Core\Database\Entities\User;
use Flute\Core\Validator\FluteValidator;
use InvalidArgumentException;

class PromoCodeService
{
    private FluteValidator $validator;

    public function __construct(FluteValidator $validator)
    {
        $this->validator = $validator;
    }

    public function validatePromoCodeData(array $data): bool
    {
        $rules = [
            'code' => 'required|string|min-str-len:3|max-str-len:50',
            'max_usages' => 'nullable|integer|min:1',
            'max_uses_per_user' => 'nullable|integer|min:1',
            'type' => 'required|string|in:amount,percentage',
            'value' => 'required|numeric|min:0',
            'minimum_amount' => 'nullable|numeric|min:0',
            'expires_at' => 'nullable|datetime:Y-m-d H:i:s|after:now',
        ];

        return $this->validator->validate($data, $rules);
    }

    public function getAllPromoCodes(): array
    {
        return PromoCode::findAll();
    }

    public function getPromoCodeById(int $id): ?PromoCode
    {
        return PromoCode::findByPK($id);
    }

    public function getPromoCodeByCode(string $code): ?PromoCode
    {
        $promoCodes = PromoCode::findAll();
        foreach ($promoCodes as $promoCode) {
            if ($promoCode->code === $code) {
                return $promoCode;
            }
        }

        return null;
    }

    public function createPromoCode(array $data): PromoCode
    {
        if (!$this->validatePromoCodeData($data)) {
            throw new InvalidArgumentException('Invalid promo code data: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        if ($this->getPromoCodeByCode($data['code'])) {
            throw new InvalidArgumentException('Promo code with this code already exists');
        }

        $promoCode = new PromoCode();
        $promoCode->code = $data['code'];
        $promoCode->max_usages = $data['max_usages'] ?? null;
        $promoCode->max_uses_per_user = $data['max_uses_per_user'] ?? null;
        $promoCode->type = $data['type'];
        $promoCode->value = (float)$data['value'];
        $promoCode->minimum_amount = isset($data['minimum_amount']) ? (float)$data['minimum_amount'] : null;

        if (isset($data['expires_at'])) {
            $promoCode->expires_at = new DateTimeImmutable($data['expires_at']);
        }

        $promoCode->saveOrFail();

        if (isset($data['role_ids']) && is_array($data['role_ids'])) {
            foreach ($data['role_ids'] as $roleId) {
                $role = Role::findByPK($roleId);
                if ($role) {
                    $promoCode->addRole($role);
                }
            }
            $promoCode->saveOrFail();
        }

        return $promoCode;
    }

    public function updatePromoCode(PromoCode $promoCode, array $data): void
    {
        if (!$this->validatePromoCodeData($data)) {
            throw new InvalidArgumentException('Invalid promo code data: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        if ($data['code'] !== $promoCode->code && $this->getPromoCodeByCode($data['code'])) {
            throw new InvalidArgumentException('Promo code with this code already exists');
        }

        $promoCode->code = $data['code'];
        $promoCode->max_usages = $data['max_usages'] ?? null;
        $promoCode->max_uses_per_user = $data['max_uses_per_user'] ?? null;
        $promoCode->type = $data['type'];
        $promoCode->value = (float)$data['value'];
        $promoCode->minimum_amount = isset($data['minimum_amount']) ? (float)$data['minimum_amount'] : null;

        if (isset($data['expires_at'])) {
            $promoCode->expires_at = new DateTimeImmutable($data['expires_at']);
        } else {
            $promoCode->expires_at = null;
        }

        if (isset($data['role_ids']) && is_array($data['role_ids'])) {
            $promoCode->clearRoles();
            foreach ($data['role_ids'] as $roleId) {
                $role = Role::findByPK($roleId);
                if ($role) {
                    $promoCode->addRole($role);
                }
            }
        }

        $promoCode->saveOrFail();
    }

    public function deletePromoCode(PromoCode $promoCode): void
    {
        if (count($promoCode->usages) > 0) {
            throw new InvalidArgumentException('Cannot delete promo code that has been used');
        }

        $promoCode->delete();
    }

    public function validatePromoCode(PromoCode $promoCode, ?int $userId, float $amount): bool
    {
        if ($promoCode->expires_at && $promoCode->expires_at < new DateTimeImmutable()) {
            return false;
        }

        if ($promoCode->minimum_amount && $amount < $promoCode->minimum_amount) {
            return false;
        }

        if ($promoCode->max_usages && count($promoCode->usages) >= $promoCode->max_usages) {
            return false;
        }

        if ($userId && $promoCode->max_uses_per_user) {
            $userUsages = 0;
            foreach ($promoCode->usages as $usage) {
                if ($usage->user->id === $userId) {
                    $userUsages++;
                }
            }
            if ($userUsages >= $promoCode->max_uses_per_user) {
                return false;
            }
        }

        if ($userId && count($promoCode->roles) > 0) {
            $user = User::findByPK($userId);
            if (!$user) {
                return false;
            }

            $hasRequiredRole = false;
            foreach ($promoCode->roles as $requiredRole) {
                if ($user->hasRole($requiredRole->name)) {
                    $hasRequiredRole = true;

                    break;
                }
            }

            if (!$hasRequiredRole) {
                return false;
            }
        }

        return true;
    }

    public function calculateDiscount(PromoCode $promoCode, float $amount): float
    {
        if ($promoCode->type === 'percentage') {
            return $amount * ($promoCode->value / 100);
        }

        return min($promoCode->value, $amount);

    }

    public function formatPromoCodeData(PromoCode $promoCode, bool $detailed = false): array
    {
        $data = [
            'id' => $promoCode->id,
            'code' => $promoCode->code,
            'type' => $promoCode->type,
            'value' => $promoCode->value,
            'max_usages' => $promoCode->max_usages,
            'max_uses_per_user' => $promoCode->max_uses_per_user,
            'minimum_amount' => $promoCode->minimum_amount,
            'expires_at' => $promoCode->expires_at?->format('Y-m-d H:i:s'),
            'created_at' => $promoCode->createdAt->format('Y-m-d H:i:s'),
        ];

        if ($detailed) {
            $data['usages_count'] = count($promoCode->usages);
            $data['roles'] = array_map(static fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
            ], $promoCode->roles);
        }

        return $data;
    }
}
