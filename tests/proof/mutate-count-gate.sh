#!/usr/bin/env bash
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
MODULE_FILE="${REPO_ROOT}/ps_languageselector.php"
BACKUP="$(mktemp)"
cp "${MODULE_FILE}" "${BACKUP}"

restore() {
  cp "${BACKUP}" "${MODULE_FILE}"
  rm -f "${BACKUP}"
}
trap restore EXIT

sed -i 's/if (1 < count($languages))/if (0 < count($languages))/' "${MODULE_FILE}"

PHP_IMAGE="php@sha256:6ce95208609dc66df163ab936c970b3b34cd901b85c747102c5999f08ade9143"
DOCKER=(docker)
if ! docker info >/dev/null 2>&1; then
  DOCKER=(sudo docker)
fi

if [[ -z "${TOOLS_DIR:-}" || ! -f "${TOOLS_DIR}/phpunit-4.8.36.phar" ]]; then
  echo "TOOLS_DIR with verified PHPUnit PHAR must be set (run tests/run-suite.sh first)" >&2
  exit 1
fi

OUTPUT="$("${DOCKER[@]}" run --rm \
  -v "${REPO_ROOT}:/work" \
  -v "${TOOLS_DIR}:/tools:ro" \
  -w /work \
  -e SMARTY_DIR="/tools/smarty-3.1.48" \
  -e PHPUNIT_PHAR="/tools/phpunit-4.8.36.phar" \
  -e PHP_BINARY=php \
  "${PHP_IMAGE}" \
  php -d error_reporting=-1 -d display_errors=1 -d display_startup_errors=1 \
  /tools/phpunit-4.8.36.phar -c /work/phpunit.xml.dist --filter testFalseForOneLanguage 2>&1)" || true

if echo "${OUTPUT}" | grep -q 'Parse error:'; then
  echo "Unexpected parse error in proof run" >&2
  echo "${OUTPUT}"
  exit 1
fi

if ! echo "${OUTPUT}" | grep -Eq 'FAILURES!|Failed asserting'; then
  echo "Expected testFalseForOneLanguage to fail after mutation" >&2
  echo "${OUTPUT}"
  exit 1
fi

echo "Mutation proof passed"
