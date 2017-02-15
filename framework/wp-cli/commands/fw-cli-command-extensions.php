<?php

if (!defined('ABSPATH')) {
    die();
}

class FW_CLI_Command_Extensions extends FW_CLI_Command {

	/**
	 * Activate extension.
	 *
	 * ## OPTIONS
	 *
	 * <extension>
	 * : Extension name.
	 *
	 * ## EXAMPLES
	 *
	 *	# Activate `seo` extension.
	 *	$ wp unyson extensions activate seo
	 */
	public function activate($params, $args) {
		$extension = (isset($params[0])) ? $params[0] : false;
		if ( $extension ) {
			$deactivated_extensions = $this->get_deactivated_extensions();
			if (isset($deactivated_extensions[$extension])) {
				$run = fw()->extensions->manager->activate_extensions(array(
					$extension => array(), true
				));

				$this->wp_error($run);

				WP_CLI::success("Extension {$extension} is active now");
			} else {
				WP_CLI::error("Extension {$extension} is not found in list of deactivated extensions.");
			}
		} else {
			WP_CLI::error("Extension doesn't exists");
		}
	}

	protected function deactivate_extension($extension_name) {
		return fw()->extensions->manager->deactivate_extensions(array(
			$extension_name => array(), true
		));
	}

	/**
	 * Deactivate extension
	 *
	 * ## OPTIONS
	 *
	 * [<extension>]
	 * : Extension name.
	 *
	 * [--all]
	 * : If set, all extensions will be deactivated.
	 *
	 * ## EXAMPLES
	 *
	 *	# Deactivate `seo` extension.
	 *	$ wp unyson extensions deactivate seo
	 */
	public function deactivate($params, $args) {
		$list = array();
		$deactivate_all = (isset($args['all'])) ? true : false;
		$extension = (isset($params[0])) ? $params[0] : false;

		$active_extenions = $this->get_active_extensions();
		if( $deactivate_all ) {
			$list = array_keys($active_extenions);
		} else {
			if($extension) {
				$list[] = $extension;
			}
		}

		if (count($list)) {
			foreach($list as $extension_name) {
				if (isset($active_extenions[$extension_name])) {
					$run = $this->deactivate_extension($extension_name);
					$this->wp_error($run);

					WP_CLI::success("Extension {$extension_name} is deactivated.");
				} else {
					WP_CLI::error("Extension {$extension_name} is not found in list of active extensions.");
				}
			}
		} else {
			WP_CLI::error("Extension doesn't exists");
		}
	}

	/**
	 * Uninstall extension.
	 *
	 * ## OPTIONS
	 *
	 * <extension>
	 * : Extension name.
	 *
	 * ## EXAMPLES
	 *
	 *	# First of all we must to deactivate extension.
	 *	$ wp unyson extensions deactivate page-builder
	 *
	 *	# Uninstall `page-builder` extension.
	 *	$ wp unyson extensions uninstall page-builder
	 */
	public function uninstall($params, $args) {
		$extension = (isset($params[0])) ? $params[0] : false;
		if ( $extension ) {
			$deactivated_extensions = $this->get_deactivated_extensions();
			if (isset($deactivated_extensions[$extension])) {
				$this->require_filesystem();
				$run = fw()->extensions->manager->uninstall_extensions(array(
					$extension => array()
				), array(
					'cancel_on_error' => true
				));

				$this->wp_error($run);

				WP_CLI::success("Extension {$extension} is removed.");
			} else {
				WP_CLI::error("Extension {$extension} is not found in list of deactivated extensions.");
			}
		} else {
			WP_CLI::error("Extension doesn't exists");
		}
	}

	/**
	 * Install extension.
	 *
	 * ## OPTIONS
	 *
	 * <extension>
	 * : Extension name.
	 *
	 * ## EXAMPLES
	 *
	 *	# Install `page-builder` extension.
	 *	$ wp unyson extensions install page-builder
	 */
	public function install($params, $args) {
		$extension = (isset($params[0])) ? $params[0] : false;
		if ( $extension ) {
			$avaible_extensions = $this->get_avaible_extensions();
			if ( isset($avaible_extensions[$extension]) ) {
				$this->require_filesystem();

				$run = fw()->extensions->manager->install_extensions(array(
					$extension => array()
				), array(
					'cancel_on_error' => true
				));

				$this->wp_error($run);

				WP_CLI::success("Extension {$extension} successfully installed.");
			} else {
				WP_CLI::error("Extension {$extension} is not found in list of avaible extensions.");
			}
		} else {
			WP_CLI::error("Extension doesn't exists");
		}
	}

	/**
	 * List of extensions.
	 *
	 * ## OPTIONS
	 *
	 * [<status>]
	 * : Extension status: active, deactivated, avaible.
	 *
	 * ## EXAMPLES
	 *
	 *	# List of all extensions.
	 *	$ wp unyson extensions list
	 *
	 *	# Display only active extensions
	 *	$ wp unyson extensions list 'active'
	 *
	 *	# Display only avaible to install.
	 *	$ wp unyson extensions list 'avaible'
	 *
	 * @subcommand list
	 */
	public function _list($params, $args) {
		$only = (isset($params[0])) ? $params[0] : 'all';

		$formater_header = array('#', 'slug', 'name');
		$active_extensions = $this->get_active_extensions();
		$deactivated_extensions = $this->get_deactivated_extensions();
		$avaible_extensions = $this->get_avaible_extensions();

		// Active extensions.
		$active_step = 1;
		$display_active = array();

		foreach( $active_extensions as $key => $data ) {
			$display_active[] = array(
				'#' => $active_step,
				'slug' => $key,
				'name' => fw_akg('manifest/name', $data, $key),
			);

			$active_step += 1;
		}

		// Deactivated extensions.
		$deactivated_step = 1;
		$display_deactivated = array();

		foreach( $deactivated_extensions as $key => $data ) {
			$display_deactivated[] = array(
				'#' => $deactivated_step,
				'slug' => $key,
				'name' => fw_akg('manifest/name', $data, $key),
			);

			$deactivated_step += 1;
		}

		// Avaible extensions.
		$avaible_step = 1;
		$display_avaible = array();

		foreach( $avaible_extensions as $key => $data ) {
			$display_avaible[] = array(
				'#' => $avaible_step,
				'slug' => $key,
				'name' => fw_akg('name', $data, $key),
			);

			$avaible_step += 1;
		}

		// Display active extenions.
		if( 'active' === $only || 'all' === $only ) {
			WP_CLI::line();
			WP_CLI::line('Active Extensions: ' . count($display_active));
			if (count($display_active)) {
				$this->display_formatter($formater_header, $display_active);
			}
		}

		// Display deactivated extensions.
		if( 'deactivated' === $only || 'all' === $only ) {
			WP_CLI::line();
			WP_CLI::line('Deactive Extensions: ' . count($display_deactivated));
			if (count($display_deactivated)) {
				$this->display_formatter($formater_header, $display_deactivated);
			}
		}

		// Display avaible extensions.
		if( 'avaible' === $only || 'all' === $only ) {
			WP_CLI::line();
			WP_CLI::line('Avaible Extensions: ' . count($display_avaible));
			if (count($display_avaible)) {
				$this->display_formatter($formater_header, $display_avaible);
			}
		}

		WP_CLI::line();
	}

	protected function display_formatter($formater_header, $items) {
		$formatter = new \WP_CLI\Formatter(
			$this->args,
			$formater_header
		);

		$formatter->display_items( $items );
	}

	protected function get_active_extensions() {
		$result = array();
		$extensions = fw()->extensions->manager->get_installed_extensions();

		foreach ( $extensions as $key => $data ) {
			$active = fw_akg('active', $data);
			$display = fw_akg('manifest/display', $data, 0);
			if ( $active ) {
				if( 1 == $display ) {
					$result[$key] = $data;
				}
			}
		}

		return $result;
	}

	protected function get_deactivated_extensions() {
		$result = array();

		$extensions = fw()->extensions->manager->get_installed_extensions();
		foreach ( $extensions as $key => $data ) {
			$active = fw_akg('active', $data);
			$display = fw_akg('manifest/display', $data, 0);
			if ( !$active ) {
				if( 1 == $display ) {
					$result[$key] = $data;
				}
			}
		}

		return $result;
	}

	protected function get_avaible_extensions() {
		$result = array();

		$installed = array_merge(
			$this->get_deactivated_extensions(),
			$this->get_active_extensions()
		);

		$avaible = fw()->extensions->manager->get_available_extensions();
		foreach( $avaible as $key => $data ) {
			$display = fw_akg('display', $data, 0);
			if( 1 == $display && !array_key_exists($key, $installed) ) {
				$result[$key] = $data;
			}
		}

		return $result;
	}

}

WP_CLI::add_command( 'unyson extensions', 'FW_CLI_Command_Extensions' );