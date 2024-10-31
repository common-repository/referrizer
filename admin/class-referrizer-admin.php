<?php


class Referrizer_Admin {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_id The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	const ADMIN_PAGE_SLUG = 'referrizer-settings';
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_id The ID of this plugin.
	 */
	private $plugin_id;
	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;
	/**
	 * Holds the values to be used in the fields callbacks
	 */
	private $options;

	/**
	 * Holds default values for options
	 */
	private $options_default;

	public function __construct( $plugin_id, $version ) {

		$this->plugin_id       = $plugin_id;
		$this->version         = $version;
		$this->options_default = array( 'display_voucher' => '1' );
	}

	/**
	 * Register the JavaScript for the admin side of the site.
	 *
	 * @since    1.0.2
	 */
	public function enqueue_scripts() {
		$options = wp_parse_args( get_option( $this->plugin_id ), $this->options_default );
		if ( ! is_referrizer_api_token_valid( $options ) ) {
			return;
		}
		$extension = ! WP_DEBUG ? '.min.js' : '.js';


		wp_enqueue_script( $this->plugin_id . '-admin', plugin_dir_url( __FILE__ ) . 'js/admin
		' . $extension, array( 'jquery' ), $this->version, true );
	}

	/**
	 * Add options page
	 */
	public function admin_menu() {
		add_menu_page(
			'Referrizer',
			'Referrizer',
			'manage_options',
			self::ADMIN_PAGE_SLUG,
			array( $this, 'create_admin_page' ),
			plugin_dir_url( __FILE__ ) . '../includes/images/logo.ico',
			26
		);
	}

	/**
	 * Options page callback
	 */
	public function create_admin_page() {

		$this->options = wp_parse_args( get_option( $this->plugin_id ), $this->options_default );
		?>
        <div class="wrap">
            <h1>Referrizer Plugin Settings</h1>
            <form method="post" action="options.php">
				<?php
				settings_errors();
				settings_fields( $this->plugin_id );
				do_settings_sections( 'referrizer-setting-admin' );
				submit_button();
				?>
            </form>
        </div>
		<?php
		if ( ! is_referrizer_api_token_valid( get_option( $this->plugin_id ) ) ) {
			return;
		}
		?>
        <h3>Available shortcodes</h3>
		<?php include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/short-codes.php'; ?>
		<?php
	}

	/**
	 * Register and add settings
	 */
	public function admin_init() {
		register_setting(
			$this->plugin_id,
			$this->plugin_id,
			array( $this, 'sanitize' )
		);

		add_settings_section(
			'general',
			'',
			null,
			'referrizer-setting-admin'
		);

		add_settings_field(
			'api_token',
			'Api Token',
			array( $this, 'api_token_callback' ),
			'referrizer-setting-admin',
			'general'
		);

		if ( ! is_referrizer_api_token_valid( get_option( $this->plugin_id ) ) ) {
			return;
		}

		add_settings_field(
			'api_key',
			'Api Key',
			array( $this, 'api_key_callback' ),
			'referrizer-setting-admin',
			'general',
			array( 'class' => 'hidden', 'type' => 'input' )
		);

		add_settings_field(
			'display_voucher',
			'Display voucher Popup',
			array( $this, 'voucher_callback' ),
			'referrizer-setting-admin',
			'general'
		);

	}

	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 *
	 * @return array
	 */
	public function sanitize( $input ) {
		if ( isset( $_POST['reset'] ) ) {
			return $this->options_default;
		}

		$output = array();

		$output['display_voucher'] = sanitize_text_field( $input['display_voucher'] );

		// if first submit after reset settings, set display_voucher to old value (true)
		if ( isset( $input['api_token'] ) && count( $input ) === 1 ) {
			$output = array_merge( $output, $this->options_default );
		}

		if ( isset( $input['api_token'] ) ) {
			$output['api_token'] = sanitize_text_field( $input['api_token'] );
		}

		$trim = trim( $output['api_token'] );
		if ( ! $output['api_token'] || empty( $trim ) ) {
			add_settings_error( $this->plugin_id, 'api_token', __( 'API Token is mandatory', $this->plugin_id ) );
			$this->revert_setting( $output, $this->plugin_id, 'api_token' );
			$this->revert_setting( $output, $this->plugin_id, 'api_key' );

			return $output;
		}

		try {
			$output['api_key'] = JWT::decode( trim( $output['api_token'] ) )->sub;
		} catch ( \Exception $exception ) {
			add_settings_error( $this->plugin_id, 'api_token', __( 'Unable to parse API Token.', $this->plugin_id ) );
			$this->revert_setting( $output, $this->plugin_id, 'api_token' );
			$this->revert_setting( $output, $this->plugin_id, 'api_key' );

			return $output;
		}

		if ( ! is_referrizer_api_token_valid( $output['api_key'] ) ) {
			add_settings_error( $this->plugin_id, 'api_token', __( 'API Token is invalid.', $this->plugin_id ) );
			$this->revert_setting( $output, $this->plugin_id, 'api_token' );
			$this->revert_setting( $output, $this->plugin_id, 'api_key' );
		}

		return $output;
	}

	/**
	 * Revert the submitted setting to its original value
	 *
	 * @param $new
	 * @param $option
	 * @param null $key
	 */
	private function revert_setting( &$new, $option, $key = null ) {
		$old = get_option( $option );

		if ( $key ) {
			$new[ $key ] = array_key_exists( $key, $old ) ? $old[ $key ] : null;
		} else {
			$new = $old;
		}
	}

	/**
	 * Get and print api token input
	 */
	public function api_token_callback() {
		printf(
			'<input type="text" class="regular-text" id="api_token" name="%s" value="%s" />',
			$this->plugin_id . '[api_token]',
			isset( $this->options['api_token'] ) ? esc_attr( $this->options['api_token'] ) : ''

		);
		if ( ! is_referrizer_api_token_valid( get_option( $this->plugin_id ) ) ) {
			printf( '<a href="https://backend.referrizer.com/apps.php?al=y" target="_blank">
Get Api Token
</a>' );
		} else {
			submit_button( __( 'Reset Settings', $this->plugin_id ), 'delete', 'reset', false );
		}

	}

	/**
	 * Get and print api token input
	 */
	public function api_key_callback() {
		printf(
			'<input type="hidden" class="regular-text" id="api_key" name="%s" value="%s" />
',
			$this->plugin_id . '[api_key]',
			isset( $this->options['api_key'] ) ? esc_attr( $this->options['api_key'] ) : ''
		);
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function voucher_callback() {
		$disabled = isset( $this->options['api_key'] ) && is_referrizer_api_token_valid( $this->options['api_key'] ) ? '' : 'disabled=disabled';
		printf(
			'<input type="checkbox" class="checkbox" id="display_voucher" name="%s" %s ' .
			checked( true, isset( $this->options['display_voucher'] ) && $this->options['display_voucher'], false ) .
			' value="1" />',
			$this->plugin_id . '[display_voucher]', $disabled
		);


	}

	function invalid_api_token_notice() {
		if ( is_referrizer_api_token_valid( get_option( $this->plugin_id ) ) ) {
			return;
		}
//todo: check wp version for class 'error'
		$notice_class = 'notice-warning';
		if ( version_compare( $GLOBALS['wp_version'], '4.2', '<' ) ) {
			$notice_class = 'error';
		}

		?>

        <div class="notice <?php echo $notice_class; ?> is-dismissible">
            <p>
				<?php _e( 'Referrizer plugin needs to be configured.', $this->plugin_id ); ?>
				<?php printf( '<a href="admin.php?page=%s" class="button action">', self::ADMIN_PAGE_SLUG );
				_e( 'Complete setup', $this->plugin_id );
				echo '</a>' ?>
            </p>
        </div>
		<?php
	}
}