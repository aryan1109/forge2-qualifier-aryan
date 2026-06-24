# Local Verification

Date: 2026-06-24

## Frontend

Command:

```bash
cd frontend
npm install
npm run build
```

Result:

- Vite production build passed.
- Build output was generated in `frontend/dist/`.

## Backend Tooling

Local PHP and Composer were not available on PATH at the start, so portable tooling was downloaded into `.tools/` for verification only.

- PHP: 8.5.7
- Composer: 2.10.1
- Laravel: 12.62.0
- SQLite extensions: `pdo_sqlite`, `sqlite3`

## Backend Checks

Commands verified:

```bash
cd backend
php artisan --version
php artisan route:list --path=api
php artisan migrate --seed --force
```

Results:

- Artisan booted successfully.
- API routes were registered.
- SQLite migrations completed.
- Seeder completed.

## HTTP Smoke Tests

`GET /api/health` returned:

```json
{
  "ok": true
}
```

`GET /api/boards/default` returned the default Forge 2 board with:

- Todo
- Doing
- Done

Card workflow verified:

- Created card in Todo.
- Edited card title, description, and due date.
- Moved card to Doing.

Final card result:

```json
{
  "created": "Submit Forge 2",
  "updated": "Submit Forge 2 qualifier",
  "moved_to_list": 2,
  "due_date": "2026-06-26"
}
```

## Screenshot Note

The in-app browser screenshot helper failed to initialize in this desktop session before it could open the local frontend. The existing Slack setup screenshot was copied to `evidence/slack-channels.png`; deployed app screenshots should be added after Vercel and Render are live.
