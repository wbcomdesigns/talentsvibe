<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    Talentsvibe_Core
 * @subpackage Talentsvibe_Core/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Talentsvibe_Core
 * @subpackage Talentsvibe_Core/public
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class Talentsvibe_Core_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Talentsvibe_Core_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Talentsvibe_Core_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/talentsvibe-core-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Talentsvibe_Core_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Talentsvibe_Core_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/talentsvibe-core-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	* Wbcom Designs - Reorder the business tab about, home tab	 		 
	*/
	public function wbcom_reorder_business_menu_items( $items, $endpoints ) {
		// Define the desired order
		$desired_order = array(
			'about',      // About
			'home',       // Home
			'reviews',    // Reviews
			'follower',   // Followers
			'inbox',      // Inbox
			'settings',   // Settings
		);
	
		// Create a new array to hold the reordered items
		$reordered_items = array();
	
		// Loop through the desired order and add items to the new array if they exist
		foreach ( $desired_order as $key ) {
			if ( array_key_exists( $key, $items ) ) {
				$reordered_items[$key] = $items[$key];
				unset( $items[$key] ); // Remove the item from the original array
			}
		}
	
		// Merge any remaining items that were not in the desired order
		return array_merge( $reordered_items, $items );
	}

	/**
	* Wbcom Designs - Make the default tab as about tab	 		 
	*/
	public function wbcom_set_about_as_default_tab( $default_tab ) {
		// Set 'about' as the default tab
		return 'about';
	}

}
