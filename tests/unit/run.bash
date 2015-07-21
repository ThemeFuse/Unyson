#!/bin/bash
# Run the tests on your local machine
#
# Usage:
# 1. Create a new database with the same name as the DB_NAME variable below
# 2. Make sure that the user from the variable below DB_USER has access to that database
# 3. Execute in console this script $ ./run.bash

# requirements: subversion, phpunit
command -v svn >/dev/null 2>&1 || { echo >&2 "subversion is not installed. Aborting."; exit 1; }
command -v phpunit >/dev/null 2>&1 || { echo >&2 "phpunit is not installed. Aborting."; exit 1; }

DB_HOST='localhost'
DB_USER='root'
DB_PASS='root'
DB_NAME='unyson_tests'
WP_VERSION='latest'

TMP_DIR='/tmp/unyson-test'
WP_TESTS_DIR="$TMP_DIR/tests"

# http://stackoverflow.com/a/246128
SOURCE="${BASH_SOURCE[0]}"
while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink
  DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"
  SOURCE="$(readlink "$SOURCE")"
  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
SCRIPT_DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

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

phpunit -c "$SCRIPT_DIR/" --exclude-group invalid
