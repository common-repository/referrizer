<?php


/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://referrizer.com
 * @since      1.0.0
 *
 * @package    referrizer
 * @subpackage referrizer/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    referrizer
 * @subpackage referrizer/includes
 * @author     Dejan Osmanovic <dejan@referrizer.com>
 */
class Referrizer {
	protected $options_key;
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Referrizer_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;
	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;
	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'REFERRIZER_VERSION' ) ) {
			$this->version = REFERRIZER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'referrizer';
		$this->options_key = 'referrizer';

		$this->load_dependencies();
		$this->register_widgets();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Helpers. Utility functions.
	 * - Referrizer_Loader. Orchestrates the hooks of the plugin.
	 * - Referrizer_Admin. Defines all hooks for the admin area.
	 * - Referrizer_Public. Defines all hooks for the public side of the site.
	 * - Referrizer_Reviews. Defines widget for displaying reviews.
	 * - Referrizer_Partner_Up. Defines widget for displaying partner up offers.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * Contains helper function for convenient usage.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/helpers.php';

		/**
		 * JWT library used to decode API token and retrieve md5h
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-jwt.php';

		/**
		 *
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-referrizer-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-referrizer-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-referrizer-public.php';

		/**
		 * Reviews widget
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-referrizer-reviews.php';

		/**
		 * Partner Up widget
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'widgets/class-referrizer-partner-up.php';

		$this->loader = new Referrizer_Loader();
	}

	/**
	 * Register widgets
	 *
	 * @since   1.0.0
	 * @access  private
	 */
	private function register_widgets() {
		$reviews_widget = new Referrizer_Reviews();
		$this->loader->add_action( 'widgets_init', $reviews_widget, 'register' );
		$this->loader->add_action( 'init', $reviews_widget, 'short_code' );
		$this->loader->add_action( 'wp_enqueue_scripts', $reviews_widget, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $reviews_widget, 'enqueue_scripts' );

		$partner_up_widget = new Referrizer_Partner_Up();
		$this->loader->add_action( 'widgets_init', $partner_up_widget, 'register' );
		$this->loader->add_action( 'init', $partner_up_widget, 'short_code' );
		$this->loader->add_action( 'wp_enqueue_scripts', $partner_up_widget, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $partner_up_widget, 'enqueue_scripts' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Referrizer_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'admin_init' );
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'invalid_api_token_notice' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Referrizer_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'voucher_popup_html', 5 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Referrizer_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}
}