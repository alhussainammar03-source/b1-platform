# B1 Platform

Phase 1 foundation for the B1 exam-preparation SaaS.

Architecture: React + TypeScript + Vite -> Laravel 12 REST API -> MySQL.

## Planned product rules
- UI languages: German, Arabic (RTL), English, Turkish, Ukrainian.
- German exam content always remains LTR.
- Sprechen: Teil 1 Vorstellung, Teil 2 Fotobeschreibung, Teil 3 Gemeinsam planen.
- Commercial model later: Lesen free; 120 minutes of Hören practice unlocks; Premium 9.99 EUR / 30 days; one active Premium device; Stripe + PayPal.

## Local setup
Backend requires Composer and PHP 8.2+; frontend requires Node 20+.

### Backend
```powershell
cd backend
composer install
Copy-Item .env.example .env
php artisan key:generate
# configure DB_* in .env
php artisan migrate --seed
php artisan serve --host=localhost --port=8000
```

### Frontend
```powershell
cd frontend
npm install
Copy-Item .env.example .env.local
npm run dev
```

Open http://localhost:5173.
