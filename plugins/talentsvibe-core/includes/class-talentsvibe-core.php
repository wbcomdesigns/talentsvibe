<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    Talentsvibe_Core
 * @subpackage Talentsvibe_Core/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Talentsvibe_Core
 * @subpackage Talentsvibe_Core/includes
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class Talentsvibe_Core {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Talentsvibe_Core_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;
	
	/**
	 * The class managing GamiPress integration.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Talentsvibe_Core_Gamipress    $gamipress    The GamiPress integration.
	 */
	protected $gamipress;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'TALENTSVIBE_CORE_VERSION' ) ) {
			$this->version = TALENTSVIBE_CORE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'talentsvibe-core';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_gamipress_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Talentsvibe_Core_Loader. Orchestrates the hooks of the plugin.
	 * - Talentsvibe_Core_i18n. Defines internationalization functionality.
	 * - Talentsvibe_Core_Admin. Defines all hooks for the admin area.
	 * - Talentsvibe_Core_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-talentsvibe-core-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-talentsvibe-core-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-talentsvibe-core-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-talentsvibe-core-public.php';
		
		/**
		 * The class responsible for GamiPress integration
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-talentsvibe-core-gamipress.php';

		$this->loader = new Talentsvibe_Core_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Talentsvibe_Core_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Talentsvibe_Core_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Talentsvibe_Core_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Talentsvibe_Core_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_filter( 'bp_business_profile_single_menu_items', $plugin_public, 'wbcom_reorder_business_menu_items', 10, 2 );
		$this->loader->add_filter( 'bp_business_profil_default_tab', $plugin_public, 'wbcom_set_about_as_default_tab' );		

	}
	
	
	/**
	 * Register all of the hooks related to GamiPress integration
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_gamipress_hooks() {
		// Only initialize if both plugins are active
		if ( ! class_exists( 'BP_Verified_Member' ) || ! function_exists( 'gamipress' ) ) {
			return;
		}

		$this->gamipress = new Talentsvibe_Core_Gamipress( $this->get_plugin_name(), $this->get_version() );

		// Settings
		$this->loader->add_action( 'admin_init', $this->gamipress, 'register_settings' );
		
		// Handle verification status changes
		$this->loader->add_action( 'bp_verified_member_verified_status_updated', $this->gamipress, 'handle_verification_status', 10, 2 );
		
		// Add custom triggers
		$this->loader->add_filter( 'gamipress_activity_triggers', $this->gamipress, 'add_gamipress_triggers' );
		$this->loader->add_filter( 'gamipress_specific_activity_trigger_label', $this->gamipress, 'add_trigger_label', 10, 2 );
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
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Talentsvibe_Core_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
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

}
