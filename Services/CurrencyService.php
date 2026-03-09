<?php

namespace Flute\Modules\API\Services;

use Flute\Core\Database\Entities\Currency;
use Flute\Core\Database\Entities\PaymentGateway;
use Flute\Core\Validator\FluteValidator;
use InvalidArgumentException;

class CurrencyService
{
    private FluteValidator $validator;

    public function __construct(FluteValidator $validator)
    {
        $this->validator = $validator;
    }

    public function validateCurrencyData(array $data): bool
    {
        $rules = [
            'code' => 'required|string|min-str-len:3|max-str-len:10',
            'minimum_value' => 'required|numeric|min:0',
            'exchange_rate' => 'required|numeric|min:0',
        ];

        return $this->validator->validate($data, $rules);
    }

    public function getAllCurrencies(): array
    {
        return Currency::findAll();
    }

    public function getCurrencyById(int $id): ?Currency
    {
        return Currency::findByPK($id);
    }

    public function createCurrency(array $data): Currency
    {
        if (!$this->validateCurrencyData($data)) {
            throw new InvalidArgumentException('Invalid currency data: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        $currency = new Currency();
        $currency->code = $data['code'];
        $currency->minimum_value = (float)$data['minimum_value'];
        $currency->exchange_rate = (float)$data['exchange_rate'];

        $currency->saveOrFail();

        return $currency;
    }

    public function updateCurrency(Currency $currency, array $data): void
    {
        if (!$this->validateCurrencyData($data)) {
            throw new InvalidArgumentException('Invalid currency data: ' . implode(', ', $this->validator->getErrors()->all()));
        }

        $currency->code = $data['code'];
        $currency->minimum_value = (float)$data['minimum_value'];
        $currency->exchange_rate = (float)$data['exchange_rate'];

        $currency->saveOrFail();
    }

    public function deleteCurrency(Currency $currency): void
    {
        $currency->delete();
    }

    public function updateCurrencyPaymentGateways(Currency $currency, array $paymentGatewayIds): void
    {
        $currency->clearPayments();

        foreach ($paymentGatewayIds as $id) {
            $paymentGateway = PaymentGateway::findByPK($id);
            if (!$paymentGateway) {
                throw new InvalidArgumentException("Payment gateway with ID {$id} not found");
            }

            $settings = $paymentGateway->getSettings();
            if (!isset($settings['currencies'])) {
                $settings['currencies'] = [];
            }

            $found = false;
            foreach ($settings['currencies'] as $curr) {
                if ($curr['id'] === $currency->id) {
                    $found = true;

                    break;
                }
            }

            if (!$found) {
                $settings['currencies'][] = [
                    'id' => $currency->id,
                    'code' => $currency->code,
                    'minimum_value' => $currency->minimum_value,
                    'exchange_rate' => $currency->exchange_rate,
                ];

                $paymentGateway->setSettings($settings);
                $paymentGateway->saveOrFail();
            }

            $currency->addPayment($paymentGateway);
        }

        $currency->saveOrFail();
    }

    public function formatCurrencyData(Currency $currency, bool $detailed = false): array
    {
        $data = [
            'id' => $currency->id,
            'code' => $currency->code,
            'minimum_value' => $currency->minimum_value,
            'exchange_rate' => $currency->exchange_rate,
        ];

        if ($detailed) {
            $data['payment_gateways'] = array_map(static fn ($gateway) => [
                'id' => $gateway->id,
                'name' => $gateway->name,
                'adapter' => $gateway->adapter,
                'enabled' => $gateway->enabled,
            ], $currency->paymentGateways);
        }

        return $data;
    }
}
