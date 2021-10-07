<?php
/**
 * The core functionality of the plugin.
 *
 * @link  https://ingenyus.com
 * @since 1.0.0
 *
 * @package Configure-Environment-Plugins
 */

namespace Ingenyus\ConfigureEnvironmentPlugins;

/**
 * Plugin manager class.
 *
 * Enables and disables plugins as specified.
 */
class PluginManager {
	/**
	 * The static instance of this class
	 *
	 * @var $instance
	 */
	public static $instance;

	/**
	 * An array of disabled plugins.
	 *
	 * @var $disabled_plugins
	 */
	protected $disabled_plugins = [];

	/**
	 * An array of enabled plugins
	 *
	 * @var $enabled_plugins
	 */
	protected $enabled_plugins = [];

	/**
	 * Sets up the options filter, and optionally handles an array of plugins to enable and disable
	 *
	 * @param array $to_enable Optional array of plugin filenames to enable.
	 * @param array $to_disable Optional array of plugin filenames to disable.
	 */
	public function __construct( array $to_enable = null, array $to_disable = null ) {

		if ( isset( self::$instance ) ) {
			return;
		}

		/**
		 * Allow other plugins to access this instance
		 */
		self::$instance = $this;

		if ( ! $this->is_mu_plugin() ) {
			if ( is_admin() ) {
				add_action( 'admin_notices', [ $this, 'admin_notice_not_mu_plugin' ] );
			}

			return;
		}

		if ( ! $to_enable ) {
			$to_enable = array();
		}

		if ( ! $to_disable ) {
			$to_disable = array();
		}

		$this->process_plugins( $to_enable, $to_disable );

		/**
		 * Add the filters
		 */
		add_filter( 'option_active_plugins', [ $this, 'configure_local_plugins' ] );
		add_filter( 'site_option_active_sitewide_plugins', [ $this, 'configure_network_plugins' ] );
	}

	/**
	 * Adds a filename to the array of plugins to enable
	 *
	 * @param array $file The filename of a plugin to enable.
	 */
	public function enable( $file ) {
		$this->enabled_plugins[] = $file;
	}

	/**
	 * Adds a filename to the array of plugins to disable
	 *
	 * @param array $file The filename of a plugin to disable.
	 */
	public function disable( $file ) {
		$this->disabled_plugins[] = $file;
	}

	/**
	 * Hooks in to the option_active_plugins filter and does the toggling
	 *
	 * @param  array $plugins An array of plugin filenames.
	 * @return array The filtered array of plugin filenames
	 */
	public function configure_local_plugins( $plugins ) {
		$plugins = $this->enable_local_plugins( $plugins );
		$plugins = $this->disable_local_plugins( $plugins );

		return $plugins;
	}

	/**
	 * Hooks in to the site_option_active_sitewide_plugins filter and does the disabling
	 *
	 * @param array $plugins An array of plugins.
	 *
	 * @return array
	 */
	public function configure_network_plugins( $plugins ) {
		$plugins = $this->enable_network_plugins( $plugins );
		$plugins = $this->disable_network_plugins( $plugins );

		return $plugins;
	}

	/**
	 * Print an admin notice when not installed as a must-use plugin.
	 *
	 * This has to be a named function for compatibility with PHP 5.2.
	 */
	public function admin_notice_not_mu_plugin() {
		printf( '<div class="error"><p>Configure Environment Plugins must be installed as must-use plugin.</p></div>' );
	}

	/**
	 * Processes an array of plugins to enable and disable
	 *
	 * @param array $to_enable Optional array of plugin filenames to enable.
	 * @param array $to_disable Optional array of plugin filenames to disable.
	 */
	protected function process_plugins( array $to_enable, array $to_disable ) {
		/**
		 * Handle what was passed in
		 */
		if ( is_array( $to_enable ) ) {
			foreach ( $to_enable as $plugin ) {
				$this->enable( $plugin );
			}
		}

		if ( is_array( $to_disable ) ) {
			foreach ( $to_disable as $plugin ) {
				$this->disable( $plugin );
			}
		}
	}

	/**
	 * Checks the plugin is installed as a must-use plugin.
	 */
	protected function is_mu_plugin() {
		return substr_compare( dirname( __FILE__ ), WPMU_PLUGIN_DIR, 0, strlen( WPMU_PLUGIN_DIR ) ) === 0;
	}

	/**
	 * Disables local plugins
	 *
	 * @param array $plugins An array of plugins to disable.
	 */
	protected function disable_local_plugins( $plugins ) {
		if ( count( (array) $this->disabled_plugins ) ) {

			$configured_plugins = [];

			foreach ( (array) $this->disabled_plugins as $plugin ) {
				$key = array_search( $plugin, $plugins, true );

				if ( false !== $key ) {
					unset( $plugins[ $key ] );
					$configured_plugins[] = $plugin;
				}
			}
		}

		do_action( 'environment_plugins_after_disabling_local_plugins', $configured_plugins );

		return $plugins;
	}

	/**
	 * Enables local plugins
	 *
	 * @param array $plugins An array of plugins to enable.
	 */
	protected function enable_local_plugins( $plugins ) {
		if ( count( (array) $this->enabled_plugins ) ) {

			$configured_plugins = [];

			foreach ( (array) $this->enabled_plugins as $plugin ) {

				if ( ! in_array( $plugin, $plugins, true ) ) {
					$plugins[]            = $plugin;
					$configured_plugins[] = $plugin;
				}
			}
		}

		do_action( 'environment_plugins_after_enabling_local_plugins', $configured_plugins );

		return $plugins;
	}

	/**
	 * Disables network plugins
	 *
	 * @param array $plugins An array of plugins to disable.
	 */
	protected function disable_network_plugins( $plugins ) {
		if ( count( (array) $this->disabled_plugins ) ) {

			$configured_plugins = [];

			foreach ( (array) $this->disabled_plugins as $plugin ) {

				if ( isset( $plugins[ $plugin ] ) ) {
					unset( $plugins[ $plugin ] );
					$configured_plugins[] = $plugin;
				}
			}
		}

		do_action( 'environment_plugins_after_disabling_network_plugins', $configured_plugins );

		return $plugins;
	}

	/**
	 * Enables network plugins
	 *
	 * @param array $plugins An array of plugins to enable.
	 */
	protected function enable_network_plugins( $plugins ) {
		if ( count( (array) $this->enabled_plugins ) ) {

			$configured_plugins = [];

			foreach ( (array) $this->enabled_plugins as $plugin ) {

				if ( ! array_key_exists( $plugin, $plugins ) ) {
					$plugins[ $plugin ]   = time();
					$configured_plugins[] = $plugin;
				}
			}
		}

		do_action( 'environment_plugins_after_enabling_network_plugins', $configured_plugins );

		return $plugins;
	}
}
