# Bumpa Customer Loyalty Program

A full-stack customer loyalty system featuring achievement tracking, badge progression, and automatic cashback rewards.

---

## Features

- **Purchase tracking** — Record customer purchases via a REST API
- **Achievement unlocking** — Automatically unlock achievements as purchase milestones are reached
- **Badge progression** — Earn badges (Beginner → Bronze → Silver → Gold) as achievements accumulate
- **Cashback rewards** — A 300 Naira cashback is triggered (logged) whenever a new badge is unlocked
- **Customer dashboard** — React SPA to visualise unlocked achievements, current badge, and progress to next badge

---

## Tech Stack

| Layer    | Technology                        |
|----------|-----------------------------------|
| Backend  | Laravel 12 (PHP 8.3), SQLite      |
| Frontend | React 19, Vite 8                  |
| Testing  | PHPUnit (Laravel feature tests)   |

---

## Achievement & Badge System

### Achievements

| Achievement    | Required Purchases |
|----------------|--------------------|
| First Purchase | 1                  |
| 5 Purchases    | 5                  |
| 10 Purchases   | 10                 |
| 25 Purchases   | 25                 |
| 50 Purchases   | 50                 |

### Badges

| Badge    | Achievements Required |
|----------|-----------------------|
| Beginner | 0                     |
| Bronze   | 2                     |
| Silver   | 4                     |
| Gold     | 5 (all)               |

When a badge is unlocked, a **300 Naira cashback** payment is triggered (simulated via Laravel's log system).

---

## Project Structure

```
bumpa-customer-loyalty/
├── backend/    # Laravel API
└── frontend/   # React dashboard
```

---

## Backend Setup

### Requirements
- PHP 8.2+
- Composer

### Installation

```bash
cd backend

# Install PHP dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# (Optional) Seed demo users
php artisan db:seed

# Start the development server
php artisan serve
```

The API will be available at `http://localhost:8000`.

### API Endpoints

#### `GET /api/users/{id}/achievements`

Returns the loyalty status for a user.

**Example response:**
```json
{
  "unlocked_achievements": ["First Purchase", "5 Purchases"],
  "next_available_achievements": ["10 Purchases"],
  "current_badge": "Bronze",
  "next_badge": "Silver",
  "remaining_to_unlock_next_badge": 2
}
```

#### `POST /api/users/{id}/purchases`

Simulate a purchase for a user. Fires `PurchaseMade` event which triggers achievement/badge checks.

**Request body:**
```json
{ "amount": 500.00 }
```

---

## Frontend Setup

### Requirements
- Node.js 18+
- npm

### Installation

```bash
cd frontend

# Install dependencies
npm install

# Copy environment file
cp .env.example .env
# Edit .env to set your backend URL (default: http://localhost:8000)

# Start the development server
npm run dev
```

The dashboard will be available at `http://localhost:5173`.

### Build for production

```bash
npm run build
```

---

## Running Tests (Backend)

```bash
cd backend
php artisan test
```

All tests use an in-memory SQLite database and are isolated via `RefreshDatabase`.

### Test coverage includes:
- API returns correct JSON structure
- New user starts at Beginner badge
- Achievements unlock at correct purchase thresholds
- Achievements are never duplicated
- Badges unlock when achievement count thresholds are met
- `BadgeUnlocked` event is dispatched (triggers 300 Naira cashback log)
- `PurchaseMade` event is dispatched on purchase
- `next_available_achievements` shows the correct next achievement
- `remaining_to_unlock_next_badge` is calculated correctly

---

## Event Architecture

```
POST /api/users/{id}/purchases
  └─► PurchaseMade event
        └─► CheckAchievementsOnPurchase listener
              ├─► AchievementUnlocked event  (per new achievement)
              └─► BadgeUnlocked event        (when badge tier increases)
                    └─► ProcessCashbackOnBadge listener
                          └─► Log: "300 Naira cashback issued to {user} for {badge} badge"
```

---

## Demo Seed Data

After running `php artisan db:seed`, the following demo users are created:

| ID | Name          | Email                 | Purchases | Badge    |
|----|---------------|-----------------------|-----------|----------|
| 1  | Alice Beginner| alice@example.com     | 0         | Beginner |
| 2  | Bob Bronze    | bob@example.com       | 1         | Beginner |
| 3  | Carol Silver  | carol@example.com     | 5         | Bronze   |
| 4  | Dave Gold     | dave@example.com      | 50        | Gold     |
