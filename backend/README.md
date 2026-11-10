# Small E-commerce Management System - Backend

Laravel 13 backend foundation and database architecture for the shared product catalog and shop listings.

## Requirements

- PHP 8.3 or newer with `pdo_mysql`
- Composer
- MySQL 8 or compatible MariaDB

## Install and configure

From the repository root:

```powershell
cd backend
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Create a MySQL database, then set `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `backend/.env`. Keep `.env` local; it is ignored by Git. The checked-in `.env.example` contains placeholders only.

Run migrations and demo data:

```powershell
php artisan migrate --seed
```

The seeder creates three demo users, three shops, six shared products, and fifteen shop listings:

| Role | Email | Password |
| --- | --- | --- |
| Admin | admin@example.com | password |
| Shop owner | owner1@example.com | password |
| Shop owner | owner2@example.com | password |

These credentials are for local development only.

## API authentication

Sanctum supports both the first-party SPA session flow and personal access tokens:

- For a browser SPA, use one hostname consistently (for example `localhost` for both Vite and Laravel). Set `SANCTUM_STATEFUL_DOMAINS` to include the frontend host and port, and list its exact origin in `CORS_ALLOWED_ORIGINS`. The frontend must request `/sanctum/csrf-cookie` with credentials before `POST /api/login`; subsequent API requests use the session cookie and XSRF header.
- For non-browser clients, `POST /api/login` returns a 24-hour bearer token. Send it as `Authorization: Bearer <token>` and `POST /api/logout` revokes that token.
- `GET /api/user` returns the authenticated user's safe profile. Password and remember-token fields are never serialized.
- Validation failures return 422, unauthenticated requests return 401, inactive accounts and role denials return 403, and login attempts are rate limited.
- Apply the registered `role:admin` or `role:shop_owner` middleware with `auth:sanctum` and `active` to protected route groups. Role checks always use the persisted user record.

## Admin API

All admin endpoints require an active admin session or bearer token. The admin route group also checks the stored role; a role sent by a client is never trusted.

- `GET /api/admin/dashboard` returns shop, owner, product, and listing counts.
- `/api/admin/shops` provides paginated shop CRUD. Supply `owner_id` to assign or reassign a shop owner. Shop `status` can be `active` or `inactive`.
- `/api/admin/users` provides paginated shop-owner account CRUD. New accounts always receive the `shop_owner` role; role changes are rejected. `is_active` controls account access.
- `/api/admin/products` provides paginated global product CRUD. Admin-created products are attributed to the authenticated admin.
- `GET /api/admin/shop-listings` lists shop assignments with each shop's resolved overrides and source product.
- Lists accept `q` or `search`, `per_page` (1-100), plus relevant status, owner, shop, or product filters.
- Shop logos and product thumbnails accept image formats without an application-level size limit and are stored on the public disk (subject to PHP/web-server upload limits). Run `php artisan storage:link` to serve them locally.
- Deleting a shop with listings returns 409 unless the request explicitly includes `delete_listings: true`; this permanently removes those listing overrides with the shop. Deleting a product with any listings returns 409. Shop-owner accounts with shops or authored products also return 409 instead of cascading their data away.

## Shop-owner API

All `/api/owner/*` endpoints require an active account with the stored `shop_owner` role. Shop and listing lookups are scoped to the authenticated owner's shop IDs; foreign shops and listings return 404.

- `GET /api/owner/dashboard` and `GET /api/owner/shops` return counts and only the authenticated owner's shops.
- `GET /api/owner/catalog` and `GET /api/owner/catalog/{id}` expose the shared global product catalog, including products created by admins and other owners.
- `POST /api/owner/products` creates a new global product attributed to the authenticated owner. `PUT /api/owner/products/{id}` only updates products created by that owner.
- `GET|POST /api/owner/shops/{shopId}/products` list or assign a global product to an owned shop. A `(shop_id, product_id)` duplicate returns 409.
- `PUT|DELETE /api/owner/shops/{shopId}/products/{listingId}` changes listing-specific values or removes only that listing record. Listing removal preserves the global product and its thumbnail.
- Owner lists accept `q` or `search`, `per_page` (1-100), and supported status filters. Product and listing images accept image formats without an application-level size limit and are stored separately on the public disk (subject to PHP/web-server upload limits).

## Public storefront API

These routes are unauthenticated. Shop directories and detail routes return active shops only. Storefront product list/detail queries start from the selected shop's `shop_products` relationship and include active listings only; unassigned products, inactive listings, and listings in another shop are not returned. Listing name, description, and thumbnail overrides fall back to global product values, while price always comes from that shop's listing.

- `GET /api/public/shops` supports search, pagination, and name/newest/oldest sorting.
- `GET /api/public/shops/{slug}` returns one active shop.
- `GET /api/public/shops/{slug}/products` supports search, pagination, and price/newest/oldest sorting.
- `GET /api/public/shops/{slug}/products/{productId}` returns the active listing for that product in that shop, or 404 when it is unassigned or inactive.

## Database design

- `products` is the shared catalog. A product created by a shop owner is available to every shop owner through normal catalog queries, but no shop assignment is created automatically.
- `shop_products` stores each shop's price and optional name, description, and thumbnail overrides. Its `(shop_id, product_id)` unique key prevents duplicate assignments.
- A listing's name, description, and thumbnail accessors fall back to the corresponding global product fields when the listing override is `NULL`.
- Deleting a shop or product cascades to its listings. Deleting a user who created products is restricted so catalog records cannot be orphaned; when allowed, deleting an owner also cascades their shops.

## Verify

```powershell
php artisan migrate:status
php artisan test
```

The feature tests use the configured MySQL database and transactions. Run migrations before tests; each test rolls back its own records.
