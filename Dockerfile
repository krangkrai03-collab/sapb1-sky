# CodeIgniter 4 on PHP + Apache (for Railway / any container host)
FROM php:8.3-apache

# --- PHP extensions required by CI4 + Shield ---
RUN apt-get update && apt-get install -y --no-install-recommends \
        libicu-dev libonig-dev libzip-dev unzip git \
    && docker-php-ext-install intl mbstring mysqli pdo_mysql zip \
    && rm -rf /var/lib/apt/lists/*

# --- Apache: single MPM (prefork, required by mod_php), docroot -> public/, rewrite ---
# Remove every enabled MPM symlink first, then enable exactly prefork — this
# guarantees a single MPM (fixes "AH00534: More than one MPM loaded").
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
    && a2enmod mpm_prefork rewrite headers
COPY docker/security-headers.conf /etc/apache2/conf-enabled/security-headers.conf
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -ri -e 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

# --- Composer (production deps only) ---
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# --- App source ---
COPY . .
RUN chown -R www-data:www-data writable && chmod -R 775 writable

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
