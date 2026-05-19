<?php
/**
 * Plugin Name: Arreluna – Countdown Visibility Switcher
 * Description: Create evergreen or fixed-date countdowns and show, hide, or redirect content when they expire.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Arreluna
 * Author URI: https://arreluna.com
 * Text Domain: arreluna-countdown-visibility-switcher
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ACVS_VERSION', '1.0.0' );
define( 'ACVS_FILE', __FILE__ );
define( 'ACVS_PATH', plugin_dir_path( __FILE__ ) );
define( 'ACVS_URL', plugin_dir_url( __FILE__ ) );

require_once ACVS_PATH . 'includes/class-acvs-plugin.php';
require_once ACVS_PATH . 'includes/class-acvs-post-type.php';
require_once ACVS_PATH . 'includes/class-acvs-settings.php';
require_once ACVS_PATH . 'includes/class-acvs-shortcode.php';
require_once ACVS_PATH . 'includes/class-acvs-assets.php';

add_action( 'plugins_loaded', array( 'ACVS_Plugin', 'init' ) );

register_activation_hook( __FILE__, array( 'ACVS_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'ACVS_Plugin', 'deactivate' ) );
