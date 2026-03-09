<?php

namespace Flute\Modules\API\Controllers;

use Exception;
use Flute\Core\Router\Annotations\Route;
use Flute\Core\Support\FluteRequest;
use Flute\Modules\API\Services\PaymentGatewayService;

#[Route("/payment-gateways", name: "api.payment-gateways")]
class PaymentGatewayController extends APIController
{
    private PaymentGatewayService $paymentGatewayService;

    public function __construct(PaymentGatewayService $paymentGatewayService)
    {
        $this->paymentGatewayService = $paymentGatewayService;
    }

    #[Route("", name: ".list", methods: ["GET"])]
    public function list(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.gateways')) {
            return $this->forbiddenResponse();
        }

        $gateways = $this->paymentGatewayService->getAllPaymentGateways();

        return $this->json([
            'payment_gateways' => array_map(
                fn ($gateway) => $this->paymentGatewayService->formatPaymentGatewayData($gateway),
                $gateways
            ),
        ]);
    }

    #[Route("/{id}", name: ".get", methods: ["GET"], where: ["id" => "\d+"])]
    public function get(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.gateways')) {
            return $this->forbiddenResponse();
        }

        $gateway = $this->paymentGatewayService->getPaymentGatewayById($id);

        if (!$gateway) {
            return $this->notFoundResponse('Payment Gateway');
        }

        return $this->json([
            'payment_gateway' => $this->paymentGatewayService->formatPaymentGatewayData($gateway, true),
        ]);
    }

    #[Route("", name: ".create", methods: ["POST"])]
    public function create(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.gateways')) {
            return $this->forbiddenResponse();
        }

        $data = $request->input();

        // Преобразование полей в формат, соответствующий новой структуре
        if (isset($data['config'])) {
            $data['settings'] = json_decode($data['config'], true) ?? [];
            unset($data['config']);
        }

        if (isset($data['key'])) {
            $data['adapter'] = $data['key'];
            unset($data['key']);
        }

        if (isset($data['icon'])) {
            $data['image'] = $data['icon'];
            unset($data['icon']);
        }

        // Перенос дополнительных полей в настройки
        $additionalFields = ['min_amount', 'max_amount', 'commission_percent', 'commission_fixed', 'description'];
        if (!isset($data['settings'])) {
            $data['settings'] = [];
        }

        foreach ($additionalFields as $field) {
            if (isset($data[$field])) {
                $data['settings'][$field] = $data[$field];
                unset($data[$field]);
            }
        }

        try {
            $gateway = $this->paymentGatewayService->createPaymentGateway($data);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Payment Gateway created successfully',
            'payment_gateway' => $this->paymentGatewayService->formatPaymentGatewayData($gateway),
        ], 201);
    }

    #[Route("/{id}", name: ".update", methods: ["PUT"], where: ["id" => "\d+"])]
    public function update(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.gateways')) {
            return $this->forbiddenResponse();
        }

        $gateway = $this->paymentGatewayService->getPaymentGatewayById($id);

        if (!$gateway) {
            return $this->notFoundResponse('Payment Gateway');
        }

        $data = $request->input();

        // Преобразование полей в формат, соответствующий новой структуре
        if (isset($data['config'])) {
            $data['settings'] = json_decode($data['config'], true) ?? [];
            unset($data['config']);
        }

        if (isset($data['key'])) {
            $data['adapter'] = $data['key'];
            unset($data['key']);
        }

        if (isset($data['icon'])) {
            $data['image'] = $data['icon'];
            unset($data['icon']);
        }

        // Перенос дополнительных полей в настройки
        $additionalFields = ['min_amount', 'max_amount', 'commission_percent', 'commission_fixed', 'description'];
        if (!isset($data['settings'])) {
            $data['settings'] = $gateway->getSettings();
        }

        foreach ($additionalFields as $field) {
            if (isset($data[$field])) {
                $data['settings'][$field] = $data[$field];
                unset($data[$field]);
            }
        }

        try {
            $this->paymentGatewayService->updatePaymentGateway($gateway, $data);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Payment Gateway updated successfully',
            'payment_gateway' => $this->paymentGatewayService->formatPaymentGatewayData($gateway),
        ]);
    }

    #[Route("/{id}", name: ".delete", methods: ["DELETE"], where: ["id" => "\d+"])]
    public function delete(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.gateways')) {
            return $this->forbiddenResponse();
        }

        $gateway = $this->paymentGatewayService->getPaymentGatewayById($id);

        if (!$gateway) {
            return $this->notFoundResponse('Payment Gateway');
        }

        try {
            $this->paymentGatewayService->deletePaymentGateway($gateway);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Payment Gateway deleted successfully',
        ]);
    }

    #[Route("/{id}/toggle", name: ".toggle", methods: ["POST"], where: ["id" => "\d+"])]
    public function toggle(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.gateways')) {
            return $this->forbiddenResponse();
        }

        $gateway = $this->paymentGatewayService->getPaymentGatewayById($id);

        if (!$gateway) {
            return $this->notFoundResponse('Payment Gateway');
        }

        $this->paymentGatewayService->togglePaymentGateway($gateway);

        return $this->json([
            'message' => 'Payment Gateway ' . ($gateway->enabled ? 'enabled' : 'disabled') . ' successfully',
            'payment_gateway' => $this->paymentGatewayService->formatPaymentGatewayData($gateway),
        ]);
    }

    #[Route("/{id}/currencies", name: ".currencies", methods: ["PUT"], where: ["id" => "\d+"])]
    public function updateCurrencies(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.gateways')) {
            return $this->forbiddenResponse();
        }

        $gateway = $this->paymentGatewayService->getPaymentGatewayById($id);

        if (!$gateway) {
            return $this->notFoundResponse('Payment Gateway');
        }

        $data = $request->input();
        $currencyIds = $data['currency_ids'] ?? [];

        try {
            $this->paymentGatewayService->updatePaymentGatewayCurrencies($gateway, $currencyIds);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Payment Gateway currencies updated successfully',
            'payment_gateway' => $this->paymentGatewayService->formatPaymentGatewayData($gateway, true),
        ]);
    }
}
