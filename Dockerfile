FROM serversideup/php:8.1-cli

WORKDIR /app

COPY composer.json composer.lock* ./

RUN composer install --prefer-dist --no-interaction --no-progress

COPY . .

RUN composer dump-autoload

CMD ["composer", "test"]
