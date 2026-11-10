# Small E-commerce Management System

A Laravel + React application for managing independent shops against a shared global product catalog. Administrators manage shop-owner accounts, shops, and global products. Shop owners manage their assigned shops' listings, including shop-specific prices and optional content/image overrides. Customers browse active shops and only the active products listed by the selected shop.

## Technology

- Backend: Laravel 13, PHP 8.3+, Laravel Sanctum, Eloquent, MySQL 8 / compatible MariaDB
- Frontend: React, Vite 8, JavaScript, React Router, Axios, Tailwind CSS 4, Lucide React
- Images: Laravel public filesystem disk

## Requirements

- Laragon (or another local PHP/MySQL environment) with PHP 8.3 or newer and `pdo_mysql`, `fileinfo`, and GD enabled
- Composer 2
- MySQL 8 or a compatible MariaDB server and a database user with schema migration privileges
- Node.js 20.19+ or 22.12+ and npm (Vite 8 requirement)
- A browser with cookies enabled for Sanctum's SPA session authentication

## Fresh install on Windows with Laragon

### 1. Clone and start Laragon

Clone this repository into a Laragon workspace (for example `C:\laragon\www\small-ecommerce`) and start MySQL in Laragon. The API and Vite development server below use ports 8000 and 5173; adjust the environment variables if those ports are occupied.

### 2. Create the MySQL databases

In HeidiSQL, phpMyAdmin, or the MySQL client, create a development database using `utf8mb4`:

```sql
CREATE DATABASE small_ecommerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

For automated tests, create a separate empty database so test records never touch development data:

```sql
CREATE DATABASE small_ecommerce_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Grant your local MySQL user access to both databases. Never point tests at production data.

### 3. Install and configure Laravel

In PowerShell from the repository root:

```powershell
Set-Location backend
composer install
Copy-Item .env.example .env
```

Edit `backend/.env` and set `DB_HOST`, `DB_PORT`, `DB_DATABASE=small_ecommerce`, `DB_USERNAME`, and `DB_PASSWORD` for your Laragon MySQL instance. The example values are placeholders. Keep `.env` private. Keep `APP_URL=http://localhost:8000`, `CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173`, and the corresponding Vite hosts in `SANCTUM_STATEFUL_DOMAINS` when using the default local URLs. Use the same host consistently in the browser (recommended: `localhost` for both servers).

Then run:

```powershell
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

The seeder is repeatable and creates three demo accounts, three shops, six global products, and shop listings. These credentials are intentionally public development fixtures; change or remove them before any deployment:

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@example.com` | `password` |
| Shop owner | `owner1@example.com` | `password` |
| Shop owner | `owner2@example.com` | `password` |

Start the API in this terminal:

```powershell
php artisan serve --host=localhost --port=8000
```

### 4. Install and start React

In a second PowerShell terminal from the repository root:

```powershell
Set-Location frontend
Copy-Item .env.example .env
npm ci
npm run dev -- --host localhost
```

The frontend example points to `http://localhost:8000/api`. Open the Vite URL printed in the terminal, normally `http://localhost:5173`. To change the backend URL, update `VITE_API_BASE_URL` in `frontend/.env` and keep the backend CORS and Sanctum stateful-domain configuration in sync. Restart Vite after changing environment values.

### 5. Use the system

- Public shop directory: `http://localhost:5173/shops`
- Admin: sign in at `/login` with the admin fixture, then use `/admin`
- Shop owner: sign in with either owner fixture, then use `/shop-owner`
- Public shop pages use `/shop/{shop-slug}` and its products use `/shop/{shop-slug}/products`

The React application uses Sanctum's first-party session flow: it obtains `/sanctum/csrf-cookie`, posts credentials to `/api/login`, and then requests `/api/user`. The browser keeps the session cookie; the UI does not save bearer tokens in local storage. Do not mix `localhost` and `127.0.0.1` between frontend and API URLs during a session.

## Main workflows

1. Sign in as admin and create shop-owner accounts and shops.
2. Admins create global products, or shop owners create products from their workspace. A new global product is shared in the catalog but is not assigned to a shop automatically.
3. A shop owner selects one of their assigned shops, adds an existing global product, and sets the shop price. They may also override the listing name, description, or thumbnail.
4. Shop owners manage and remove only their shop listing records. Removing a listing does not delete the global product.
5. Customers browse active shops. A shop storefront only requests active listings through that shop's public API; global catalog products that are unassigned, inactive, or assigned to a different shop are not displayed.

Image uploads accept supported image formats without an application-level size cap. The effective upload size is still subject to PHP and web-server limits. Files are saved under separate public-disk folders for shop logos, global product thumbnails, and shop listing thumbnails.

## API reference

All endpoints are under `http://localhost:8000/api`. JSON validation errors return HTTP 422, unauthenticated protected calls return 401, role denials return 403, and unavailable records generally return 404. Paginated resources use Laravel's `data`, `links`, and `meta` response shape.

### Authentication

| Method | Endpoint | Purpose |
| --- | --- | --- |
| POST | `/login` | Authenticate with email/password (Sanctum session for stateful browser requests; token for non-stateful clients) |
| POST | `/logout` | End the current session or revoke the current token |
| GET | `/user` | Return the authenticated safe user profile |

### Admin (active admin role required)

| Method | Endpoint | Purpose |
| --- | --- | --- |
| GET | `/admin/dashboard` | Global shop, owner, product, and listing statistics |
| GET/POST | `/admin/shops` | List or create shops |
| GET/PUT/DELETE | `/admin/shops/{id}` | Read, update, or safely delete a shop |
| GET/POST | `/admin/users` | List or create shop-owner accounts |
| GET/PUT/DELETE | `/admin/users/{id}` | Read, update, or safely delete a shop-owner account |
| GET/POST | `/admin/products` | List or create shared global products |
| GET/PUT/DELETE | `/admin/products/{id}` | Read, update, or delete an unlisted global product |
| GET | `/admin/shop-listings` | Review shop-specific product listings |

Admin list endpoints support pagination and `q`/`search` where relevant. Deleting a shop with listings requires an explicit `delete_listings=true` confirmation; global products with listings and owners with dependent shops/products cannot be deleted.

### Shop owner (active shop-owner role required)

| Method | Endpoint | Purpose |
| --- | --- | --- |
| GET | `/owner/dashboard` | Owner-only shop and listing counts |
| GET | `/owner/shops` | Only the authenticated owner's assigned shops |
| GET | `/owner/catalog`, `/owner/catalog/{id}` | Shared global product catalog and detail |
| POST | `/owner/products` | Create a shared global product |
| PUT | `/owner/products/{id}` | Update a global product created by this owner |
| GET/POST | `/owner/shops/{shopId}/products` | List or add a product to an owned shop |
| PUT/DELETE | `/owner/shops/{shopId}/products/{listingId}` | Update or remove a listing in an owned shop |

Owner list endpoints support `q`/`search` and pagination. Duplicate product assignment within one shop returns HTTP 409. Foreign shop/listing IDs are not accessible to another owner.

### Public customer storefront (no login required)

| Method | Endpoint | Purpose |
| --- | --- | --- |
| GET | `/public/shops` | Active shop directory; search, pagination, and sorting |
| GET | `/public/shops/{slug}` | Active shop details |
| GET | `/public/shops/{slug}/products` | Active products assigned to that shop; search, pagination, and sorting |
| GET | `/public/shops/{slug}/products/{productId}` | One active listing in that shop, or 404 |
| GET | `/public/products` | Latest shared-catalog products for the home page |

Public product responses are built from the selected shop's listing relationship. The listing price is always shop-specific; nullable listing name, description, and thumbnail values fall back to the global product values.

## Testing and verification

The backend feature tests use Laravel `DatabaseTransactions`, so migrate an empty, dedicated test database before running them. Do not use the demo or production database. From `backend/` in PowerShell, after creating `small_ecommerce_test` and configuring the development `.env` MySQL connection:

```powershell
$env:DB_DATABASE = 'small_ecommerce_test'
php artisan config:clear
php artisan migrate:fresh --force
php artisan test
Remove-Item Env:DB_DATABASE
```

`migrate:fresh` deletes and recreates tables in the selected database. Confirm that `DB_DATABASE` is the disposable test schema before running it. Frontend production build:

```powershell
Set-Location ..\frontend
npm ci
npm run build
```

The automated backend suite covers authentication/roles, admin CRUD, shared catalog behavior, owner isolation, independent shop overrides and prices, image validation/storage, listing deletion, and customer visibility. The frontend currently has a production build check but no automated browser/component test suite.

## Troubleshooting

- **MySQL connection refused:** Start MySQL in Laragon and verify host/port/user/password in `backend/.env`.
- **Unknown database:** Create the selected MySQL schema first and ensure `DB_DATABASE` matches it.
- **`could not find driver`:** Enable/restart PHP with `pdo_mysql` (and `fileinfo`/GD for image validation) in Laragon.
- **401 or CSRF mismatch at login:** Use the same host for Vite and API, refresh the page, confirm `/sanctum/csrf-cookie` succeeds, and check that `SANCTUM_STATEFUL_DOMAINS` includes the Vite host and port.
- **CORS error:** Add the exact frontend origin, including scheme and port, to `CORS_ALLOWED_ORIGINS`; clear cached config with `php artisan config:clear` and restart Laravel.
- **Images return 404:** Run `php artisan storage:link`, verify the public storage folder is writable, and check the `APP_URL` host.
- **Port already in use:** Start Laravel/Vite on another port and update API URL, CORS allowed origins, and Sanctum stateful domains consistently.
- **Owner cannot see or edit a shop:** An administrator must assign that shop to the owner's account. The API intentionally hides foreign shop records.

## Scope and limitations

This application implements shop/catalog management and customer product browsing. It does not currently implement shopping carts, checkout, orders, payment processing, or customer accounts. Production deployment also requires replacing local demo credentials, configuring HTTPS and secure cookie settings, and setting production CORS/stateful domains.

## GitHub submission checklist

- Commit `backend/.env.example` and `frontend/.env.example`, never real `.env` files or secret values.
- Keep the demo credentials only as documented local seed fixtures; change them for any deployed environment.
- Review `git status` and staged files for `.env`, database dumps, logs, uploaded images, `vendor/`, and `node_modules/` before pushing.
- Run backend tests against the disposable test database and run `npm run build` before submission.
- The current workspace has no `.git` metadata, so this audit cannot inspect Git history, tracked files, or confirm what a GitHub remote contains. Initialize/check the repository and scan staged files before pushing.
