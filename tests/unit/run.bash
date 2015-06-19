#!/bin/bash
# requirements: subversion, phpunit

command -v svn >/dev/null 2>&1 || { echo >&2 "subversion is not installed. Aborting."; exit 1; }
command -v phpunit >/dev/null 2>&1 || { echo >&2 "phpunit is not installed. Aborting."; exit 1; }

DB_HOST='localhost'
DB_USER='root'
DB_PASS='root'
DB_NAME='unyson_tests'
WP_VERSION='latest'

CWD=$(pwd)
TMP_DIR='/tmp/unyson-test'

export WP_TESTS_DIR="$TMP_DIR/tests"

if test "`find $TMP_DIR -mmin +30`"; then
	echo "Removing old tmp dir"
	rm -r "$TMP_DIR"
fi

if [ ! -d "$TMP_DIR" ]; then

	mkdir -p "$TMP_DIR"
	cd "$TMP_DIR"
	wget https://raw.github.com/wp-cli/sample-plugin/master/bin/install-wp-tests.sh -O "$TMP_DIR/install-wp-tests.sh"
	bash "$TMP_DIR/install-wp-tests.sh" $DB_NAME $DB_USER $DB_PASS $DB_HOST $WP_VERSION

fi

cd "$WP_TESTS_DIR"

phpunit -c "$CWD/" --exclude-group invalid
