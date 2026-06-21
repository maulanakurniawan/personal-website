#!/usr/bin/env bash
set -euo pipefail

usage() {
  cat <<USAGE
Usage: sudo $0 --domain DOMAIN --email LETSENCRYPT_EMAIL [--php-socket SOCKET] [--web-root /var/www]

Creates an nginx virtual host, Linux user, SSH keypair, MariaDB database/user,
sudoers entry, and Let's Encrypt certificate for DOMAIN.

Example:
  sudo $0 --domain maulanakurniawan.com --email admin@maulanakurniawan.com
USAGE
}

require_root() {
  if [[ ${EUID:-$(id -u)} -ne 0 ]]; then
    echo "Please run as root (e.g., sudo $0 ...)." >&2
    exit 1
  fi
}

sanitize_domain() {
  local domain="$1"
  domain="${domain,,}"
  domain="${domain#http://}"
  domain="${domain#https://}"
  domain="${domain%%/*}"
  domain="${domain%.}"
  if [[ ! "${domain}" =~ ^[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?(\.[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?)+$ ]]; then
    echo "Invalid domain: ${domain}" >&2
    exit 1
  fi
  printf '%s' "${domain}"
}

domain_label() {
  local domain="$1"
  local first_label="${domain%%.*}"
  local username
  username="$(printf '%s' "${first_label}" | tr -cd '[:alnum:]_-')"
  if [[ -z "${username}" || ${#username} -gt 32 ]]; then
    echo "Cannot derive a valid Linux username from ${domain}." >&2
    exit 1
  fi
  printf '%s' "${username}"
}

sql_escape() {
  printf '%s' "$1" | sed "s/'/''/g"
}

random_password() {
  openssl rand -base64 36 | tr -d '\n'
}

DOMAIN=""
LETSENCRYPT_EMAIL=""
WEB_ROOT="/var/www"
PHP_SOCKET=""

while [[ $# -gt 0 ]]; do
  case "$1" in
    --domain) DOMAIN="${2:-}"; shift 2 ;;
    --email|--letsencrypt-email) LETSENCRYPT_EMAIL="${2:-}"; shift 2 ;;
    --web-root) WEB_ROOT="${2:-}"; shift 2 ;;
    --php-socket) PHP_SOCKET="${2:-}"; shift 2 ;;
    -h|--help) usage; exit 0 ;;
    *) echo "Unknown argument: $1" >&2; usage; exit 1 ;;
  esac
done

require_root

if [[ -z "${DOMAIN}" || -z "${LETSENCRYPT_EMAIL}" ]]; then
  usage >&2
  exit 1
fi

DOMAIN="$(sanitize_domain "${DOMAIN}")"
WWW_DOMAIN="www.${DOMAIN}"
VHOST_USER="$(domain_label "${DOMAIN}")"
APP_DIR="${WEB_ROOT}/${DOMAIN}"
PUBLIC_DIR="${APP_DIR}/public"
MYSQL_DB_NAME="${VHOST_USER//-/_}"
MYSQL_APP_USER="${MYSQL_DB_NAME}_user"
USER_PASSWORD="$(random_password)"
MYSQL_APP_PASSWORD="$(random_password)"
SSH_KEY_PATH="/home/${VHOST_USER}/.ssh/${VHOST_USER}_deploy"
SUDOERS_FILE="/etc/sudoers.d/${VHOST_USER}"

if [[ -z "${PHP_SOCKET}" ]]; then
  PHP_SOCKET="/run/php/php-fpm.sock"
  if compgen -G "/run/php/php*-fpm.sock" >/dev/null 2>&1; then
    PHP_SOCKET="$(find /run/php -maxdepth 1 -name 'php*-fpm.sock' | sort -V | tail -n 1)"
  fi
fi

for command in openssl nginx mysql certbot ssh-keygen visudo; do
  if ! command -v "${command}" >/dev/null 2>&1; then
    echo "Missing required command: ${command}" >&2
    exit 1
  fi
done

if ! id -u "${VHOST_USER}" >/dev/null 2>&1; then
  useradd -m -s /bin/bash "${VHOST_USER}"
fi
echo "${VHOST_USER}:${USER_PASSWORD}" | chpasswd
usermod -aG sudo "${VHOST_USER}"
usermod -aG www-data "${VHOST_USER}" 2>/dev/null || true
printf '%s ALL=(ALL:ALL) ALL\n' "${VHOST_USER}" > "${SUDOERS_FILE}"
chmod 0440 "${SUDOERS_FILE}"
visudo -cf "${SUDOERS_FILE}" >/dev/null

install -d -m 755 "${PUBLIC_DIR}"
if [[ ! -f "${PUBLIC_DIR}/index.html" ]]; then
  cat > "${PUBLIC_DIR}/index.html" <<HTML
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><title>${DOMAIN}</title></head>
<body><h1>${DOMAIN}</h1><p>Virtual host created successfully.</p></body>
</html>
HTML
fi
chown -R "${VHOST_USER}:www-data" "${APP_DIR}"

install -d -m 700 "/home/${VHOST_USER}/.ssh"
if [[ ! -f "${SSH_KEY_PATH}" ]]; then
  ssh-keygen -t ed25519 -f "${SSH_KEY_PATH}" -N "" -C "${VHOST_USER}@${DOMAIN}"
fi
touch "/home/${VHOST_USER}/.ssh/authorized_keys"
if ! grep -qxF "$(cat "${SSH_KEY_PATH}.pub")" "/home/${VHOST_USER}/.ssh/authorized_keys"; then
  cat "${SSH_KEY_PATH}.pub" >> "/home/${VHOST_USER}/.ssh/authorized_keys"
fi
chown -R "${VHOST_USER}:${VHOST_USER}" "/home/${VHOST_USER}/.ssh"
chmod 600 "/home/${VHOST_USER}/.ssh/authorized_keys"

mysql -e "CREATE DATABASE IF NOT EXISTS \`${MYSQL_DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '$(sql_escape "${MYSQL_APP_USER}")'@'localhost' IDENTIFIED BY '$(sql_escape "${MYSQL_APP_PASSWORD}")';"
mysql -e "ALTER USER '$(sql_escape "${MYSQL_APP_USER}")'@'localhost' IDENTIFIED BY '$(sql_escape "${MYSQL_APP_PASSWORD}")';"
mysql -e "GRANT ALL PRIVILEGES ON \`${MYSQL_DB_NAME}\`.* TO '$(sql_escape "${MYSQL_APP_USER}")'@'localhost'; FLUSH PRIVILEGES;"

cat > "/etc/nginx/sites-available/${VHOST_USER}" <<NGINX
server {
    listen 80;
    server_name ${DOMAIN} ${WWW_DOMAIN};
    root ${PUBLIC_DIR};
    index index.php index.html;

    location ^~ /.well-known/acme-challenge/ {
        allow all;
        try_files \$uri =404;
    }

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        fastcgi_param DOCUMENT_ROOT \$realpath_root;
        fastcgi_pass unix:${PHP_SOCKET};
    }

    location ~ /\. {
        deny all;
    }
}
NGINX
ln -sf "/etc/nginx/sites-available/${VHOST_USER}" "/etc/nginx/sites-enabled/${VHOST_USER}"
nginx -t
systemctl reload nginx

certbot --nginx --non-interactive --agree-tos --redirect --email "${LETSENCRYPT_EMAIL}" -d "${DOMAIN}" -d "${WWW_DOMAIN}"
nginx -t
systemctl reload nginx

cat <<CREDS

Virtual host created.
Domain: ${DOMAIN}
WWW Domain: ${WWW_DOMAIN}
Let's Encrypt email: ${LETSENCRYPT_EMAIL}

User: ${VHOST_USER}
User password: ${USER_PASSWORD}
Sudo status: enabled

MariaDB database: ${MYSQL_DB_NAME}
MariaDB user: ${MYSQL_APP_USER}
MariaDB password: ${MYSQL_APP_PASSWORD}

SSH private key path: ${SSH_KEY_PATH}
SSH private key:
$(cat "${SSH_KEY_PATH}")

SSH public key path: ${SSH_KEY_PATH}.pub
SSH public key:
$(cat "${SSH_KEY_PATH}.pub")
CREDS
