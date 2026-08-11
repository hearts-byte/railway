FROM php:8.1-apache

# تثبيت الإضافات عبر أداة mlocati (تتعامل مع التوافق تلقائياً)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions gd zip curl mbstring opcache pdo_mysql mysqli ioncube_loader

# تفعيل mod_rewrite
RUN a2enmod rewrite

# السماح بـ .htaccess
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# --- إصلاح "More than one MPM loaded" ---
# --- إصلاح جذري لمشكلة MPM ---
RUN rm -rf /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
    && ln -s /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load \
    && ln -s /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf \
    && apache2ctl -M 2>&1 | grep -i mpm

# مجلد الجلسات
RUN mkdir -p /var/lib/php/sessions \
    && chown -R www-data:www-data /var/lib/php/sessions \
    && chmod -R 777 /var/lib/php/sessions

# إعدادات PHP
RUN { \
    echo 'memory_limit = 512M'; \
    echo 'max_execution_time = 300'; \
    echo 'upload_max_filesize = 64M'; \
    echo 'post_max_size = 64M'; \
    echo 'display_errors = On'; \
    echo 'error_reporting = E_ALL'; \
    echo 'session.save_path = "/var/lib/php/sessions"'; \
    echo 'session.cookie_httponly = On'; \
    echo 'session.use_only_cookies = On'; \
    echo 'session.same_site = "Lax"'; \
    } > /usr/local/etc/php/conf.d/custom.ini

# نسخ ملفات المشروع
COPY . /var/www/html/

# صلاحيات المجلدات
RUN mkdir -p /var/www/html/avatar /var/www/html/cover /var/www/html/upload \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/avatar /var/www/html/cover /var/www/html/upload

# سكربت التشغيل — منشأ مباشرة (بدون COPY لتفادي مشاكل CRLF)
RUN printf '#!/bin/bash\n: "${PORT:=80}"\nsed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf\nsed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf\nexec apache2-foreground\n' > /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]
