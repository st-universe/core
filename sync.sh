#!/bin/sh

UPSTREAM=${1:-'@{u}'}
LOCAL=$(git rev-parse @)
REMOTE=$(git rev-parse "$UPSTREAM")
BASE=$(git merge-base @ "$UPSTREAM")

send_mail () {
   recipient=$( jq '.game.admin.email' config.json )
   echo "sending failure email to ${recipient}"
   sendmail $recipient < syncFailure.mail
}

if [ $LOCAL = $REMOTE ]; then
    :
    #echo "git: up-to-date"
else
    echo "git: need to pull"

    git reset --hard HEAD && git pull --rebase
    if [ $? -eq 0 ]; then
  	echo "Success: pulled from git"
    else
        echo "Failure: Could not pull from git. Script failed" >&2
        send_mail
        exit 1
    fi

    make init-production
    if [ $? -eq 0 ]; then
        echo "Success: make init-production"
    else
        echo "Failure: make init-production. Script failed" >&2
        send_mail
        exit 1
    fi

    make clearCache
    if [ $? -eq 0 ]; then
        echo "Success: make clearCache"
    else
        echo "Failure: make clearCache. Script failed" >&2
        send_mail
        exit 1
    fi

    make migrateDatabase
    if [ $? -eq 0 ]; then
        echo "Success: make migrateDatabase"
    else
        echo "Failure: make migrateDatabase. Script failed" >&2
        send_mail
        exit 1
    fi

    jq '.game.version += 1' config/config.json | sponge config/config.json

    exit 0
fi
