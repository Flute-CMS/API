# Currencies

Provides functionality for managing currencies.

**Base URL:** `/api/currencies`

**Permissions required:** `admin.currencies`

---

## Get all Currencies

-   **Method:** `GET`
-   **URL:** `/`
-   **Description:** Retrieves a list of all currencies.
-   **Success Response (200):**
    ```json
    {
        "currencies": [
            {
                "id": 1,
                "code": "USD",
                "minimum_value": 1,
                "exchange_rate": 1
            }
        ]
    }
    ```

---

## Get a single Currency

-   **Method:** `GET`
-   **URL:** `/{id}`
-   **Description:** Retrieves details for a specific currency.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the currency.
-   **Success Response (200):**
    ```json
    {
        "currency": {
            "id": 1,
            "code": "USD",
            "minimum_value": 1,
            "exchange_rate": 1,
            "payment_gateways": [
                {
                    "id": 1,
                    "name": "PayPal",
                    "adapter": "paypal",
                    "enabled": true
                }
            ]
        }
    }
    ```
-   **Error Response (404):**
    ```json
    {
      "message": "Currency not found"
    }
    ```

---

## Create Currency

-   **Method:** `POST`
-   **URL:** `/`
-   **Description:** Creates a new currency.
-   **Request Body:**
    -   `code` (string, required, min: 3, max: 10): The currency code (e.g., "USD").
    -   `minimum_value` (numeric, required, min: 0): The minimum payment amount in this currency.
    -   `exchange_rate` (numeric, required, min: 0): The exchange rate against the base currency.
-   **Success Response (201):**
    ```json
    {
        "message": "Currency created successfully",
        "currency": {
            "id": 2,
            "code": "EUR",
            "minimum_value": 1,
            "exchange_rate": 0.9
        }
    }
    ```
-   **Error Response (422):**
    ```json
    {
      "error": "Invalid currency data: The code must be at least 3 characters."
    }
    ```

---

## Update Currency

-   **Method:** `PUT`
-   **URL:** `/{id}`
-   **Description:** Updates an existing currency.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the currency to update.
-   **Request Body:**
    -   `code` (string, required, min: 3, max: 10): The currency code.
    -   `minimum_value` (numeric, required, min: 0): The minimum payment amount.
    -   `exchange_rate` (numeric, required, min: 0): The exchange rate.
-   **Success Response (200):**
    ```json
    {
        "message": "Currency updated successfully",
        "currency": {
            "id": 1,
            "code": "USD",
            "minimum_value": 1.5,
            "exchange_rate": 1
        }
    }
    ```

---

## Delete Currency

-   **Method:** `DELETE`
-   **URL:** `/{id}`
-   **Description:** Deletes a currency.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the currency to delete.
-   **Success Response (200):**
    ```json
    {
      "message": "Currency deleted successfully"
    }
    ```

---

## Update Currency Payment Gateways

-   **Method:** `PUT`
-   **URL:** `/{id}/payment-gateways`
-   **Description:** Updates the payment gateways associated with a currency.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the currency.
-   **Request Body:**
    -   `payment_gateway_ids` (array of integers, required): An array of payment gateway IDs to associate with the currency.
-   **Success Response (200):**
    ```json
    {
        "message": "Currency payment gateways updated successfully",
        "currency": {
            "id": 1,
            "code": "USD",
            "minimum_value": 1.5,
            "exchange_rate": 1,
            "payment_gateways": [
                {
                    "id": 1,
                    "name": "PayPal",
                    "adapter": "paypal",
                    "enabled": true
                },
                {
                    "id": 2,
                    "name": "Stripe",
                    "adapter": "stripe",
                    "enabled": true
                }
            ]
        }
    }
    ```
-   **Error Response (422):**
    ```json
    {
        "error": "Payment gateway with ID 999 not found"
    }
    ```