# Titles and Meta

A sub-extension of SEO extension, used to setup the theme SEO titles and meta keywords for search engines.

## Configuration

```php
// Custom posts types that you want to exclude from titles and meta settings
$cfg['excluded_post_types'] = array('attachment');

// Custom taxonomies that you want to exclude from titles and meta settings.
$cfg['excluded_taxonomies'] = array('post_tag');
```

## Views

* `meta.php` - Template to render the meta keywords and description.

## Hooks


* `fw_ext_seo_titles_metas_load_metas` - Filter that allows you to modify some meta properties before it will be rendered in front-end.

	```php
	/**
	 * @internal
	 * @param array $data All metas that needs to be rendered on the current page
	 * @param array $location Current page location details
	 */
	function _filter_modify_seo_meta($data, $location) {
        /**
         * The view to display current meta.
         * If the view key is not set, then will be loaded meta.php.
         */
        $data['view'] = 'my-view';

        return $data;
    }
    add_filter('fw_ext_seo_titles_metas_load_metas', '_filter_modify_seo_meta');
	```

* `fw_ext_seo_titles_metas_load_title` - Filter that allows you to make some modifications in page title before it will be rendered.

	```php
	/**
	 * @internal
	 * @param string $title The current title
	 * @param string $separator Separator symbol
	 * @param string $sepdirection Separator position
	 * @param array $location Current page location details
	 */
	function _filter_modify_seo_title($title, $separator, $sepdirection, $location) {
	    // ...

	    return $title;
	}
	add_filter('fw_ext_seo_titles_metas_load_title', '_filter_modify_seo_title');
	```
