<?php
/**
 * GamiPress Integration for BP Verified Member
 *
 * @link       https://wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    Talentsvibe_Core
 * @subpackage Talentsvibe_Core/public
 */

/**
 * GamiPress Integration functionality
 *
 * @package    Talentsvibe_Core
 * @subpackage Talentsvibe_Core/public
 * @author     Wbcom Designs <admin@wbcomdesigns.com>
 */
class Talentsvibe_Core_Gamipress {

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
	 * The option group for settings
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $option_group    The option group.
	 */
	private $option_group = 'bp_verified_member';

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_name    The name of the plugin.
	 * @param    string    $version        The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Check if all dependencies are met
	 *
	 * @return bool
	 */
	public function check_dependencies() {
		return function_exists( 'gamipress' ) && class_exists( 'BP_Verified_Member' );
	}

	/**
	 * Register settings for GamiPress integration
	 */
	public function register_settings() {
		if ( ! $this->check_dependencies() ) {
			return;
		}

		$page_slug = 'bp-verified-member';
		
		// Register settings
		register_setting( $this->option_group, "{$this->option_group}_gamipress_verified_achievement", 'sanitize_text_field' );
		register_setting( $this->option_group, "{$this->option_group}_gamipress_verified_points_type", 'sanitize_text_field' );
		register_setting( $this->option_group, "{$this->option_group}_gamipress_verified_points_amount", 'intval' );
		register_setting( $this->option_group, "{$this->option_group}_gamipress_unverified_deduct_points", 'intval' );
		register_setting( $this->option_group, "{$this->option_group}_gamipress_unverified_revoke_achievement", 'intval' );
		
		// Add settings section
		add_settings_section(
			"{$this->option_group}_gamipress_section",
			__( 'GamiPress Integration', 'talentsvibe-core' ),
			array( $this, 'render_section_description' ),
			$page_slug
		);
		
		// Add settings fields
		$this->add_settings_fields( $page_slug );
	}

	/**
	 * Render section description
	 */
	public function render_section_description() {
		echo '<p>' . __( 'Configure GamiPress rewards for verified members.', 'talentsvibe-core' ) . '</p>';
	}

	/**
	 * Add settings fields
	 */
	private function add_settings_fields( $page_slug ) {
		$section_id = "{$this->option_group}_gamipress_section";
		
		// Achievement on verification
		add_settings_field(
			"{$this->option_group}_gamipress_verified_achievement",
			__( 'Achievement on Verification', 'talentsvibe-core' ),
			array( $this, 'render_achievement_field' ),
			$page_slug,
			$section_id
		);
		
		// Points on verification
		add_settings_field(
			"{$this->option_group}_gamipress_verified_points_type",
			__( 'Points Type on Verification', 'talentsvibe-core' ),
			array( $this, 'render_points_type_field' ),
			$page_slug,
			$section_id
		);
		
		add_settings_field(
			"{$this->option_group}_gamipress_verified_points_amount",
			__( 'Points Amount', 'talentsvibe-core' ),
			array( $this, 'render_points_amount_field' ),
			$page_slug,
			$section_id
		);
		
		// Unverification options
		add_settings_field(
			"{$this->option_group}_gamipress_unverified_deduct_points",
			__( 'Deduct Points on Unverification', 'talentsvibe-core' ),
			array( $this, 'render_deduct_points_field' ),
			$page_slug,
			$section_id
		);
		
		add_settings_field(
			"{$this->option_group}_gamipress_unverified_revoke_achievement",
			__( 'Revoke Achievement on Unverification', 'talentsvibe-core' ),
			array( $this, 'render_revoke_achievement_field' ),
			$page_slug,
			$section_id
		);
	}

	/**
	 * Render achievement selection field
	 */
	public function render_achievement_field() {
		$field_id = "{$this->option_group}_gamipress_verified_achievement";
		$value = get_option( $field_id, '' );
		$options = $this->get_achievement_options();
		?>
		<select name="<?php echo esc_attr( $field_id ); ?>" id="<?php echo esc_attr( $field_id ); ?>">
			<?php foreach ( $options as $option_value => $option_label ) : ?>
				<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $value, $option_value ); ?>>
					<?php echo esc_html( $option_label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php _e( 'Select an achievement to award when a user gets verified', 'talentsvibe-core' ); ?></p>
		<?php
	}

	/**
	 * Render points type field
	 */
	public function render_points_type_field() {
		$field_id = "{$this->option_group}_gamipress_verified_points_type";
		$value = get_option( $field_id, '' );
		$options = $this->get_points_type_options();
		?>
		<select name="<?php echo esc_attr( $field_id ); ?>" id="<?php echo esc_attr( $field_id ); ?>">
			<?php foreach ( $options as $option_value => $option_label ) : ?>
				<option value="<?php echo esc_attr( $option_value ); ?>" <?php selected( $value, $option_value ); ?>>
					<?php echo esc_html( $option_label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php _e( 'Select points type to award when a user gets verified', 'talentsvibe-core' ); ?></p>
		<?php
	}

	/**
	 * Render points amount field
	 */
	public function render_points_amount_field() {
		$field_id = "{$this->option_group}_gamipress_verified_points_amount";
		$value = get_option( $field_id, 0 );
		?>
		<input type="number" 
			   name="<?php echo esc_attr( $field_id ); ?>" 
			   id="<?php echo esc_attr( $field_id ); ?>" 
			   value="<?php echo esc_attr( $value ); ?>"
			   min="0" />
		<p class="description"><?php _e( 'Number of points to award (0 = disabled)', 'talentsvibe-core' ); ?></p>
		<?php
	}

	/**
	 * Render deduct points field
	 */
	public function render_deduct_points_field() {
		$field_id = "{$this->option_group}_gamipress_unverified_deduct_points";
		$value = get_option( $field_id, 0 );
		?>
		<input type="checkbox" 
			   name="<?php echo esc_attr( $field_id ); ?>" 
			   id="<?php echo esc_attr( $field_id ); ?>" 
			   value="1" 
			   <?php checked( $value, 1 ); ?> />
		<label for="<?php echo esc_attr( $field_id ); ?>"><?php _e( 'Deduct the same amount of points when user gets unverified', 'talentsvibe-core' ); ?></label>
		<?php
	}

	/**
	 * Render revoke achievement field
	 */
	public function render_revoke_achievement_field() {
		$field_id = "{$this->option_group}_gamipress_unverified_revoke_achievement";
		$value = get_option( $field_id, 0 );
		?>
		<input type="checkbox" 
			   name="<?php echo esc_attr( $field_id ); ?>" 
			   id="<?php echo esc_attr( $field_id ); ?>" 
			   value="1" 
			   <?php checked( $value, 1 ); ?> />
		<label for="<?php echo esc_attr( $field_id ); ?>"><?php _e( 'Revoke the achievement when user gets unverified', 'talentsvibe-core' ); ?></label>
		<?php
	}

	/**
	 * Get achievement options
	 */
	private function get_achievement_options() {
		$options = array( '' => '— ' . __( 'None', 'talentsvibe-core' ) . ' —' );
		
		if ( ! function_exists( 'gamipress_get_achievement_types' ) ) {
			return $options;
		}
		
		$achievement_types = gamipress_get_achievement_types();
		
		foreach ( $achievement_types as $type => $data ) {
			$achievements = gamipress_get_achievements( array(
				'post_type' => $type,
				'posts_per_page' => -1,
				'orderby' => 'title',
				'order' => 'ASC'
			));
			
			if ( ! empty( $achievements ) ) {
				foreach ( $achievements as $achievement ) {
					$options[ $achievement->ID ] = $data['singular_name'] . ': ' . $achievement->post_title;
				}
			}
		}
		
		return $options;
	}

	/**
	 * Get points type options
	 */
	private function get_points_type_options() {
		$options = array( '' => '— ' . __( 'None', 'talentsvibe-core' ) . ' —' );
		
		if ( ! function_exists( 'gamipress_get_points_types' ) ) {
			return $options;
		}
		
		$points_types = gamipress_get_points_types();
		
		foreach ( $points_types as $type => $data ) {
			$options[ $type ] = $data['plural_name'];
		}
		
		return $options;
	}

	/**
	 * Handle verification status changes
	 *
	 * @param int    $user_id     User ID
	 * @param string $new_status  New verification status
	 */
	public function handle_verification_status( $user_id, $new_status ) {
		if ( ! $this->check_dependencies() ) {
			return;
		}
		
		if ( 'verified' === $new_status ) {
			$this->handle_user_verified( $user_id );
		} elseif ( 'unverified' === $new_status ) {
			$this->handle_user_unverified( $user_id );
		}
	}

	/**
	 * Handle user verification
	 */
	private function handle_user_verified( $user_id ) {
		// Award achievement
		$achievement_id = get_option( "{$this->option_group}_gamipress_verified_achievement" );
		if ( ! empty( $achievement_id ) ) {
			gamipress_award_achievement_to_user( $achievement_id, $user_id );
		}
		
		// Award points
		$points_type = get_option( "{$this->option_group}_gamipress_verified_points_type" );
		$points_amount = intval( get_option( "{$this->option_group}_gamipress_verified_points_amount", 0 ) );
		
		if ( ! empty( $points_type ) && $points_amount > 0 ) {
			gamipress_award_points_to_user( $user_id, $points_amount, $points_type, array(
				'reason' => __( 'Profile verified', 'talentsvibe-core' )
			));
		}
	}

	/**
	 * Handle user unverification
	 */
	private function handle_user_unverified( $user_id ) {
		// Revoke achievement if enabled
		if ( get_option( "{$this->option_group}_gamipress_unverified_revoke_achievement" ) ) {
			$achievement_id = get_option( "{$this->option_group}_gamipress_verified_achievement" );
			if ( ! empty( $achievement_id ) ) {
				gamipress_revoke_achievement_to_user( $achievement_id, $user_id );
			}
		}
		
		// Deduct points if enabled
		if ( get_option( "{$this->option_group}_gamipress_unverified_deduct_points" ) ) {
			$points_type = get_option( "{$this->option_group}_gamipress_verified_points_type" );
			$points_amount = intval( get_option( "{$this->option_group}_gamipress_verified_points_amount", 0 ) );
			
			if ( ! empty( $points_type ) && $points_amount > 0 ) {
				gamipress_deduct_points_to_user( $user_id, $points_amount, $points_type, array(
					'reason' => __( 'Profile unverified', 'talentsvibe-core' )
				));
			}
		}
	}

	/**
	 * Add custom GamiPress triggers
	 */
	public function add_gamipress_triggers( $triggers ) {
		$triggers['bp_verified_member_verified'] = __( 'Get verified on BuddyPress', 'talentsvibe-core' );
		$triggers['bp_verified_member_unverified'] = __( 'Get unverified on BuddyPress', 'talentsvibe-core' );
		return $triggers;
	}

	/**
	 * Add trigger labels
	 */
	public function add_trigger_label( $label, $trigger ) {
		switch ( $trigger ) {
			case 'bp_verified_member_verified':
				return __( 'verified on BuddyPress', 'talentsvibe-core' );
			case 'bp_verified_member_unverified':
				return __( 'unverified on BuddyPress', 'talentsvibe-core' );
		}
		return $label;
	}
}