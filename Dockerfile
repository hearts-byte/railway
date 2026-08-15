FROM php:8.1-apache

# تثبيت أداة تثبيت الإضافات الجاهزة (تتعامل مع كل الاعتماديات تلقائيًا)
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

RUN install-php-extensions gd zip curl mbstring opcache pdo_mysql mysqli ioncube_loader redis

# تفعيل mod_rewrite حتى يشتغل ملف htaccess
RUN a2enmod rewrite

# السماح بـ .htaccess (AllowOverride All)
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# إنشاء مجلد مخصص للجلسات وتعيين صلاحيات الكتابة لـ Apache
RUN mkdir -p /var/lib/php/sessions \
    && chown -R www-data:www-data /var/lib/php/sessions \
    && chmod -R 777 /var/lib/php/sessions

# إعدادات PHP: رفع الحدود + تثبيت وحماية الجلسات + عرض الأخطاء + تسجيل الأخطاء
RUN { \
    echo 'memory_limit = 512M'; \
    echo 'max_execution_time = 300'; \
    echo 'upload_max_filesize = 64M'; \
    echo 'post_max_size = 64M'; \
    echo 'display_errors = On'; \
    echo 'error_reporting = E_ALL'; \
    echo 'log_errors = On'; \
    echo 'error_log = /var/www/html/php_errors.log'; \
    echo 'auto_prepend_file = /var/www/html/system/_debug_prepend.php'; \
    echo 'session.save_path = "/var/lib/php/sessions"'; \
    echo 'session.cookie_httponly = On'; \
    echo 'session.use_only_cookies = On'; \
    echo 'session.same_site = "Lax"'; \
    } > /usr/local/etc/php/conf.d/custom.ini

# نسخ ملفات المشروع
COPY . /var/www/html/

# سكربت تشخيص: يسجل تفاصيل أي طلب متعلق بإضافة Avatar_Frame-BLK
# (POST data + آخر خطأ PHP + حالة الـ output buffer) حتى لو سكربت مشفّر عمل exit بصمت
RUN mkdir -p /var/www/html/system && { \
    echo '<?php'; \
    echo "if (strpos(\$_SERVER['REQUEST_URI'] ?? '', 'Avatar_Frame-BLK') !== false) {"; \
    echo '    register_shutdown_function(function() {'; \
    echo '        $err = error_get_last();'; \
    echo "        \$log = date('[d-M-Y H:i:s e] ') . '[debug_prepend] uri=' . (\$_SERVER['REQUEST_URI'] ?? '') ."; \
    echo "            ' post=' . json_encode(\$_POST) ."; \
    echo "            ' last_error=' . json_encode(\$err) ."; \
    echo "            ' ob_level=' . ob_get_level() . \"\\n\";"; \
    echo "        file_put_contents('/var/www/html/php_errors.log', \$log, FILE_APPEND);"; \
    echo '    });'; \
    echo '}'; \
    } > /var/www/html/system/_debug_prepend.php

# إعداد الصلاحيات للمجلدات المطلوبة في صفحة التثبيت والرفع
RUN mkdir -p /var/www/html/avatar /var/www/html/cover /var/www/html/upload \
    && touch /var/www/html/php_errors.log \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/avatar /var/www/html/cover /var/www/html/upload \
    && chmod 666 /var/www/html/php_errors.log

# ملف تشغيل يهيّئ منفذ Railway الديناميكي ويصلح مشكلة MPM
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]
