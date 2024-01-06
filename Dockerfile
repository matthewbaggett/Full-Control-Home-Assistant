FROM ghcr.io/home-assistant/home-assistant:stable
ENV TERM=xterm-256color
RUN apk \
      add \
        --update \
        --no-cache \
            php81-cli \
            php81-curl \
            php81-fileinfo \
            php81-tokenizer \
            php81-pdo \
            composer \
            ncurses

# turn this into a phar in the future
COPY . /usr/src/fullcontrol
RUN /usr/bin/composer \
      --working-dir=/usr/src/fullcontrol \
      --no-cache \
        install
RUN chmod +x /usr/src/fullcontrol/bin/fullcontrol && \
    chmod a+rw /var/log
ENV PATH="/usr/src/fullcontrol/bin:${PATH}"
RUN sed -i 's|python3 -m homeassistant|python3 -m homeassistant --log-file /var/log/home-assistant.log|g' /etc/services.d/home-assistant/run && \
    touch /etc/services.d/home-assistant/run
RUN cat  /etc/services.d/home-assistant/run