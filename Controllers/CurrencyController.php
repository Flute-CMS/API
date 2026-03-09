<?php

namespace Flute\Modules\API\Controllers;

use Exception;
use Flute\Core\Router\Annotations\Route;
use Flute\Core\Support\FluteRequest;
use Flute\Modules\API\Services\CurrencyService;

#[Route("/currencies", name: "api.currencies")]
class CurrencyController extends APIController
{
    private CurrencyService $currencyService;

    public function __construct(CurrencyService $currencyService)
    {
        $this->currencyService = $currencyService;
    }

    #[Route("", name: ".list", methods: ["GET"])]
    public function list(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.currencies')) {
            return $this->forbiddenResponse();
        }

        $currencies = $this->currencyService->getAllCurrencies();

        return $this->json([
            'currencies' => array_map(
                fn ($currency) => $this->currencyService->formatCurrencyData($currency),
                $currencies
            ),
        ]);
    }

    #[Route("/{id}", name: ".get", methods: ["GET"], where: ["id" => "\d+"])]
    public function get(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.currencies')) {
            return $this->forbiddenResponse();
        }

        $currency = $this->currencyService->getCurrencyById($id);

        if (!$currency) {
            return $this->notFoundResponse('Currency');
        }

        return $this->json([
            'currency' => $this->currencyService->formatCurrencyData($currency, true),
        ]);
    }

    #[Route("", name: ".create", methods: ["POST"])]
    public function create(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.currencies')) {
            return $this->forbiddenResponse();
        }

        $data = $request->input();

        try {
            $currency = $this->currencyService->createCurrency($data);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Currency created successfully',
            'currency' => $this->currencyService->formatCurrencyData($currency),
        ], 201);
    }

    #[Route("/{id}", name: ".update", methods: ["PUT"], where: ["id" => "\d+"])]
    public function update(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.currencies')) {
            return $this->forbiddenResponse();
        }

        $currency = $this->currencyService->getCurrencyById($id);

        if (!$currency) {
            return $this->notFoundResponse('Currency');
        }

        $data = $request->input();
        $this->currencyService->updateCurrency($currency, $data);

        return $this->json([
            'message' => 'Currency updated successfully',
            'currency' => $this->currencyService->formatCurrencyData($currency),
        ]);
    }

    #[Route("/{id}", name: ".delete", methods: ["DELETE"], where: ["id" => "\d+"])]
    public function delete(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.currencies')) {
            return $this->forbiddenResponse();
        }

        $currency = $this->currencyService->getCurrencyById($id);

        if (!$currency) {
            return $this->notFoundResponse('Currency');
        }

        $this->currencyService->deleteCurrency($currency);

        return $this->json([
            'message' => 'Currency deleted successfully',
        ]);
    }

    #[Route("/{id}/payment-gateways", name: ".payment-gateways", methods: ["PUT"], where: ["id" => "\d+"])]
    public function updatePaymentGateways(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.currencies')) {
            return $this->forbiddenResponse();
        }

        $currency = $this->currencyService->getCurrencyById($id);

        if (!$currency) {
            return $this->notFoundResponse('Currency');
        }

        $data = $request->input();
        $paymentGatewayIds = $data['payment_gateway_ids'] ?? [];

        try {
            $this->currencyService->updateCurrencyPaymentGateways($currency, $paymentGatewayIds);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Currency payment gateways updated successfully',
            'currency' => $this->currencyService->formatCurrencyData($currency, true),
        ]);
    }
}
