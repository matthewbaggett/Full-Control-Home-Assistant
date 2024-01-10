FROM ghcr.io/home-assistant/home-assistant:stable
ENV TERM=xterm-256color
# Install PHP8.2
RUN apk \
      add \
        --update \
        --no-cache \
            php82-cli \
            php82-curl \
            php82-fileinfo \
            php82-tokenizer \
            php82-pdo \
            php82-phar \
            php82-iconv \
            php82-openssl \
            php82-mbstring \
            php82-zip \
            ncurses \
    && \
    ln -s /usr/bin/php82 /usr/bin/php

# Install composer
RUN wget https://raw.githubusercontent.com/composer/getcomposer.org/76a7060ccb93902cd7576b67264ad91c8a2700e2/web/installer -O - -q | php -- --quiet && \
    mv composer.phar /usr/local/bin/composer && \
    chmod +x /usr/local/bin/composer && \
    composer self-update

# turn this into a phar in the future
COPY . /usr/src/fullcontrol
RUN composer \
      --working-dir=/usr/src/fullcontrol \
      --no-cache \
        install
RUN chmod +x /usr/src/fullcontrol/bin/fullcontrol && \
    chmod a+rw /var/log
ENV PATH="/usr/src/fullcontrol/bin:${PATH}"
RUN sed -i 's|python3 -m homeassistant|python3 -m homeassistant --log-file /var/log/home-assistant.log|g' /etc/services.d/home-assistant/run && \
    touch /etc/services.d/home-assistant/run
