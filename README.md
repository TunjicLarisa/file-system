# File System

A browser-based file system built with Laravel and Vue.

The application allows users to browse folders, create nested folders and files,
search for files, and delete files or complete folder trees.

Files are represented only by their names and do not contain file contents.

## Features

- Create folders and nested subfolders
- Create files in folders or in the root directory
- Browse folder contents
- Delete files
- Delete folders together with their nested contents
- Search for a file by exact name
- Search within the current folder or across all files
- Show up to 10 filename suggestions while typing
- Prevent duplicate file or folder names within the same parent folder
- Server-side validation and appropriate HTTP responses
- Feature tests covering the core API behaviour
- Docker setup with persistent SQLite storage

> Pagination is also used for folder and file listings to avoid loading an
> unbounded number of records at once.

## Tech Stack

- PHP 8.3+ (PHP 8.4 used by Docker)
- Laravel 13
- Vue 3
- Vue Options API
- Inertia.js
- Tailwind CSS
- SQLite
- Pest / PHPUnit
- Vite
- Docker

## Requirements

### Recommended: Docker

The easiest way to run the project is with Docker.

Requirements:

- Git
- Docker Desktop, or Docker Engine with Docker Compose

No local PHP, Composer, Node.js or SQLite installation is required when using
the Docker setup.

### Local setup

For running the project without Docker, the following tools are required:

- PHP 8.3 or newer
- Composer 2.x
- Node.js 22 LTS or newer
- npm
- SQLite support
- PHP extensions:
  - `pdo_sqlite`
  - `sqlite3`
  - `mbstring`
  - `zip`

### Windows

The simplest local setup on Windows is Laravel Herd, which provides PHP,
Composer and a local web server.

Node.js must still be installed separately.

Verify the required tools:

```powershell
php -v
composer -V
node -v
npm -v
```

Verify SQLite support:

```powershell
php -m | findstr /I "pdo_sqlite sqlite3"
```

If the SQLite extensions are missing from a standalone PHP installation,
enable the appropriate SQLite extensions in the active `php.ini`.

To find the active configuration file:

```powershell
php --ini
```

### macOS

Laravel Herd can also be used on macOS.

Alternatively, PHP, Composer and Node.js can be installed using a package
manager such as Homebrew.

Verify the required tools:

```bash
php -v
composer -V
node -v
npm -v
```

Verify SQLite support:

```bash
php -m | grep -Ei "pdo_sqlite|sqlite3"
```

## Getting Started

Clone the repository:

```bash
git clone https://github.com/TunjicLarisa/file-system.git
cd file-system
```

## Docker

Build and start the application:

```bash
docker compose up --build
```

The application will be available at:

```text
http://localhost:8000
```

On the first start, Docker creates the SQLite database, runs the migrations
and seeds example data automatically.

### Run tests inside Docker

The test suite uses a separate in-memory SQLite database so the development
database is not modified:

```bash
docker compose exec -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: app php artisan test
```

Check migration status:

```bash
docker compose exec app php artisan migrate:status
```

Stop the application:

```bash
docker compose down
```

Remove the containers and persisted SQLite database:

```bash
docker compose down -v
```

The SQLite database is stored in a named Docker volume, so data persists
between normal container restarts.

The Docker image uses built frontend assets with a Laravel debug backend.
For frontend hot module replacement and the full development experience,
use the local setup with `npm run dev`.

## Local Installation

Install PHP dependencies:

```bash
composer install
```

Install JavaScript dependencies:

```bash
npm ci
```

Create the environment file:

```bash
php -r "copy('.env.example', '.env');"
```

Generate the Laravel application key:

```bash
php artisan key:generate
```

Create the SQLite database:

```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
```

Run migrations and seed example data:

```bash
php artisan migrate --seed
```

## Running Locally

### Using Laravel's development server

Start Laravel:

```bash
php artisan serve
```

In another terminal, start Vite:

```bash
npm run dev
```

The application will normally be available at:

```text
http://127.0.0.1:8000
```

### Using Laravel Herd

When using Laravel Herd, the Laravel development server does not need to be
started manually.

Start Vite:

```bash
npm run dev
```

The application can then be accessed through the Herd URL, for example:

```text
http://file-system.test
```

## Architecture

The application is separated into three main areas:

- **Frontend** — Vue handles the browser UI and communicates with the backend using `fetch`
- **API** — Laravel controllers, FormRequests and API Resources expose JSON endpoints
- **Data layer** — Eloquent models backed by SQLite

Inertia is used only to serve the initial Vue page. File-system operations
are performed through the JSON API.

### Data Model

Folders have a self-referencing relationship:

- `folders.parent_id` references another folder
- `parent_id = null` represents the root directory

Files belong to folders:

- `files.folder_id` references a folder
- `folder_id = null` represents the root directory

Foreign keys use cascading deletes. Deleting a folder therefore also removes
its nested folders and files.

Folder and file listing endpoints are paginated to avoid loading an unbounded
number of records into the browser.

## API

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/api/folders` | List folders in a parent folder |
| POST | `/api/folders` | Create a folder |
| DELETE | `/api/folders/{folder}` | Delete a folder and nested contents |
| GET | `/api/files` | List files in a folder |
| POST | `/api/files` | Create a file |
| DELETE | `/api/files/{file}` | Delete a file |
| GET | `/api/search/suggestions` | Return up to 10 prefix matches |
| GET | `/api/search/exact` | Search for an exact filename |

Folder and file listing endpoints return 50 records per page.

## Search Behaviour

### Suggestions

While typing, the API returns up to 10 files whose names start with the
provided search string.

Search input is treated literally, including SQL `LIKE` wildcard characters
such as `%` and `_`.

### Exact Search

Submitting a search looks for the exact filename.

Both search modes can operate:

- inside the currently selected folder
- across all files

When searching across all files, results also contain the folder path.

## Tests

Run the automated test suite locally:

```bash
php artisan test
```

Run the test suite inside Docker:

```bash
docker compose exec -e APP_ENV=testing -e DB_CONNECTION=sqlite -e DB_DATABASE=:memory: app php artisan test
```

The feature tests cover behaviour including:

- root and nested folder creation
- file creation
- duplicate-name validation
- file deletion
- cascading folder deletion
- exact filename search
- prefix suggestions
- search scoping by folder
- searching across all folders
- the 10-result suggestion limit
- paginated file and folder listings

## Scope and Trade-offs

SQLite was chosen because the assignment explicitly permits a file database
and it keeps the project self-contained.

Vue 3 with the Options API was used for the frontend. The application contains
a single main screen, so the Options API keeps state and actions explicit
without introducing additional composition abstractions.

Inertia is used only for the initial page response. File-system functionality
is deliberately exposed through JSON API endpoints to keep the UI and backend
logic separated.

Authentication and authorization are intentionally omitted because they are
outside the assignment scope.

Deletion is permanent. Soft deletes were considered but removed because restore
functionality is outside the requirements and database-level cascading deletes
provide simpler and predictable folder deletion semantics.

SQLite is appropriate for this self-contained assignment. For a larger
production deployment with higher concurrency requirements, a server-based
database would be a more appropriate persistence layer.

For a substantially larger production system, possible next steps would include:

- cursor-based pagination for very large directories
- a materialized-path or similar strategy for efficient folder-path lookup
- extracting the file-manager screen into smaller UI components
- moving from SQLite to a server database depending on concurrency requirements