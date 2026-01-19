<?php
/**
 * The core plugin class.
 *
 * @package    WCNPL_Abilities_Demo
 * @subpackage WCNPL_Abilities_Demo/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 */
class WCNPL_Abilities_Demo {

	/**
	 * The loader that's responsible for maintaining and registering all hooks.
	 *
	 * @var WCNPL_Abilities_Demo_Loader
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @var string
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 */
	public function __construct() {
		$this->version     = WCNPL_ABILITIES_DEMO_VERSION;
		$this->plugin_name = 'wcnpl-abilities-demo';

		$this->load_dependencies();
		$this->define_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 */
	private function load_dependencies() {
		require_once WCNPL_ABILITIES_DEMO_PATH . 'includes/class-wcnpl-abilities-demo-loader.php';
		require_once WCNPL_ABILITIES_DEMO_PATH . 'includes/class-wcnpl-abilities-demo-abilities.php';

		$this->loader = new WCNPL_Abilities_Demo_Loader();
	}

	/**
	 * Register all of the hooks related to the functionality of the plugin.
	 */
	private function define_hooks() {
		$abilities = new WCNPL_Abilities_Demo_Abilities();

		$this->loader->add_action( 'init', $abilities, 'register_abilities' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return string The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return WCNPL_Abilities_Demo_Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return string The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
