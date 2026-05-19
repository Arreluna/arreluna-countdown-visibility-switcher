<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACVS_Assets {
	protected static $inline_added = false;
	protected static $enqueued     = false;

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_frontend_assets' ) );
	}

	public static function register_frontend_assets() {
		wp_register_style( 'acvs-frontend', ACVS_URL . 'assets/css/frontend.css', array(), ACVS_VERSION );
		wp_register_script( 'acvs-frontend', ACVS_URL . 'assets/js/frontend.js', array(), ACVS_VERSION, true );
	}

	public static function enqueue_frontend() {
		if ( ! wp_style_is( 'acvs-frontend', 'registered' ) ) {
			self::register_frontend_assets();
		}

		wp_enqueue_style( 'acvs-frontend' );
		wp_enqueue_script( 'acvs-frontend' );
		self::$enqueued = true;

		if ( ! self::$inline_added ) {
			wp_add_inline_style( 'acvs-frontend', self::inline_css() );
			self::$inline_added = true;
		}
	}

	public static function inline_css() {
		$options = ACVS_Settings::get();

		$font_family      = isset( $options['font_family'] ) ? preg_replace( '/[^a-zA-Z0-9\s,\-_]/', '', trim( (string) $options['font_family'] ) ) : 'inherit';
		$font_family      = '' !== $font_family ? $font_family : 'inherit';
		$block_bg         = ! empty( $options['block_bg'] ) ? $options['block_bg'] : '#ffffff';
		$block_color      = ! empty( $options['block_color'] ) ? $options['block_color'] : '#000000';
		$expired_bg       = ! empty( $options['expired_bg'] ) ? $options['expired_bg'] : '#eae6d9';
		$expired_color    = ! empty( $options['expired_color'] ) ? $options['expired_color'] : '#000000';
		$border_radius    = absint( $options['border_radius'] );
		$block_width      = absint( $options['block_width'] );
		$block_height     = absint( $options['block_height'] );
		$gap              = absint( $options['gap'] );
		$number_font_size = absint( $options['number_font_size'] );
		$label_font_size  = absint( $options['label_font_size'] );

		$css  = ':root{' .
			'--acvs-font-family:' . $font_family . ';' .
			'--acvs-block-bg:' . $block_bg . ';' .
			'--acvs-block-color:' . $block_color . ';' .
			'--acvs-expired-bg:' . $expired_bg . ';' .
			'--acvs-expired-color:' . $expired_color . ';' .
			'--acvs-radius:' . $border_radius . 'px;' .
			'--acvs-block-width:' . $block_width . 'px;' .
			'--acvs-block-height:' . $block_height . 'px;' .
			'--acvs-gap:' . $gap . 'px;' .
			'--acvs-number-size:' . $number_font_size . 'px;' .
			'--acvs-label-size:' . $label_font_size . 'px;' .
		'}';

		$css .= '.acvs-countdown .acvs-countdown__wrapper{gap:' . $gap . 'px !important;}';
		$css .= '.acvs-countdown .acvs-countdown__block{' .
			'background-color:' . $block_bg . ' !important;' .
			'color:' . $block_color . ' !important;' .
			'border-radius:' . $border_radius . 'px !important;' .
			'width:' . $block_width . 'px !important;' .
			'height:' . $block_height . 'px !important;' .
			'font-family:' . $font_family . ' !important;' .
		'}';
		$css .= '.acvs-countdown .acvs-countdown__number{font-size:' . $number_font_size . 'px !important;}';
		$css .= '.acvs-countdown .acvs-countdown__label{font-size:' . $label_font_size . 'px !important;}';
		$css .= '.acvs-countdown.acvs-countdown--expired .acvs-countdown__block{' .
			'background-color:' . $expired_bg . ' !important;' .
			'color:' . $expired_color . ' !important;' .
		'}';

		return $css;
	}
}
