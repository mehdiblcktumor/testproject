FROM php:8.3-apache-bookworm
RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev \
    && docker-php-ext-install pdo_mysql curl \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*
ENV PORT=10000
COPY . /var/www/html/
COPY render/entrypoint.sh /usr/local/bin/render-entrypoint.sh
COPY render/generate-config.php /usr/local/bin/generate-config.php
RUN chmod +x /usr/local/bin/render-entrypoint.sh \
    && mkdir -p /var/www/html/storage/uploads /var/www/html/storage/designs /var/www/html/config \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/config
EXPOSE 10000
ENTRYPOINT ["/usr/local/bin/render-entrypoint.sh"]
CMD ["apache2-foreground"]
