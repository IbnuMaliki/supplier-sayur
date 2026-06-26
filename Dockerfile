FROM php:8.3-cli

RUN docker-php-ext-install pdo pdo_mysql mysqli

RUN mkdir -p /tmp/sessions && chmod 777 /tmp/sessions

WORKDIR /app
COPY . /app/

EXPOSE 8080

CMD ["php", "-d", "session.save_path=/tmp/sessions", "-d", "session.gc_maxlifetime=3600", "-S", "0.0.0.0:8080", "/app/router.php"]
