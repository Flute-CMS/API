# API Keys

Provides functionality for managing API keys.

**Base URL:** `/api/api-keys`

**Permissions required:** `admin.api-keys`

---

## Get all API Keys

-   **Method:** `GET`
-   **URL:** `/`
-   **Description:** Retrieves a list of all API keys.
-   **Success Response (200):**
    ```json
    {
      "api_keys": [
        {
          "id": 1,
          "name": "Default Key",
          "key": "a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4",
          "created_at": "2023-01-01 12:00:00",
          "last_used_at": "2023-01-10 08:30:00"
        }
      ]
    }
    ```

---

## Get a single API Key

-   **Method:** `GET`
-   **URL:** `/{id}`
-   **Description:** Retrieves details for a specific API key.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the API key.
-   **Success Response (200):**
    ```json
    {
      "api_key": {
        "id": 1,
        "name": "Default Key",
        "key": "a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4",
        "created_at": "2023-01-01 12:00:00",
        "last_used_at": "2023-01-10 08:30:00",
        "permissions": [
          {
            "id": 1,
            "name": "admin.users",
            "description": "Manage users"
          }
        ]
      }
    }
    ```
-   **Error Response (404):**
    ```json
    {
      "message": "API Key not found"
    }
    ```

---

## Create API Key

-   **Method:** `POST`
-   **URL:** `/`
-   **Description:** Creates a new API key.
-   **Request Body:**
    -   `name` (string, required, min: 3, max: 100): The name for the API key.
-   **Success Response (201):**
    ```json
    {
      "message": "API Key создан успешно",
      "api_key": {
        "id": 2,
        "name": "New Key",
        "key": "f6e5d4c3b2a1f6e5d4c3b2a1f6e5d4c3",
        "created_at": "2023-01-11 10:00:00",
        "last_used_at": null
      }
    }
    ```
-   **Error Response (422):**
    ```json
    {
      "error": "Неверные данные API ключа: The name must be at least 3 characters."
    }
    ```

---

## Update API Key

-   **Method:** `PUT`
-   **URL:** `/{id}`
-   **Description:** Updates an existing API key's name.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the API key to update.
-   **Request Body:**
    -   `name` (string, required, min: 3, max: 100): The new name for the API key.
-   **Success Response (200):**
    ```json
    {
      "message": "API Key обновлен успешно",
      "api_key": {
        "id": 1,
        "name": "Updated Key Name",
        "key": "a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4",
        "created_at": "2023-01-01 12:00:00",
        "last_used_at": "2023-01-10 08:30:00"
      }
    }
    ```

---

## Delete API Key

-   **Method:** `DELETE`
-   **URL:** `/{id}`
-   **Description:** Deletes an API key.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the API key to delete.
-   **Success Response (200):**
    ```json
    {
      "message": "API Key удален успешно"
    }
    ```

---

## Regenerate API Key

-   **Method:** `POST`
-   **URL:** `/{id}/regenerate`
-   **Description:** Generates a new key for an existing API key.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the API key.
-   **Success Response (200):**
    ```json
    {
      "message": "API Key перегенерирован успешно",
      "api_key": {
        "id": 1,
        "name": "Updated Key Name",
        "key": "newlygeneratedkeynewlygeneratedkey",
        "created_at": "2023-01-01 12:00:00",
        "last_used_at": "2023-01-10 08:30:00"
      }
    }
    ```

---

## Update API Key Permissions

-   **Method:** `PUT`
-   **URL:** `/{id}/permissions`
-   **Description:** Updates the permissions associated with an API key.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the API key.
-   **Request Body:**
    -   `permission_ids` (array of integers, required): An array of permission IDs to associate with the key.
-   **Success Response (200):**
    ```json
    {
      "message": "Разрешения API Key обновлены успешно",
      "api_key": {
        "id": 1,
        "name": "Default Key",
        "key": "a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4",
        "created_at": "2023-01-01 12:00:00",
        "last_used_at": "2023-01-10 08:30:00",
        "permissions": [
            {
                "id": 2,
                "name": "admin.pages",
                "description": "Manage pages"
            }
        ]
      }
    }
    ```
-   **Error Response (422):**
    ```json
    {
        "error": "Разрешение с ID 999 не найдено"
    }
    ``` 