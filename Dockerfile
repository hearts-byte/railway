FROM php:8.1-apache

RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    unzip \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        mysqli \
        pdo_mysql \
        gd \
        zip \
        mbstring \
        opcache \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

# تثبيت Ioncube Loader
RUN curl -sSL -o /tmp/ioncube.tar.gz https://downloads.ioncube.com/loader_downloads/ioncube_loaders_lin_x86-64.tar.gz \
    && mkdir -p /tmp/ioncube \
    && tar -xzf /tmp/ioncube.tar.gz -C /tmp/ioncube --strip-components=1 \
    && cp /tmp/ioncube/ioncube_loader_lin_8.1.so $(php -r 'echo ini_get("extension_dir");')/ioncube_loader.so \
    && echo "zend_extension=$(php -r 'echo ini_get("extension_dir");')/ioncube_loader.so" > /usr/local/etc/php/conf.d/00-ioncube.ini \
    && rm -rf /tmp/ioncube /tmp/ioncube.tar.gz

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# سكربت التشغيل: يضبط المنفذ ديناميكياً ثم يشغّل Apache
RUN echo '#!/bin/bash\n\
: "${PORT:=80}"\n\
sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf\n\
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf\n\
exec apache2-foreground' > /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]
