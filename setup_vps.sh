#!/usr/bin/env bash
set -euo pipefail

if [[ ${EUID:-$(id -u)} -ne 0 ]]; then
  echo "Please run as root (e.g., sudo bash setup_vps.sh domain.tld)."
  exit 1
fi

usage() {
  cat <<USAGE
Usage: sudo bash setup_vps.sh domain.tld

Example: sudo bash setup_vps.sh example.com

Derived values:
  app name:       domain without the TLD (example)
  app path:       /var/www/app_name
  database name:  app_name_db
  database user:  app_name_user
USAGE
}

if [[ $# -ne 1 || "${1:-}" == "-h" || "${1:-}" == "--help" ]]; then
  usage
  exit 1
fi

DOMAIN_NAME="${1,,}"
DOMAIN_NAME="${DOMAIN_NAME#http://}"
DOMAIN_NAME="${DOMAIN_NAME#https://}"
DOMAIN_NAME="${DOMAIN_NAME%%/*}"
DOMAIN_NAME="${DOMAIN_NAME%.}"

if [[ ! "${DOMAIN_NAME}" =~ ^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)+$ ]]; then
  echo "Invalid domain: ${DOMAIN_NAME}"
  usage
  exit 1
fi

APP_NAME="${DOMAIN_NAME%%.*}"
APP_NAME="${APP_NAME//-/_}"
APP_DIR="/var/www/${APP_NAME}"
DEPLOY_USER="deploy"
PHP_VERSION="8.2"
LETSENCRYPT_EMAIL="admin@${DOMAIN_NAME}"
WWW_DOMAIN="www.${DOMAIN_NAME}"
MYSQL_DB_NAME="${APP_NAME}_db"
MYSQL_APP_USER="${APP_NAME}_user"

domain_already_configured() {
  local config_dirs=(
    /etc/nginx/sites-available
    /etc/nginx/sites-enabled
    /etc/apache2/sites-available
    /etc/apache2/sites-enabled
    /etc/httpd/conf.d
  )
  local dir
  local file

  if [[ -d "/etc/letsencrypt/live/${DOMAIN_NAME}" ]]; then
    return 0
  fi

  for dir in "${config_dirs[@]}"; do
    [[ -d "${dir}" ]] || continue

    while IFS= read -r -d '' file; do
      if grep -Fq -- "${DOMAIN_NAME}" "${file}" || grep -Fq -- "${WWW_DOMAIN}" "${file}"; then
        return 0
      fi
    done < <(find "${dir}" -type f -print0)
  done

  return 1
}

if domain_already_configured; then
  echo "Domain ${DOMAIN_NAME} is already configured on this server; skipping setup."
  exit 0
fi

export DEBIAN_FRONTEND=noninteractive

apt-get update

has_apt_package() {
  apt-cache show "$1" >/dev/null 2>&1
}

is_installed() {
  dpkg-query -W -f='${Status}' "$1" 2>/dev/null | grep -q "install ok installed"
}

install_missing_packages() {
  local missing=()

  for package in "$@"; do
    if ! is_installed "${package}"; then
      missing+=("${package}")
    fi
  done

  if ((${#missing[@]})); then
    apt-get install -y --no-install-recommends "${missing[@]}"
  else
    echo "All required apt packages are already installed."
  fi
}

php_pkg() {
  local extension="$1"
  local versioned="php${PHP_VERSION}-${extension}"

  if has_apt_package "${versioned}"; then
    echo "${versioned}"
  else
    echo "php-${extension}"
  fi
}

PHP_PACKAGES=(
  "$(php_pkg fpm)"
  "$(php_pkg cli)"
  "$(php_pkg mbstring)"
  "$(php_pkg xml)"
  "$(php_pkg curl)"
  "$(php_pkg zip)"
  "$(php_pkg mysql)"
  "$(php_pkg bcmath)"
  "$(php_pkg intl)"
)

REQUIRED_PACKAGES=(
  ca-certificates
  curl
  git
  openssl
  sudo
  unzip
  supervisor
  nginx
  mariadb-server
  certbot
  python3-certbot-nginx
  ufw
  redis-server
  "${PHP_PACKAGES[@]}"
)

install_missing_packages "${REQUIRED_PACKAGES[@]}"

PHP_FPM_SOCKET="/run/php/php-fpm.sock"
PHP_FPM_SERVICE="php-fpm"

if compgen -G "/etc/php/*/fpm/pool.d/www.conf" >/dev/null 2>&1; then
  PHP_FPM_VERSION=$(basename "$(dirname "$(dirname "$(dirname "$(ls /etc/php/*/fpm/pool.d/www.conf | head -n 1)")")")")
  PHP_FPM_SOCKET="/run/php/php${PHP_FPM_VERSION}-fpm.sock"
  PHP_FPM_SERVICE="php${PHP_FPM_VERSION}-fpm"
fi

if ! command -v composer >/dev/null 2>&1; then
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
else
  echo "Composer is already installed."
fi

if ! command -v node >/dev/null 2>&1; then
  curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
  install_missing_packages nodejs
else
  echo "Node.js is already installed."
fi

if ! id -u "${DEPLOY_USER}" >/dev/null 2>&1; then
  useradd -m -s /bin/bash "${DEPLOY_USER}"
fi

usermod -aG sudo "${DEPLOY_USER}"
usermod -aG www-data "${DEPLOY_USER}"

DEPLOY_PASSWORD=$(openssl rand -hex 24)
echo "${DEPLOY_USER}:${DEPLOY_PASSWORD}" | chpasswd

install -d -m 700 "/home/${DEPLOY_USER}/.ssh"
if [[ ! -f "/home/${DEPLOY_USER}/.ssh/${APP_NAME}_deploy" ]]; then
  ssh-keygen -t ed25519 -f "/home/${DEPLOY_USER}/.ssh/${APP_NAME}_deploy" -N ""
fi

touch "/home/${DEPLOY_USER}/.ssh/authorized_keys"
if ! grep -qxF "$(cat "/home/${DEPLOY_USER}/.ssh/${APP_NAME}_deploy.pub")" "/home/${DEPLOY_USER}/.ssh/authorized_keys"; then
  cat "/home/${DEPLOY_USER}/.ssh/${APP_NAME}_deploy.pub" >> "/home/${DEPLOY_USER}/.ssh/authorized_keys"
fi
chown -R "${DEPLOY_USER}:${DEPLOY_USER}" "/home/${DEPLOY_USER}/.ssh"
chmod 600 "/home/${DEPLOY_USER}/.ssh/authorized_keys"

install -d -m 755 "${APP_DIR}"
chown -R "${DEPLOY_USER}:www-data" "${APP_DIR}"

cat > "/etc/nginx/sites-available/${APP_NAME}" <<NGINX
server {
    listen 80;
    server_name ${DOMAIN_NAME} ${WWW_DOMAIN};
    root ${APP_DIR}/public;

    location ^~ /.well-known/acme-challenge/ {
        allow all;
        try_files \$uri =404;
    }

    location / {
        return 301 https://${WWW_DOMAIN}\$request_uri;
    }
}
NGINX

ln -sf "/etc/nginx/sites-available/${APP_NAME}" "/etc/nginx/sites-enabled/${APP_NAME}"
rm -f /etc/nginx/sites-enabled/default

cat > "/etc/supervisor/conf.d/${APP_NAME}.conf" <<SUP
[program:${APP_NAME}-queue]
command=php ${APP_DIR}/artisan queue:work --sleep=3 --tries=3 --timeout=90
directory=${APP_DIR}
user=${DEPLOY_USER}
autostart=true
autorestart=true
stderr_logfile=/var/log/${APP_NAME}-queue.err.log
stdout_logfile=/var/log/${APP_NAME}-queue.out.log

[program:${APP_NAME}-schedule]
command=php ${APP_DIR}/artisan schedule:work
directory=${APP_DIR}
user=${DEPLOY_USER}
autostart=true
autorestart=true
stderr_logfile=/var/log/${APP_NAME}-schedule.err.log
stdout_logfile=/var/log/${APP_NAME}-schedule.out.log
SUP

ufw --force reset
ufw default deny incoming
ufw default allow outgoing
ufw limit OpenSSH
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable

systemctl enable --now "${PHP_FPM_SERVICE}" nginx supervisor redis-server mariadb
nginx -t
systemctl reload nginx

certbot certonly --nginx \
  --non-interactive \
  --agree-tos \
  --email "${LETSENCRYPT_EMAIL}" \
  -d "${DOMAIN_NAME}" \
  -d "${WWW_DOMAIN}"

cat > "/etc/nginx/sites-available/${APP_NAME}" <<NGINX
server {
    listen 80;
    server_name ${DOMAIN_NAME} ${WWW_DOMAIN};

    location ^~ /.well-known/acme-challenge/ {
        root ${APP_DIR}/public;
        allow all;
        try_files \$uri =404;
    }

    location / {
        return 301 https://${WWW_DOMAIN}\$request_uri;
    }
}

server {
    listen 443 ssl http2;
    server_name ${DOMAIN_NAME};

    ssl_certificate /etc/letsencrypt/live/${DOMAIN_NAME}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/${DOMAIN_NAME}/privkey.pem;

    return 301 https://${WWW_DOMAIN}\$request_uri;
}

server {
    listen 443 ssl http2;
    server_name ${WWW_DOMAIN};
    root ${APP_DIR}/public;

    ssl_certificate /etc/letsencrypt/live/${DOMAIN_NAME}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/${DOMAIN_NAME}/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT \$realpath_root;
        fastcgi_pass unix:${PHP_FPM_SOCKET};
    }

    location ~ /\. {
        deny all;
    }
}
NGINX

mysql -e "CREATE DATABASE IF NOT EXISTS \`${MYSQL_DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
MYSQL_APP_PASSWORD=$(openssl rand -hex 24)
mysql -e "CREATE USER IF NOT EXISTS '${MYSQL_APP_USER}'@'localhost' IDENTIFIED BY '${MYSQL_APP_PASSWORD}';"
mysql -e "ALTER USER '${MYSQL_APP_USER}'@'localhost' IDENTIFIED BY '${MYSQL_APP_PASSWORD}';"
mysql -e "GRANT ALL PRIVILEGES ON \`${MYSQL_DB_NAME}\`.* TO '${MYSQL_APP_USER}'@'localhost'; FLUSH PRIVILEGES;"

nginx -t
systemctl reload nginx
supervisorctl reread
supervisorctl update

cat <<CREDS

Setup complete.

Domain: ${DOMAIN_NAME}
WWW Domain: ${WWW_DOMAIN}
Let's Encrypt email: ${LETSENCRYPT_EMAIL}

Deploy user: ${DEPLOY_USER}
Deploy password: ${DEPLOY_PASSWORD}
Deploy user sudo: enabled

MariaDB database: ${MYSQL_DB_NAME}
MariaDB user: ${MYSQL_APP_USER}
MariaDB password: ${MYSQL_APP_PASSWORD}

SSH private key: /home/${DEPLOY_USER}/.ssh/${APP_NAME}_deploy
SSH public key:  /home/${DEPLOY_USER}/.ssh/${APP_NAME}_deploy.pub

Public key contents:
$(cat "/home/${DEPLOY_USER}/.ssh/${APP_NAME}_deploy.pub")

Next steps:
- Point ${DOMAIN_NAME} and ${WWW_DOMAIN} DNS A/AAAA records to this server before running setup.
- Deploy the app to ${APP_DIR} as ${DEPLOY_USER}
- Copy .env.example to .env and configure database/mail
- Run: composer install && php artisan key:generate && php artisan migrate
- Run: npm install && npm run build
CREDS
