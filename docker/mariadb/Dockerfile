# infra-10032026-Maurice
# Modification type: generate persisted local credentials before the official MariaDB entrypoint.
FROM mariadb:10.11.11@sha256:96be0d3dfbeb07bc420e5fb8a6dc05c492676f1f89980a497a55e6fbbba3f1c4

COPY docker/mariadb/entrypoint.sh /usr/local/bin/ojt-mariadb-entrypoint
RUN chmod 0755 /usr/local/bin/ojt-mariadb-entrypoint

ENTRYPOINT ["/usr/local/bin/ojt-mariadb-entrypoint"]
CMD ["mariadbd"]
