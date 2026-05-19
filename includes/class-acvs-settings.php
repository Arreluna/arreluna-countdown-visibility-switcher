<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACVS_Settings {
	const OPTION = 'acvs_style_settings';

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
	}

	public static function defaults() {
		return array(
			'font_family'      => 'inherit',
			'block_bg'         => '#ffffff',
			'block_color'      => '#000000',
			'expired_bg'       => '#eae6d9',
			'expired_color'    => '#000000',
			'border_radius'    => '7',
			'block_width'      => '120',
			'block_height'     => '120',
			'gap'              => '16',
			'number_font_size' => '28',
			'label_font_size'  => '14',
		);
	}

	public static function get() {
		$options = get_option( self::OPTION, array() );
		return wp_parse_args( is_array( $options ) ? $options : array(), self::defaults() );
	}

	public static function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=' . ACVS_Post_Type::POST_TYPE,
			__( 'Countdown Styles', 'arreluna-countdown-visibility-switcher' ),
			__( 'Styles', 'arreluna-countdown-visibility-switcher' ),
			'manage_options',
			'acvs-styles',
			array( __CLASS__, 'render_page' )
		);
	}

	public static function register_settings() {
		register_setting( 'acvs_styles', self::OPTION, array( __CLASS__, 'sanitize' ) );
	}

	public static function sanitize( $input ) {
		$defaults = self::defaults();
		$input    = is_array( $input ) ? $input : array();
		$output   = array();
		$output['font_family']      = isset( $input['font_family'] ) ? self::sanitize_css_font_family( $input['font_family'] ) : $defaults['font_family'];
		$output['block_bg']         = isset( $input['block_bg'] ) ? sanitize_hex_color( $input['block_bg'] ) : $defaults['block_bg'];
		$output['block_color']      = isset( $input['block_color'] ) ? sanitize_hex_color( $input['block_color'] ) : $defaults['block_color'];
		$output['expired_bg']       = isset( $input['expired_bg'] ) ? sanitize_hex_color( $input['expired_bg'] ) : $defaults['expired_bg'];
		$output['expired_color']    = isset( $input['expired_color'] ) ? sanitize_hex_color( $input['expired_color'] ) : $defaults['expired_color'];
		$output['border_radius']    = isset( $input['border_radius'] ) ? absint( $input['border_radius'] ) : $defaults['border_radius'];
		$output['block_width']      = isset( $input['block_width'] ) ? absint( $input['block_width'] ) : $defaults['block_width'];
		$output['block_height']     = isset( $input['block_height'] ) ? absint( $input['block_height'] ) : $defaults['block_height'];
		$output['gap']              = isset( $input['gap'] ) ? absint( $input['gap'] ) : $defaults['gap'];
		$output['number_font_size'] = isset( $input['number_font_size'] ) ? absint( $input['number_font_size'] ) : $defaults['number_font_size'];
		$output['label_font_size']  = isset( $input['label_font_size'] ) ? absint( $input['label_font_size'] ) : $defaults['label_font_size'];
		return $output;
	}

	protected static function sanitize_css_font_family( $value ) {
		$value = sanitize_text_field( wp_unslash( $value ) );
		$value = preg_replace( '/[^a-zA-Z0-9\s,\-_]/', '', $value );
		$value = trim( (string) $value );

		return '' !== $value ? $value : 'inherit';
	}

	public static function render_page() {
		$options = self::get();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Countdown Styles', 'arreluna-countdown-visibility-switcher' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'acvs_styles' ); ?>
				<table class="form-table" role="presentation">
					<tr><th scope="row"><?php esc_html_e( 'Font family', 'arreluna-countdown-visibility-switcher' ); ?></th><td><input type="text" name="<?php echo esc_attr( self::OPTION ); ?>[font_family]" value="<?php echo esc_attr( $options['font_family'] ); ?>" class="regular-text"></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Block background', 'arreluna-countdown-visibility-switcher' ); ?></th><td><input type="color" name="<?php echo esc_attr( self::OPTION ); ?>[block_bg]" value="<?php echo esc_attr( $options['block_bg'] ); ?>"></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Block text color', 'arreluna-countdown-visibility-switcher' ); ?></th><td><input type="color" name="<?php echo esc_attr( self::OPTION ); ?>[block_color]" value="<?php echo esc_attr( $options['block_color'] ); ?>"></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Expired background', 'arreluna-countdown-visibility-switcher' ); ?></th><td><input type="color" name="<?php echo esc_attr( self::OPTION ); ?>[expired_bg]" value="<?php echo esc_attr( $options['expired_bg'] ); ?>"></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Expired text color', 'arreluna-countdown-visibility-switcher' ); ?></th><td><input type="color" name="<?php echo esc_attr( self::OPTION ); ?>[expired_color]" value="<?php echo esc_attr( $options['expired_color'] ); ?>"></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Border radius', 'arreluna-countdown-visibility-switcher' ); ?></th><td><input type="number" min="0" name="<?php echo esc_attr( self::OPTION ); ?>[border_radius]" value="<?php echo esc_attr( $options['border_radius'] ); ?>" class="small-text"> px</td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Block size', 'arreluna-countdown-visibility-switcher' ); ?></th><td><input type="number" min="30" name="<?php echo esc_attr( self::OPTION ); ?>[block_width]" value="<?php echo esc_attr( $options['block_width'] ); ?>" class="small-text"> × <input type="number" min="30" name="<?php echo esc_attr( self::OPTION ); ?>[block_height]" value="<?php echo esc_attr( $options['block_height'] ); ?>" class="small-text"> px</td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Gap', 'arreluna-countdown-visibility-switcher' ); ?></th><td><input type="number" min="0" name="<?php echo esc_attr( self::OPTION ); ?>[gap]" value="<?php echo esc_attr( $options['gap'] ); ?>" class="small-text"> px</td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Font sizes', 'arreluna-countdown-visibility-switcher' ); ?></th><td><?php esc_html_e( 'Number', 'arreluna-countdown-visibility-switcher' ); ?> <input type="number" min="8" name="<?php echo esc_attr( self::OPTION ); ?>[number_font_size]" value="<?php echo esc_attr( $options['number_font_size'] ); ?>" class="small-text"> px / <?php esc_html_e( 'Label', 'arreluna-countdown-visibility-switcher' ); ?> <input type="number" min="8" name="<?php echo esc_attr( self::OPTION ); ?>[label_font_size]" value="<?php echo esc_attr( $options['label_font_size'] ); ?>" class="small-text"> px</td></tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
