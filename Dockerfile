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
            composer \
            ncurses

# turn this into a phar in the future
COPY . /usr/src/fullcontrol
RUN /usr/bin/composer \
      --working-dir=/usr/src/fullcontrol \
      --no-cache \
        install
RUN chmod +x /usr/src/fullcontrol/bin/fullcontrol
ENV PATH="/usr/src/fullcontrol/bin:${PATH}"
