FROM ubuntu:focal

# Update and install core components
RUN apt-get update -y && apt-get upgrade -y
RUN apt-get install -y 
    software-properties-common supervisor certbot

# Install php components
RUN add-apt-repository ppa:ondrej/php -y
RUN apt-get install -y 
    php8.0-cli php8.0-mysql

# Prepare folders and files
RUN mkdir -p /var/log/supervisor
COPY worker.php /opt/worker.php

COPY docker-supervisord.conf /etc/supervisor/conf.d/supervisord.conf
CMD ["/usr/bin/supervisord"]
