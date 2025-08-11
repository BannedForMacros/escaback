# --- Imagen base con PHP CLI 8.2 ---
FROM php:8.2-cli

# Paquetes del sistema necesarios para GD y ZIP
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libjpeg62-turbo-dev libfreetype6-dev libzip-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j$(nproc) pdo_mysql gd zip bcmath \
 && rm -rf /var/lib/apt/lists/*

# Composer dentro de la imagen
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

# Instalar dependencias (sin dev), optimizar autoloader
# Si algún script de post-install falla por falta de .env, no rompas la build
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress || true

# Railway expone el puerto en $PORT
ENV PORT=8080
EXPOSE 8080

# Arranque: intenta migrar y levanta el servidor embebido de PHP apuntando a public/
CMD sh -lc 'php artisan migrate --force || true; \
            chmod -R 775 storage bootstrap/cache || true; \
            php -d variables_order=EGPCS -S 0.0.0.0:$PORT -t public public/index.php'
