# HR System Deployment Guide (Railway / Render)

This guide provides instructions for deploying the HR System to Platform-as-a-Service (PaaS) providers.

## Prerequisites
- A GitHub, GitLab, or Bitbucket repository with the HR System code.
- An account on [Railway](https://railway.app/) or [Render](https://render.com/).

## Environment Variables
The following environment variables MUST be configured in your PaaS dashboard:

| Variable | Recommended Value | Description |
| --- | --- | --- |
| `APP_ENV` | `production` | Set to production for optimizations. |
| `APP_DEBUG` | `false` | Disable debug mode for security. |
| `APP_KEY` | `base64:...` | Run `php artisan key:generate --show` locally. |
| `APP_URL` | `https://your-app.up.railway.app` | Your production URL. |
| `DB_CONNECTION` | `mysql` | Typically MySQL or PostgreSQL. |
| `DB_HOST` | *(From Provider)* | Database host. |
| `DB_PORT` | `3306` | Default MySQL port. |
| `DB_DATABASE` | *(From Provider)* | Database name. |
| `DB_USERNAME` | *(From Provider)* | Database user. |
| `DB_PASSWORD` | *(From Provider)* | Database password. |
| `FILESYSTEM_DISK` | `public` | Storage disk. |
| `SESSION_DRIVER` | `database` | or `redis` if available. |
| `CACHE_STORE` | `database` | or `redis` if available. |
| `QUEUE_CONNECTION` | `database` | or `redis` if available. |

## Deployment Steps

### 1. Railway (Recommended)
Railway uses **Nixpacks** to automatically detect and build Laravel applications.

1. Create a new project on Railway.
2. Connect your GitHub repository.
3. Provision a **MySQL** database from the "View Infrastructure" tab.
4. Copy the MySQL environment variables to your Web service settings.
5. Add the `APP_KEY` and other variables listed above.
6. The build command is automatic, but you can ensure migrations run by adding a custom start command:
   `php artisan migrate --force && php artisan optimize && php artisan serve --host=0.0.0.0 --port=$PORT`

### 2. Render
Render uses a `render.yaml` blueprint or manual configuration.

1. Create a new **Web Service** on Render.
2. Connect your GitHub repository.
3. Select **PHP** as the environment.
4. Add a **Managed MySQL** database.
5. Set the **Build Command**:
   `composer install --no-dev --optimize-autoloader && npm install && npm run build`
6. Set the **Start Command**:
   `php artisan migrate --force && php artisan optimize && apache2-foreground` (or similar depending on Render's PHP image)

## Optimizations
After deployment, ensure these commands have run (usually part of the build/start process):
- `php artisan optimize` (caches config, routes, and services)
- `php artisan view:cache`
- `php artisan event:cache`

## Troubleshooting
- **Storage Permissions**: PaaS providers usually handle this, but ensure `storage` and `bootstrap/cache` are writable.
- **Vite Assets**: Ensure `npm run build` runs during the build phase so `public/build` exists.
- **Database Connection**: Verify that the database is accessible from the web service (use internal networking if available).
