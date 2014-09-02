<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * HTML <meta> tag view
 *
 * View supports 2 parameters: $name, $content
 *
 * @var $name , meta tag name attribute value
 * @var $content , meta tag name attribute value
 */

?>
<meta name="<?php echo $name ?>" content="<?php echo $content ?>"/>