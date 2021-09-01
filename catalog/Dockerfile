FROM php:8-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install pdo_mysql zip exif pcntl bcmath gd

# Install Composer
COPY --from=composer:2.0 /usr/bin/composer /usr/bin/composer

RUN usermod -u 1000 www-data

RUN chown www-data:www-data /var/www

WORKDIR /var/www

COPY entrypoint.sh .

RUN ls

RUN chmod +x entrypoint.sh

USER www-data

EXPOSE 9000

ENTRYPOINT ["./entrypoint.sh"]
