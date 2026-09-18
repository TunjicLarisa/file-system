# File System

A browser-based file system.

The application allows users to browse folders, create nested folders and files, search for files, and delete files or entire folder trees.

Files in this project contain only a name and do not store file contents.

## Features

- Create folders and nested subfolders
- Create files inside folders or in the root(Home) directory
- Browse folder contents
- Delete files
- Delete folders and their nested contents
- Search for a file by exact name
- Search within the current folder or across all files
- Show up to 10 filename suggestions while typing
- Prevent duplicate file or folder names within the same parent folder
- Server-side validation and appropriate HTTP responses
- Feature tests for the core API behaviour

## Tech Stack

- PHP 8.4
- Laravel 13
- Vue 3
- Vue Options API
- Inertia.js
- Tailwind CSS
- SQLite
- Pest / Laravel testing tools
- Vite

## Architecture

The application is separated into three main areas:

- **Frontend** – Vue components responsible for the file browser UI
- **API** – Laravel controllers, requests and resources expose JSON endpoints
- **Data layer** – Eloquent models backed by SQLite

Inertia is used only to serve the initial Vue page. File-system data is loaded and modified through the JSON API using `fetch`.

### Data model

Folders use a self-referencing relationship:

- `folders.parent_id` references another folder
- `parent_id = null` represents a folder in the root directory

Files belong to a folder:

- `files.folder_id` references a folder
- `folder_id = null` represents a file in the root directory

Foreign keys use cascading deletes. Deleting a folder therefore also deletes its nested folders and files.

## Search behaviour

Two types of search are supported.

**Suggestions**

While typing, the API returns up to 10 files whose names start with the provided search string.

**Exact search**

Submitting a search searches for the exact filename.

Both search modes can operate either:

- inside the currently selected folder
- across all files

When searching across all files, the API also returns the folder path so files with the same or similar names can be located.

## Installation

Clone the repository:

```bash
git clone https://github.com/TunjicLarisa/file-system.git
cd file-system
```

Install PHP dependencies:

```bash
    composer install
```

Install JavaScript dependencies:

```bash
    npm install
```

Create the environment file:

```bash
    php -r "copy('.env.example', '.env');"
```

Generate an application key:

```bash
    php artisan key:generate
```

Create the SQLite database:

```bash
    php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
```

Run the database migrations and seed example data:

```bash
    php artisan migrate --seed
```

## Running locally

Start the Laravel development server:

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

If using Laravel Herd, the project can instead be accessed through its Herd URL, for example:

```text
    http://file-system.test
```

Vite still needs to be running:

```bash
    npm run dev
```

## Tests

Run the automated test suite with:

```bash
    php artisan test
```

The feature tests cover core behaviour including:

- creating root folders and nested folders
- creating files
- deleting files
- cascading folder deletion
- exact filename search
- prefix-based filename suggestions
- the 10-result suggestion limit