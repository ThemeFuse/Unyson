<?php defined( 'FW' ) or die(); ?>

<?php if ( $updates['framework'] !== false ): ?>
    <div id="fw-ext-update-framework">
        <a name="fw-framework"></a>
        <h3><?php _e( 'Framework', 'fw' ) ?></h3>
		<?php if ( empty( $updates['framework'] ) ): ?>
            <p><?php echo sprintf( __( 'You have the latest version of %s.', 'fw' ), fw()->manifest->get_name() ) ?></p>
		<?php else: ?>
			<?php if ( is_wp_error( $updates['framework'] ) ): ?>
                <p class="wp-ui-text-notification"><?php echo $updates['framework']->get_error_message() ?></p>
			<?php else: ?>
                <form id="fw-ext-update-framework" method="post" action="update-core.php?action=fw-update-framework">
                    <p>
                        <?php
                            _e( sprintf( 'You have version %s installed. Update to %s.',
                                fw()->manifest->get_version(),
                                $updates['framework']['fixed_latest_version']
                            ), 'fw' )
						?>
                    </p>
					<?php wp_nonce_field( - 1, '_nonce_fw_ext_update_framework' ); ?>
                    <p>
                        <input class="button" type="submit" value="<?php echo esc_attr( __( 'Update Framework', 'fw' ) ); ?>" name="update">
                    </p>
                </form>
			<?php endif; ?>
		<?php endif; ?>
    </div>
<?php endif; ?>

<?php if ( $updates['theme'] !== false ): ?>
    <div id="fw-ext-update-theme">
        <a name="fw-theme"></a>
        <h3><?php $theme = wp_get_theme();
			_e( sprintf( '%s Theme', ( is_child_theme() ? $theme->parent()->get( 'Name' ) : $theme->get( 'Name' ) ) ), 'fw' ) ?></h3>
		<?php if ( empty( $updates['theme'] ) ): ?>
            <p><?php _e( 'Your theme is up to date.', 'fw' ) ?></p>
		<?php else: ?>
			<?php if ( is_wp_error( $updates['theme'] ) ): ?>
                <p class="wp-ui-text-notification"><?php echo $updates['theme']->get_error_message() ?></p>
			<?php else: ?>
                <form id="fw-ext-update-theme" method="post" action="<?php echo esc_url( add_query_arg( 'action', 'fw-update-theme', $form_action ) ); ?>">
                    <p>
                        <?php
                            _e( sprintf( 'You have version %s installed. Update to %s.',
                                fw()->theme->manifest->get_version(),
                                $updates['theme']['fixed_latest_version']
                            ), 'fw' )
						?>
                    </p>
					<?php wp_nonce_field( - 1, '_nonce_fw_ext_update_theme' ); ?>
                    <p>
                        <input class="button" type="submit" value="<?php echo esc_attr( __( 'Update Theme', 'fw' ) ) ?>" name="update">
                    </p>
                </form>
			<?php endif; ?>
		<?php endif; ?>
    </div>
<?php endif; ?>

<div id="fw-ext-update-extensions">
    <a name="fw-extensions"></a>
    <h3><?php echo sprintf( __( '%s Extensions', 'fw' ), fw()->manifest->get_name() ); ?></h3>
	<?php if ( empty( $updates['extensions'] ) ): ?>
        <p><?php echo sprintf( __( 'You have the latest version of %s Extensions.', 'fw' ), fw()->manifest->get_name() ); ?></p>
	<?php else: ?>
		<?php
            $one_update_mode = fw()->extensions->get( 'update' )->ext_as_one_update();

            foreach ( $updates['extensions'] as $extension ) {
                if ( is_wp_error( $extension ) ) {
                    /**
                     * Cancel the "One update mode" and display all extensions list table with details
                     * if at least one extension has an error that needs to be visible
                     */
                    $one_update_mode = false;
                    break;
                }
            }
		?>
        <form id="fw-ext-update-extensions" method="post" action="<?php echo esc_url( add_query_arg( 'action', 'fw-update-extensions', $form_action ) ); ?>">
            <div class="fw-ext-update-extensions-form-detailed"<?php echo( $one_update_mode ? ' style="display: none;"' : '' ); ?>>
                <p>
                    <input class="button" type="submit" value="<?php echo esc_attr( __( 'Update Extensions', 'fw' ) ) ?>" name="update">
                </p>
				<?php
                    if ( ! class_exists( '_FW_Ext_Update_Extensions_List_Table' ) ) {
                        fw_include_file_isolated(
                            fw()->extensions->get( 'update' )->get_declared_path( '/includes/classes/class--fw-ext-update-extensions-list-table.php' )
                        );
                    }

                    $list_table = new _FW_Ext_Update_Extensions_List_Table( array( 'extensions' => $updates['extensions'] ) );
                    $list_table->display();
				?>
				<?php wp_nonce_field( - 1, '_nonce_fw_ext_update_extensions' ); ?>
                <p>
                    <input class="button" type="submit" value="<?php echo esc_attr( esc_html__( 'Update Extensions', 'fw' ) ); ?>" name="update">
                </p>
            </div>
			<?php if ( $one_update_mode ) : ?>
                <div class="fw-ext-update-extensions-form-simple">
                    <p style="color:#d54e21;"><?php _e( 'New extensions updates available.', 'fw' ); ?></p>
                    <p><input class="button" type="submit"
                              value="<?php echo esc_attr( __( 'Update Extensions', 'fw' ) ) ?>" name="update"></p>
                    <script type="text/javascript">
						jQuery( function ( $ ) {
							$( 'form#fw-ext-update-extensions' ).on( 'submit', function () {
								$( this ).find( '.check-column input[type="checkbox"]' ).prop( 'checked', true );
							} );
						} );
                    </script>
                </div>
			<?php endif; ?>
        </form>
	<?php endif; ?>
</div>
