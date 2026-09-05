# Project Status App

A Laravel application for recording road/pipe project details and tracking each project's status through its assignment lifecycle. Supports English and Khmer.

## What it does

- A project starts as **PENDING** with no assignee.
- An **admin** assigns it to any user, which moves it to **IN_PROGRESS** and records an entry in the assignment log.
- The current **assignee** can reassign it to someone else (stays **IN_PROGRESS**, logged) or mark it **COMPLETED** (requires entering `project_amount` and `request_number`).
- Every assignment, reassignment, and completion is recorded in `project_assignment_logs` with who did it, who it went to, and when.
- Everyone can view every project; only an admin or the current assignee can act on one.

Fields tracked per project: `on_road`, `start_road`, `end_road`, `pipe_type`, `pipe_diameter`, `pipe_length`, `received_date`, `project_code`, `work_code`, `project_amount`, `request_number`, plus `status` and the assignment trail.

## Tech stack

- **Backend/Frontend:** Laravel 13 (Blade + Tailwind CSS via Laravel Breeze)
- **Database:** MariaDB
- **Localization:** English (`en`) and Khmer (`km`)

## Requirements

- PHP 8.3+
- Composer
- Node.js 18+ and npm
- MariaDB 10.6+ (or MySQL 8+) server, running
- Git (optional, for version control)

If any of these aren't installed yet, see [Installing prerequisites](#installing-prerequisites-fresh-machine) below.

## Quick start

If you have [GNU Make](#installing-prerequisites-fresh-machine) available:

```sh
make setup   # installs dependencies, creates .env, creates the DB, migrates, seeds, builds assets
make serve   # starts the app at http://127.0.0.1:8000
```

That's it — skip to [Admin & login access](#admin--login-access) below.

Run `make help` any time to see every available target.

### Without `make`

Do the same steps manually, from the project root:

```sh
composer install
npm install

cp .env.example .env          # Windows PowerShell: Copy-Item .env.example .env
php artisan key:generate

# create the database (see .env for host/user/password/name)
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS project_status_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
# or: php scripts/create-database.php

php artisan migrate
php artisan db:seed
npm run build

php artisan serve
```

Then open **http://127.0.0.1:8000**.

## Configuring `.env`

Copy `.env.example` to `.env` (done automatically by `make env` / `make setup`) and adjust if your MariaDB credentials differ from the defaults:

```
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=project_status_app
DB_USERNAME=root
DB_PASSWORD=root

APP_LOCALE=en
APP_SUPPORTED_LOCALES=en,km
```

## Admin & login access

The seeder (`database/seeders/DatabaseSeeder.php`) creates these accounts. **Every account uses the password `password`.**

| Name        | Email                 | Role  | Can do                                                             |
|-------------|-----------------------|-------|---------------------------------------------------------------------|
| Admin User  | `admin@example.com`   | Admin | Create projects, assign any project, reassign/complete any project |
| Staff One   | `staff1@example.com`  | User  | Reassign/complete projects currently assigned to them              |
| Staff Two   | `staff2@example.com`  | User  | Reassign/complete projects currently assigned to them              |
| Staff Three | `staff3@example.com`  | User  | Reassign/complete projects currently assigned to them              |
| Staff Four  | `staff4@example.com`  | User  | Reassign/complete projects currently assigned to them              |

15 sample projects are seeded (5 pending, 5 in-progress, 5 completed) so there's data to look at immediately.

## User management (role-based access control)

Admins have a **Users** page in the top navigation (`/users`, hidden from non-admins) where they can:

- List every user with their role and how many projects they've created/are assigned to.
- Create a new user, choosing their name, email, password, and role (Admin or User).
- Edit a user: change their name, email, role, or reset their password. Non-admins cannot access this page at all (`403`).
- Delete a user.

Safeguards baked into the authorization rules (`app/Policies/UserPolicy.php`, `app/Http/Controllers/UserController.php`):

- Only admins can view, create, edit, or delete users.
- You cannot delete your own account from this page (use the profile page for that).
- The last remaining admin cannot be demoted to a regular user — the role field is disabled on their edit page with an explanation, and the server rejects it too if bypassed.
- A user who has created projects or has assignment history (they've assigned, reassigned, or completed a project) cannot be deleted — doing so would corrupt the project records and audit trail. You'll get a message telling you to reassign or remove those projects first, instead of a database error.

Roles determine what a user can do elsewhere in the app: an **Admin** can create projects and make the first assignment on any project; a regular **User** can only reassign or complete a project currently assigned to them (see `app/Policies/ProjectPolicy.php`).

## Database access

**Laravel Tinker** (query via Eloquent, no extra tools needed):

```sh
php artisan tinker
```
```php
App\Models\Project::with('assignee', 'creator')->get();
App\Models\ProjectAssignmentLog::latest()->get();
App\Models\User::all();
```

**MariaDB command line**:

```sh
mysql -u root -p project_status_app
```
(password `root` unless you changed it in `.env`). Then e.g. `SELECT * FROM projects;`.

**A GUI browser** (optional, easiest for browsing/editing visually): install [HeidiSQL](https://www.heidisql.com/) (Windows) or [DBeaver](https://dbeaver.io/) (cross-platform), and connect with:
- Host: `127.0.0.1`, Port: `3306`
- User: `root`, Password: `root` (or whatever you set in `.env`)
- Database: `project_status_app`

## Localization

- Language switcher is in the top navigation (EN / ខ្មែរ). The choice is stored in the session.
- Translated strings for the app's own UI live in `lang/km.json` (short-string JSON translations, keyed by the English text).
- Framework strings (validation messages, auth messages, pagination) are in `lang/en/*.php` and `lang/km/*.php`.
- To add a new language: duplicate `lang/km.json` and the `lang/km/` folder for the new locale code, then add that code to `APP_SUPPORTED_LOCALES` in `.env`.

## Running tests

```sh
make test
# or: php artisan test
```

46 feature tests cover the project assignment/completion workflow and the user management authorization rules (who can create, assign, reassign, complete, list, edit, and delete).

## Code style

```sh
make pint
# or: php vendor/bin/pint
```

## Useful Make targets

| Command             | What it does                                                        |
|----------------------|----------------------------------------------------------------------|
| `make setup`         | Full first-time setup: install, .env, DB creation, migrate, seed, build |
| `make install`       | `composer install` + `npm install`                                  |
| `make env`           | Create `.env` (if missing) and generate `APP_KEY` (if not already set) |
| `make db-create`     | Create the database named in `.env`                                 |
| `make migrate`       | Run pending migrations                                              |
| `make migrate-fresh` | Drop all tables and re-run every migration                          |
| `make seed`          | Re-run the seeders                                                  |
| `make fresh`         | `migrate-fresh` + `seed`                                            |
| `make serve`         | Start the dev server at http://127.0.0.1:8000                       |
| `make dev`           | Start the dev server + Vite together (hot reload while editing CSS/JS) |
| `make build`         | Build production frontend assets                                    |
| `make test`          | Run the PHPUnit test suite                                          |
| `make pint`          | Run Laravel Pint (code style fixer)                                 |
| `make clean`         | Remove `vendor/`, `node_modules/`, build output, and framework caches |

## Installing prerequisites (fresh machine)

If you're setting this up on a machine that doesn't have PHP/Composer/Node/MariaDB/Git/Make yet:

**Windows (winget, run in an elevated PowerShell):**
```powershell
winget install --id PHP.PHP.8.4 -e
winget install --id OpenJS.NodeJS.LTS -e
winget install --id Git.Git -e
winget install --id MariaDB.Server -e
winget install --id ezwinports.make -e   # optional, only needed for the `make` commands
```
Then in `php.ini`, enable: `pdo_mysql`, `mbstring`, `openssl`, `curl`, `fileinfo`, `bcmath`, `zip` (and `pdo_sqlite`/`sqlite3` if you want to run the test suite, which uses an in-memory SQLite database). Composer isn't on winget — install it from [getcomposer.org/download](https://getcomposer.org/download/).

**macOS:**
```sh
brew install php composer node mariadb git make
brew services start mariadb
```

**Linux (Debian/Ubuntu):**
```sh
sudo apt install php php-mbstring php-xml php-mysql php-curl php-bcmath php-zip composer nodejs npm mariadb-server git make
sudo systemctl start mariadb
```

After installing, open a **new** terminal window before running `make setup` — PATH changes from an installer don't apply to terminals that were already open.
