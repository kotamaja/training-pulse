#!/usr/bin/env sh
set -e

cat <<'EOF'

============================================================
 Node / Angular development container
============================================================

npm helper commands are available.

Run inside the container:

  npm-help

Examples:

  npm-ci-dev
  npm-audit
  npm-security-check
  npm-outdated
  npm-update-safe @angular/core @angular/cli

Angular CLI is available through:

  ng

============================================================

EOF

exec "$@"
