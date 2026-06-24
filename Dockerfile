FROM php:8.2-cli

# Install dependencies including PostgreSQL driver
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    nodejs \
    npm

# Install PHP extensions for Laravel and PostgreSQL
RUN docker-php-ext-install pdo_mysql pdo_pgsql mbstring exif pcntl bcmath gd

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Build the application
RUN composer install --no-dev --optimize-autoloader
RUN npm install
RUN npm run build

# Start the application
CMD php artisan migrate --force && php artisan db:seed --force && php artisan optimize && php artisan serve --host=0.0.0.0 --port=$PORT
