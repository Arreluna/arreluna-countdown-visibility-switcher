<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACVS_Plugin {
	public static function init() {
		ACVS_Post_Type::init();
		ACVS_Settings::init();
		ACVS_Shortcode::init();
		ACVS_Assets::init();
	}

	public static function activate() {
		ACVS_Post_Type::register_post_type();
		flush_rewrite_rules();
	}

	public static function deactivate() {
		flush_rewrite_rules();
	}
}
