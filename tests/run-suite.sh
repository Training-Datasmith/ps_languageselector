#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PHP_IMAGE="php@sha256:6ce95208609dc66df163ab936c970b3b34cd901b85c747102c5999f08ade9143"
PHPUNIT_VERSION="4.8.36"
PHPUNIT_SHA256="ab8bc3017a64cf75a2112f5e089a1d5b0c4cc2116556d60c1af5e3f584e85c5e"
PHPUNIT_FPR="D8406D0D82947747293778314AA394086372C20A"
SMARTY_VERSION="3.1.48"
SMARTY_SHA256="6a8a7d35b9be33c3a9fcf70df8a3b3652f0388f8015dda259ed04ff49fe38b4c"
SMARTY_URL="https://github.com/smarty-php/smarty/archive/refs/tags/v${SMARTY_VERSION}.tar.gz"

DOCKER=(docker)
if ! docker info >/dev/null 2>&1; then
  DOCKER=(sudo docker)
fi

TOOLS_DIR="${TOOLS_DIR:-$(mktemp -d)}"
export TOOLS_DIR

if [[ ! -f "${TOOLS_DIR}/phpunit-${PHPUNIT_VERSION}.phar" ]]; then
  GNUPGHOME="$(mktemp -d)"
  export GNUPGHOME
  curl -fsSL -o "${TOOLS_DIR}/phpunit-${PHPUNIT_VERSION}.phar" "https://phar.phpunit.de/phpunit-${PHPUNIT_VERSION}.phar"
  curl -fsSL -o "${TOOLS_DIR}/phpunit-${PHPUNIT_VERSION}.phar.asc" "https://phar.phpunit.de/phpunit-${PHPUNIT_VERSION}.phar.asc"
  echo "${PHPUNIT_SHA256}  ${TOOLS_DIR}/phpunit-${PHPUNIT_VERSION}.phar" | sha256sum -c -
  gpg --batch --keyserver hkps://keyserver.ubuntu.com --recv-keys "${PHPUNIT_FPR}"
  IMPORTED_FPR="$(gpg --batch --with-colons --fingerprint "${PHPUNIT_FPR}" 2>/dev/null | awk -F: '/^fpr:/ {print toupper($10); exit}')"
  if [[ "${IMPORTED_FPR}" != "${PHPUNIT_FPR}" ]]; then
    echo "Unexpected PHPUnit signing key fingerprint: ${IMPORTED_FPR}" >&2
    exit 1
  fi
  gpg --batch --status-fd 1 --verify "${TOOLS_DIR}/phpunit-${PHPUNIT_VERSION}.phar.asc" "${TOOLS_DIR}/phpunit-${PHPUNIT_VERSION}.phar" > "${TOOLS_DIR}/gpg-status.txt"
  VALIDSIG_FPR="$(awk '/^\[GNUPG:\] VALIDSIG/ {print $NF}' "${TOOLS_DIR}/gpg-status.txt" | head -1)"
  if [[ "${VALIDSIG_FPR}" != "${PHPUNIT_FPR}" ]]; then
    echo "PHPUnit PHAR VALIDSIG fingerprint mismatch: ${VALIDSIG_FPR}" >&2
    exit 1
  fi
  rm -rf "${GNUPGHOME}"
fi

if [[ ! -f "${TOOLS_DIR}/smarty-${SMARTY_VERSION}/libs/Smarty.class.php" ]]; then
  curl -fsSL -o "${TOOLS_DIR}/smarty-${SMARTY_VERSION}.tar.gz" "${SMARTY_URL}"
  echo "${SMARTY_SHA256}  ${TOOLS_DIR}/smarty-${SMARTY_VERSION}.tar.gz" | sha256sum -c -
  tar -xzf "${TOOLS_DIR}/smarty-${SMARTY_VERSION}.tar.gz" -C "${TOOLS_DIR}"
  if [[ ! -f "${TOOLS_DIR}/smarty-${SMARTY_VERSION}/libs/Smarty.class.php" ]]; then
    echo "Smarty extract layout unexpected" >&2
    exit 1
  fi
fi

"${DOCKER[@]}" pull "${PHP_IMAGE}"

"${DOCKER[@]}" run --rm \
  -v "${REPO_ROOT}:/work:ro" \
  -v "${TOOLS_DIR}:/tools:ro" \
  -w /work \
  -e SMARTY_DIR="/tools/smarty-${SMARTY_VERSION}" \
  -e PHPUNIT_PHAR="/tools/phpunit-${PHPUNIT_VERSION}.phar" \
  -e PHP_BINARY=php \
  "${PHP_IMAGE}" \
  php -d error_reporting=-1 -d display_errors=1 -d display_startup_errors=1 -d date.timezone=UTC \
  /work/tests/run-suite.php
