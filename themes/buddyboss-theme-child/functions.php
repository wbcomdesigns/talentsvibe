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

/**
 * Post Views Counter - GamiPress Integration for Portfolio
 * 
 * This integration awards points to portfolio authors based on view milestones
 * Default: Only works with 'portfolio' post type
 * Use filter 'pvc_gamipress_supported_post_types' to add more post types
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get supported post types for GamiPress integration
 * Default: Only portfolio
 */
function pvc_gamipress_get_supported_post_types() {
    return apply_filters( 'pvc_gamipress_supported_post_types', array( 'portfolio' ) );
}

/**
 * Award points when portfolio reaches certain view milestones
 */
add_action( 'pvc_after_count_visit', 'pvc_gamipress_award_points_on_views', 10, 2 );
function pvc_gamipress_award_points_on_views( $post_id, $content_type ) {
    // Only process post content type (not term, user, etc.)
    if ( $content_type !== 'post' ) {
        return;
    }
    
    // Check if GamiPress is active
    if ( ! function_exists( 'gamipress_award_points' ) ) {
        return;
    }
    
    // Get the post
    $post = get_post( $post_id );
    if ( ! $post ) {
        return;
    }
    
    // Check if this post type is supported
    $supported_post_types = pvc_gamipress_get_supported_post_types();
    if ( ! in_array( $post->post_type, $supported_post_types, true ) ) {
        return;
    }
    
    // Get post author
    $author_id = $post->post_author;
    
    // Get current post views
    $current_views = pvc_get_post_views( $post_id );
    
    // Define view milestones and points to award for portfolio
    $milestones = apply_filters( 'pvc_gamipress_portfolio_milestones', array(
        50    => 15,   // 15 points for 50 views
        100   => 30,   // 30 points for 100 views
        250   => 50,   // 50 points for 250 views
        500   => 100,  // 100 points for 500 views
        1000  => 200,  // 200 points for 1000 views
        2500  => 350,  // 350 points for 2500 views
        5000  => 500,  // 500 points for 5000 views
    ) );
    
    // Check if we've hit a milestone
    foreach ( $milestones as $milestone => $points ) {
        // Check if we just reached this milestone
        if ( $current_views == $milestone ) {
            // Check if already awarded (prevent double awards)
            $already_awarded = get_post_meta( $post_id, '_pvc_milestone_' . $milestone . '_awarded', true );
            
            if ( ! $already_awarded ) {
                // Award points to the portfolio author
                gamipress_award_points( $author_id, $points, 'points', array(
                    'reason' => sprintf( __( 'Portfolio item "%s" reached %d views', 'textdomain' ), $post->post_title, $milestone ),
                    'log_type' => 'portfolio_views_milestone',
                    'post_id' => $post_id
                ) );
                
                // Mark as awarded
                update_post_meta( $post_id, '_pvc_milestone_' . $milestone . '_awarded', true );
                
                // Trigger custom action
                do_action( 'pvc_gamipress_portfolio_milestone_reached', $author_id, $post_id, $milestone, $points );
                
                // Log this achievement
                if ( function_exists( 'gamipress_trigger_event' ) ) {
                    gamipress_trigger_event( array(
                        'event' => 'pvc_portfolio_views_milestone',
                        'user_id' => $author_id,
                        'post_id' => $post_id,
                        'milestone' => $milestone,
                        'points_awarded' => $points
                    ) );
                }
                
                break; // Only award for one milestone at a time
            }
        }
    }
}

/**
 * Award 1 point for every view on portfolio items
 */
add_action( 'pvc_after_count_visit', 'pvc_gamipress_award_points_per_view', 10, 2 );
function pvc_gamipress_award_points_per_view( $post_id, $content_type ) {
    // Only process post content type
    if ( $content_type !== 'post' ) {
        return;
    }
    
    // Check if GamiPress is active
    if ( ! function_exists( 'gamipress_award_points' ) ) {
        return;
    }
    
    // Get the post
    $post = get_post( $post_id );
    if ( ! $post ) {
        return;
    }
    
    // Check if this post type is supported
    $supported_post_types = pvc_gamipress_get_supported_post_types();
    if ( ! in_array( $post->post_type, $supported_post_types, true ) ) {
        return;
    }
    
    // Get post author
    $author_id = $post->post_author;
    
    // Award 1 point per view
    $points_per_view = apply_filters( 'pvc_gamipress_points_per_view', 1 );
    
    // Award points to the author
    gamipress_award_points( $author_id, $points_per_view, 'points', array(
        'reason' => sprintf( __( 'Portfolio item "%s" received a view', 'textdomain' ), $post->post_title ),
        'log_type' => 'portfolio_view',
        'post_id' => $post_id
    ) );
}

/**
 * Register GamiPress achievement triggers for Portfolio Views
 */
add_filter( 'gamipress_activity_triggers', 'pvc_gamipress_register_portfolio_triggers' );
function pvc_gamipress_register_portfolio_triggers( $triggers ) {
    $triggers['Portfolio Views'] = array(
        'pvc_portfolio_views_milestone' => __( 'Portfolio reaches a view milestone', 'textdomain' ),
        'pvc_portfolio_gets_views' => __( 'Portfolio gets a specific number of views', 'textdomain' ),
        'pvc_portfolio_total_views' => __( 'User\'s portfolios reach total views', 'textdomain' ),
    );
    
    return $triggers;
}

/**
 * Get portfolio view count for achievements
 */
add_filter( 'gamipress_get_user_trigger_count', 'pvc_gamipress_get_portfolio_trigger_count', 10, 4 );
function pvc_gamipress_get_portfolio_trigger_count( $count, $user_id, $trigger, $args ) {
    if ( $trigger === 'pvc_portfolio_gets_views' || $trigger === 'pvc_portfolio_total_views' ) {
        // Get all portfolio items by user
        $portfolios = get_posts( array(
            'author' => $user_id,
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'post_type' => 'portfolio',
            'fields' => 'ids'
        ) );
        
        $total_views = 0;
        foreach ( $portfolios as $portfolio_id ) {
            $total_views += (int) pvc_get_post_views( $portfolio_id );
        }
        
        return $total_views;
    }
    
    return $count;
}

/**
 * Shortcode to display user's portfolio views
 * Usage: [pvc_portfolio_views] or [pvc_portfolio_views user_id="123"]
 */
add_shortcode( 'pvc_portfolio_views', 'pvc_gamipress_portfolio_views_shortcode' );
function pvc_gamipress_portfolio_views_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'user_id' => get_current_user_id(),
    ), $atts );
    
    if ( ! $atts['user_id'] ) {
        return '';
    }
    
    // Get all portfolio items by user
    $portfolios = get_posts( array(
        'author' => $atts['user_id'],
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'post_type' => 'portfolio',
        'fields' => 'ids'
    ) );
    
    $total_views = 0;
    $portfolio_count = count( $portfolios );
    
    foreach ( $portfolios as $portfolio_id ) {
        $total_views += (int) pvc_get_post_views( $portfolio_id );
    }
    
    return sprintf( 
        __( 'Portfolio Views: %s across %d items', 'textdomain' ), 
        number_format_i18n( $total_views ),
        $portfolio_count
    );
}

/**
 * Widget to display top portfolio authors
 */
class PVC_GamiPress_Portfolio_Authors_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'pvc_gamipress_portfolio_authors',
            __( 'Top Portfolio Authors', 'textdomain' ),
            array( 'description' => __( 'Display authors with most portfolio views', 'textdomain' ) )
        );
    }
    
    public function widget( $args, $instance ) {
        echo $args['before_widget'];
        
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }
        
        // Get authors with portfolio items
        $authors = get_users( array(
            'who' => 'authors',
            'number' => -1
        ) );
        
        $author_views = array();
        
        foreach ( $authors as $author ) {
            $portfolios = get_posts( array(
                'author' => $author->ID,
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'post_type' => 'portfolio',
                'fields' => 'ids'
            ) );
            
            if ( ! empty( $portfolios ) ) {
                $total_views = 0;
                foreach ( $portfolios as $portfolio_id ) {
                    $total_views += (int) pvc_get_post_views( $portfolio_id );
                }
                
                if ( $total_views > 0 ) {
                    $author_views[] = array(
                        'author' => $author,
                        'views' => $total_views,
                        'count' => count( $portfolios )
                    );
                }
            }
        }
        
        // Sort by views
        usort( $author_views, function( $a, $b ) {
            return $b['views'] - $a['views'];
        } );
        
        // Display top authors
        if ( ! empty( $author_views ) ) {
            echo '<ol class="pvc-top-portfolio-authors">';
            $limit = isset( $instance['number'] ) ? $instance['number'] : 5;
            foreach ( array_slice( $author_views, 0, $limit ) as $data ) {
                printf(
                    '<li><a href="%s">%s</a> - %s views (%d items)</li>',
                    add_query_arg( 'post_type', 'portfolio', get_author_posts_url( $data['author']->ID ) ),
                    esc_html( $data['author']->display_name ),
                    number_format_i18n( $data['views'] ),
                    $data['count']
                );
            }
            echo '</ol>';
        } else {
            echo '<p>' . __( 'No portfolio authors found.', 'textdomain' ) . '</p>';
        }
        
        echo $args['after_widget'];
    }
    
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Top Portfolio Authors', 'textdomain' );
        $number = ! empty( $instance['number'] ) ? $instance['number'] : 5;
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php _e( 'Number of authors:' ); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" step="1" min="1" max="10" value="<?php echo esc_attr( $number ); ?>">
        </p>
        <?php
    }
    
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
        $instance['number'] = ( ! empty( $new_instance['number'] ) ) ? absint( $new_instance['number'] ) : 5;
        return $instance;
    }
}

// Register widget
add_action( 'widgets_init', function() {
    register_widget( 'PVC_GamiPress_Portfolio_Authors_Widget' );
} );

/**
 * Add milestone progress column to portfolio admin
 */
add_filter( 'manage_portfolio_posts_columns', 'pvc_gamipress_add_portfolio_milestone_column' );
function pvc_gamipress_add_portfolio_milestone_column( $columns ) {
    $columns['pvc_milestone_progress'] = __( 'Milestone Progress', 'textdomain' );
    return $columns;
}

add_action( 'manage_portfolio_posts_custom_column', 'pvc_gamipress_portfolio_milestone_column_content', 10, 2 );
function pvc_gamipress_portfolio_milestone_column_content( $column, $post_id ) {
    if ( $column === 'pvc_milestone_progress' ) {
        $views = pvc_get_post_views( $post_id );
        $milestones = apply_filters( 'pvc_gamipress_portfolio_milestones', array() );
        
        $next_milestone = 0;
        $last_milestone = 0;
        $reached_milestones = 0;
        
        foreach ( $milestones as $milestone => $points ) {
            if ( $views >= $milestone ) {
                $last_milestone = $milestone;
                $reached_milestones++;
            } else {
                $next_milestone = $milestone;
                break;
            }
        }
        
        if ( $next_milestone > 0 ) {
            $progress = $views - $last_milestone;
            $needed = $next_milestone - $last_milestone;
            $percentage = round( ( $progress / $needed ) * 100 );
            
            echo '<div style="margin-bottom: 5px;">';
            echo '<strong>' . sprintf( __( '%s views', 'textdomain' ), number_format_i18n( $views ) ) . '</strong>';
            echo '</div>';
            
            echo '<div style="background: #f0f0f0; height: 20px; border-radius: 3px; overflow: hidden; margin-bottom: 5px;">';
            echo '<div style="background: #0073aa; height: 100%; width: ' . $percentage . '%; transition: width 0.3s;"></div>';
            echo '</div>';
            
            echo '<small>';
            echo sprintf( __( 'Next: %s views (+%s points)', 'textdomain' ), 
                number_format_i18n( $next_milestone ), 
                $milestones[$next_milestone] 
            );
            echo '</small>';
        } else {
            echo '<span style="color: #46b450;">✓ ' . sprintf( __( 'All %d milestones reached!', 'textdomain' ), $reached_milestones ) . '</span>';
        }
    }
}

/**
 * Helper function to get portfolio author's total views
 */
function pvc_gamipress_get_portfolio_author_views( $user_id ) {
    $portfolios = get_posts( array(
        'author' => $user_id,
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'post_type' => 'portfolio',
        'fields' => 'ids'
    ) );
    
    $total_views = 0;
    foreach ( $portfolios as $portfolio_id ) {
        $total_views += (int) pvc_get_post_views( $portfolio_id );
    }
    
    return $total_views;
}

/**
 * Example: How to extend to other post types
 * Uncomment and modify as needed
 */
/*
add_filter( 'pvc_gamipress_supported_post_types', function( $post_types ) {
    // Add more post types
    $post_types[] = 'product';     // WooCommerce products
    $post_types[] = 'download';    // Easy Digital Downloads
    return $post_types;
} );
*/

?>
