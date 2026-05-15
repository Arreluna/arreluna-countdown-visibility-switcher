<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EVC_Assets {
	protected static $inline_added    = false;
	protected static $footer_printed  = false;
	protected static $enqueued        = false;

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_frontend_assets' ) );
		add_action( 'wp_footer', array( __CLASS__, 'print_frontend_style_fallback' ), 99 );
	}

	public static function register_frontend_assets() {
		wp_register_style( 'evc-frontend', EVC_URL . 'assets/css/frontend.css', array(), EVC_VERSION );
		wp_register_script( 'evc-frontend', EVC_URL . 'assets/js/frontend.js', array(), EVC_VERSION, true );
	}

	public static function enqueue_frontend() {
		if ( ! wp_style_is( 'evc-frontend', 'registered' ) ) {
			self::register_frontend_assets();
		}

		wp_enqueue_style( 'evc-frontend' );
		wp_enqueue_script( 'evc-frontend' );
		self::$enqueued = true;

		if ( ! self::$inline_added ) {
			wp_add_inline_style( 'evc-frontend', self::inline_css() );
			self::$inline_added = true;
		}
	}

	/**
	 * Prints a late fallback style block.
	 *
	 * Some builders/rendering flows process shortcodes after the document head has
	 * already been printed. In those cases wp_add_inline_style() can be too late.
	 * This fallback makes the saved style settings effective even in those layouts.
	 * Only runs when a countdown shortcode has actually been rendered on the page.
	 */
	public static function print_frontend_style_fallback() {
		if ( ! self::$enqueued || self::$footer_printed ) {
			return;
		}

		self::$footer_printed = true;
		printf(
			"\n<style id=\"evc-frontend-dynamic-css\">\n%s\n</style>\n",
			wp_strip_all_tags( self::inline_css() ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSS output; all property values are sanitized individually at save time.
		);
	}

	public static function inline_css() {
		$options = EVC_Settings::get();

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
			'--evc-font-family:' . $font_family . ';' .
			'--evc-block-bg:' . $block_bg . ';' .
			'--evc-block-color:' . $block_color . ';' .
			'--evc-expired-bg:' . $expired_bg . ';' .
			'--evc-expired-color:' . $expired_color . ';' .
			'--evc-radius:' . $border_radius . 'px;' .
			'--evc-block-width:' . $block_width . 'px;' .
			'--evc-block-height:' . $block_height . 'px;' .
			'--evc-gap:' . $gap . 'px;' .
			'--evc-number-size:' . $number_font_size . 'px;' .
			'--evc-label-size:' . $label_font_size . 'px;' .
		'}';

		// Direct rules after variables so saved settings win over default stylesheet and page-builder styles.
		$css .= '.evc-countdown .evc-countdown__wrapper{gap:' . $gap . 'px !important;}';
		$css .= '.evc-countdown .evc-countdown__block{' .
			'background-color:' . $block_bg . ' !important;' .
			'color:' . $block_color . ' !important;' .
			'border-radius:' . $border_radius . 'px !important;' .
			'width:' . $block_width . 'px !important;' .
			'height:' . $block_height . 'px !important;' .
			'font-family:' . $font_family . ' !important;' .
		'}';
		$css .= '.evc-countdown .evc-countdown__number{font-size:' . $number_font_size . 'px !important;}';
		$css .= '.evc-countdown .evc-countdown__label{font-size:' . $label_font_size . 'px !important;}';
		$css .= '.evc-countdown.evc-countdown--expired .evc-countdown__block{' .
			'background-color:' . $expired_bg . ' !important;' .
			'color:' . $expired_color . ' !important;' .
		'}';

		if ( ! empty( $options['custom_css'] ) ) {
			$css .= "\n" . $options['custom_css'];
		}

		return $css;
	}
}
