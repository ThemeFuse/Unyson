<?php if (!defined('FW')) die('Forbidden');
/**
 * @var array $extension_titles
 * @var array $list_page_link
 */

$count = count($extension_titles);
?>

<p><?php echo _n(
	'You are about to install the following extension:',
	'You are about to install the following extensions:',
	$count,
	'fw'
) ?></p>

<ul class="ul-disc">
	<?php foreach ($extension_titles as $extension_title): ?>
		<li><strong><?php echo $extension_title; ?></strong></li>
	<?php endforeach; ?>
</ul>

<p><?php
	echo _n(
		'Are you sure you wish to install this extension?',
		'Are you sure you wish to install these extensions?',
		$count,
		'fw'
	)
?></p>

<input type="submit" name="submit" id="submit" class="button" value="<?php
	echo esc_attr( _n(
		'Yes, Install this extension',
		'Yes, Install these extensions',
		$count,
		'fw'
	) )
?>">

<a class="button" href="<?php echo esc_attr($list_page_link) ?>" ><?php _e('No, Return me to the extension list', 'fw') ?></a>
