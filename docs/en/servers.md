# Servers

Provides functionality for managing game servers.

**Base URL:** `/api/servers`

**Permissions required:** `admin.servers`

---

## Get all Servers

-   **Method:** `GET`
-   **URL:** `/`
-   **Description:** Retrieves a list of all servers.
-   **Success Response (200):**
    ```json
    {
        "servers": [
            {
                "id": 1,
                "name": "Main Server",
                "ip": "127.0.0.1",
                "port": 27015,
                "mod": "csgo",
                "display_ip": "play.example.com",
                "connection_string": "play.example.com",
                "enabled": true,
                "createdAt": "2023-01-01T12:00:00+00:00"
            }
        ]
    }
    ```

---

## Get a single Server

-   **Method:** `GET`
-   **URL:** `/{id}`
-   **Description:** Retrieves details for a specific server.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the server.
-   **Success Response (200):**
    ```json
    {
        "server": {
            "id": 1,
            "name": "Main Server",
            "ip": "127.0.0.1",
            "port": 27015,
            "mod": "csgo",
            "display_ip": "play.example.com",
            "connection_string": "play.example.com",
            "enabled": true,
            "createdAt": "2023-01-01T12:00:00+00:00",
            "ranks": "default",
            "ranks_format": "webp",
            "db_connections": [
                {
                    "id": 1,
                    "mod": "sourcemod",
                    "dbname": "sm_main",
                    "additional": null
                }
            ]
        }
    }
    ```
-   **Error Response (404):**
    ```json
    {
      "message": "Server not found"
    }
    ```

---

## Create Server

-   **Method:** `POST`
-   **URL:** `/`
-   **Description:** Creates a new server.
-   **Request Body:**
    -   `name` (string, required, min: 3, max: 255, unique): The server name.
    -   `ip` (string, required, IPv4 format): The server IP address.
    -   `port` (integer, required, 1-65535): The server port.
    -   `mod` (string, required, min: 2, max: 50): The game/mod identifier (e.g., "csgo").
    -   `display_ip` (string, nullable, max: 255): An alternative IP or domain to display.
    -   `ranks` (string, nullable): The ranking system identifier.
    -   `ranks_format` (string, nullable, max: 255): The image format for rank icons (e.g., "webp").
    -   `enabled` (boolean, optional, default: true): Whether the server is enabled.
    -   `db_connections` (array of objects, nullable): Database connections for the server.
        -   `mod` (string, required): The mod the connection is for.
        -   `dbname` (string, required): The database name.
        -   `additional` (string, nullable): Additional connection parameters (JSON string).
-   **Success Response (201):**
    ```json
    {
        "message": "Server created successfully",
        "server": { ... }
    }
    ```
-   **Error Response (422):**
    ```json
    {
      "error": "Invalid server data: Server with this name already exists"
    }
    ```

---

## Update Server

-   **Method:** `PUT`
-   **URL:** `/{id}`
-   **Description:** Updates an existing server.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the server to update.
-   **Request Body:** (Same as create)
-   **Success Response (200):**
    ```json
    {
        "message": "Server updated successfully",
        "server": { ... }
    }
    ```

---

## Delete Server

-   **Method:** `DELETE`
-   **URL:** `/{id}`
-   **Description:** Deletes a server.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the server to delete.
-   **Success Response (200):**
    ```json
    {
      "message": "Server deleted successfully"
    }
    ```

---

## Toggle Server Status

-   **Method:** `POST`
-   **URL:** `/{id}/toggle`
-   **Description:** Enables or disables a server.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the server.
-   **Success Response (200):**
    ```json
    {
        "message": "Server disabled successfully",
        "enabled": false
    }
    ``` 