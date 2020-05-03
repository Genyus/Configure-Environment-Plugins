# Configure Environment Plugins
A WordPress MU plugin that allows plugins to be enabled or disabled based on current environment.

It's created for sites built on [Bedrock](https://roots.io/bedrock) or any other Composer-based framework. It has no GUI and is configured purely by defining environment details. Listed plugins will be disabled or enabled for the network in a multi-site instance.


Installation
==========

**Install using Composer**

```
composer require ingenyus/configure-environment-plugins
```

Usage
==========

Add the following constants to your `.env` file (or set in your hosting environment using alternative means). Multiple plugins can be specified with a comma-delimited string:

```
DISABLED_PLUGINS='akismet/akismet.php, hello-dolly/hello-dolly.php'
ENABLED_PLUGINS='disable-emails/disable-emails.php'
```

Hooks
==========

The plugin defines four actions you can hook into:

```PHP
// Called after local plugins have been disabled
add_action( 'environment_plugins_after_disabling_local_plugins', 'after_disabling_local_plugins' );

// Called after local plugins have been enabled
add_action( 'environment_plugins_after_enabling_local_plugins', 'after_enabling_local_plugins' );

// Called after network plugins have been disabled
add_action( 'environment_plugins_after_disabling_network_plugins', 'after_disabling_network_plugins' );

// Called after network plugins have been enabled
add_action( 'environment_plugins_after_enabling_network_plugins', 'after_enabling_network_plugins' );
```
