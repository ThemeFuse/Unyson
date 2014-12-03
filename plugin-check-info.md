# Plugin Check Info

```text
WARNING: Found PHP short tags in file unyson/framework/core/components/extensions/manager/includes/parsedown/Parsedown.php.
Line 856: if (preg_match('/^\[(.+?)\]:[ ]*<?(\S+?)>?(?:[ ]+['\'(](.+)['\')])?[ ]*$/', $Line['text'], $matches))
```

It's a plugin-check bug, there is a string containing `<?` not a real php short tag.

----

```text
WARNING: Found ->exec in the file unyson/framework/extensions/backup/includes/lib/srdb.class.php. PHP system calls are often disabled by server admins and should not be in themes.
Line 585: return $this->db->exec( $query );
```

```text
REQUIRED: get_theme() found in the file unyson/framework/extensions/backup/includes/entity/class-fw-backup-info.php. Deprecated since version 3.4. Use wp_get_theme() instead.
Line 37: public function get_theme() { return $this->theme; }
```

```text
REQUIRED: get_settings() found in the file unyson/framework/extensions/portfolio/class-fw-extension-portfolio.php. Deprecated since version 2.1. Use get_option() instead.
Line 456: public function get_settings() {
```

```text
REQUIRED: get_link() found in the file unyson/framework/core/components/extensions/manager/class--fw-extensions-manager.php. Deprecated since version 2.1. Use get_bookmark() instead.
Line 189: $link = $this->get_link();
```

It's a plugin-check bug, that is a class method not a global function.

----

```text
WARNING: readfile was found in the file ... File operations should use the WP_Filesystem methods instead of direct PHP filesystem calls.
WARNING: fopen was found in the file ... File operations should use the WP_Filesystem methods instead of direct PHP filesystem calls.
WARNING: fread was found in the file ... File operations should use the WP_Filesystem methods instead of direct PHP filesystem calls.
WARNING: fwrite was found in the file ... File operations should use the WP_Filesystem methods instead of direct PHP filesystem calls.
WARNING: file_put_contents was found in the file ... File operations should use the WP_Filesystem methods instead of direct PHP filesystem calls.
```

We use `WP_Filesystem` when we need to change plugin files:

* Download and extract a new extension
* Delete an extension
* Update an extension

Usually `WP_Filesystem` prints a form for the user to enter the ftp credentials.

But there are cases when we don't know how to use `WP_Filesystem`:

* How to write files within the `wp-content/uploads` directory?

    All files in that directory must be owned by the user which runs the php (usually it is `www-data`).
    We tried to make `WP_Filesystem` work with `wp-content/uploads` without asking user ftp credentials 
    and without hacks (forcing it to prevent asking ftp credentials), but without success.
    If you know how to do that, please let us know.

* How to write to a file in background *(without asking ftp credentials)* after it was checked if it's writable and the user set explicitly chmod `777`?

    This case is used in the SEO extension: we update the `sitemap.xml` file, only when it exists and is writable. 

* How automatic backup schedule must work *(read files and save the zip to `wp-content/uploads`)* in background *(without asking ftp credentials)*?
