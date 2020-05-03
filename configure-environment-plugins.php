<?php
/*
* Plugin Name: Configure Environment Plugins
* Description:  Enables or disables plugins based on environment settings. Inspired by https://gist.github.com/Rarst/4402927, which was inspired by: https://gist.github.com/markjaquith/1044546
* Author: Gary McPherson
* Author URI: https://ingenyus.com
* Plugin URI: https://github.com/Genyus/Configure-Environment-Plugins
* License: GPL version 2+
* Version: 1.0.3
*/

namespace Ingenyus\ConfigureEnvironmentPlugins;

class PluginManager {
    static $instance;
    protected $disabled_plugins = [];
    protected $enabled_plugins = [];

    /**
     * Sets up the options filter, and optionally handles an array of plugins to disable
     * @param array $to_disable Optional array of plugin filenames to disable
     */
    public function __construct( Array $to_enable = NULL, Array $to_disable = NULL ) {

        if (isset(self::$instance)) {
            return;
        }
        
        /**
         * Allow other plugins to access this instance
         */
        self::$instance = $this;


        if ( ! $this->is_mu_plugin() ) {
            if( is_admin() ) {
                add_action('admin_notices', array( $this, 'admin_notice_not_mu_plugin' ) );
            }
            
            return;
        }

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

        /**
         * Add the filters
         */
        add_filter( 'option_active_plugins', [ $this, 'configure_local_plugins' ] );
        add_filter( 'site_option_active_sitewide_plugins', [ $this, 'configure_network_plugins' ] );
    }

    /**
     * Adds a filename to the list of plugins to enable
     */
    public function enable( $file ) {
        $this->enabled_plugins[] = $file;
    }

    /**
     * Adds a filename to the list of plugins to disable
     */
    public function disable( $file ) {
        $this->disabled_plugins[] = $file;
    }

    /**
     * Hooks in to the option_active_plugins filter and does the toggling
     * @param array $plugins WP-provided list of plugin filenames
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
     * @param array $plugins
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
    function admin_notice_not_mu_plugin() {
        printf( '<div class="error"><p>Configure Environment Plugins must be installed as must-use plugin.</p></div>' );
    }

    /**
     * Checks the plugin is installed as a must-use plugin.
     */
    protected function is_mu_plugin() {
        return substr_compare( dirname( __FILE__ ), WPMU_PLUGIN_DIR, 0, strlen( WPMU_PLUGIN_DIR ) ) === 0;
    }

    protected function disable_local_plugins( $plugins ) {
        if ( count( $this->disabled_plugins ) ) {

            $configured_plugins = [];

            foreach ( (array)$this->disabled_plugins as $plugin ) {
                $key = array_search( $plugin, $plugins );

                if ( false !== $key ) {
                    unset( $plugins[ $key ] );
                    $configured_plugins[] = $plugin;
                }
            }
        }

        do_action( 'environment_plugins_after_disabling_local_plugins', $configured_plugins );

        return $plugins;
    }

    protected function enable_local_plugins( $plugins ) {
        if ( count( $this->enabled_plugins ) ) {

            $configured_plugins = [];

            foreach ( (array)$this->enabled_plugins as $plugin ) {

                if( !in_array( $plugin, $plugins ) ){
                    $plugins[] = $plugin;
                    $configured_plugins[] = $plugin;
                }
            }
        }

        do_action( 'environment_plugins_after_enabling_local_plugins', $configured_plugins );

        return $plugins;
    }

    protected function disable_network_plugins( $plugins ) {
        if ( count( $this->disabled_plugins ) ) {

            $configured_plugins = [];
            
            foreach ( (array)$this->disabled_plugins as $plugin ) {

                if ( isset( $plugins[ $plugin ] ) ) {
                    unset( $plugins[ $plugin ] );
                    $configured_plugins[] = $plugin;
                }
            }
        }

        do_action( 'environment_plugins_after_disabling_network_plugins', $configured_plugins );

        return $plugins;
    }

    protected function enable_network_plugins( $plugins ) {
        if ( count( $this->enabled_plugins ) ) {

            $configured_plugins = [];

            foreach ( (array)$this->enabled_plugins as $plugin ) {

                if( !in_array( $plugin, $plugins ) ){
                    $plugins[] = $plugin;
                    $configured_plugins[] = $plugin;
                }
            }
        }

        do_action( 'environment_plugins_after_enabling_network_plugins', $configured_plugins );

        return $plugins;
    }
}

$plugins_to_enable = null;
$plugins_to_disable = null;

if ( defined( 'ENABLED_PLUGINS' ) ) {
    $plugins_to_enable = ENABLED_PLUGINS;
}
    
if ( defined( 'DISABLED_PLUGINS' ) ) {
    $plugins_to_disable = DISABLED_PLUGINS;
}
    
if ( ( ! empty( $plugins_to_enable ) && is_array( $plugins_to_enable ) ) || ( ! empty( $plugins_to_disable ) && is_array( $plugins_to_disable ) ) ) {
    $plugin_manager = new PluginManager( $plugins_to_enable, $plugins_to_disable );
}