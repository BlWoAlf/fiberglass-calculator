# syntax=docker/dockerfile:1.7
FROM node:22-bookworm-slim

ARG UID=1000
ARG GID=1000

RUN if [ "$(id -u node)" != "${UID}" ]; then \
        groupmod -o -g "${GID}" node && usermod -o -u "${UID}" node; \
    fi

WORKDIR /var/www/html

COPY docker/node/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

USER node
EXPOSE 5173

ENTRYPOINT ["entrypoint"]
