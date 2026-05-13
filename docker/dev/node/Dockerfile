ARG NODE_VERSION=24-bookworm-slim


FROM node:${NODE_VERSION}

WORKDIR /app

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        bash \
        git \
        ca-certificates \
        curl \
    ;

COPY docker/dev/node/bin/ /usr/local/bin/
RUN chmod +x /usr/local/bin/npm-* /usr/local/bin/ng

COPY docker/dev/node/docker-entrypoint-dev.sh /usr/local/bin/docker-entrypoint-dev
RUN chmod +x /usr/local/bin/docker-entrypoint-dev

EXPOSE 4200

ENTRYPOINT ["docker-entrypoint-dev"]
CMD ["bash"]
