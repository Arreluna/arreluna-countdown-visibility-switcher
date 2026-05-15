<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EVC_Plugin {
	public static function init() {
		EVC_Post_Type::init();
		EVC_Settings::init();
		EVC_Shortcode::init();
		EVC_Assets::init();
	}

	public static function activate() {
		EVC_Post_Type::register_post_type();
		flush_rewrite_rules();
	}

	public static function deactivate() {
		flush_rewrite_rules();
	}
}
