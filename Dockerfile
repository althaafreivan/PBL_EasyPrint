FROM php:8.3-apache

# Install PHP extensions required by EasyPrint (PostgreSQL, cURL, mbstring)
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpq-dev \
        libcurl4-openssl-dev \
        libonig-dev \
    && docker-php-ext-install pdo_pgsql pgsql curl mbstring \
    && a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork rewrite \
    && printf "ServerName localhost\n" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && rm -rf /var/lib/apt/lists/*

# Upload limit 10 MB (BUG-01)
RUN printf "upload_max_filesize = 10M\npost_max_size = 12M\n" \
    > /usr/local/etc/php/conf.d/easyprint-uploads.ini

WORKDIR /var/www/html
COPY Frontend/ /var/www/html/Frontend/
COPY backend/  /var/www/html/backend/

RUN mkdir -p /var/www/html/backend/uploads \
    && chown -R www-data:www-data /var/www/html/backend/uploads

# Apache: "/" -> Frontend/public, "/api" -> backend/public
RUN printf '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/Frontend/public\n\
    <Directory /var/www/html/Frontend/public>\n\
        Options -Indexes +FollowSymLinks\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
    Alias /api /var/www/html/backend/public\n\
    <Directory /var/www/html/backend/public>\n\
        Options -Indexes +FollowSymLinks\n\
        AllowOverride None\n\
        Require all granted\n\
        FallbackResource /api/index.php\n\
    </Directory>\n\
</VirtualHost>\n' > /etc/apache2/sites-available/000-default.conf

EXPOSE 8080
CMD ["sh", "-c", "PORT=${PORT:-8080}; export API_BASE_URL=${API_BASE_URL:-http://127.0.0.1:$PORT/api}; rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*; printf 'Listen %s\n' \"$PORT\" > /etc/apache2/ports.conf; sed -i -E \"s/<VirtualHost \\*:[0-9]+>/<VirtualHost *:$PORT>/\" /etc/apache2/sites-available/000-default.conf; exec apache2-foreground"]
