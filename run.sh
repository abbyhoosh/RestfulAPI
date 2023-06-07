#!/bin/bash
URL=$(echo "$REPL_SLUG.$REPL_OWNER.repl.co" | tr '[:upper:]' '[:lower:]')
echo '----------------------------------------------------------------'
echo "Code Server: $URL"
echo "App Server: $URL/proxy/5000"
echo '----------------------------------------------------------------'
./bin/cake server --host localhost --port 5000 >/dev/null & code-server --bind-addr 0.0.0.0:8080 >/dev/null