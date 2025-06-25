# استخدم صورة PHP مع Apache
FROM php:8.2-apache

# انسخ كل ملفات المشروع إلى مجلد الويب
COPY . /var/www/html/

# فعل mod_rewrite إذا تحتاجه
RUN a2enmod rewrite

# تعيين صلاحيات مناسبة (اختياري)
RUN chown -R www-data:www-data /var/www/html

# المنفذ الافتراضي في Apache داخل الحاوية
EXPOSE 80
