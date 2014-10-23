<?php if (!defined('FW')) die('Forbidden'); ?>
<div class="submitbox" id="submitpost">
	<div class="misc-pub-section misc-pub-post-status">
		<p class="description">
			<?php _e('Note that the type and population can\'t be changed later. You\'ll need to create a new slider to have a different slider type or population method.','fw')?>
		</p>
	</div>

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
						<?php submit_button(__('Create', 'fw'), 'primary button-large', 'publish', false, array('accesskey' => 'p')); ?>
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
				       value="<?php esc_attr_e('Update') ?>"/>
				<input name="save" type="submit" class="button button-primary button-large" id="publish"
				       accesskey="p" value="<?php esc_attr_e('Update') ?>"/>
			<?php
			} ?>
		</div>
		<div class="clear"></div>
	</div>
</div>