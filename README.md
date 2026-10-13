# GreenCart Grocery Store Management

GreenCart is a Laravel-based grocery store management app for tracking products and recording point-of-sale transactions.

## Features

- Store dashboard with today's revenue, transaction count, inventory totals, and low-stock alerts.
- Product inventory with create, edit, delete, and low-stock filtering.
- Supplier contact management with product assignments.
- Stock movement history for receiving or removing inventory, with stock validation.
- Checkout with cash, card, or mobile payment methods.
- Sale history and receipts, with stock levels reduced automatically after a successful checkout.
- Five related grocery data tables (products, suppliers, sales, sale items, and stock movements), with factories generating 50 sample records per table on seed.

Laravel's default user, database-cache, and database-queue migrations are intentionally omitted so a fresh setup creates only the five grocery application tables. Sessions and cache use files, and queues run synchronously.

## Setup

1. Install PHP dependencies with `composer install`.
2. Configure the database connection in `.env` and create the configured database.
3. Run `php artisan migrate --seed`.
4. Start the app with `php artisan serve`.

## Tests

Run the automated suite with `php artisan test`.
