# Dockerfile para Moodle - Construcción desde código fuente
FROM php:8.3-apache

# Variables de entorno para PHP
ENV PHP_MEMORY_LIMIT=512M \
    PHP_MAX_EXECUTION_TIME=300 \
    PHP_UPLOAD_MAX_FILESIZE=100M \
    PHP_POST_MAX_SIZE=100M

# Instalar dependencias del sistema y extensiones PHP requeridas por Moodle
RUN apt-get update && apt-get install -y \
    # Librerías de imágenes
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    # Compresión
    libzip-dev \
    zlib1g-dev \
    # Internacionalización
    libicu-dev \
    # XML y SOAP
    libxml2-dev \
    libxslt1-dev \
    # Base de datos
    libpq-dev \
    postgresql-client \
    # Utilidades
    git \
    unzip \
    curl \
    cron \
    && rm -rf /var/lib/apt/lists/*

# Configurar e instalar extensiones PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
    gd \
    zip \
    intl \
    soap \
    opcache \
    exif \
    pdo \
    pdo_pgsql \
    pgsql

# Configurar PHP para Moodle
RUN { \
    echo 'memory_limit=${PHP_MEMORY_LIMIT}'; \
    echo 'max_execution_time=${PHP_MAX_EXECUTION_TIME}'; \
    echo 'upload_max_filesize=${PHP_UPLOAD_MAX_FILESIZE}'; \
    echo 'post_max_size=${PHP_POST_MAX_SIZE}'; \
    echo 'max_input_vars=5000'; \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.revalidate_freq=60'; \
    } > /usr/local/etc/php/conf.d/moodle.ini

# Habilitar módulos de Apache necesarios
RUN a2enmod rewrite expires headers ssl

# Configurar Apache para Moodle
RUN { \
    echo '<VirtualHost *:80>'; \
    echo '    ServerAdmin admin@localhost'; \
    echo '    DocumentRoot /var/www/html/public'; \
    echo '    <Directory /var/www/html/public>'; \
    echo '        Options Indexes FollowSymLinks'; \
    echo '        AllowOverride All'; \
    echo '        Require all granted'; \
    echo '    </Directory>'; \
    echo '    ErrorLog ${APACHE_LOG_DIR}/error.log'; \
    echo '    CustomLog ${APACHE_LOG_DIR}/access.log combined'; \
    echo '</VirtualHost>'; \
    } > /etc/apache2/sites-available/000-default.conf

# Crear directorio para moodledata
RUN mkdir -p /var/www/moodledata && \
    chown -R www-data:www-data /var/www/moodledata && \
    chmod -R 0777 /var/www/moodledata

# Copiar el código fuente de Moodle desde tu fork
COPY --chown=www-data:www-data . /var/www/html/

# Configurar permisos correctos
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Script de entrada para inicialización
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Exponer puerto 80
EXPOSE 80

# Healthcheck
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Punto de entrada
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]