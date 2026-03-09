# Payment Gateways

Provides functionality for managing payment gateways.

**Base URL:** `/api/payment-gateways`

**Permissions required:** `admin.gateways`

---

## Get all Payment Gateways

-   **Method:** `GET`
-   **URL:** `/`
-   **Description:** Retrieves a list of all payment gateways.
-   **Success Response (200):**
    ```json
    {
        "payment_gateways": [
            {
                "id": 1,
                "name": "PayPal",
                "adapter": "paypal",
                "image": "path/to/icon.png",
                "enabled": true,
                "min_amount": 10,
                "max_amount": 1000,
                "commission_percent": 2.9,
                "commission_fixed": 0.30,
                "description": "Pay with PayPal"
            }
        ]
    }
    ```

---

## Get a single Payment Gateway

-   **Method:** `GET`
-   **URL:** `/{id}`
-   **Description:** Retrieves details for a specific payment gateway.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the gateway.
-   **Success Response (200):**
    ```json
    {
        "payment_gateway": {
            "id": 1,
            "name": "PayPal",
            "adapter": "paypal",
            "image": "path/to/icon.png",
            "enabled": true,
            "min_amount": 10,
            "max_amount": 1000,
            "commission_percent": 2.9,
            "commission_fixed": 0.30,
            "description": "Pay with PayPal",
            "settings": {
                "client_id": "...",
                "client_secret": "...",
                "min_amount": 10,
                "max_amount": 1000,
                "commission_percent": 2.9,
                "commission_fixed": 0.30,
                "description": "Pay with PayPal"
            },
            "currencies": [
                { "id": 1, "code": "USD", "name": "US Dollar", "symbol": "$" }
            ],
            "transactions_count": 120,
            "created_at": "2023-01-01T12:00:00+00:00"
        }
    }
    ```
-   **Error Response (404):**
    ```json
    {
      "message": "Payment Gateway not found"
    }
    ```

---

## Create Payment Gateway

-   **Method:** `POST`
-   **URL:** `/`
-   **Description:** Creates a new payment gateway.
-   **Request Body:**
    -   `name` (string, required, min: 2, max: 100): Display name of the gateway.
    -   `adapter` (string, required, min: 2, max: 50): The adapter key for the gateway (e.g., "paypal").
    -   `image` (string, nullable, max: 255): URL to an icon for the gateway.
    -   `enabled` (boolean, optional, default: false): Whether the gateway is active.
    -   `settings` (object, required): Gateway-specific settings. This can include fields like `min_amount`, `max_amount`, `commission_percent`, `commission_fixed`, `description`, and any credentials needed by the adapter (e.g., `client_id`).
-   **Success Response (201):**
    ```json
    {
        "message": "Payment Gateway created successfully",
        "payment_gateway": {
            "id": 2,
            "name": "Stripe",
            "adapter": "stripe",
            ...
        }
    }
    ```
-   **Error Response (422):**
    ```json
    {
      "error": "Неверные данные платежного шлюза: The name field is required."
    }
    ```

---

## Update Payment Gateway

-   **Method:** `PUT`
-   **URL:** `/{id}`
-   **Description:** Updates an existing payment gateway.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the gateway to update.
-   **Request Body:** (Same as create)
-   **Success Response (200):**
    ```json
    {
        "message": "Payment Gateway updated successfully",
        "payment_gateway": {
            "id": 1,
            "name": "PayPal Express",
            ...
        }
    }
    ```

---

## Delete Payment Gateway

-   **Method:** `DELETE`
-   **URL:** `/{id}`
-   **Description:** Deletes a payment gateway.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the gateway to delete.
-   **Success Response (200):**
    ```json
    {
      "message": "Payment Gateway deleted successfully"
    }
    ```

---

## Toggle Payment Gateway

-   **Method:** `POST`
-   **URL:** `/{id}/toggle`
-   **Description:** Enables or disables a payment gateway.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the gateway.
-   **Success Response (200):**
    ```json
    {
        "message": "Payment Gateway enabled successfully",
        "payment_gateway": { ... }
    }
    ```

---

## Update Gateway Currencies

-   **Method:** `PUT`
-   **URL:** `/{id}/currencies`
-   **Description:** Updates the currencies supported by a payment gateway.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the gateway.
-   **Request Body:**
    -   `currency_ids` (array of integers, required): An array of currency IDs.
-   **Success Response (200):**
    ```json
    {
        "message": "Payment Gateway currencies updated successfully",
        "payment_gateway": {
            "id": 1,
            "name": "PayPal",
            ...
            "currencies": [
                { "id": 1, "code": "USD", "name": "US Dollar", "symbol": "$" },
                { "id": 2, "code": "EUR", "name": "Euro", "symbol": "€" }
            ]
        }
    }
    ```
-   **Error Response (422):**
    ```json
    {
        "error": "Валюта с ID 999 не найдена"
    }
    ``` 