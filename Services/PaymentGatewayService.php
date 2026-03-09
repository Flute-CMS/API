<?php

namespace Flute\Modules\API\Services;

use Flute\Core\Database\Entities\Currency;
use Flute\Core\Database\Entities\PaymentGateway;
use Flute\Core\Validator\FluteValidator;
use InvalidArgumentException;

class PaymentGatewayService
{
    private FluteValidator $validator;

    public function __construct(FluteValidator $validator)
    {
        $this->validator = $validator;
    }

    public function validatePaymentGatewayData(array $data): bool
    {
        $rules = [
            'name' => 'required|string|min-str-len:2|max-str-len:100',
            'adapter' => 'required|string|min-str-len:2|max-str-len:50',
            'image' => 'nullable|string|max-str-len:255',
            'enabled' => 'boolean',
            'settings' => 'required|array',
        ];

        return $this->validator->validate($data, $rules);
    }

    public function getAllPaymentGateways(): array
    {
        return PaymentGateway::findAll();
    }

    public function getPaymentGatewayById(int $id): ?PaymentGateway
    {
        return PaymentGateway::findByPK($id);
    }

    public function createPaymentGateway(array $data): PaymentGateway
    {
        if (!$this->validatePaymentGatewayData($data)) {
            throw new InvalidArgumentException('Неверные данные платежного шлюза: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        $gateway = new PaymentGateway();
        $this->fillPaymentGateway($gateway, $data);

        $gateway->saveOrFail();

        return $gateway;
    }

    public function updatePaymentGateway(PaymentGateway $gateway, array $data): void
    {
        if (!$this->validatePaymentGatewayData($data)) {
            throw new InvalidArgumentException('Неверные данные платежного шлюза: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        $this->fillPaymentGateway($gateway, $data);
        $gateway->saveOrFail();
    }

    public function deletePaymentGateway(PaymentGateway $gateway): void
    {
        $gateway->delete();
    }

    public function togglePaymentGateway(PaymentGateway $gateway): void
    {
        $gateway->enabled = !$gateway->enabled;
        $gateway->saveOrFail();
    }

    public function updatePaymentGatewayCurrencies(PaymentGateway $gateway, array $currencyIds): void
    {
        $settings = $gateway->getSettings();
        $settings['currencies'] = [];

        foreach ($currencyIds as $id) {
            $currency = Currency::findByPK($id);
            if (!$currency) {
                throw new InvalidArgumentException("Валюта с ID {$id} не найдена");
            }
            $settings['currencies'][] = [
                'id' => $currency->id,
                'code' => $currency->code ?? '',
                'name' => $currency->name ?? '',
                'symbol' => $currency->symbol ?? '',
            ];
        }

        $gateway->setSettings($settings);
        $gateway->saveOrFail();
    }

    public function formatPaymentGatewayData(PaymentGateway $gateway, bool $detailed = false): array
    {
        $settings = $gateway->getSettings();

        $data = [
            'id' => $gateway->id,
            'name' => $gateway->name,
            'adapter' => $gateway->adapter,
            'image' => $gateway->image,
            'enabled' => $gateway->enabled,
            'min_amount' => $settings['min_amount'] ?? 0,
            'max_amount' => $settings['max_amount'] ?? 0,
            'commission_percent' => $settings['commission_percent'] ?? 0,
            'commission_fixed' => $settings['commission_fixed'] ?? 0,
            'description' => $settings['description'] ?? null,
        ];

        if ($detailed) {
            $data['settings'] = $settings;
            $data['currencies'] = $settings['currencies'] ?? [];
            $data['transactions_count'] = $settings['transactions_count'] ?? 0;
            $data['created_at'] = $gateway->createdAt->format('Y-m-d H:i:s');
        }

        return $data;
    }

    private function fillPaymentGateway(PaymentGateway $gateway, array $data): void
    {
        $gateway->name = $data['name'];
        $gateway->adapter = $data['adapter'];
        $gateway->image = $data['image'] ?? null;
        $gateway->enabled = $data['enabled'] ?? false;

        $settings = $data['settings'] ?? [];
        $gateway->setSettings($settings);
    }
}
