# Docker CDN Certbot

![Docker Cloud Automated build](https://img.shields.io/docker/cloud/automated/envoyr/cdn-certbot)
![Docker Cloud Build status](https://img.shields.io/docker/cloud/build/envoyr/cdn-certbot)

## Documentation

### Use with docker-compose

````
version: "3"
services:
  app:
    image: envoyr/cdn-certbot:latest
    volumes:
      - certs:/etc/letsencrypt
      - acme:/var/www/.well-known/acme-challenge
volumes:
  certs:
  acme:
````

## License

This project is licensed under the terms of the MIT License.
