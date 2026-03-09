# Users

Provides functionality for managing users.

**Base URL:** `/api/users`

**Permissions required:** `admin.users`

---

## Get all Users

-   **Method:** `GET`
-   **URL:** `/`
-   **Description:** Retrieves a list of all users.
-   **Success Response (200):**
    ```json
    {
        "users": [
            {
                "id": 1,
                "name": "Admin",
                "login": "admin",
                "email": "admin@example.com",
                "avatar": null,
                "created_at": "2023-01-01T12:00:00+00:00",
                "is_online": true,
                "is_blocked": false,
                "roles": ["Administrator"]
            }
        ]
    }
    ```

---

## Search for Users

-   **Method:** `GET`
-   **URL:** `/search`
-   **Description:** Searches for users by name, login, or email.
-   **Query Parameters:**
    -   `q` (string, required, min: 3): The search query.
-   **Success Response (200):**
    ```json
    {
        "users": [ ... ]
    }
    ```
-   **Error Response (422):**
    ```json
    {
        "error": "Search query must be at least 3 characters long"
    }
    ```

---

## Get a single User

-   **Method:** `GET`
-   **URL:** `/{id}`
-   **Description:** Retrieves detailed information for a specific user.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the user.
-   **Success Response (200):** (Includes much more detail like banner, block info, full role objects, social networks, and devices)
    ```json
    {
        "user": {
            "id": 1,
            "name": "Admin",
            "login": "admin",
            ...
            "detailed_info": "..."
        }
    }
    ```
-   **Error Response (404):**
    ```json
    { "message": "User not found" }
    ```

---

## Create User

-   **Method:** `POST`
-   **URL:** `/`
-   **Description:** Creates a new user.
-   **Request Body:**
    -   `name` (string, required, min: 3, max: 100)
    -   `login` (string, required, min: 3, max: 50)
    -   `email` (string, required, email format)
    -   `password` (string, required, min: 8)
    -   `avatar` (string, nullable, max: 500)
    -   `banner` (string, nullable, max: 500)
-   **Success Response (201):**
    ```json
    {
        "message": "User created successfully",
        "user": { ... }
    }
    ```

---

## Update User

-   **Method:** `PUT`
-   **URL:** `/{id}`
-   **Description:** Updates an existing user's profile.
-   **URL Parameters:**
    -   `id` (integer, required): The user ID.
-   **Request Body:** (Same as create, `password` is optional)
-   **Success Response (200):**
    ```json
    {
        "message": "User updated successfully",
        "user": { ... }
    }
    ```

---

## Block User

-   **Method:** `POST`
-   **URL:** `/{id}/block`
-   **Description:** Blocks a user.
-   **URL Parameters:**
    -   `id` (integer, required): The user ID.
-   **Request Body:**
    -   `reason` (string, required): The reason for blocking the user.
    -   `until` (datetime, nullable, format: 'Y-m-d H:i:s'): The date until the user is blocked. If null, the block is permanent.
-   **Success Response (200):**
    ```json
    { "message": "User blocked successfully" }
    ```

---

## Unblock User

-   **Method:** `POST`
-   **URL:** `/{id}/unblock`
-   **Description:** Unblocks a user.
-   **URL Parameters:**
    -   `id` (integer, required): The user ID.
-   **Success Response (200):**
    ```json
    { "message": "User unblocked successfully" }
    ```

---

## Update User Roles

-   **Method:** `PUT`
-   **URL:** `/{id}/roles`
-   **Description:** Updates the roles assigned to a user.
-   **URL Parameters:**
    -   `id` (integer, required): The user ID.
-   **Request Body:**
    -   `role_ids` (array of integers, required): The list of role IDs to assign.
-   **Success Response (200):**
    ```json
    {
        "message": "User roles updated successfully",
        "user": { ... } // Detailed user object
    }
    ```

---

## Social Networks (User)

-   **Get Linked Accounts:**
    -   **Method:** `GET`
    -   **URL:** `/{id}/social-networks`
    -   **Description:** Get a user's linked social network accounts.
-   **Add Linked Account:**
    -   **Method:** `POST`
    -   **URL:** `/{id}/social-networks`
    -   **Description:** Link a new social network account.
    -   **Body:** `social_network_id` (int), `value` (string), `url` (string), `name` (string), `hidden` (bool).
-   **Remove Linked Account:**
    -   **Method:** `DELETE`
    -   **URL:** `/{userId}/social-networks/{networkId}`
    -   **Description:** Remove a linked social network account.

---

## Devices (User)

-   **Get Devices:**
    -   **Method:** `GET`
    -   **URL:** `/{id}/devices`
    -   **Description:** Get a list of a user's registered devices.
-   **Remove Device:**
    -   **Method:** `DELETE`
    -   **URL:** `/{userId}/devices/{deviceId}`
    -   **Description:** Remove a user's device.

---

## Delete User

-   **Method:** `DELETE`
-   **URL:** `/{id}/delete`
-   **Description:** Soft-deletes a user.
-   **URL Parameters:**
    -   `id` (integer, required): The user ID.
-   **Success Response (200):**
    ```json
    { "message": "User deleted successfully" }
    ``` 