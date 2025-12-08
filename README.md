# Todo API

> This API is a simple Todo application demonstrating common patterns such as resource controllers, service/repository layers, API resources, and authentication.

## Table of Contents

* [Features](#features)
* [Todo Functionality](#todo-functionality)
  * [Todo API Endpoints](#todo-api-endpoints)
  * [Todo Model Fields](#todo-model-fields)
* [Getting Started](#getting-started)
* [Usage](#usage)
* [Postman Collection](#postman-collection)
* [API Documentation](#api-documentation)
* [Testing](#testing)
* [Contributing](#contributing)
* [License](#license)

---

## Features

- User authentication (via Sanctum)
- RESTful API for managing todos
- Service and repository layers for business logic and data access
- API resource formatting

[⬆ back to top](#table-of-contents)

---

## Todo Functionality

The core of this app is a Todo management API. The main files involved are:

- [`app/Models/Todo.php`](app/Models/Todo.php): Eloquent model for todos, using soft deletes and factories for generating test data.
- [`database/migrations/2025_04_03_124307_create_todos_table.php`](database/migrations/2025_04_03_124307_create_todos_table.php): Migration for the `todos` table, with fields for `title`, `description`, `completed`, timestamps, and soft deletes.
- [`database/factories/TodoFactory.php`](database/factories/TodoFactory.php): Factory for generating fake todo data with realistic values.
- [`database/seeders/TodoSeeder.php`](database/seeders/TodoSeeder.php): Seeder that uses the `TodoFactory` to populate the database with sample todos.
- [`app/Repositories/TodoRepository.php`](app/Repositories/TodoRepository.php): Handles data access for todos.
- [`app/Services/TodoService.php`](app/Services/TodoService.php): Contains business logic for todos.
- [`app/Http/Controllers/TodoController.php`](app/Http/Controllers/TodoController.php): API controller for CRUD operations on todos.
- [`app/Http/Resources/TodoResource.php`](app/Http/Resources/TodoResource.php): Formats todo data for API responses.

### Todo API Endpoints

- `GET /api/todos` — List all todos
- `GET /api/todos/{id}` — Get a single todo
- `POST /api/todos` — Create a new todo
- `PUT/PATCH /api/todos/{id}` — Update a todo
- `DELETE /api/todos/{id}` — Delete a todo

All endpoints require authentication via Sanctum.

Authentication

- **Create token:** `POST /api/tokens` — Exchange user credentials for an access token (used with the `Authorization: Bearer <token>` header).

    Example request body:

    ```json
    {
        "email": "admin@example.com",
        "password": "admin"
    }
    ```

    Example `curl` to obtain a token:

    ```bash
    curl -X POST http://localhost:8000/api/tokens \
        -H "Content-Type: application/json" \
        -d '{"email":"admin@example.com","password":"admin"}'
    ```

    Use the returned token with `Authorization: Bearer <token>` for protected routes.

### Todo Model Fields

- `title` (string, required)
- `description` (text, optional)
- `completed` (boolean, default: false)
- `created_at`, `updated_at` (timestamps)
- `deleted_at` (for soft deletes)

[⬆ back to top](#table-of-contents)

---

## Getting Started

- **Clone the repository**

    ```bash
    git clone <repository-url>
    cd todo-api
    ```

- **Install Node.js dependencies**

    ```bash
    npm install
    ```

- **Install PHP dependencies**

    ```bash
    composer install
    ```

- **Copy environment file**

    ```bash
    cp .env.example .env
    ```

- **Generate application key**

    ```bash
    php artisan key:generate
    ```

- **Configure environment variables**

    - Edit `.env` to set your database and other settings.

- **Run database migrations and seeders**

    ```bash
    php artisan migrate:fresh --seed
    ```

    This will run all migrations and populate the database with initial data (users and todos).

    Alternatively, if you've already migrated:

    ```bash
    php artisan migrate
    php artisan db:seed
    ```

    **Default admin user:** The seeders create a default admin account for local development.
    - **Email:** `admin@example.com`
    - **Password:** `admin`

    Change this password immediately for any non-local environment.

- **(Optional) Set up pre-commit hook**

    To automatically run code checks before each commit, you can enable the provided pre-commit hook:

    ```bash
    cp .extras/git/pre-commit .git/hooks/pre-commit
    chmod +x .git/hooks/pre-commit
    ```

    This will run `composer run check` before every commit.

[⬆ back to top](#table-of-contents)

---

## Usage

- **Start the development server and assets watcher:**

    ```bash
    composer run dev
    ```

    This will:
    - Start the Laravel server
    - Start the queue listener
    - Start the log viewer (pail)
    - Start Vite for frontend assets

- Or, start servers individually:

    ```bash
    php artisan serve
    npm run dev
    ```

[⬆ back to top](#table-of-contents)

---

## Postman Collection

A ready-to-use Postman collection and environment are provided in the `.extras/postman` folder to help you quickly test the API endpoints.

- **Collection:**  
  [`todo-api.postman_collection.json`](.extras/postman/todo-api.postman_collection.json)  
  Contains requests for authentication, user info, and all Todo API operations (list, create, update, delete).

- **Environment:**  
  [`todo-api-development.postman_environment.json`](.extras/postman/todo-api-development.postman_environment.json)  
  Pre-configured with the base API URL and a placeholder for your access token.

[⬆ back to top](#table-of-contents)

---

## API Documentation

This project includes `dedoc/scramble` (a dev dependency) to generate OpenAPI-compatible API documentation automatically from the application code.

- **Routes added by Scramble:**
    - `/docs/api` — interactive UI viewer for the documentation
    - `/docs/api.json` — the OpenAPI JSON document describing the API

- **Notes:**
    - By default Scramble exposes these routes only in the `local` environment. To make them available in other environments, configure the `viewApiDocs` gate as documented at https://scramble.dedoc.co/usage/getting-started#docs-authorization.
    - The JSON document is available at `http://<your-app>/docs/api.json` and can be used with tools that accept OpenAPI/Swagger JSON.

[⬆ back to top](#table-of-contents)

---


## Testing

- **Run tests:**

    ```bash
    composer run check
    ```

[⬆ back to top](#table-of-contents)

---

## Contributing

Contributions are welcome! Please open an issue or submit a pull request.

[⬆ back to top](#table-of-contents)

---

## License

This project is licensed under the MIT License. See [LICENSE](LICENSE) for details.

[⬆ back to top](#table-of-contents)
