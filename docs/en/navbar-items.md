# Navbar Items

Provides functionality for managing navigation bar items.

**Base URL:** `/api/navbar-items`

**Permissions required:** `admin.navbar`

---

## Get all Navbar Items

-   **Method:** `GET`
-   **URL:** `/`
-   **Description:** Retrieves a list of all navbar items.
-   **Success Response (200):**
    ```json
    {
        "navbar_items": [
            {
                "id": 1,
                "title": "Home",
                "url": "/",
                "new_tab": false,
                "icon": "home-icon",
                "position": 0,
                "visible_only_for_guests": false,
                "visible_only_for_logged_in": false,
                "visibility": "all",
                "parent_id": null
            }
        ]
    }
    ```

---

## Get a single Navbar Item

-   **Method:** `GET`
-   **URL:** `/{id}`
-   **Description:** Retrieves details for a specific navbar item.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the navbar item.
-   **Success Response (200):**
    ```json
    {
        "navbar_item": {
            "id": 1,
            "title": "Home",
            "url": "/",
            "new_tab": false,
            "icon": "home-icon",
            "position": 0,
            "visible_only_for_guests": false,
            "visible_only_for_logged_in": false,
            "visibility": "all",
            "parent_id": null,
            "roles": [
                {"id": 1, "name": "Admin"},
                {"id": 2, "name": "User"}
            ],
            "children": []
        }
    }
    ```
-   **Error Response (404):**
    ```json
    {
      "message": "Navbar Item not found"
    }
    ```

---

## Create Navbar Item

-   **Method:** `POST`
-   **URL:** `/`
-   **Description:** Creates a new navbar item.
-   **Request Body:**
    -   `title` (string, required, min: 1, max: 255)
    -   `url` (string, nullable, max: 500)
    -   `new_tab` (boolean, optional, default: false)
    -   `icon` (string, nullable, max: 100)
    -   `position` (integer, optional, default: 0)
    -   `visible_only_for_guests` (boolean, optional, default: false)
    -   `visible_only_for_logged_in` (boolean, optional, default: false)
    -   `visibility` (string, optional, default: 'all', in: ['all', 'desktop', 'mobile'])
    -   `parent_id` (integer, nullable)
-   **Success Response (201):**
    ```json
    {
        "message": "Navbar Item created successfully",
        "navbar_item": {
            "id": 2,
            "title": "About",
            "url": "/about",
            "new_tab": false,
            "icon": null,
            "position": 1,
            "visible_only_for_guests": false,
            "visible_only_for_logged_in": false,
            "visibility": "all",
            "parent_id": null
        }
    }
    ```
-   **Error Response (422):**
    ```json
    {
      "error": "Invalid navbar item data: ..."
    }
    ```

---

## Update Navbar Item

-   **Method:** `PUT`
-   **URL:** `/{id}`
-   **Description:** Updates an existing navbar item.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the navbar item to update.
-   **Request Body:** (Same as create)
-   **Success Response (200):**
    ```json
    {
        "message": "Navbar Item updated successfully",
        "navbar_item": {
            "id": 1,
            "title": "Home Page",
            "url": "/",
            "new_tab": false,
            "icon": "home-icon",
            "position": 0,
            "visible_only_for_guests": false,
            "visible_only_for_logged_in": false,
            "visibility": "all",
            "parent_id": null
        }
    }
    ```

---

## Delete Navbar Item

-   **Method:** `DELETE`
-   **URL:** `/{id}`
-   **Description:** Deletes a navbar item.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the navbar item to delete.
-   **Success Response (200):**
    ```json
    {
      "message": "Navbar Item deleted successfully"
    }
    ```

---

## Update Navbar Item Roles

-   **Method:** `PUT`
-   **URL:** `/{id}/roles`
-   **Description:** Updates the roles associated with a navbar item.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the navbar item.
-   **Request Body:**
    -   `role_ids` (array of integers, required): An array of role IDs.
-   **Success Response (200):**
    ```json
    {
        "message": "Navbar Item roles updated successfully",
        "navbar_item": {
            "id": 1,
            "title": "Home",
            "url": "/",
            ...
            "roles": [
                {"id": 1, "name": "Admin"}
            ],
            "children": []
        }
    }
    ```

---

## Reorder Navbar Items

-   **Method:** `POST`
-   **URL:** `/reorder`
-   **Description:** Updates the order of multiple navbar items.
-   **Request Body:**
    -   `order` (array of objects, required): Each object must contain `id` (integer) and `position` (integer).
    -   Example: `{"order": [{"id": 1, "position": 2}, {"id": 2, "position": 1}]}`
-   **Success Response (200):**
    ```json
    {
        "message": "Navbar Items reordered successfully"
    }
    ```
-   **Error Response (422):**
    ```json
    {
        "error": "No order data provided"
    }
    ``` 