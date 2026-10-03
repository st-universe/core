#!/bin/sh

cd "$(dirname "$0")" || exit 1
exec 9>"$(git rev-parse --git-path stu-sync.lock)"
flock -n 9 || exit 0

UPSTREAM=${1:-'@{u}'}
LOCAL=$(git rev-parse @) || exit 1
REMOTE=$(git rev-parse "$UPSTREAM") || exit 1

send_mail () {
   recipient=$( jq -r '.game.admin.email' config/config.json )
   echo "sending failure email to ${recipient}"
   sendmail "$recipient" < syncFailure.mail
}

if [ "$LOCAL" != "$REMOTE" ]; then
    echo "git: need to pull"

    git reset --hard HEAD && git pull --rebase
    if [ $? -eq 0 ]; then
  	echo "Success: pulled from git"
    else
        echo "Failure: Could not pull from git. Script failed" >&2
        send_mail
        exit 1
    fi

    mkdir -p var/extensions || exit 1
    touch var/extensions/.pending-core-install || exit 1
fi

if [ -f var/extensions/.pending-core-install ]; then
    make init-production
    if [ $? -eq 0 ]; then
        echo "Success: make init-production"
    else
        echo "Failure: make init-production. Script failed" >&2
        send_mail
        exit 1
    fi

    touch var/extensions/.pending-deploy || exit 1
    rm var/extensions/.pending-core-install
fi

php bin/deploy-extensions.php
EXTENSION_RESULT=$?
if [ "$EXTENSION_RESULT" -eq 10 ]; then
    mkdir -p var/extensions || exit 1
    touch var/extensions/.pending-deploy || exit 1
elif [ "$EXTENSION_RESULT" -ne 0 ]; then
    echo "Warning: optional module deployment failed" >&2
fi

if [ -f var/extensions/.pending-deploy ]; then
    if ! make clearCache || ! make migrateDatabase || ! php bin/publish-extension-assets.php; then
        echo "Failure: deployment finalization failed; retrying on next sync" >&2
        send_mail
        exit 1
    fi

    if ! php bin/deploy-extensions.php --restart; then
        echo "Failure: module restart failed; retrying on next sync" >&2
        exit 1
    fi
    jq '.game.version += 1' config/config.json | sponge config/config.json
    rm var/extensions/.pending-deploy
fi
