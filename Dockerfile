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

# --- الإصلاح: تعطيل أي MPM آخر وتفعيل prefork فقط ---
RUN a2dismod mpm_event mpm_worker 2>/dev/null; \
    a2enmod mpm_prefork

RUN curl -sSL -o /tmp/ioncube.tar.gz https://downloads.ioncube.com/loader_downloads/ioncube_loaders_lin_x86-64.tar.gz \
    && mkdir -p /tmp/ioncube \
    && tar -xzf /tmp/ioncube.tar.gz -C /tmp/ioncube --strip-components=1 \
    && cp /tmp/ioncube/ioncube_loader_lin_8.1.so $(php -r 'echo ini_get("extension_dir");')/ioncube_loader.so \
    && echo "zend_extension=$(php -r 'echo ini_get("extension_dir");')/ioncube_loader.so" > /usr/local/etc/php/conf.d/00-ioncube.ini \
    && rm -rf /tmp/ioncube /tmp/ioncube.tar.gz

COPY . /var/www/html/
COPY start.sh /usr/local/bin/start.sh

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod +x /usr/local/bin/start.sh

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]
