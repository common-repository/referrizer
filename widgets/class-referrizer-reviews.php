<?php

class Referrizer_Reviews extends WP_Widget {

	/**
	 *
	 * Unique identifier for widget.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $widget_slug;
	protected $options_key;
	protected $plugin_name;

	/**
	 * @since 1.0.0
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Specifies the classname and description, instantiates the widget
	 * and includes necessary stylesheets and JavaScript.
	 *
	 */
	public function __construct() {
		if ( defined( 'REFERRIZER_VERSION' ) ) {
			$this->version = REFERRIZER_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		$this->plugin_name = 'referrizer';
		$this->options_key = 'referrizer';
		$this->widget_slug = 'referrizer-reviews';

		parent::__construct(
			$this->widget_slug,
			'Referrizer Reviews',
			array(
				'classname'   => 'class-' . $this->widget_slug,
				'description' => __( 'Display all Referrizer reviews on your website.', $this->options_key )
			)
		);
	}

	public function short_code() {
		add_shortcode( 'referrizer_reviews', array( $this, 'widget' ) );
	}

	public function register() {
		register_widget( __CLASS__ );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
	}

	/**
	 * Register the styles for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
	}

	/**
	 * Outputs the content of the widget.
	 *
	 * @param array $args The array of form elements
	 * @param array $instance The current instance of the widget
	 *
	 * @return string|void
	 */
	public function widget( $args, $instance ) {
		return $this->referrizer_display_reviews_widget();
	}

	protected function referrizer_display_reviews_widget() {
		if ( is_admin() ) {
			return;
		}
		$options = get_option( $this->options_key );
		if ( ! is_referrizer_api_token_valid( $options ) ) {
			return '';
		}

		$extension = !WP_DEBUG ? '.min.js' : '.js';

		wp_enqueue_script( $this->plugin_name . '-reviews-widget', plugin_dir_url( __FILE__ ) . 'js/reviews-widget' . $extension, array(), $this->version, true );

		return sprintf( '<div id="referrizerReviewsComponent" data-aid="%s"></div>', $options['api_key'] );
	}

}