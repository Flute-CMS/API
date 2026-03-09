# Promo Codes

Provides functionality for managing promotional codes.

**Base URL:** `/api/promo-codes`

---

## Get all Promo Codes

-   **Method:** `GET`
-   **URL:** `/`
-   **Permissions required:** `admin.promo-codes`
-   **Description:** Retrieves a list of all promo codes.
-   **Success Response (200):**
    ```json
    {
        "promo_codes": [
            {
                "id": 1,
                "code": "SALE2023",
                "type": "percentage",
                "value": 15,
                "max_usages": 100,
                "max_uses_per_user": 1,
                "minimum_amount": 50,
                "expires_at": "2023-12-31 23:59:59",
                "created_at": "2023-01-01 12:00:00"
            }
        ]
    }
    ```

---

## Get a single Promo Code

-   **Method:** `GET`
-   **URL:** `/{id}`
-   **Permissions required:** `admin.promo-codes`
-   **Description:** Retrieves details for a specific promo code.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the promo code.
-   **Success Response (200):**
    ```json
    {
        "promo_code": {
            "id": 1,
            "code": "SALE2023",
            "type": "percentage",
            "value": 15,
            "max_usages": 100,
            "max_uses_per_user": 1,
            "minimum_amount": 50,
            "expires_at": "2023-12-31 23:59:59",
            "created_at": "2023-01-01 12:00:00",
            "usages_count": 42,
            "roles": [
                { "id": 1, "name": "VIP Users" }
            ]
        }
    }
    ```
-   **Error Response (404):**
    ```json
    {
      "message": "Promo Code not found"
    }
    ```

---

## Create Promo Code

-   **Method:** `POST`
-   **URL:** `/`
-   **Permissions required:** `admin.promo-codes`
-   **Description:** Creates a new promo code.
-   **Request Body:**
    -   `code` (string, required, min: 3, max: 50): The unique code.
    -   `type` (string, required, in: ['amount', 'percentage']): The type of discount.
    -   `value` (numeric, required, min: 0): The discount value.
    -   `max_usages` (integer, nullable, min: 1): Total number of times the code can be used.
    -   `max_uses_per_user` (integer, nullable, min: 1): How many times a single user can use the code.
    -   `minimum_amount` (numeric, nullable, min: 0): The minimum purchase amount for the code to be valid.
    -   `expires_at` (datetime, nullable, format: 'Y-m-d H:i:s'): The expiration date.
    -   `role_ids` (array of integers, optional): Array of role IDs that can use this code.
-   **Success Response (201):**
    ```json
    {
        "message": "Promo Code created successfully",
        "promo_code": { ... }
    }
    ```
-   **Error Response (422):**
    ```json
    {
      "error": "Invalid promo code data: The code already exists."
    }
    ```

---

## Update Promo Code

-   **Method:** `PUT`
-   **URL:** `/{id}`
-   **Permissions required:** `admin.promo-codes`
-   **Description:** Updates an existing promo code.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the promo code to update.
-   **Request Body:** (Same as create)
-   **Success Response (200):**
    ```json
    {
        "message": "Promo Code updated successfully",
        "promo_code": { ... }
    }
    ```

---

## Delete Promo Code

-   **Method:** `DELETE`
-   **URL:** `/{id}`
-   **Permissions required:** `admin.promo-codes`
-   **Description:** Deletes a promo code. Cannot be deleted if it has been used.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the promo code to delete.
-   **Success Response (200):**
    ```json
    {
      "message": "Promo Code deleted successfully"
    }
    ```
-   **Error Response (422):**
    ```json
    {
      "error": "Cannot delete promo code that has been used"
    }
    ```

---

## Validate Promo Code

-   **Method:** `POST`
-   **URL:** `/{id}/validate`
-   **Permissions required:** `payments.use`
-   **Description:** Validates if a promo code can be used.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the promo code.
-   **Request Body:**
    -   `user_id` (integer, nullable): The ID of the user attempting to use the code.
    -   `amount` (numeric, optional, default: 0): The amount of the transaction, to check against `minimum_amount`.
-   **Success Response (200):**
    ```json
    {
        "valid": true,
        "promo_code": { ... }
    }
    ```
-   **Error Response (422):**
    ```json
    {
        "error": "Promo code has expired."
    }
    ``` 