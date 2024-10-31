<?php

/*
Plugin Name: Referrizer
Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
Description: Integrate Referrizer's reviews and vouchers with your website.
Version: 1.1.6
Author: Referrizer
Author URI: https://www.referrizer.com
License: GPL2
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


define( 'REFERRIZER_VERSION', '1.1.5' );

/**
 * The core plugin class that is used to define admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-referrizer.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_referrizer() {

	$plugin = new Referrizer();
	$plugin->run();
}

run_referrizer();
