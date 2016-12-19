Running Unyson Unin Tests via PHPUnit.

Requirements:
1. Make sure you have installed PHPUnit and you have it available in your PATH as `phpunit`
2. Get [`install-wp-tests.sh`](https://github.com/wp-cli/wp-cli/blob/master/templates/install-wp-tests.sh) helper and put it somewhere in your PATH, name it `install-wp-tests`


# Steps to follow:

Run `install-wp-tests` helper. Please make sure that you have a mysql daemon
running. This script will do a couple of things:

- Will install a fresh `/tmp/wordpress` WordPress with correct `wp-config.php` for you
- Will copy WordPress test helpers to `/tmp/wordpress-tests-lib` - this should point the $WP_TESTS_DIR to. Unyson [knows](https://github.com/ThemeFuse/Unyson/blob/v2.6.10/tests/bootstrap.php#L20) how to handle it well
- Will create an empty database that will be re-created at each tests run


```bash
install-wp-tests <DB_NAME> <DB_USER> <DB_PASS> <DB_HOST - optionally, localhost by default>
```

In your `.bashrc` or `.zshrc` add this line:

```bash
export WP_TESTS_DIR=/tmp/wordpress-tests-lib/
```

Restart your terminal and check that it was indeed set correctly:

```bash
# should print /tmp/wordpress-tests-lib/
echo $WP_TESTS_DIR
```

Now you can run your tests. 

```
cd unyson/tests

# See them pass
phpunit
```

