#!/bin/bash

SSH_ACCOUNT="elpadi@thejackmag.com"
SERVER_PATH="/home2/elpadi/jack"

upload() {
	echo "Uploading $1"
	scp -r -P 2222 "$1" "$SSH_ACCOUNT:$SERVER_PATH/$1"
}

download() {
	echo "Downloading $1"
	scp -r -P 2222 "$SSH_ACCOUNT:$SERVER_PATH/$1" "$1"
}

if [[ -z "$1" ]]; then
	echo "First argument must be the path to transfer"
	exit 1;
fi

if [[ -n "$3" ]]; then
	echo "Second argument must be d or u"
	exit 1;
fi

if [[ "$1" == 'all' ]]; then
	for f in $(git status -s | grep '^ \(M\|A\)' | awk '{ print $2; }'); do
		upload "$f"
	done
else
	if [[ "$2" == 'u' ]]; then
		upload "$1"
	fi
	if [[ "$2" == 'd' ]]; then
		download "$1"
	fi
fi

exit 0
