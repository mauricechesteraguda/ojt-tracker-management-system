# infra-10032026-Maurice
# Modification type: bake the public application tree into a non-root web image.
FROM nginx:1.27.5-alpine@sha256:65645c7bb6a0661892a8b03b89d0743208a18dd2f3f17a54ef4b76fb8e2f2a10

RUN mkdir -p /var/cache/nginx /var/log/nginx /var/lib/nginx /tmp/nginx \
    && chown -R nginx:nginx /var/cache/nginx /var/log/nginx /var/lib/nginx /tmp/nginx \
    && sed -i '/^[[:space:]]*user[[:space:]]/d; /^[[:space:]]*pid[[:space:]]/d' /etc/nginx/nginx.conf

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/nginx/entrypoint.sh /usr/local/bin/ojt-nginx-entrypoint
COPY . /var/www/html
RUN chown -R nginx:nginx /var/www/html
RUN chmod 0755 /usr/local/bin/ojt-nginx-entrypoint

WORKDIR /var/www/html

USER nginx
ENTRYPOINT ["/usr/local/bin/ojt-nginx-entrypoint"]
