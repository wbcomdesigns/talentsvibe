<?php
/**
 * @package BuddyBoss Child
 * The parent theme functions are located at /buddyboss-theme/inc/theme/functions.php
 * Add your own functions at the bottom of this file.
 */


/****************************** THEME SETUP ******************************/

/**
 * Sets up theme for translation
 *
 * @since BuddyBoss Child 1.0.0
 */
function buddyboss_theme_child_languages()
{
  /**
   * Makes child theme available for translation.
   * Translations can be added into the /languages/ directory.
   */

  // Translate text from the PARENT theme.
  load_theme_textdomain( 'buddyboss-theme', get_stylesheet_directory() . '/languages' );

  // Translate text from the CHILD theme only.
  // Change 'buddyboss-theme' instances in all child theme files to 'buddyboss-theme-child'.
  // load_theme_textdomain( 'buddyboss-theme-child', get_stylesheet_directory() . '/languages' );

}
add_action( 'after_setup_theme', 'buddyboss_theme_child_languages' );

/**
 * Enqueues scripts and styles for child theme front-end.
 *
 * @since Boss Child Theme  1.0.0
 */
function buddyboss_theme_child_scripts_styles()
{
  /**
   * Scripts and Styles loaded by the parent theme can be unloaded if needed
   * using wp_deregister_script or wp_deregister_style.
   *
   * See the WordPress Codex for more information about those functions:
   * http://codex.wordpress.org/Function_Reference/wp_deregister_script
   * http://codex.wordpress.org/Function_Reference/wp_deregister_style
   **/

  // Styles
  wp_enqueue_style( 'buddyboss-child-css', get_stylesheet_directory_uri().'/assets/css/custom.css' );

  // Javascript
  wp_enqueue_script( 'buddyboss-child-js', get_stylesheet_directory_uri().'/assets/js/custom.js' );
}
add_action( 'wp_enqueue_scripts', 'buddyboss_theme_child_scripts_styles', 9999 );


/****************************** CUSTOM FUNCTIONS ******************************/

// Add your own custom functions here

// ACF Form
function wbcom_portfolio_post_set_acf_header() {
    // acf_enqueue_scripts();
    acf_form_head();
}
add_action( 'wp_head', 'wbcom_portfolio_post_set_acf_header', 10 );

// Register a shortcode to display an ACF front-end form for post submission
function wbcom_portfolio_post_submission_form_shortcode() {
    if( is_user_logged_in() ){
        // Check if ACF function exists (to ensure ACF Pro is activated)
        if (function_exists('acf_form')) {
            
            // Define the form arguments
            $form_args = array(
                'id'                => 'acf-form',
                'post_id'           => 'new_post', // For new post submission
                'new_post'          => array(
                    'post_type'     => 'portfolio',   // Set post type to 'post' (you can change it)
                    'post_status'   => 'publish'   // Set the post status (draft, publish, etc.)
                ),
                'post_title'        => true,      // Enable post title input
                'post_content'      => false,      // Enable post title input
                'field_groups'      => array('group_67af1ffa42aa2'), // Replace with ACF field keys
                'submit_value'      => 'Submit', // Submit button text
                'return'            => home_url('/activity'), // Redirect to a page after submission
            );
            
            // Display the form
            ob_start();
            acf_form($form_args);
            return ob_get_clean();
        } else {
            return 'ACF Pro is not activated.';
        }
    } else {
        return 'You are currently logged out.';
    }
}
add_shortcode('wbcom_portfolio_submission_form', 'wbcom_portfolio_post_submission_form_shortcode');



function wbcom_add_upload_files_capability_to_all_roles() {
    // Get all roles
    $roles = wp_roles()->roles;

    // Loop through each role
    foreach ( $roles as $role_name => $role_info ) {
        $role = get_role( $role_name );

        // Check if the role does not already have the capability
        if ( ! $role->has_cap( 'upload_files' ) ) {
            // Add the capability to upload files
            $role->add_cap( 'upload_files' );
        }
    }
}
// Hook into 'admin_init' to ensure it runs in the admin area
add_action('admin_init', 'wbcom_add_upload_files_capability_to_all_roles');




?>