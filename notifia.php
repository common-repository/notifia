<?php

/**
 * @link              https://notifia.io/
 * @since             1.0.0
 * @package           Notifia
 *
 * @wordpress-plugin
 * Plugin Name:       Notifia - Website Plugins to Increase Sales
 * Plugin URI:        https://notifia.io/page/about-us
 * Description:       Website Plugins that help you generate more leads, acquire new customers, optimize user onboarding, boost customer retention, and earn referrals.
 * Version:           1.0.1
 * Author:            Notifia
 * Author URI:        https://notifia.io/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       notifia
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'NOTIFIA_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-notifia-activator.php
 */
function activate_notifia() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-notifia-activator.php';
	Notifia_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-notifia-deactivator.php
 */
function deactivate_notifia() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-notifia-deactivator.php';
	Notifia_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_notifia' );
register_deactivation_hook( __FILE__, 'deactivate_notifia' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-notifia.php';

/**
 * Begins execution of the plugin.
 * @since    1.0.0
 */
function run_notifia() {

	$plugin = new Notifia();
	$plugin->run();

}
run_notifia();
