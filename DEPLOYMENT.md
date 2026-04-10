# 🚀 Production Deployment Checklist

This document contains critical steps to ensure the Payroll System runs correctly on your live server next week.

## 1. Environment Configuration
Update these values in your `.env` file once on the live server:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain.com`
- `QUEUE_CONNECTION=database` (Already set in current `.env`)

## 2. Background Process Management (CRITICAL)
Since we moved payroll processing to the background, you **MUST** have a queue worker running.

### Command to run:
```bash
php artisan queue:work --timeout=300
```

## 2. Background Process Management (CRITICAL)
Since payroll is processed in the background, you MUST have a queue worker. On Hostinger, the most reliable "24/7" way is the **Cron-based Worker** method.

### Add this to your Hostinger hPanel "Cron Jobs":
**Frequency:** Every Minute (`* * * * *`)  
**Command:** 
```bash
/usr/local/bin/php /home/YOUR_USERNAME/public_html/artisan queue:work --stop-when-empty --timeout=300
```
*(Replace `YOUR_USERNAME` and `/public_html/` with your actual Hostinger server path, which you can find by typing `pwd` in SSH.)*

**Why this is the best for you:**
- **Self-Healing**: If the worker crashes or the server restarts, Cron starts a new one 60 seconds later.
- **Zero Maintenance**: Once you set it up, you never have to touch it again.
- **Resource Efficient**: It only runs when there are actual payroll tasks to process.

---

## 3. Task Scheduling
You also need the standard Laravel Scheduler to handle things like automatic payroll status updates. Add this as a SECOND Cron Job:

**Frequency:** Every Minute (`* * * * *`)  
**Command:**
```bash
/usr/local/bin/php /home/YOUR_USERNAME/public_html/artisan schedule:run >> /dev/null 2>&1
```

---

## 4. Performance Optimization
Run these commands via SSH every time you deploy new code:
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
