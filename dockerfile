# ---- Stage 1: build frontend assets (React + Tailwind via Vite) ----
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# ---- Stage 2: PHP app ----
FROM richarvey/nginx-php-fpm:3.1.6

# Laravel/Nginx config
ENV SKIP_COMPOSER=1
ENV WEBROOT=/var/www/html/public
ENV PHP_ERRORS_STDERR=1
ENV RUN_SCRIPTS=1
ENV REAL_IP_HEADER=1
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr
ENV COMPOSER_ALLOW_SUPERUSER=1

COPY . .
# bring in built assets from stage 1
COPY --from=frontend /app/public/build ./public/build

# install PHP deps
RUN composer install --no-dev --optimize-autoloader --working-dir=/var/www/html

# cache config/routes at build time is risky (env vars not set yet),
# so do it in start.sh instead — see below
COPY scripts/00-laravel-deploy.sh /etc/entrypoint.d/00-laravel-deploy.sh
RUN chmod +x /etc/entrypoint.d/00-laravel-deploy.sh

CMD ["/start.sh"]