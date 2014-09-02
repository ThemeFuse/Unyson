# Sitemap

Generates `sitemap.xml` file for search engines.

## Configuration

```php
/**
 * Search engines where to report about the sitemap existence.
 * By default the extension supports only Google and Bing.
 */
$cfg['search_engines'] = array('google', 'bing');

/**
 * The frequently of the sitemap refresh (measured in days).
 */
$cfg['sitemap_refresh_rate'] = 2;

/**
 * Exclude post types from sitemap indexing.
 */
$cfg['excluded_post_types'] = array('attachment');

/**
 * Exclude taxonomies from sitemap indexing.
 */
$cfg['excluded_taxonomies']  = array('post_tag');

/**
 * Setup the URL frequency and priority for each post_type, taxonomy and the homepage
 */
$cfg['url_settings'] = array(
	'home' => array(
        'priority'  => 1,
        'frequency' => 'daily',
    ),
    'posts' => array(
        'priority' => 0.6,
        'frequency' => 'daily',
        /**
         * In case you have specific posts type that you want to set different settings
         */
        'type' => array(
            'page' => array(
                'priority'  => 0.5,
                'frequency' => 'weekly',
            )
        )
    ),
    'taxonomies' => array(
        'priority'  => 0.4,
        'frequency' => 'weekly',
        /**
         * In case you have specific taxonomy that you want to set different settings
         */
        'type' => array(
            'post_tag'  => array(
                'priority'  => 0.3,
                'frequency' => 'weekly',
            )
        )
    )
);
```

## Views

There are 3 views you can customize:

* `sitemap-header.php` - Header content for the `sitemap.xml` file.
* `sitemap.php` - Content for the `sitemap.xml` file. You can edit this file in case you want to exclude some items from sitemap.
* `sitemap-style.php` - Gives sitemap a user friendly view when it's accessed in the browser.

## Hooks

* `fw_ext_seo_sitemap_date_format` - Filter to change the date format of the last modified date in sitemap.

	```php
	/**
	 * @internal
	 */
	function _filter_modify_sitemap_date_format( $format ) {
        return 'Y M, d';
    }
    add_filter('fw_ext_seo_sitemap_date_format', '_filter_modify_sitemap_date_format');
	```

* `fw_ext_seo_sitemap_pre_update` - Action fired when the sitemap prepares to be updated.

	```php
	/**
	 * @internal
	 */
    function _action_pre_update_sitemap() {
    	// ...
    }
    add_action('fw_ext_seo_sitemap_pre_update', '_action_pre_update_sitemap');
    ```

* `fw_ext_seo_sitemap_updated` - Action fired after the sitemap was updated.

	```php
	/**
	 * @internal
	 */
    function _action_sitemap_updated() {
    	// ...
    }
    add_action('fw_ext_seo_sitemap_updated', '_action_sitemap_updated');
    ```

* `fw_ext_seo_sitemap_pre_delete` - Action fired when the sitemap prepares to be deleted.

	```php
	/**
	 * @internal
	 */
    function _action_pre_delete_sitemap() {
    	// ...
    }
    add_action('fw_ext_seo_sitemap_pre_delete', '_action_pre_delete_sitemap');
    ```
    
* `fw_ext_seo_sitemap_deleted` - Action fired after the sitemap was deleted.

	```php
	/**
	 * @internal
	 */
    function _action_sitemap_deleted() {
    	// ...
    }
    add_action('fw_ext_seo_sitemap_deleted', '_action_sitemap_deleted');
    ```