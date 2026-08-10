FROM php:8.1-apache

# تثبيت الإضافات المطلوبة (MySQLi, GD, Zip, Mbstring, cURL موجود أساساً)
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

# تفعيل mod_rewrite (يحتاجه أغلب سكربتات PHP)
RUN a2enmod rewrite

# تثبيت Ioncube Loader لـ PHP 8.1
RUN curl -sSL -o /tmp/ioncube.tar.gz https://downloads.ioncube.com/loader_downloads/ioncube_loaders_lin_x86-64.tar.gz \
    && mkdir -p /tmp/ioncube \
    && tar -xzf /tmp/ioncube.tar.gz -C /tmp/ioncube --strip-components=1 \
    && cp /tmp/ioncube/ioncube_loader_lin_8.1.so $(php -r 'echo ini_get("extension_dir");')/ioncube_loader.so \
    && echo "zend_extension=$(php -r 'echo ini_get("extension_dir");')/ioncube_loader.so" > /usr/local/etc/php/conf.d/00-ioncube.ini \
    && rm -rf /tmp/ioncube /tmp/ioncube.tar.gz

# نسخ ملفات السكربت
COPY . /var/www/html/

# ضبط الصلاحيات
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Apache يحتاج الاستماع على متغير PORT الذي يوفره Railway
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

EXPOSE 80

CMD ["apache2-foreground"]
