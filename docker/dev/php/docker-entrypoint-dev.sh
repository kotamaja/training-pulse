#!/usr/bin/env sh
set -e

cat <<'EOF'

============================================================
 PHP development container ready
============================================================

Composer helper commands are available in this container.

Run:

  composer-help

to list the available Composer security and maintenance commands.

============================================================

EOF

exec "$@"
