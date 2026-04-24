# Sports Betting Platform

A backend project I built as part of my prep for a senior backend engineering role. It's a realistic sports betting platform built with a Laravel API, a Python microservice for data ingestion, PostgreSQL, and Redis.

## Stack

- **Laravel** — user auth, bet placement, event management
- **Python / Flask** — sports data ingestion microservice
- **PostgreSQL** — primary database
- **Redis** — caching
- **Docker** — everything runs in containers

## Architecture

### Database Schema

**users / wallets**
Wallet is a separate table rather than a balance column on users. This keeps things clean and makes it easy to extend later (multiple currencies, payment methods, etc.). Role is an enum rather than an `is_admin` boolean.

**events**
Sports games that users bet on. Indexed on `status`, `event_date`, and a composite `(status, event_date)` for the common query pattern of "upcoming active events."

**bets**
Stores `odds_at_placement` — a snapshot of the odds at the time the bet was placed, since odds change constantly and you need to lock in what the user actually saw.

**wallet_transactions**
Full audit trail of every balance change. Useful for dispute resolution and compliance.

### Key Design Decisions

- **Database transactions** for bet placement — balance deduction and bet creation both succeed or both fail, no partial state
- **Odds snapshot** on the bet record rather than a live lookup
- **Separate wallet table** so it can be locked independently during concurrent bets
- **Transaction table** as an immutable ledger, not just a running balance

## Getting Started

**Prerequisites:** Docker & Docker Compose

```bash
# Start all services
docker-compose up -d

# Install dependencies
docker-compose exec laravel-api composer install

# Setup Laravel
docker-compose exec laravel-api php artisan key:generate
docker-compose exec laravel-api php artisan migrate
docker-compose exec laravel-api php artisan db:seed

# Run tests
docker-compose exec laravel-api php artisan test
```

### Ports
- Laravel API: http://localhost:8000
- Python Service: http://localhost:5000
- PostgreSQL: localhost:5432
- Redis: localhost:6379

## Testing

Tests cover the full bet placement flow including happy path, validation errors, and edge cases:

- Valid bet placement
- Betting more than wallet balance
- Betting on a non-existent or cancelled event
- Transaction rollback on failure
- Concurrent bet attempts
- Balance precision

Feature tests hit the full stack (real DB, real auth), unit tests cover the service layer and model logic in isolation.
