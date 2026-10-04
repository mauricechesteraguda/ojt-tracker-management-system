#!/bin/sh
set -eu

# observability-10042026-Maurice: keep Nginx access JSON and suppress native
# startup/access diagnostics that are not structured application events.
nginx -g 'pid /tmp/nginx/nginx.pid; daemon off;' 2>&1 | while IFS= read -r line; do
    case "$line" in
        *'{'*) printf '%s\n' "${line#*\{}" | sed 's/^/{/' ;;
    esac
done
