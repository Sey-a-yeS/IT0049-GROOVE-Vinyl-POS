#!/bin/sh
set -eu

case "${PORT:-10000}" in
    ''|*[!0-9]*)
        echo "PORT must be a numeric TCP port." >&2
        exit 1
        ;;
esac

if [ "${PORT:-10000}" -lt 1 ] || [ "${PORT:-10000}" -gt 65535 ]; then
    echo "PORT must be between 1 and 65535." >&2
    exit 1
fi

export PORT="${PORT:-10000}"

# /var/data is the Render persistent-disk mount. These directories also work
# on the container's ephemeral filesystem for local smoke tests.
install -d -o www-data -g www-data -m 0750 \
    /var/data/uploads/products \
    /var/data/uploads/avatars \
    /var/data/session \
    /var/www/html/writable/cache \
    /var/www/html/writable/logs \
    /var/www/html/writable/uploads

if [ ! -f /var/data/uploads/.htaccess ]; then
    install -o www-data -g www-data -m 0640 \
        /usr/local/share/groove/uploads.htaccess \
        /var/data/uploads/.htaccess
fi

chown www-data:www-data \
    /var/data \
    /var/data/uploads \
    /var/data/uploads/products \
    /var/data/uploads/avatars \
    /var/data/session

exec "$@"
