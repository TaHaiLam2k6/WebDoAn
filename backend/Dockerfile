FROM php:8.3-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli

RUN a2enmod rewrite headers

COPY . /var/www/html/

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type d -exec chmod 755 {} \; \
    && find /var/www/html -type f -exec chmod 644 {} \;

RUN printf '%s\n' \
    '<Directory /var/www/html>' \
    '    Options FollowSymLinks' \
    '    AllowOverride All' \
    '    Require all granted' \
    '</Directory>' \
    > /etc/apache2/conf-available/phoneshop.conf

RUN a2enconf phoneshop

RUN printf '%s\n' \
    '<IfModule mod_dir.c>' \
    '    DirectoryIndex index.php index.html' \
    '</IfModule>' \
    > /etc/apache2/conf-available/phoneshop-index.conf

RUN a2enconf phoneshop-index

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
