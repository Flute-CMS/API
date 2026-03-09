# Roles

Provides functionality for managing user roles.

**Base URL:** `/api/roles`

**Permissions required:** `admin.roles`

---

## Get all Roles

-   **Method:** `GET`
-   **URL:** `/`
-   **Description:** Retrieves a list of all roles.
-   **Success Response (200):**
    ```json
    {
        "roles": [
            {
                "id": 1,
                "name": "Administrator",
                "color": "#FF0000",
                "priority": 100
            },
            {
                "id": 2,
                "name": "User",
                "color": "#0000FF",
                "priority": 10
            }
        ]
    }
    ```

---

## Get a single Role

-   **Method:** `GET`
-   **URL:** `/{id}`
-   **Description:** Retrieves details for a specific role.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the role.
-   **Success Response (200):**
    ```json
    {
        "role": {
            "id": 1,
            "name": "Administrator",
            "color": "#FF0000",
            "priority": 100,
            "permissions": [
                {
                    "id": 1,
                    "name": "admin.users"
                },
                {
                    "id": 2,
                    "name": "admin.pages"
                }
            ]
        }
    }
    ```
-   **Error Response (404):**
    ```json
    {
      "message": "Role not found"
    }
    ```

---

## Create Role

-   **Method:** `POST`
-   **URL:** `/`
-   **Description:** Creates a new role.
-   **Request Body:**
    -   `name` (string, required, min: 3, max: 100): The name of the role.
    -   `color` (string, optional, hex format): A color code for the role (e.g., "#FF5733").
    -   `priority` (integer, optional, min: 0): The priority of the role, for display order.
-   **Success Response (201):**
    ```json
    {
        "message": "Role created successfully",
        "role": {
            "id": 3,
            "name": "Moderator",
            "color": "#33FF57",
            "priority": 50
        }
    }
    ```
-   **Error Response (422):**
    ```json
    {
      "error": "Invalid role data: The name must be at least 3 characters."
    }
    ```

---

## Update Role

-   **Method:** `PUT`
-   **URL:** `/{id}`
-   **Description:** Updates an existing role.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the role to update.
-   **Request Body:** (Same as create)
-   **Success Response (200):**
    ```json
    {
        "message": "Role updated successfully",
        "role": {
            "id": 1,
            "name": "Super Administrator",
            "color": "#FFD700",
            "priority": 999
        }
    }
    ```

---

## Delete Role

-   **Method:** `DELETE`
-   **URL:** `/{id}`
-   **Description:** Deletes a role.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the role to delete.
-   **Success Response (200):**
    ```json
    {
      "message": "Role deleted successfully"
    }
    ```

---

## Update Role Permissions

-   **Method:** `PUT`
-   **URL:** `/{id}/permissions`
-   **Description:** Updates the permissions associated with a role.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the role.
-   **Request Body:**
    -   `permission_ids` (array of integers, required): An array of permission IDs.
-   **Success Response (200):**
    ```json
    {
        "message": "Role permissions updated successfully",
        "role": {
            "id": 1,
            "name": "Administrator",
            ...
            "permissions": [
                { "id": 1, "name": "admin.users" },
                { "id": 2, "name": "admin.pages" },
                { "id": 3, "name": "shop.manage.products" }
            ]
        }
    }
    ```
-   **Error Response (422):**
    ```json
    {
        "error": "Permission with ID 999 not found"
    }
    ``` 