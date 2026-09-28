#!/usr/bin/env bash
# Builds an upload-ready copy of the app for InfinityFree (no SSH, no Composer,
# no Node on the host) in build/infinityfree/, plus build/infinityfree.zip.
#
# Your local .env, vendor/ and node_modules/ are left untouched: the production
# .env and the --no-dev vendor/ only exist inside the build copy.
#
# Upload the *contents* of build/infinityfree/ into htdocs/. The generated
# root .htaccess forwards every request to public/.
set -euo pipefail

cd "$(dirname "$0")/.."
OUT=build/infinityfree

npm run build

rm -rf "$OUT" build/infinityfree.zip
mkdir -p "$OUT"

rsync -a \
    --exclude='/.git' --exclude='/.idea' --exclude='/.claude' --exclude='/.ai' \
    --exclude='/node_modules' --exclude='/vendor' --exclude='/build' --exclude='/tests' \
    --exclude='/.env' --exclude='/.env.*' --exclude='/.mcp.json' --exclude='/boost.json' \
    --exclude='/AGENTS.md' --exclude='/CLAUDE.md' --exclude='/.phpunit.result.cache' \
    --exclude='/public/hot' --exclude='/bootstrap/cache/*.php' \
    --exclude='/storage/logs/*.log' --exclude='/storage/framework/testing' \
    --exclude='/storage/framework/cache/data/*' --exclude='/storage/framework/sessions/*' \
    --exclude='/storage/framework/views/*.php' \
    ./ "$OUT"/

# Production .env: fresh key, debug off. Fill in the DB_* and APP_URL values
# from the InfinityFree control panel before uploading.
APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
sed \
    -e 's|^APP_ENV=.*|APP_ENV=production|' \
    -e 's|^APP_DEBUG=.*|APP_DEBUG=false|' \
    -e "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" \
    -e 's|^APP_URL=.*|APP_URL=https://CHANGE-ME.infinityfreeapp.com|' \
    -e 's|^LOG_LEVEL=.*|LOG_LEVEL=error|' \
    -e 's|^DB_CONNECTION=.*|DB_CONNECTION=mysql|' \
    -e 's|^DB_HOST=.*|DB_HOST=sqlXXX.infinityfree.com|' \
    -e 's|^DB_DATABASE=.*|DB_DATABASE=if0_XXXXXXXX_project_status_app|' \
    -e 's|^DB_USERNAME=.*|DB_USERNAME=if0_XXXXXXXX|' \
    -e 's|^DB_PASSWORD=.*|DB_PASSWORD=CHANGE-ME|' \
    .env.example > "$OUT/.env"

cat > "$OUT/.htaccess" <<'HTACCESS'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
HTACCESS

composer install --working-dir="$OUT" --no-dev --optimize-autoloader --no-interaction

(cd build && zip -qr infinityfree.zip infinityfree)

echo "Done: $OUT (and build/infinityfree.zip). Edit $OUT/.env DB_* and APP_URL before uploading."
