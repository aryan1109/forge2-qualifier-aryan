# Forge 2 Qualifier Kanban

Minimum viable Forge 2 qualifier submission: a Laravel + SQLite REST API and a React + Vite Kanban board.

## Live URLs

- Frontend: TODO - deploy `frontend/` to Vercel
- Backend API: TODO - deploy `backend/` to Render

## Features

- Boards with a default Forge 2 board.
- Lists: Todo, Doing, Done.
- Cards with title, description, due date, list, and position.
- Create cards.
- Edit card title, description, and due date.
- Move cards between lists using a select menu.

## Backend Setup

Requirements: PHP 8.2+, Composer, SQLite extension.

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8000
```

Health check:

```bash
curl http://127.0.0.1:8000/api/health
```

## Frontend Setup

Requirements: Node 18+.

```bash
cd frontend
cp .env.example .env.local
npm install
npm run dev
```

Open the Vite URL and make sure `VITE_API_BASE_URL` points at the Laravel API.

## Models Used

- `Board`: owns the Kanban workspace.
- `ListModel`: represents a board column. The class name avoids PHP reserved-word friction around `List`.
- `Card`: stores card title, description, due date, list, and position.

## REST API

- `GET /api/health`
- `GET /api/boards/default`
- `GET /api/boards`
- `POST /api/boards`
- `GET /api/boards/{board}`
- `PATCH /api/boards/{board}`
- `DELETE /api/boards/{board}`
- `POST /api/boards/{board}/lists`
- `PATCH /api/lists/{listModel}`
- `DELETE /api/lists/{listModel}`
- `POST /api/lists/{listModel}/cards`
- `PATCH /api/cards/{card}`
- `POST /api/cards/{card}/move`
- `DELETE /api/cards/{card}`

## Deployment Notes

### Render Backend

Fast path:

1. Create a new Render Web Service from the public GitHub repo.
2. Set root directory to `backend`.
3. Use Docker runtime. The included `backend/Dockerfile` installs Composer dependencies, creates the SQLite file, runs migrations, and serves Laravel.
4. Add environment variables if needed:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://your-render-url`
   - `FRONTEND_URL=https://your-vercel-url`

### Vercel Frontend

Fast path:

1. Create a new Vercel project from the public GitHub repo.
2. Set root directory to `frontend`.
3. Set `VITE_API_BASE_URL=https://your-render-url/api`.
4. Deploy.

## Evidence

See `evidence/` and `screenshots/` for checklists and Slack setup evidence.
