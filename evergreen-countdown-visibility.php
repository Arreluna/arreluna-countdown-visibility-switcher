<?php
/**
 * Plugin Name: Countdown Visibility Switcher
 * Description: Create evergreen or fixed-date countdowns and show, hide, or redirect content when they expire.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Arreluna
 * Author URI: https://arreluna.com
 * Text Domain: evergreen-countdown-visibility
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EVC_VERSION', '1.0.0' );
define( 'EVC_FILE', __FILE__ );
define( 'EVC_PATH', plugin_dir_path( __FILE__ ) );
define( 'EVC_URL', plugin_dir_url( __FILE__ ) );

require_once EVC_PATH . 'includes/class-evc-plugin.php';
require_once EVC_PATH . 'includes/class-evc-post-type.php';
require_once EVC_PATH . 'includes/class-evc-settings.php';
require_once EVC_PATH . 'includes/class-evc-shortcode.php';
require_once EVC_PATH . 'includes/class-evc-assets.php';

add_action( 'plugins_loaded', array( 'EVC_Plugin', 'init' ) );

register_activation_hook( __FILE__, array( 'EVC_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'EVC_Plugin', 'deactivate' ) );
