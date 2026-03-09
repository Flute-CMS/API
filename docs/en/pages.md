# Pages

Provides functionality for managing custom pages.

**Base URL:** `/api/flute-pages`

**Permissions required:** `admin.pages`

---

## Get all Pages

-   **Method:** `GET`
-   **URL:** `/`
-   **Description:** Retrieves a list of all pages.
-   **Success Response (200):**
    ```json
    {
        "pages": [
            {
                "id": 1,
                "route": "/example-page",
                "title": "Example Page",
                "description": "This is an example page.",
                "keywords": "example, page",
                "robots": "index, follow",
                "og_image": "http://example.com/image.jpg"
            }
        ]
    }
    ```

---

## Get a single Page

-   **Method:** `GET`
-   **URL:** `/{id}`
-   **Description:** Retrieves details for a specific page.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the page.
-   **Success Response (200):**
    ```json
    {
        "page": {
            "id": 1,
            "route": "/example-page",
            "title": "Example Page",
            "description": "This is an example page.",
            "keywords": "example, page",
            "robots": "index, follow",
            "og_image": "http://example.com/image.jpg",
            "blocks": [
                {
                    "id": 1,
                    "type": "html",
                    "title": "Content Block",
                    "content": "<h1>Hello World</h1>",
                    "order": 1,
                    "is_active": true
                }
            ],
            "permissions": [
                 {
                    "id": 1,
                    "name": "public"
                 }
            ]
        }
    }
    ```
-   **Error Response (404):**
    ```json
    {
      "message": "Page not found"
    }
    ```

---

## Create Page

-   **Method:** `POST`
-   **URL:** `/`
-   **Description:** Creates a new page.
-   **Request Body:**
    -   `route` (string, required, min: 1, max: 255): The URL route for the page (e.g., "/my-new-page").
    -   `title` (string, required, min: 3, max: 255): The title of the page.
    -   `description` (string, nullable, max: 1000): SEO description.
    -   `keywords` (string, nullable, max: 500): SEO keywords.
    -   `robots` (string, nullable, max: 100): Robots meta tag content (e.g., "noindex, nofollow").
    -   `og_image` (string, nullable, max: 500): URL for the OpenGraph image.
-   **Success Response (201):**
    ```json
    {
        "message": "Page created successfully",
        "page": {
            "id": 2,
            "route": "/new-page",
            "title": "New Page",
            "description": null,
            "keywords": null,
            "robots": null,
            "og_image": null
        }
    }
    ```
-   **Error Response (422):**
    ```json
    {
      "error": "Invalid page data: The route field is required."
    }
    ```

---

## Update Page

-   **Method:** `PUT`
-   **URL:** `/{id}`
-   **Description:** Updates an existing page.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the page to update.
-   **Request Body:** (Same as create)
-   **Success Response (200):**
    ```json
    {
        "message": "Page updated successfully",
        "page": {
            "id": 1,
            "route": "/updated-page",
            "title": "Updated Page Title",
            "description": "Updated description.",
            "keywords": "updated, keywords",
            "robots": "index, follow",
            "og_image": null
        }
    }
    ```

---

## Delete Page

-   **Method:** `DELETE`
-   **URL:** `/{id}`
-   **Description:** Deletes a page.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the page to delete.
-   **Success Response (200):**
    ```json
    {
      "message": "Page deleted successfully"
    }
    ```

---

## Get Page Blocks

-   **Method:** `GET`
-   **URL:** `/{id}/blocks`
-   **Description:** Retrieves the content blocks for a specific page.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the page.
-   **Success Response (200):**
    ```json
    {
        "blocks": [
            {
                "id": 1,
                "type": "html",
                "title": "Content Block",
                "content": "<h1>Hello World</h1>",
                "order": 1,
                "is_active": true
            }
        ]
    }
    ```

---

## Update Page Permissions

-   **Method:** `PUT`
-   **URL:** `/{id}/permissions`
-   **Description:** Updates the permissions required to view a page.
-   **URL Parameters:**
    -   `id` (integer, required): The ID of the page.
-   **Request Body:**
    -   `permission_ids` (array of integers, required): An array of permission IDs.
-   **Success Response (200):**
    ```json
    {
        "message": "Page permissions updated successfully",
        "page": {
            "id": 1,
            "route": "/example-page",
            "title": "Example Page",
            ...
            "permissions": [
                {
                    "id": 2,
                    "name": "members_only"
                }
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