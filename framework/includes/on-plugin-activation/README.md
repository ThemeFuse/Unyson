```php
// file: plugins/plugin-name/plugin-name.php

/** @internal */
function _action_fw_plugin_activate() {
	foreach ( glob( dirname( __FILE__ ) .'/framework/includes/on-plugin-activation/*.php' ) as $file ) {
		require_once $file;
	}
}
register_activation_hook( __FILE__, '_action_fw_plugin_activate' );
```