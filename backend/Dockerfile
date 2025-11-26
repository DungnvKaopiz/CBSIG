FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

# Install and configure Xdebug (via PECL)
# Reference: https://xdebug.org/docs/install
RUN set -eux; \
    apt-get update && apt-get install -y --no-install-recommends $PHPIZE_DEPS; \
    pecl install xdebug; \
    rm -rf /tmp/pear; \
    EXTENSION_DIR=$(php -r 'echo ini_get("extension_dir");'); \
    { \
        echo "zend_extension=${EXTENSION_DIR}/xdebug.so"; \
        echo "xdebug.mode=develop,debug"; \
        echo "xdebug.start_with_request=yes"; \
        echo "xdebug.client_host=host.docker.internal"; \
        echo "xdebug.client_port=9003"; \
        echo "xdebug.log=/tmp/xdebug.log"; \
        echo "xdebug.log_level=7"; \
    } > /usr/local/etc/php/conf.d/99-xdebug.ini; \
    apt-get purge -y --auto-remove $PHPIZE_DEPS; \
    rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy existing application directory contents
COPY . /var/www/html

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Use entrypoint to fix permissions on startup
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Start PHP-FPM (runs as root, spawns workers as www-data)
CMD ["php-fpm"]

