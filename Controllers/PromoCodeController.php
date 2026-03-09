<?php

namespace Flute\Modules\API\Controllers;

use Exception;
use Flute\Core\Router\Annotations\Route;
use Flute\Core\Support\FluteRequest;
use Flute\Modules\API\Services\PromoCodeService;

#[Route("/promo-codes", name: "api.promo-codes")]
class PromoCodeController extends APIController
{
    private PromoCodeService $promoCodeService;

    public function __construct(PromoCodeService $promoCodeService)
    {
        $this->promoCodeService = $promoCodeService;
    }

    #[Route("", name: ".list", methods: ["GET"])]
    public function list(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.promo-codes')) {
            return $this->forbiddenResponse();
        }

        $promoCodes = $this->promoCodeService->getAllPromoCodes();

        return $this->json([
            'promo_codes' => array_map(
                fn ($promoCode) => $this->promoCodeService->formatPromoCodeData($promoCode),
                $promoCodes
            ),
        ]);
    }

    #[Route("/{id}", name: ".get", methods: ["GET"], where: ["id" => "\d+"])]
    public function get(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.promo-codes')) {
            return $this->forbiddenResponse();
        }

        $promoCode = $this->promoCodeService->getPromoCodeById($id);

        if (!$promoCode) {
            return $this->notFoundResponse('Promo Code');
        }

        return $this->json([
            'promo_code' => $this->promoCodeService->formatPromoCodeData($promoCode, true),
        ]);
    }

    #[Route("", name: ".create", methods: ["POST"])]
    public function create(FluteRequest $request)
    {
        if (!$this->requirePermission($request, 'admin.promo-codes')) {
            return $this->forbiddenResponse();
        }

        $data = $request->input();

        try {
            $promoCode = $this->promoCodeService->createPromoCode($data);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Promo Code created successfully',
            'promo_code' => $this->promoCodeService->formatPromoCodeData($promoCode),
        ], 201);
    }

    #[Route("/{id}", name: ".update", methods: ["PUT"], where: ["id" => "\d+"])]
    public function update(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.promo-codes')) {
            return $this->forbiddenResponse();
        }

        $promoCode = $this->promoCodeService->getPromoCodeById($id);

        if (!$promoCode) {
            return $this->notFoundResponse('Promo Code');
        }

        $data = $request->input();

        try {
            $this->promoCodeService->updatePromoCode($promoCode, $data);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Promo Code updated successfully',
            'promo_code' => $this->promoCodeService->formatPromoCodeData($promoCode),
        ]);
    }

    #[Route("/{id}", name: ".delete", methods: ["DELETE"], where: ["id" => "\d+"])]
    public function delete(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'admin.promo-codes')) {
            return $this->forbiddenResponse();
        }

        $promoCode = $this->promoCodeService->getPromoCodeById($id);

        if (!$promoCode) {
            return $this->notFoundResponse('Promo Code');
        }

        try {
            $this->promoCodeService->deletePromoCode($promoCode);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'message' => 'Promo Code deleted successfully',
        ]);
    }

    #[Route("/{id}/validate", name: ".validate", methods: ["POST"], where: ["id" => "\d+"])]
    public function validateCode(FluteRequest $request, int $id)
    {
        if (!$this->requirePermission($request, 'payments.use')) {
            return $this->forbiddenResponse();
        }

        $promoCode = $this->promoCodeService->getPromoCodeById($id);

        if (!$promoCode) {
            return $this->notFoundResponse('Promo Code');
        }

        $data = $request->input();
        $userId = $data['user_id'] ?? null;
        $amount = $data['amount'] ?? 0;

        try {
            $isValid = $this->promoCodeService->validatePromoCode($promoCode, $userId, $amount);
        } catch (Exception $e) {
            return $this->json([
                'error' => $e->getMessage(),
            ], 422);
        }

        return $this->json([
            'valid' => $isValid,
            'promo_code' => $this->promoCodeService->formatPromoCodeData($promoCode),
        ]);
    }
}
