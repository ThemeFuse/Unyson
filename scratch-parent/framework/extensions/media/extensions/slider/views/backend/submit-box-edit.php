<?php if (!defined('FW')) die('Forbidden'); ?>
<?php $options = array(
	'slider-type' =>
		array(
			'label' => 'Type',
			'desc' => false,
			'type' => 'html',
			'html' => '<img height="100" src="'.$slider_type['small']['src'].'"/>'
		),
	'population-method' => array(
		'label' => __('Population Method', 'fw'),
		'type' => 'html',
		'attr' => array('disabled' => 'disabled','class'=>'fw-no-border'),
		'html' => '<i>'.$population_method.'</i>',
	)
);?>

<div class="submitbox" id="submitpost">
	<?php
	$out = '';
	foreach($options as $key=>$option){
		$out.=fw()->backend->render_option($key, $option);
	}
	echo $out;
	?>
	<div id="major-publishing-actions">
		<div id="delete-action">
			<?php
			if (current_user_can("delete_post", $post->ID)) {
				if (!EMPTY_TRASH_DAYS)
					$delete_text = __('Delete Permanently', 'fw');
				else
					$delete_text = __('Move to Trash', 'fw');
				?>
				<a class="submitdelete deletion"
				   href="<?php echo get_delete_post_link($post->ID); ?>"><?php echo $delete_text; ?></a><?php
			} ?>
		</div>

		<div id="publishing-action">
			<span class="spinner"></span>
			<?php
			if (!in_array($post->post_status, array('publish', 'future', 'private')) || 0 == $post->ID) {
				if ($can_publish) :
					if (!empty($post->post_date_gmt) && time() < strtotime($post->post_date_gmt . ' +0000')) : ?>
						<input name="original_publish" type="hidden" id="original_publish"
						       value="<?php esc_attr_e('Schedule') ?>"/>
						<?php submit_button(__('Schedule', 'fw'), 'primary button-large', 'publish', false, array('accesskey' => 'p')); ?>
					<?php else : ?>
						<input name="original_publish" type="hidden" id="original_publish"
						       value="<?php esc_attr_e('Publish') ?>"/>
						<?php submit_button(__('Publish', 'fw'), 'primary button-large', 'publish', false, array('accesskey' => 'p')); ?>
					<?php    endif;
				else : ?>
					<input name="original_publish" type="hidden" id="original_publish"
					       value="<?php esc_attr_e('Submit for Review') ?>"/>
					<?php submit_button(__('Submit for Review', 'fw'), 'primary button-large', 'publish', false, array('accesskey' => 'p')); ?>
				<?php
				endif;
			} else {
				?>
				<input name="original_publish" type="hidden" id="original_publish"
				       value="<?php esc_attr_e('Update', 'fw') ?>"/>
				<input name="save" type="submit" class="button button-primary button-large" id="publish"
				       accesskey="p" value="<?php esc_attr_e('Save', 'fw') ?>"/>
			<?php
			} ?>
		</div>
		<div class="clear"></div>
	</div>
</div>