# 🚀 Hostinger Deployment Guide

Follow these simplified steps to ensure everything works perfectly on your live server.

## 1. Upload & Setup
1.  **Upload Files:** Zip your project (excluding `node_modules` and `vendor`) and upload it to your `public_html/`.
2.  **Environment:** Copy `.env.example` to `.env` and update:
    *   `APP_ENV=production` & `APP_DEBUG=false`
    *   `APP_URL=https://your-domain.com`
    *   **Database:** Enter your new MySQL credentials from Hostinger hPanel.
    *   `QUEUE_CONNECTION=database`

## 2. Terminal Commands (via SSH)
Run these inside your project folder to optimize and prepare:
```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 3. Background Services
Since payroll calculations are now handled manually and in real-time, there is no longer a strict requirement for background cron jobs on the server. All processing is triggered directly via the Admin interface.

## 4. Troubleshooting Checklist
- [ ] If CSS/JS doesn't load: Ensure you ran `npm run build` locally before uploading the `public/build` folder.
- [ ] 500 error: Check logs in `storage/logs/laravel.log`.
- [ ] Permission issues: Run `chmod -R 775 storage bootstrap/cache`.
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

## 5. Security & Persistence
- Ensure `storage` and `bootstrap/cache` directories are writable (`chmod -R 775`).
- Run `php artisan migrate --force` to update the database schema.
- **Backups**: Ensure your Hostinger auto-backups are enabled for both Files and Database.

---
*Created on 2026-04-11 - Good luck with the launch!*
