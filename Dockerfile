# Use official PHP 8.2 with Apache
FROM php:8.2-apache

# Install mysqli & pdo_mysql extensions required for database connectivity
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy entrypoint script and make it executable
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Copy project files into Apache web root
COPY . /var/www/html/

# Set proper permissions for Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose standard HTTP port
EXPOSE 80

# Entrypoint configures dynamic $PORT on Render, then launches apache2-foreground
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
