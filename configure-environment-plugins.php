<?php
/**
 * Plugin Name: Configure Environment Plugins
 * Description:  Enables or disables plugins based on environment settings. Inspired by https://gist.github.com/Rarst/4402927, which was inspired by: https://gist.github.com/markjaquith/1044546
 * Author: Gary McPherson
 * Author URI: https://ingenyus.com
 * Plugin URI: https://github.com/Genyus/Configure-Environment-Plugins
 * License: GPL version 2+
 * Version: 1.0.8
 *
 * @package Configure-Environment-Plugins
 */

namespace Ingenyus\ConfigureEnvironmentPlugins;

require_once 'includes/class-pluginmanager.php';

$plugins_to_enable  = null;
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
