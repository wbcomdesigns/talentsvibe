<?php
/**
 * Plugin Name: Portfolio Likes for GamiPress
 * Plugin URI: https://wbcomdesigns.com/
 * Description: Adds a like system for portfolio items with GamiPress achievement integration
 * Version: 1.0.0
 * Author: Wbcom Designs
 * Author URI: https://wbcomdesigns.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: portfolio-likes
 * Domain Path: /languages
 * 
 * @package Portfolio_Likes_GamiPress
 * @author Wbcom Designs
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main Portfolio Likes Class
 * 
 * @since 1.0.0
 */
class Wbcom_Portfolio_Likes_GamiPress {
    
    /**
     * Instance
     * 
     * @var Wbcom_Portfolio_Likes_GamiPress
     */
    private static $instance = null;
    
    /**
     * Get instance
     * 
     * @return Wbcom_Portfolio_Likes_GamiPress
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->wbcom_plg_define_constants();
        $this->wbcom_plg_includes();
        $this->wbcom_plg_init_hooks();
    }
    
    /**
     * Define plugin constants
     */
    private function wbcom_plg_define_constants() {
        define( 'WBCOM_PLG_VERSION', '1.0.0' );
        define( 'WBCOM_PLG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
        define( 'WBCOM_PLG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
        define( 'WBCOM_PLG_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
    }
    
    /**
     * Include required files
     */
    private function wbcom_plg_includes() {
        // Include additional files if needed
    }
    
    /**
     * Initialize hooks
     */
    private function wbcom_plg_init_hooks() {
        // Activation/Deactivation
        register_activation_hook( __FILE__, array( $this, 'wbcom_plg_activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'wbcom_plg_deactivate' ) );
        
        // Init
        add_action( 'init', array( $this, 'wbcom_plg_init' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'wbcom_plg_enqueue_scripts' ) );
        
        // AJAX handlers
        add_action( 'wp_ajax_wbcom_portfolio_like', array( $this, 'wbcom_plg_handle_like' ) );
        add_action( 'wp_ajax_nopriv_wbcom_portfolio_like', array( $this, 'wbcom_plg_handle_like' ) );
        
        // Display
        add_filter( 'the_content', array( $this, 'wbcom_plg_add_like_button' ), 20 );
        add_action( 'wbcom_portfolio_like_button', array( $this, 'wbcom_plg_display_like_button' ) );
        
        // GamiPress integration
        add_filter( 'gamipress_activity_triggers', array( $this, 'wbcom_plg_register_gamipress_triggers' ) );
        add_filter( 'gamipress_specific_activity_trigger_label', array( $this, 'wbcom_plg_gamipress_trigger_label' ), 10, 3 );
        add_filter( 'gamipress_specific_activity_triggers', array( $this, 'wbcom_plg_gamipress_specific_triggers' ), 10, 2 );
        add_action( 'wbcom_portfolio_likes_milestone_reached', array( $this, 'wbcom_plg_trigger_gamipress_achievement' ), 10, 4 );
        
        // Admin columns
        add_filter( 'manage_portfolio_posts_columns', array( $this, 'wbcom_plg_add_likes_column' ) );
        add_action( 'manage_portfolio_posts_custom_column', array( $this, 'wbcom_plg_likes_column_content' ), 10, 2 );
        add_filter( 'manage_edit-portfolio_sortable_columns', array( $this, 'wbcom_plg_likes_sortable_column' ) );
        add_action( 'pre_get_posts', array( $this, 'wbcom_plg_likes_orderby' ) );
    }
    
    /**
     * Plugin activation
     */
    public function wbcom_plg_activate() {
        // Create database table
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'wbcom_portfolio_likes';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            post_id bigint(20) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            ip_address varchar(100) DEFAULT NULL,
            date_time datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY post_id (post_id),
            KEY user_id (user_id),
            UNIQUE KEY unique_like (post_id, user_id, ip_address)
        ) $charset_collate;";
        
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        
        // Add version option
        add_option( 'wbcom_portfolio_likes_version', WBCOM_PLG_VERSION );
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function wbcom_plg_deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Initialize plugin
     */
    public function wbcom_plg_init() {
        // Load text domain
        load_plugin_textdomain( 'portfolio-likes', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function wbcom_plg_enqueue_scripts() {
        if ( ! is_singular( 'portfolio' ) ) {
            return;
        }
        
        // Enqueue styles
        wp_enqueue_style( 
            'wbcom-portfolio-likes', 
            WBCOM_PLG_PLUGIN_URL . 'assets/style.css', 
            array(), 
            WBCOM_PLG_VERSION 
        );
        
        // Enqueue scripts
        wp_enqueue_script( 
            'wbcom-portfolio-likes', 
            WBCOM_PLG_PLUGIN_URL . 'assets/script.js', 
            array( 'jquery' ), 
            WBCOM_PLG_VERSION, 
            true 
        );
        
        // Localize script
        wp_localize_script( 'wbcom-portfolio-likes', 'wbcom_portfolio_likes', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'wbcom_portfolio_likes_nonce' ),
            'liked_text' => __( 'Liked', 'portfolio-likes' ),
            'like_text' => __( 'Like', 'portfolio-likes' ),
            'loading_text' => __( 'Loading...', 'portfolio-likes' )
        ) );
    }
    
    /**
     * Add like button to portfolio content
     */
    public function wbcom_plg_add_like_button( $content ) {
        if ( is_singular( 'portfolio' ) && in_the_loop() && is_main_query() ) {
            $like_button = $this->wbcom_plg_get_like_button( get_the_ID() );
            $content .= $like_button;
        }
        return $content;
    }
    
    /**
     * Display like button
     */
    public function wbcom_plg_display_like_button( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }
        echo $this->wbcom_plg_get_like_button( $post_id );
    }
    
    /**
     * Get like button HTML
     */
    private function wbcom_plg_get_like_button( $post_id ) {
        $likes_count = $this->wbcom_plg_get_likes_count( $post_id );
        $user_liked = $this->wbcom_plg_has_user_liked( $post_id );
        $like_class = $user_liked ? 'liked' : '';
        $like_text = $user_liked ? __( 'Liked', 'portfolio-likes' ) : __( 'Like', 'portfolio-likes' );
        
        $html = '<div class="wbcom-portfolio-likes-wrapper">';
        $html .= '<button class="wbcom-portfolio-like-button ' . esc_attr( $like_class ) . '" data-post-id="' . esc_attr( $post_id ) . '">';
        $html .= '<span class="like-icon">❤</span>';
        $html .= '<span class="like-text">' . esc_html( $like_text ) . '</span>';
        $html .= '<span class="like-count">' . esc_html( $likes_count ) . '</span>';
        $html .= '</button>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Handle AJAX like request
     */
    public function wbcom_plg_handle_like() {
        // Verify nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'wbcom_portfolio_likes_nonce' ) ) {
            wp_die( __( 'Security check failed', 'portfolio-likes' ) );
        }
        
        $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
        
        if ( ! $post_id || get_post_type( $post_id ) !== 'portfolio' ) {
            wp_send_json_error( array( 'message' => __( 'Invalid post', 'portfolio-likes' ) ) );
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'wbcom_portfolio_likes';
        
        $user_id = get_current_user_id();
        $ip_address = $this->wbcom_plg_get_user_ip();
        
        // Check if already liked
        $existing_like = $this->wbcom_plg_get_existing_like( $post_id, $user_id, $ip_address );
        
        if ( $existing_like ) {
            // Unlike
            $wpdb->delete(
                $table_name,
                array( 'id' => $existing_like->id ),
                array( '%d' )
            );
            
            $liked = false;
            $message = __( 'Like removed', 'portfolio-likes' );
        } else {
            // Like
            $wpdb->insert(
                $table_name,
                array(
                    'post_id' => $post_id,
                    'user_id' => $user_id ?: null,
                    'ip_address' => $user_id ? null : $ip_address,
                    'date_time' => current_time( 'mysql' )
                ),
                array( '%d', '%d', '%s', '%s' )
            );
            
            $liked = true;
            $message = __( 'Liked!', 'portfolio-likes' );
            
            // Check for milestones
            $this->wbcom_plg_check_milestones( $post_id );
        }
        
        $likes_count = $this->wbcom_plg_get_likes_count( $post_id );
        
        wp_send_json_success( array(
            'liked' => $liked,
            'likes_count' => $likes_count,
            'message' => $message
        ) );
    }
    
    /**
     * Check if user has liked
     */
    public function wbcom_plg_has_user_liked( $post_id ) {
        $user_id = get_current_user_id();
        $ip_address = $this->wbcom_plg_get_user_ip();
        
        return $this->wbcom_plg_get_existing_like( $post_id, $user_id, $ip_address ) ? true : false;
    }
    
    /**
     * Get existing like
     */
    private function wbcom_plg_get_existing_like( $post_id, $user_id, $ip_address ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wbcom_portfolio_likes';
        
        if ( $user_id ) {
            return $wpdb->get_row( $wpdb->prepare(
                "SELECT * FROM $table_name WHERE post_id = %d AND user_id = %d",
                $post_id,
                $user_id
            ) );
        } else {
            return $wpdb->get_row( $wpdb->prepare(
                "SELECT * FROM $table_name WHERE post_id = %d AND ip_address = %s AND user_id IS NULL",
                $post_id,
                $ip_address
            ) );
        }
    }
    
    /**
     * Get likes count
     */
    public function wbcom_plg_get_likes_count( $post_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wbcom_portfolio_likes';
        
        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE post_id = %d",
            $post_id
        ) );
        
        return intval( $count );
    }
    
    /**
     * Get user's total likes across all portfolios
     */
    public function wbcom_plg_get_author_total_likes( $user_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'wbcom_portfolio_likes';
        
        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name l
            INNER JOIN {$wpdb->posts} p ON l.post_id = p.ID
            WHERE p.post_author = %d AND p.post_type = 'portfolio'",
            $user_id
        ) );
        
        return intval( $count );
    }
    
    /**
     * Get user IP
     */
    private function wbcom_plg_get_user_ip() {
        $ip_keys = array( 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
        
        foreach ( $ip_keys as $key ) {
            if ( array_key_exists( $key, $_SERVER ) === true ) {
                $ip = trim( $_SERVER[$key] );
                if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
                    return $ip;
                }
            }
        }
        
        return isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
    }
    
    /**
     * Check for milestones
     */
    private function wbcom_plg_check_milestones( $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post ) {
            return;
        }
        
        $author_id = $post->post_author;
        $total_likes = $this->wbcom_plg_get_author_total_likes( $author_id );
        
        // Define milestones
        $milestones = array(
            50 => 'engagement_guru',
            500 => 'top_creator'
        );
        
        foreach ( $milestones as $threshold => $achievement ) {
            if ( $total_likes >= $threshold ) {
                $milestone_key = 'wbcom_portfolio_likes_milestone_' . $threshold;
                $already_triggered = get_user_meta( $author_id, $milestone_key, true );
                
                if ( ! $already_triggered ) {
                    // Mark milestone as achieved
                    update_user_meta( $author_id, $milestone_key, true );
                    
                    // Trigger action for GamiPress
                    do_action( 'wbcom_portfolio_likes_milestone_reached', $author_id, $threshold, $achievement, $post_id );
                    
                    // Trigger GamiPress event
                    if ( function_exists( 'gamipress_trigger_event' ) ) {
                        gamipress_trigger_event( array(
                            'event' => 'wbcom_portfolio_likes_milestone_' . $threshold,
                            'user_id' => $author_id,
                            'post_id' => $post_id,
                            'likes_count' => $total_likes
                        ) );
                    }
                }
            }
        }
    }
    
    /**
     * Register GamiPress triggers
     */
    public function wbcom_plg_register_gamipress_triggers( $triggers ) {
        $triggers['Portfolio Likes'] = array(
            'wbcom_portfolio_likes_milestone_50' => __( 'Reach 50 total likes on portfolios (Engagement Guru)', 'portfolio-likes' ),
            'wbcom_portfolio_likes_milestone_500' => __( 'Reach 500 total likes on portfolios (Top Creator)', 'portfolio-likes' ),
            'wbcom_portfolio_get_like' => __( 'Get a like on a portfolio item', 'portfolio-likes' ),
            'wbcom_portfolio_get_x_likes' => __( 'Get a specific number of likes on portfolios', 'portfolio-likes' )
        );
        
        return $triggers;
    }
    
    /**
     * GamiPress trigger label
     */
    public function wbcom_plg_gamipress_trigger_label( $label, $trigger, $requirement_id ) {
        switch ( $trigger ) {
            case 'wbcom_portfolio_likes_milestone_50':
                return __( 'Reach 50 total likes on portfolios (Engagement Guru)', 'portfolio-likes' );
            case 'wbcom_portfolio_likes_milestone_500':
                return __( 'Reach 500 total likes on portfolios (Top Creator)', 'portfolio-likes' );
        }
        return $label;
    }
    
    /**
     * GamiPress specific triggers
     */
    public function wbcom_plg_gamipress_specific_triggers( $triggers, $trigger_type ) {
        if ( $trigger_type === 'wbcom_portfolio_get_x_likes' ) {
            $triggers['wbcom_portfolio_get_x_likes'] = __( 'Get a specific number of likes on portfolios', 'portfolio-likes' );
        }
        return $triggers;
    }
    
    /**
     * Trigger GamiPress achievement
     */
    public function wbcom_plg_trigger_gamipress_achievement( $author_id, $threshold, $achievement, $post_id ) {
        if ( ! function_exists( 'gamipress_trigger_event' ) ) {
            return;
        }
        
        // Trigger the specific milestone event
        gamipress_trigger_event( array(
            'event' => 'wbcom_portfolio_likes_milestone_' . $threshold,
            'user_id' => $author_id
        ) );
    }
    
    /**
     * Add likes column to admin
     */
    public function wbcom_plg_add_likes_column( $columns ) {
        $columns['wbcom_portfolio_likes'] = __( 'Likes', 'portfolio-likes' );
        return $columns;
    }
    
    /**
     * Likes column content
     */
    public function wbcom_plg_likes_column_content( $column, $post_id ) {
        if ( $column === 'wbcom_portfolio_likes' ) {
            $likes = $this->wbcom_plg_get_likes_count( $post_id );
            echo '<span class="wbcom-portfolio-likes-count">' . $likes . '</span>';
        }
    }
    
    /**
     * Make likes column sortable
     */
    public function wbcom_plg_likes_sortable_column( $columns ) {
        $columns['wbcom_portfolio_likes'] = 'wbcom_portfolio_likes';
        return $columns;
    }
    
    /**
     * Handle likes orderby
     */
    public function wbcom_plg_likes_orderby( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }
        
        if ( $query->get( 'orderby' ) === 'wbcom_portfolio_likes' ) {
            global $wpdb;
            $query->set( 'meta_key', 'wbcom_portfolio_likes_count' );
            $query->set( 'orderby', 'meta_value_num' );
        }
    }
}

// Initialize plugin
function wbcom_portfolio_likes_gamipress() {
    return Wbcom_Portfolio_Likes_GamiPress::get_instance();
}

// Start the plugin
wbcom_portfolio_likes_gamipress();

/**
 * Template tag to display like button
 * 
 * @param int $post_id Optional post ID
 */
function wbcom_portfolio_likes_button( $post_id = null ) {
    do_action( 'wbcom_portfolio_like_button', $post_id );
}

/**
 * Get portfolio likes count
 * 
 * @param int $post_id Post ID
 * @return int Likes count
 */
function wbcom_get_portfolio_likes_count( $post_id ) {
    return Wbcom_Portfolio_Likes_GamiPress::get_instance()->wbcom_plg_get_likes_count( $post_id );
}

/**
 * Get author total likes
 * 
 * @param int $user_id User ID
 * @return int Total likes count
 */
function wbcom_get_author_portfolio_likes( $user_id ) {
    return Wbcom_Portfolio_Likes_GamiPress::get_instance()->wbcom_plg_get_author_total_likes( $user_id );
}