# Building the Betting Platform - Step by Step Guide

This guide walks you through building the entire project from scratch.

## Prerequisites Check

Before starting, make sure Docker is running:
```bash
docker --version
docker-compose --version
```

## Step 1: Initialize Laravel Project

First, we need to create a fresh Laravel project inside the `laravel-api` folder.

### Option A: Using Docker (Recommended)
```bash
# Start just the Laravel container
docker-compose up -d laravel-api

# Create new Laravel project inside container
docker-compose exec laravel-api composer create-project laravel/laravel .

# Or if you prefer to do it locally:
composer create-project laravel/laravel laravel-api
```

### Option B: Manual Setup
If you want to start with the absolute basics, you can:
1. Create the Laravel structure manually
2. Follow along building each file

## Step 2: Database Migrations - Build These Yourself!

Create migrations in this order (timestamps matter!):

### Migration 1: Users Table
```bash
docker-compose exec laravel-api php artisan make:migration create_users_table
```

**Your task:** Define the schema with:
- Standard auth fields (id, name, email, password)
- `role` enum ('user', 'admin', 'moderator')
- Proper indexes

### Migration 2: Wallets Table
```bash
docker-compose exec laravel-api php artisan make:migration create_wallets_table
```

**Your task:** Define the schema with:
- user_id (foreign key, unique)
- balance (decimal 15,2)
- currency (default 'USD')
- status enum ('active', 'frozen', 'closed')
- Indexes and constraints

### Migration 3: Events Table
```bash
docker-compose exec laravel-api php artisan make:migration create_events_table
```

**Your task:** Define the schema with:
- Teams, sport type, dates
- Odds fields
- Status and result
- **Think carefully about indexes!**

### Migration 4: Bets Table
```bash
docker-compose exec laravel-api php artisan make:migration create_bets_table
```

**Your task:** Define the schema with:
- Foreign keys to users and events
- Bet details and odds snapshot
- Status tracking
- Composite indexes for common queries

### Migration 5: Wallet Transactions Table
```bash
docker-compose exec laravel-api php artisan make:migration create_wallet_transactions_table
```

**Your task:** Define the schema with:
- Full audit trail
- Balance before/after
- Reference fields for linking to bets

## Step 3: Create Models

```bash
docker-compose exec laravel-api php artisan make:model User
docker-compose exec laravel-api php artisan make:model Wallet
docker-compose exec laravel-api php artisan make:model Event
docker-compose exec laravel-api php artisan make:model Bet
docker-compose exec laravel-api php artisan make:model WalletTransaction
```

**Your task:** Define relationships in each model:
- User hasOne Wallet
- User hasMany Bets
- Event hasMany Bets
- Wallet belongsTo User
- Wallet hasMany WalletTransactions
- Bet belongsTo User and Event

## Step 4: Test Setup

Create your first test:
```bash
docker-compose exec laravel-api php artisan make:test BetPlacementTest
```

**Your task:** Write test for bet placement BEFORE implementing the feature (TDD!)

## Step 5: Build BettingService

Create a service class:
```bash
docker-compose exec laravel-api php artisan make:class Services/BettingService
```

**Your task:** Implement `placeBet()` method with:
1. Validation
2. Database transaction
3. Balance deduction
4. Bet creation
5. Transaction recording

## Step 6: API Routes and Controllers

```bash
docker-compose exec laravel-api php artisan make:controller AuthController
docker-compose exec laravel-api php artisan make:controller BetController
docker-compose exec laravel-api php artisan make:controller EventController
```

Define routes in `routes/api.php`

## Step 7: Python Microservice

Build the Flask app in `python-service/app.py`:

**Your task:** Create:
1. Health check endpoint
2. Sports data ingestion endpoint
3. Database connection
4. Basic error handling

## Commands You'll Use

### Running Migrations
```bash
docker-compose exec laravel-api php artisan migrate
docker-compose exec laravel-api php artisan migrate:fresh  # Reset and re-run
```

### Running Tests
```bash
docker-compose exec laravel-api php artisan test
docker-compose exec laravel-api php artisan test --filter BetPlacementTest
```

### Creating Seeders
```bash
docker-compose exec laravel-api php artisan make:seeder UserSeeder
docker-compose exec laravel-api php artisan db:seed
```

### Laravel Tinker (REPL)
```bash
docker-compose exec laravel-api php artisan tinker
```

### Check Routes
```bash
docker-compose exec laravel-api php artisan route:list
```

## Troubleshooting

### Database Connection Issues
```bash
docker-compose logs postgres
docker-compose exec postgres psql -U betting_user -d betting_platform
```

### Clear Cache
```bash
docker-compose exec laravel-api php artisan config:clear
docker-compose exec laravel-api php artisan cache:clear
```

### Rebuild Containers
```bash
docker-compose down
docker-compose up --build
```

## What to Build First?

I recommend this order:
1. ✅ Migrations (get the schema right)
2. ✅ Models with relationships
3. ✅ One test (bet placement happy path)
4. ✅ BettingService with that one feature
5. ✅ Controller and routes
6. ✅ More tests (edge cases)
7. ✅ Repeat for other features

## Questions to Ask Yourself

As you build each part:
- **Migrations:** What indexes do I need? Why?
- **Models:** What relationships exist? Eager loading?
- **Service:** How do I handle failures? What needs a transaction?
- **Tests:** What edge cases am I missing?
- **Controllers:** Am I validating input? Handling errors?

## Ready to Start?

1. Make sure Docker is running
2. Run `docker-compose up -d`
3. Start with Step 1: Initialize Laravel
4. Build migrations one at a time
5. Test as you go!

**Use Claude Code to guide you through each step!**
