<?php if (!defined('FW')) die('Forbidden');
/**
 * Display extension in list on the extensions page
 * @var string $name
 * @var string $title
 * @var string $description
 * @var string $link
 * @var array $lists
 * @var array $nonces
 * @var string $default_thumbnail
 * @var bool $can_install
 * @var bool $is_active
 */

$ext       = fw_ext( $name );
$is_active = isset( $lists['active'][ $name ] ) ? true : false;
$version   = $ext ? $ext->manifest->get_version() : '';
$ext_page  = $ext ? $ext->_get_link() : '';
$url_set   = '';

if ( $ext && $ext->get_settings_options() ) {
	$url_set = "{$link}&sub-page=extension&extension={$name}";
} else {
	if ( ! empty( $lists['available'][ $name ]['download']['url_set'] ) ) {
		$url_set = admin_url( $lists['available'][ $name ]['download']['url_set'] );
	}
}

if (isset($lists['installed'][$name])) {
	$installed_data = &$lists['installed'][$name];
} else {
	$installed_data = false;
}

if (isset($lists['available'][$name])) {
	$available_data = &$lists['available'][$name];
} else {
	$available_data = false;
}

{
	if (isset($lists['available'][$name])) {
		$thumbnail = $lists['available'][$name]['thumbnail'];
	} else {
		$thumbnail = $default_thumbnail;
	}

	if ( isset( $lists['installed'][ $name ] ) ) {

		$manifest  = ! empty( $lists['installed'][ $name ]['thumbnail'] ) ? $lists['installed'][ $name ]['thumbnail'] : $lists['installed'][ $name ]['manifest'];
		$thumbnail = fw_akg( 'thumbnail', $manifest, $thumbnail );

		// local image
		if (
			substr( $thumbnail, 0, 11 ) !== 'data:image/'
			&&
			! filter_var( $thumbnail, FILTER_VALIDATE_URL )
			&&
			file_exists( $thumbnail_path = $lists['installed'][ $name ]['path'] . '/' . $thumbnail )
		) {
			$thumbnail = fw_get_path_url( $thumbnail_path );
		}
	}
}

$is_compatible =
	isset($lists['supported'][$name]) // is listed in the supported extensions list in theme manifest
	||
	($installed_data && ! empty( $installed_data['is']['theme'] ) ); // is located in the theme

$wrapper_class = 'fw-col-xs-12 fw-col-lg-6 fw-extensions-list-item';

if ($installed_data && !$is_active) {
	$wrapper_class .= ' disabled';
}

if (!$installed_data && !$is_compatible) {
	$wrapper_class .= ' not-compatible';
}
?>
<div class="<?php echo esc_attr($wrapper_class) ?>" id="fw-ext-<?php echo esc_attr($name) ?>">
	<a class="fw-ext-anchor" name="ext-<?php echo esc_attr($name) ?>"></a>
	<div class="inner">
		<div class="fw-extension-list-item-table">
			<div class="fw-extension-list-item-table-row">
				<div class="fw-extension-list-item-table-cell cell-1">
					<div class="fw-extensions-list-item-thumbnail-wrapper">
						<?php echo fw_string_to_icon_html($thumbnail, array('class' => 'fw-extensions-list-item-thumbnail')); ?>
					</div>
				</div>
				<div class="fw-extension-list-item-table-cell cell-2">

					<h3 class="fw-extensions-list-item-title"<?php echo( $is_active && $version ? ' title="v' . esc_attr( $version ) . '"' : '' ); ?>>
                        <?php
                            if ( $is_active && $ext_page ) {
                                echo fw_html_tag( 'a', array( 'href' => $ext_page ), $title );
                            } else {
                                echo $title;
                            }
					    ?>
                    </h3>

					<?php if ($description): ?>
						<p class="fw-extensions-list-item-desc"><?php echo esc_html($description); ?></p>
					<?php endif; ?>

					<?php
					if ( $installed_data ) {
						$_links = array();

						if ( $is_active && $url_set ) {
							$_links[] = '<a href="' . esc_url( $url_set ) . '">' . __( 'Settings', 'fw' ) . '</a>';
						}

						if ( $is_active && isset( $installed_data['path'] ) && file_exists( $installed_data['path'] . '/readme.md.php' ) ) {
							if ( isset($lists['supported'][$name]) ) {
								// no sense to teach how to install the extension if theme is already configured and the is extension marked as compatible
							} else {
								$_links[] = '<a href="' . esc_attr( $link ) . '&sub-page=extension&extension=' . esc_attr( $name ) . '&tab=docs">' . __( 'Install Instructions', 'fw' ) . '</a>';
							}
						}

						if ( ! empty( $_links ) ):
							?><p class="fw-extensions-list-item-links"><?php echo implode( ' <span class="fw-text-muted">|</span> ', $_links ); ?></p><?php
						endif;

						unset( $_links );
					}
					?>
					<?php if ($is_compatible): ?>
						<p><em><strong><span class="dashicons dashicons-yes"></span> <?php _e('Compatible', 'fw') ?></strong> <?php _e('with your current theme', 'fw') ?></em></p>
					<?php endif; ?>
				</div>
				<div class="fw-extension-list-item-table-cell cell-3">
					<?php if ($is_active): ?>
						<form action="<?php echo esc_attr($link) ?>&sub-page=deactivate&extension=<?php echo esc_attr( $name ) ?>" method="post">
							<?php wp_nonce_field($nonces['deactivate']['action'], $nonces['deactivate']['name']); ?>
							<input class="button" type="submit" value="<?php esc_attr_e('Deactivate', 'fw'); ?>"/>
						</form>
					<?php elseif ($installed_data): ?>
						<div class="fw-text-center">
							<form action="<?php echo esc_attr($link) ?>&sub-page=activate&extension=<?php echo esc_attr($name) ?>"
							      method="post"
							      class="extension-activate-form"
								>
								<?php wp_nonce_field($nonces['activate']['action'], $nonces['activate']['name']); ?>
								<input class="button" type="submit" value="<?php esc_attr_e('Activate', 'fw'); ?>"/>
							</form>
							<?php
							/**
							 * Do not show the "Delete extension" button if the extension is not in the available list.
							 * If you delete such extension you will not be able to install it back.
							 * Most often these will be extensions located in the theme.
							 */
							if ($can_install && $available_data):
							?>
							<form action="<?php echo esc_attr($link) ?>&sub-page=delete&extension=<?php echo esc_attr($name) ?>"
							      method="post"
							      class="fw-extension-ajax-form extension-delete-form"
							      data-confirm-message="<?php esc_attr_e('Are you sure you want to remove this extension?', 'fw') ?>"
							      data-extension-name="<?php echo esc_attr($name) ?>"
							      data-extension-action="uninstall"
								>
								<?php wp_nonce_field($nonces['delete']['action'], $nonces['delete']['name']); ?>
								<p class="fw-visible-xs-block"></p>
								<a href="#"
								   onclick="jQuery(this).closest('form').submit(); return false;"
								   data-remove-extension="<?php echo esc_attr($name) ?>"
								   title="<?php esc_attr_e('Remove', 'fw'); ?>"
									><span class="btn-text fw-visible-xs-inline"><?php _e('Remove', 'fw'); ?></span><span class="btn-icon unycon unycon-trash fw-hidden-xs"></span></a>
							</form>
							<?php endif; ?>
						</div>
					<?php elseif ($can_install && $available_data): ?>
						<form action="<?php echo esc_attr($link) ?>&sub-page=install&extension=<?php echo esc_attr($name) ?>"
						      method="post"
						      class="fw-extension-ajax-form"
						      data-extension-name="<?php echo esc_attr($name) ?>"
						      data-extension-action="install"
							>
							<?php wp_nonce_field($nonces['install']['action'], $nonces['install']['name']); ?>
							<input type="submit" class="button" value="<?php esc_attr_e('Download', 'fw') ?>">
						</form>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php if ($installed_data): ?>
			<?php if (!$is_active): ?>
				<?php if (!fw()->extensions->_get_db_active_extensions($name)): ?>
					<span><!-- Is not set as active in db --></span>
				<?php elseif ($installed_data['parent'] && !fw()->extensions->get($installed_data['parent'])): ?>
					<?php
					$parent_extension_name  = $installed_data['parent'];
					$parent_extension_title = fw_id_to_title($parent_extension_name);

					if (isset($lists['installed'][$parent_extension_name])) {
						$parent_extension_title = fw_akg('name', $lists['installed'][$parent_extension_name]['manifest'], $parent_extension_title);
					} elseif (isset($lists['available'][$parent_extension_name])) {
						$parent_extension_title = $lists['available'][$parent_extension_name]['name'];
					}
					?>
					<p class="fw-text-muted"><?php echo sprintf(__('Parent extension "%s" is disabled', 'fw'), $parent_extension_title); ?></p>
				<?php else: ?>
				<div class="fw-extension-disabled fw-border-box-sizing">
					<div class="fw-extension-disabled-panel fw-border-box-sizing">
						<div class="fw-row">
							<div class="fw-col-xs-12 fw-col-sm-3">
								<span class="fw-text-danger">!&nbsp;<?php _e('Disabled', 'fw'); ?></span>
							</div>
							<div class="fw-col-xs-12 fw-col-sm-9 fw-text-right">
							<?php

							$requirements = fw()
								->extensions
								->manager->collect_extension_requirements(
									$name
								);

							?>
							<a onclick="return false;" href="#" class="fw-extension-tip" title="<?php
								echo fw_htmlspecialchars(
									'<div class="fw-extension-tip-content">'.
									'<ul class="fw-extension-requirements"><li>- '. implode('</li><li>- ', $requirements) .'</li></ul>'.
									'</div>'
								);
								unset($requirements);
								?>"><?php _e('View Requirements', 'fw') ?></a>
								&nbsp; <p class="fw-visible-xs-block"></p><?php
									if ($can_install):
										?><a href="<?php echo esc_attr($link) ?>&sub-page=delete&extension=<?php echo esc_attr($name) ?>" class="button" ><?php _e('Remove', 'fw'); ?></a><?php
									endif;
								?>
							</div>
						</div>
					</div>
				</div>
				<?php endif; ?>
			<?php endif; ?>
		<?php elseif ($available_data): ?>
			<!-- -->
		<?php else: ?>
			<!-- -->
		<?php endif; ?>
	</div>
</div>
