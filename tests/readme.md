# Running Unyson Unit Tests via PHPUnit

#### Requirements:

1. Make sure you have installed PHPUnit `sudo apt install phpunit`
2. Get [`install-wp-tests.sh`](https://github.com/wp-cli/scaffold-command/blob/v1.0.4/templates/install-wp-tests.sh) helper

## Steps to follow:

Run `install-wp-tests` helper. Please make sure that you have a mysql daemon
running. This script will do a couple of things:

- Will install a fresh WordPress to the `/tmp/wordpress` directory with correct `wp-config.php`
- Will copy WordPress test helpers to `/tmp/wordpress-tests-lib` - this should point the $WP_TESTS_DIR to. Unyson [knows](https://github.com/ThemeFuse/Unyson/blob/v2.6.10/tests/bootstrap.php#L20) how to handle it well
- Will create an empty database that will be re-created at each tests run

Run this:

```bash
install-wp-tests <DB_NAME> <DB_USER> <DB_PASS> <DB_HOST - optionally, localhost by default>
```

Now you can run your tests:

```
cd unyson/tests

# See them pass
env WP_TESTS_DIR=/tmp/wordpress-tests-lib/ phpunit
```
