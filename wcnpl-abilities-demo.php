<?php
/**
 * Plugin Name: WCNPL Abilities Demo
 * Plugin URI: https://github.com/abhishekrijal/wcnpl-abilities-demo
 * Description: A demo plugin showcasing WordPress 6.9 Abilities API with a simple forms system.
 * Version: 1.0.0
 * Requires at least: 6.9
 * Requires PHP: 7.4
 * Author: Abhishek Rijal
 * Author URI: https://github.com/abhishekrijal
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wcnpl-abilities-demo
 * Domain Path: /languages
 *
 * @package WCNPL_Abilities_Demo
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'WCNPL_ABILITIES_DEMO_VERSION', '1.0.0' );

/**
 * Plugin directory path.
 */
define( 'WCNPL_ABILITIES_DEMO_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Plugin directory URL.
 */
define( 'WCNPL_ABILITIES_DEMO_URL', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function wcnpl_abilities_demo_activate() {
	require_once WCNPL_ABILITIES_DEMO_PATH . 'includes/class-wcnpl-abilities-demo-activator.php';
	WCNPL_Abilities_Demo_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function wcnpl_abilities_demo_deactivate() {
	require_once WCNPL_ABILITIES_DEMO_PATH . 'includes/class-wcnpl-abilities-demo-deactivator.php';
	WCNPL_Abilities_Demo_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'wcnpl_abilities_demo_activate' );
register_deactivation_hook( __FILE__, 'wcnpl_abilities_demo_deactivate' );

/**
 * The core plugin class.
 */
require_once WCNPL_ABILITIES_DEMO_PATH . 'includes/class-wcnpl-abilities-demo.php';

/**
 * Begins execution of the plugin.
 */
function wcnpl_abilities_demo_run() {
	$plugin = new WCNPL_Abilities_Demo();
	$plugin->run();
}
wcnpl_abilities_demo_run();
