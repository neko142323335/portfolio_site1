FROM php:8.1-apache

# Install SQLite development libraries and dependencies
RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    curl \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Enable required PHP modules
RUN docker-php-ext-install pdo pdo_sqlite

# Copy custom PHP configuration
COPY php.ini /usr/local/etc/php/conf.d/uploads.ini

# Enable mod_rewrite
RUN a2enmod rewrite

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set Apache document root to public directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies with Composer
RUN composer install --no-interaction --prefer-dist && \
    composer dump-autoload --optimize

# Create directories and symlinks for assets
RUN mkdir -p public database && \
    ln -sf /var/www/html/assets /var/www/html/public/assets

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 /var/www/html/database

# Expose port
EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]