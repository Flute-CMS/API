# Permissions

Provides functionality for managing permissions.

**Base URL:** `/api/permissions`

**Permissions required:** `admin.permissions`

---

## Get all Permissions

-   **Method:** `GET`
-   **URL:** `/`
-   **Description:** Retrieves a list of all permissions available in the system.
-   **Success Response (200):**
    ```json
    {
        "permissions": [
            {
                "id": 1,
                "name": "admin.users",
                "description": "Manage users"
            },
            {
                "id": 2,
                "name": "admin.pages",
                "description": "Manage pages"
            }
        ]
    }
    ```

---

## Get a single Permission

-   **Method:** `GET`
-   **URL:** `/{id}`
-   **Description:** Retrieves details for a specific permission.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the permission.
-   **Success Response (200):**
    ```json
    {
        "permission": {
            "id": 1,
            "name": "admin.users",
            "description": "Allows managing users"
        }
    }
    ```
-   **Error Response (404):**
    ```json
    {
      "message": "Permission not found"
    }
    ```

---

## Create Permission

-   **Method:** `POST`
-   **URL:** `/`
-   **Description:** Creates a new permission.
-   **Request Body:**
    -   `name` (string, required, min: 3, max: 100): The permission key (e.g., "module.feature.action").
    -   `desc` (string, required, min: 3, max: 255): A short description of the permission.
-   **Success Response (201):**
    ```json
    {
        "message": "Permission created successfully",
        "permission": {
            "id": 3,
            "name": "shop.manage.products",
            "description": "Allows managing shop products"
        }
    }
    ```
-   **Error Response (422):**
    ```json
    {
      "error": "Invalid permission data: The name must be at least 3 characters."
    }
    ```

---

## Update Permission

-   **Method:** `PUT`
-   **URL:** `/{id}`
-   **Description:** Updates an existing permission.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the permission to update.
-   **Request Body:**
    -   `name` (string, required, min: 3, max: 100): The permission key.
    -   `desc` (string, required, min: 3, max: 255): The description.
-   **Success Response (200):**
    ```json
    {
        "message": "Permission updated successfully",
        "permission": {
            "id": 1,
            "name": "admin.users.manage",
            "description": "Allows full management of users"
        }
    }
    ```

---

## Delete Permission

-   **Method:** `DELETE`
-   **URL:** `/{id}`
-   **Description:** Deletes a permission.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the permission to delete.
-   **Success Response (200):**
    ```json
    {
      "message": "Permission deleted successfully"
    }
    ``` 