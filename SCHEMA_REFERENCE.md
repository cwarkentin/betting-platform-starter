# Database Schema Reference

Quick reference for building migrations. Use this as a guide, not copy-paste!

## Schema Design

### users
```
id                  bigint (PK)
name                varchar
email               varchar (unique)
password            varchar
role                enum('user', 'admin', 'moderator')
email_verified_at   timestamp (nullable)
remember_token      varchar (nullable)
created_at          timestamp
updated_at          timestamp

Indexes:
- email (unique)
- created_at
```

### wallets
```
id                  bigint (PK)
user_id             bigint (FK -> users.id, unique)
balance             decimal(15,2) default 0.00
currency            varchar default 'USD'
status              enum('active', 'frozen', 'closed')
created_at          timestamp
updated_at          timestamp

Indexes:
- user_id (unique)

Constraints:
- balance >= 0
```

### events
```
id                  bigint (PK)
home_team           varchar
away_team           varchar
sport_type          enum('basketball', 'football', 'baseball', 'hockey', 'soccer')
event_date          timestamp
status              enum('pending', 'live', 'completed', 'cancelled')
home_odds           decimal(8,2)
away_odds           decimal(8,2)
draw_odds           decimal(8,2) (nullable)
result              enum('home', 'away', 'draw') (nullable)
home_score          integer (nullable)
away_score          integer (nullable)
created_at          timestamp
updated_at          timestamp

Indexes:
- sport_type
- status
- event_date
- (status, event_date) composite
- (sport_type, event_date) composite
```

### bets
```
id                  bigint (PK)
user_id             bigint (FK -> users.id)
event_id            bigint (FK -> events.id)
bet_type            enum('home', 'away', 'draw')
amount              decimal(15,2)
odds_at_placement   decimal(8,2)
potential_payout    decimal(15,2)
status              enum('pending', 'won', 'lost', 'cancelled')
actual_payout       decimal(15,2) (nullable)
settled_at          timestamp (nullable)
created_at          timestamp
updated_at          timestamp

Indexes:
- user_id
- event_id
- status
- (user_id, status) composite
- (event_id, status) composite
- created_at

Constraints:
- amount > 0
- ON DELETE CASCADE for user_id and event_id
```

### wallet_transactions
```
id                      bigint (PK)
wallet_id               bigint (FK -> wallets.id)
transaction_type        enum('deposit', 'withdrawal', 'bet_placed', 'bet_won', 'bet_refund')
amount                  decimal(15,2)
balance_before          decimal(15,2)
balance_after           decimal(15,2)
payment_method          varchar (nullable)
external_reference_id   varchar (nullable)
reference_type          varchar (nullable)  // 'App\Models\Bet'
reference_id            bigint (nullable)   // bet_id
description             text (nullable)
metadata                json (nullable)
created_at              timestamp
updated_at              timestamp

Indexes:
- wallet_id
- transaction_type
- (wallet_id, created_at) composite
- (reference_type, reference_id) composite
```

## Relationships

```
User 1:1 Wallet
User 1:N Bets
Event 1:N Bets
Wallet 1:N WalletTransactions
Bet N:1 User
Bet N:1 Event
WalletTransaction N:1 Wallet
```

## Why These Design Choices?

### Separate Wallets Table
- Isolation from user authentication
- Future: multiple wallets per user
- Transaction locking without locking user row
- Cleaner audit trail

### Odds Snapshot (odds_at_placement)
- Odds change constantly
- Lock in what user saw
- Dispute resolution
- Legal compliance

### Wallet Transactions Table
- Every balance change recorded
- Audit trail for compliance
- Dispute resolution
- Financial reporting

### Composite Indexes
- `(status, event_date)` - Common query: "upcoming games"
- `(user_id, status)` - Common query: "my pending bets"
- `(wallet_id, created_at)` - Transaction history

### Enums vs Lookup Tables
Using enums for small, stable sets:
- Faster queries (no join)
- Simpler code
- Values rarely change

Use lookup tables if:
- Values change frequently
- Need descriptions/metadata
- Large number of values

## Migration Order Matters!

Create in this order due to foreign key constraints:
1. users (no dependencies)
2. wallets (depends on users)
3. events (no dependencies)
4. bets (depends on users and events)
5. wallet_transactions (depends on wallets)

## Laravel Migration Tips

### Foreign Key Syntax
```php
$table->foreignId('user_id')->constrained()->onDelete('cascade');
// Creates: user_id bigint, foreign key to users.id, cascade on delete
```

### Enum Syntax
```php
$table->enum('status', ['pending', 'active', 'completed']);
```

### Composite Index
```php
$table->index(['status', 'event_date']);
```

### Decimal for Money
```php
$table->decimal('balance', 15, 2);
// 15 total digits, 2 after decimal
// Max: 9,999,999,999,999.99
```

### Check Constraints
```php
$table->decimal('amount', 15, 2);
$table->check('amount > 0');
```
