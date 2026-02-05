# Vibe To-Do 🌌

A lightweight to-do web app with a small PHP JSON API backend.

## Features

- Add tasks from the browser UI
- Remove tasks with one click
- Optional reminder date per task
- Browser notification reminders
- Dark mode toggle
- Task persistence via `tasks.json`

## Project structure

- `index.html` — frontend UI (Bootstrap + vanilla JavaScript)
- `api/tasks.php` — REST-like API for loading/adding/deleting tasks
- `tasks.json` — file-based storage

## Requirements

- PHP 7.4+ (or newer)
- A browser with JavaScript enabled

## Run locally

From the project root:

```bash
php -S 0.0.0.0:8000
```

Then open:

- `http://localhost:8000`

## API overview

### `GET /api/tasks.php`
Returns all tasks from `tasks.json`.

### `POST /api/tasks.php` (JSON)
Adds a task.

Example payload:

```json
{
  "text": "Buy groceries",
  "reminder": "2026-02-10",
  "done": false
}
```

### `POST /api/tasks.php` (form-encoded)
Deletes task(s) by exact text match.

Form fields:

- `action=delete`
- `text=<task text>`

### `DELETE /api/tasks.php?text=<task text>`
Deletes task(s) by exact text match.

## Notes

- If `tasks.json` does not exist, the API creates it automatically.
- Notifications require browser permission and only trigger while the page is open.
