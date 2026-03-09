# Social Networks

Provides functionality for managing social network integrations for user authentication and linking.

**Base URL:** `/api/social-networks`

**Permissions required:** `admin.social-networks`

---

## Get all Social Networks

-   **Method:** `GET`
-   **URL:** `/`
-   **Description:** Retrieves a list of all configured social networks.
-   **Success Response (200):**
    ```json
    {
        "social_networks": [
            {
                "id": 1,
                "key": "steam",
                "cooldown_time": 30,
                "allow_to_register": true,
                "icon": "<svg>...</svg>",
                "enabled": true
            }
        ]
    }
    ```

---

## Get a single Social Network

-   **Method:** `GET`
-   **URL:** `/{id}`
-   **Description:** Retrieves details for a specific social network.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the social network.
-   **Success Response (200):**
    ```json
    {
        "social_network": {
            "id": 1,
            "key": "steam",
            "cooldown_time": 30,
            "allow_to_register": true,
            "icon": "<svg>...</svg>",
            "enabled": true,
            "settings": {
                "api_key": "..."
            },
            "users_count": 150
        }
    }
    ```
-   **Error Response (404):**
    ```json
    {
      "message": "Social Network not found"
    }
    ```

---

## Create Social Network

-   **Method:** `POST`
-   **URL:** `/`
-   **Description:** Creates a new social network configuration.
-   **Request Body:**
    -   `key` (string, required, min: 2, max: 50): Unique key for the network (e.g., "steam", "discord").
    -   `settings` (string, required, JSON format): JSON string containing API keys and other settings.
    -   `cooldown_time` (integer, optional, min: 0): Cooldown in seconds between linking attempts.
    -   `allow_to_register` (boolean, optional, default: true): Allow new user registration via this network.
    -   `icon` (string, required): SVG, icon class, or image URL for the network's icon.
    -   `enabled` (boolean, optional, default: false): Whether this integration is active.
-   **Success Response (201):**
    ```json
    {
        "message": "Social Network created successfully",
        "social_network": { ... }
    }
    ```
-   **Error Response (422):**
    ```json
    {
      "error": "Invalid social network data: The key is required."
    }
    ```

---

## Update Social Network

-   **Method:** `PUT`
-   **URL:** `/{id}`
-   **Description:** Updates an existing social network configuration.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the network to update.
-   **Request Body:** (Same as create)
-   **Success Response (200):**
    ```json
    {
        "message": "Social Network updated successfully",
        "social_network": { ... }
    }
    ```

---

## Delete Social Network

-   **Method:** `DELETE`
-   **URL:** `/{id}`
-   **Description:** Deletes a social network configuration. Fails if users are linked to it.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the network to delete.
-   **Success Response (200):**
    ```json
    {
      "message": "Social Network deleted successfully"
    }
    ```
-   **Error Response (422):**
    ```json
    {
      "error": "Cannot delete social network as it is being used by users"
    }
    ```

---

## Get Linked Users

-   **Method:** `GET`
-   **URL:** `/users/{networkId}`
-   **Description:** Retrieves a list of users who have linked their account with a specific social network.
-   **URL Parameters:**
    -   `networkId` (integer, required): The ID of the social network.
-   **Success Response (200):**
    ```json
    {
        "users": [
            {
                "id": 101,
                "name": "User One",
                "login": "userone",
                "value": "76561197960287930",
                "url": "http://steamcommunity.com/id/userone/",
                "linked_at": "2023-01-15T10:30:00+00:00"
            }
        ]
    }
    ``` 