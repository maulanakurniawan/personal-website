#!/usr/bin/env bash
set -euo pipefail

usage() {
  cat <<USAGE
Usage: sudo $0 --domain DOMAIN --user OLD_USER [--web-root /var/www]

Renames an nginx virtual-host Linux user to the domain label (domain without the
last TLD segment), moves the matching /var/www directory to /var/www/DOMAIN,
rewrites common nginx and supervisor references, and changes ownership to the
renamed user.

Example:
  sudo $0 --domain maulanakurniawan.com --user deploy
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

replace_in_file() {
  local file="$1"
  local from="$2"
  local to="$3"
  if [[ -f "${file}" ]]; then
    perl -0pi -e 's/\Q$ENV{FROM}\E/$ENV{TO}/g' "${file}"
  fi
}

move_if_exists() {
  local source="$1"
  local target="$2"
  if [[ -e "${source}" && "${source}" != "${target}" ]]; then
    if [[ -e "${target}" ]]; then
      echo "Target already exists, not moving ${source} -> ${target}." >&2
    else
      mv "${source}" "${target}"
    fi
  fi
}

DOMAIN=""
OLD_USER=""
WEB_ROOT="/var/www"

while [[ $# -gt 0 ]]; do
  case "$1" in
    --domain) DOMAIN="${2:-}"; shift 2 ;;
    --user|--old-user) OLD_USER="${2:-}"; shift 2 ;;
    --web-root) WEB_ROOT="${2:-}"; shift 2 ;;
    -h|--help) usage; exit 0 ;;
    *) echo "Unknown argument: $1" >&2; usage; exit 1 ;;
  esac
done

require_root

if [[ -z "${DOMAIN}" || -z "${OLD_USER}" ]]; then
  usage >&2
  exit 1
fi

DOMAIN="$(sanitize_domain "${DOMAIN}")"
NEW_USER="$(domain_label "${DOMAIN}")"
OLD_HOME="/home/${OLD_USER}"
NEW_HOME="/home/${NEW_USER}"
OLD_APP_DIR="${WEB_ROOT}/${OLD_USER}"
NEW_APP_DIR="${WEB_ROOT}/${DOMAIN}"
DOMAIN_APP_DIR="${WEB_ROOT}/${DOMAIN}"

if ! id -u "${OLD_USER}" >/dev/null 2>&1; then
  echo "User does not exist: ${OLD_USER}" >&2
  exit 1
fi

if [[ "${OLD_USER}" != "${NEW_USER}" ]]; then
  if id -u "${NEW_USER}" >/dev/null 2>&1; then
    echo "Target user already exists: ${NEW_USER}" >&2
    exit 1
  fi
  usermod -l "${NEW_USER}" "${OLD_USER}"
  if getent group "${OLD_USER}" >/dev/null 2>&1 && ! getent group "${NEW_USER}" >/dev/null 2>&1; then
    groupmod -n "${NEW_USER}" "${OLD_USER}"
  fi
  if [[ -d "${OLD_HOME}" ]]; then
    usermod -d "${NEW_HOME}" -m "${NEW_USER}"
  else
    usermod -d "${NEW_HOME}" "${NEW_USER}"
  fi
fi

move_if_exists "${OLD_APP_DIR}" "${NEW_APP_DIR}"
install -d -m 755 "${NEW_APP_DIR}"

mapfile -t CONFIG_FILES < <(find /etc/nginx /etc/supervisor /etc/systemd/system /etc/sudoers.d -type f 2>/dev/null || true)
for file in "${CONFIG_FILES[@]}"; do
  FROM="${OLD_USER}" TO="${NEW_USER}" replace_in_file "${file}" "${OLD_USER}" "${NEW_USER}"
  FROM="${OLD_APP_DIR}" TO="${NEW_APP_DIR}" replace_in_file "${file}" "${OLD_APP_DIR}" "${NEW_APP_DIR}"
  FROM="${DOMAIN_APP_DIR}" TO="${NEW_APP_DIR}" replace_in_file "${file}" "${DOMAIN_APP_DIR}" "${NEW_APP_DIR}"
done

for dir in /etc/nginx/sites-available /etc/nginx/sites-enabled /etc/supervisor/conf.d /etc/sudoers.d; do
  [[ -d "${dir}" ]] || continue
  if [[ -e "${dir}/${OLD_USER}" && ! -e "${dir}/${NEW_USER}" ]]; then
    mv "${dir}/${OLD_USER}" "${dir}/${NEW_USER}"
  fi
  if [[ -e "${dir}/${OLD_USER}.conf" && ! -e "${dir}/${NEW_USER}.conf" ]]; then
    mv "${dir}/${OLD_USER}.conf" "${dir}/${NEW_USER}.conf"
  fi
done

find "${NEW_APP_DIR}" "${NEW_HOME}" -xdev -exec chown "${NEW_USER}:${NEW_USER}" {} + 2>/dev/null || true
usermod -aG www-data "${NEW_USER}" 2>/dev/null || true

nginx -t
systemctl reload nginx
if command -v supervisorctl >/dev/null 2>&1; then
  supervisorctl reread || true
  supervisorctl update || true
fi

cat <<SUMMARY
Rename complete.
Domain: ${DOMAIN}
Old user: ${OLD_USER}
New user: ${NEW_USER}
App directory: ${NEW_APP_DIR}
Home directory: ${NEW_HOME}
Ownership: ${NEW_USER}:${NEW_USER}
SUMMARY
