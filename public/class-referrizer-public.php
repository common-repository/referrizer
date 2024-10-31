<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    referrizer
 * @subpackage referrizer/public
 * @author     Dejan Osmanovic <dejan@referrizer.com>
 */
class Referrizer_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of the plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Append voucher popup div to the html dom.
	 *
	 * @since   1.0.0
	 */
	public function voucher_popup_html() {
		if ( ! $this->is_configuration_valid() ) {
			return;
		}

		$options = get_option( $this->plugin_name );
		printf( '<div id="referrizer-popup-widget" data-aid="%s"></div>', $options['api_key'] );
	}

	private function is_configuration_valid() {
		$options = get_option( $this->plugin_name );
		if ( ! $options || ! is_referrizer_api_token_valid( $options ) || ! ( isset( $options['display_voucher'] ) && $options['display_voucher'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		if ( ! $this->is_configuration_valid() ) {
			return;
		}

		$extension = WP_DEBUG ? '.min.js' : '.js';

		wp_enqueue_script( $this->plugin_name . '-popup-voucher', plugin_dir_url( __FILE__ ) . 'js/popup-voucher' . $extension, array(
			'jquery'
		), $this->version, true );
	}

	/**
	 * Register the styles for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		if ( ! $this->is_configuration_valid() ) {
			return;
		}

		$extension = WP_DEBUG ? '.min.css' : '.css';
	}

}
