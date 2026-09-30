#!/bin/bash
set -e

# Support dynamic $PORT for Render / Cloud Web Services
if [ -n "$PORT" ]; then
    sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
    sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-available/000-default.conf
fi

exec apache2-foreground "$@"
