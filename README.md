# Discord Moderation Bot: Laravel API

A Laravel backend for a Discord moderation bot. The bot never connects to the database itself. Everything it stores or reads (server configuration, moderation cases, message history, timed bans) goes through this API, and Laravel is the only part of the system that talks to MySQL.

The bot itself is a separate Python project (`discord.py`). This repository contains only the API.

## Why an API layer

The bot used to open a direct MySQL connection, which meant it held the database password and could run any query. Putting Laravel in between changes that:

- The bot only holds an API key. The database credentials exist only in Laravel's `.env`.
- The bot can only do what the endpoints allow, not run arbitrary SQL.
- Validation, case numbering and data trimming happen in one place, not in every bot command.

## Architecture

```
Discord  <-->  Python bot (discord.py)  --HTTP + API key-->  Laravel API  -->  MySQL
```

## Features

- **Server configuration**: store and read the role and channel IDs created by the bot's `!setup` command, and change individual log channels afterwards.
- **Moderation cases**: record bans, unbans and other actions with a per-server case number, and read them back by case or by user.
- **Message history**: keep the last 25 messages per user per server, used to build ban-proof transcripts.
- **Timed bans**: record when a temporary ban should end, and remove the record when the user is unbanned early.

## Tech stack

| Part | Technology |
|---|---|
| Backend | Laravel (PHP 8.2 or newer) |
| Database | MySQL / MariaDB |
| Auth | Shared secret in a custom middleware (`BotApiKey`) |
| Local server | Laravel Herd or `php artisan serve` |
| Client | Python bot using `aiohttp` |

## Getting started

### Requirements

- PHP 8.2 or newer and Composer
- MySQL running locally
- Laravel Herd (or use `php artisan serve`)

### Install

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### Configure `.env`

| Variable | Purpose |
|---|---|
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` / `DB_PORT` | MySQL host and port, e.g. `127.0.0.1` / `3306` |
| `DB_DATABASE` | Database name, e.g. `priv` (create it first) |
| `DB_USERNAME` / `DB_PASSWORD` | Database login. A dedicated user with only SELECT, INSERT, UPDATE and DELETE is recommended |
| `BOT_API_KEY` | Long random secret shared with the bot. Generate one with `php -r "echo bin2hex(random_bytes(32));"` |
| `SESSION_DRIVER` | `file` (the API does not need database sessions) |

Then register the key in `config/services.php`:

```php
'bot' => ['key' => env('BOT_API_KEY')],
```

### Create the tables

```bash
php artisan migrate
```

The migrations create `roles`, `channels`, `moderation_actions`, `user_message_history` and `temp_moderations`, and add the extra log-channel columns. Use plain `migrate` on this database. `migrate:fresh` would drop every table, including the stored moderation history.

### Run it

```bash
php artisan serve
```

With Herd, the project is served automatically at `http://<folder-name>.test`.

### Check that it works

```bash
# Without the key: 401 Unauthorized
curl -i -H "Accept: application/json" http://127.0.0.1:8000/api/test

# With the key: {"message":"API is working!"}
curl -i -H "Accept: application/json" -H "Authorization: Bearer YOUR_BOT_API_KEY" http://127.0.0.1:8000/api/test
```

On Windows PowerShell use `curl.exe` instead of `curl`, because `curl` is an alias for a different command there.

The bot needs two values in its own `.env`:

```
API_BASE_URL=http://127.0.0.1:8000/api
BOT_API_KEY=the same key as above
```

## Authentication

Every API route sits inside a route group using the `bot` middleware. The middleware reads the `Authorization: Bearer <key>` header and compares it with `BOT_API_KEY` using `hash_equals`, which avoids leaking information through timing. Anything else gets `401 Unauthorized` and never reaches a controller.

The middleware is registered under the alias `bot` in `bootstrap/app.php` and applied in `routes/api.php`.

## API reference

All routes are prefixed with `/api` and need the bearer key. Send `Accept: application/json` so errors come back as JSON.

| Method | Path | Purpose |
|---|---|---|
| GET | `/test` | Health check |
| GET | `/guilds/{guildId}/config` | Role and channel IDs for a server (404 if it was never set up) |
| POST | `/guilds/{guildId}/config` | Save the config after `!setup` (both tables in one transaction) |
| DELETE | `/guilds/{guildId}/config` | Remove a server's config (used by `!nuke`) |
| PATCH | `/guilds/{guildId}/channels` | Change one log channel (only allowed columns) |
| POST | `/guilds/{guildId}/moderation-actions` | Record an action, returns the new case number |
| GET | `/guilds/{guildId}/moderation-actions/{caseId}` | One case |
| GET | `/guilds/{guildId}/users/{userId}/moderation-actions` | A user's full history |
| POST | `/guilds/{guildId}/messages` | Store a message (keeps the newest 25 per user) |
| GET | `/guilds/{guildId}/users/{userId}/messages` | A user's stored messages, oldest first |
| POST | `/guilds/{guildId}/temp-moderations` | Record when a timed ban ends |
| DELETE | `/guilds/{guildId}/users/{userId}/temp-moderations` | Remove that record |

### Example: record a ban

```
POST /api/guilds/1501842125371871332/moderation-actions
```

```json
{
  "user_id": 1263769559505047644,
  "action": "ban",
  "reason": "spam",
  "mod_id": 902170494839697468,
  "duration": "5d",
  "username": "example_user"
}
```

Response, `201 Created`:

```json
{ "case_id": 12 }
```

Invalid input returns `422` with the failing fields. The server fills in `guild_id`, `case_id` and the timestamp, and the bot cannot override them.

## Database

| Table | Contents |
|---|---|
| `roles` | One row per server with the IDs of the roles created by `!setup` |
| `channels` | One row per server with the IDs of the channels created by `!setup` |
| `moderation_actions` | Every case: action, reason, moderator, duration, case number, time |
| `user_message_history` | Recent messages per user, including attachment links and reply info |
| `temp_moderations` | Pending end times for temporary bans |

Discord IDs are 64-bit numbers, so every ID column is `BIGINT`. A normal `INT` would overflow. Lookup columns have indexes on `(guild_id, user_id)` and `(guild_id, case_id)`.

## Design decisions and security

- **Validation as a whitelist**: each endpoint validates its input and only uses the validated array, so unknown fields are dropped and can never become column names.
- **Column allowlist**: `PATCH /channels` accepts a column name from the bot, so it is checked against a fixed list of log channels before it reaches the query.
- **Server-controlled fields**: `guild_id`, `case_id`, `been_setup` and timestamps are set by Laravel.
- **Safe case numbering**: the next case number is calculated inside a transaction with `lockForUpdate()`, so two simultaneous actions cannot receive the same number.
- **All-or-nothing writes**: saving a server's roles and channels happens in one transaction, so a failure cannot leave a half-saved setup.
- **Parameter binding**: values reach MySQL as bound parameters through Laravel's query builder.
- **Secrets**: the database login and the API key live only in `.env`, which is not committed.

## Project structure

```
app/Http/Controllers/
  ApiController.php             health check
  GuildConfigController.php     roles + channels config
  ModerationController.php      moderation cases
  MessageHistoryController.php  per-user message history
  TempModerationController.php  timed bans
app/Http/Middleware/
  BotApiKey.php                 API key check
bootstrap/app.php               middleware alias
routes/api.php                  all API routes
database/migrations/            table definitions
```

## Roadmap

The queries currently use Laravel's query builder (`DB::table(...)`). Planned work:

- [ ] Move the queries to Eloquent models
- [ ] Discord OAuth2 sign-in for a staff dashboard that lists the servers a user can manage
- [ ] Staff activity points (moderation actions, messages, voice time) with a weekly report and week-over-week comparison
- [ ] Automated feature tests for each endpoint
- [ ] Rate limiting and an IP allowlist for the API
- [ ] Logging of API calls for auditing

## Troubleshooting

- **404 with an HTML page on `/api/...`**: the request did not reach this project. Check the base URL, for example `http://127.0.0.1:8000/api` when using `artisan serve`.
- **401 Unauthorized**: the key is missing or differs between the bot's `.env` and Laravel's. After editing `.env`, run `php artisan config:clear`.
- **500 errors**: the real message is in `storage/logs/laravel.log`. Typical causes are a missing import in a controller, a table that was never migrated, or wrong `DB_*` values.
