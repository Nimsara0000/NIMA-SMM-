FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libcurl4-openssl-dev \
    libpq-dev \
    && docker-php-ext-install curl pdo pdo_mysql pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite && echo "ServerName localhost" >> /etc/apache2/apache2.conf

COPY . /var/www/html/

RUN mkdir -p /var/www/html/uploads/receipts && \
    chmod -R 755 /var/www/html && \
    chown -R www-data:www-data /var/www/html/uploads

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80
CMD ["docker-entrypoint.sh"]
