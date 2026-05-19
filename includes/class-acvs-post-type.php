<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ACVS_Post_Type {
	const POST_TYPE   = 'acvs_countdown';
	const NONCE_ACTION = 'acvs_save_countdown';
	const NONCE_NAME   = 'acvs_countdown_nonce';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save_meta' ), 10, 2 );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
		add_filter( 'post_row_actions', array( __CLASS__, 'row_actions' ), 10, 2 );
		add_action( 'admin_post_acvs_reset_countdown', array( __CLASS__, 'handle_reset' ) );
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue' ) );
	}

	public static function admin_enqueue( $hook ) {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
			return;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || self::POST_TYPE !== $screen->post_type ) {
			return;
		}
		wp_enqueue_style( 'acvs-admin', ACVS_URL . 'assets/css/admin.css', array(), ACVS_VERSION );
		wp_enqueue_script( 'acvs-admin-post-type', ACVS_URL . 'assets/js/admin-post-type.js', array(), ACVS_VERSION, true );
	}

	public static function register_post_type() {
		$labels = array(
			'name'          => __( 'Countdowns', 'arreluna-countdown-visibility-switcher' ),
			'singular_name' => __( 'Countdown', 'arreluna-countdown-visibility-switcher' ),
			'add_new_item'  => __( 'Add New Countdown', 'arreluna-countdown-visibility-switcher' ),
			'edit_item'     => __( 'Edit Countdown', 'arreluna-countdown-visibility-switcher' ),
			'menu_name'     => __( 'Countdowns', 'arreluna-countdown-visibility-switcher' ),
		);

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'              => $labels,
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_icon'           => 'dashicons-clock',
				'supports'            => array( 'title' ),
				'capability_type'     => 'post',
				'exclude_from_search' => true,
				'show_in_rest'        => false,
			)
		);
	}

	public static function get_defaults() {
		return array(
			'mode'             => 'evergreen',
			'days'             => 0,
			'hours'            => 1,
			'minutes'          => 0,
			'seconds'          => 0,
			'fixed_datetime'   => '',
			'status'           => 'active',
			'action'           => 'visibility',
			'redirect_url'     => '',
			'expired_display'  => 'zero',
			'show_days'        => 0,
			'show_hours'       => 1,
			'show_minutes'     => 1,
			'show_seconds'     => 1,
			'label_days'       => __( 'Days', 'arreluna-countdown-visibility-switcher' ),
			'label_hours'      => __( 'Hours', 'arreluna-countdown-visibility-switcher' ),
			'label_minutes'    => __( 'Minutes', 'arreluna-countdown-visibility-switcher' ),
			'label_seconds'    => __( 'Seconds', 'arreluna-countdown-visibility-switcher' ),
			'storage_version'  => 1,
		);
	}

	public static function get_meta( $post_id ) {
		$defaults = self::get_defaults();
		$stored   = get_post_meta( $post_id, '_acvs_settings', true );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
		return wp_parse_args( $stored, $defaults );
	}

	public static function add_meta_boxes() {
		add_meta_box( 'acvs_settings', __( 'Countdown setup', 'arreluna-countdown-visibility-switcher' ), array( __CLASS__, 'render_settings_box' ), self::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'acvs_usage', __( 'Shortcode and classes', 'arreluna-countdown-visibility-switcher' ), array( __CLASS__, 'render_usage_box' ), self::POST_TYPE, 'side', 'high' );
	}

	public static function admin_notices() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || self::POST_TYPE !== $screen->post_type ) {
			return;
		}
		if ( empty( $_GET['acvs_reset'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Evergreen countdown reset applied immediately. You do not need to save this countdown again.', 'arreluna-countdown-visibility-switcher' ) . '</p></div>';
	}

	public static function render_settings_box( $post ) {
		$meta = self::get_meta( $post->ID );
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		?>
		<p class="description"><?php esc_html_e( 'Configure the timer, choose what happens when it expires, and customize the visible units for this countdown.', 'arreluna-countdown-visibility-switcher' ); ?></p>
		<table class="form-table acvs-admin-table" role="presentation">
			<tr class="acvs-section-row"><th colspan="2"><h3 class="acvs-admin-section-title"><?php esc_html_e( 'Timer', 'arreluna-countdown-visibility-switcher' ); ?></h3></th></tr>
			<tr>
				<th scope="row"><label for="acvs_status"><?php esc_html_e( 'Status', 'arreluna-countdown-visibility-switcher' ); ?></label></th>
				<td>
					<select id="acvs_status" name="acvs[status]">
						<option value="active" <?php selected( $meta['status'], 'active' ); ?>><?php esc_html_e( 'Active', 'arreluna-countdown-visibility-switcher' ); ?></option>
						<option value="inactive" <?php selected( $meta['status'], 'inactive' ); ?>><?php esc_html_e( 'Inactive', 'arreluna-countdown-visibility-switcher' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Inactive countdowns do not render on the frontend, but their settings are kept.', 'arreluna-countdown-visibility-switcher' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="acvs_mode"><?php esc_html_e( 'Countdown type', 'arreluna-countdown-visibility-switcher' ); ?></label></th>
				<td>
					<select id="acvs_mode" name="acvs[mode]">
						<option value="evergreen" <?php selected( $meta['mode'], 'evergreen' ); ?>><?php esc_html_e( 'Evergreen', 'arreluna-countdown-visibility-switcher' ); ?></option>
						<option value="fixed" <?php selected( $meta['mode'], 'fixed' ); ?>><?php esc_html_e( 'Fixed date and time', 'arreluna-countdown-visibility-switcher' ); ?></option>
					</select>
					<p class="description acvs-mode-help acvs-mode-help-evergreen"><?php esc_html_e( 'Evergreen starts individually for each visitor and stores the expiration in that visitor\'s browser.', 'arreluna-countdown-visibility-switcher' ); ?></p>
					<p class="description acvs-mode-help acvs-mode-help-fixed"><?php esc_html_e( 'Fixed date and time ends at the same moment for everyone, using the WordPress site timezone.', 'arreluna-countdown-visibility-switcher' ); ?></p>
				</td>
			</tr>
			<tr class="acvs-field-evergreen">
				<th scope="row"><?php esc_html_e( 'Evergreen duration', 'arreluna-countdown-visibility-switcher' ); ?></th>
				<td>
					<input type="number" min="0" name="acvs[days]" value="<?php echo esc_attr( $meta['days'] ); ?>" class="small-text"> <?php esc_html_e( 'days', 'arreluna-countdown-visibility-switcher' ); ?>
					<input type="number" min="0" name="acvs[hours]" value="<?php echo esc_attr( $meta['hours'] ); ?>" class="small-text"> <?php esc_html_e( 'hours', 'arreluna-countdown-visibility-switcher' ); ?>
					<input type="number" min="0" name="acvs[minutes]" value="<?php echo esc_attr( $meta['minutes'] ); ?>" class="small-text"> <?php esc_html_e( 'minutes', 'arreluna-countdown-visibility-switcher' ); ?>
					<input type="number" min="0" name="acvs[seconds]" value="<?php echo esc_attr( $meta['seconds'] ); ?>" class="small-text"> <?php esc_html_e( 'seconds', 'arreluna-countdown-visibility-switcher' ); ?>
					<p class="description"><?php esc_html_e( 'The private browser storage key is generated automatically and is not editable.', 'arreluna-countdown-visibility-switcher' ); ?></p>
				</td>
			</tr>
			<tr class="acvs-field-fixed">
				<th scope="row"><label for="acvs_fixed_datetime"><?php esc_html_e( 'End date and time', 'arreluna-countdown-visibility-switcher' ); ?></label></th>
				<td>
					<input type="datetime-local" id="acvs_fixed_datetime" name="acvs[fixed_datetime]" value="<?php echo esc_attr( $meta['fixed_datetime'] ); ?>">
					<p class="description"><?php esc_html_e( 'Uses the WordPress site timezone.', 'arreluna-countdown-visibility-switcher' ); ?></p>
				</td>
			</tr>
			<tr class="acvs-section-row"><th colspan="2"><h3 class="acvs-admin-section-title"><?php esc_html_e( 'Expiration behavior', 'arreluna-countdown-visibility-switcher' ); ?></h3><p class="acvs-admin-inline-help"><?php esc_html_e( 'Use visibility classes when the page should stay accessible after expiration. Use immediate redirect when expired visitors should not stay on the current page.', 'arreluna-countdown-visibility-switcher' ); ?></p></th></tr>
			<tr>
				<th scope="row"><label for="acvs_action"><?php esc_html_e( 'Action when expired', 'arreluna-countdown-visibility-switcher' ); ?></label></th>
				<td>
					<select id="acvs_action" name="acvs[action]">
						<option value="visibility" <?php selected( $meta['action'], 'visibility' ); ?>><?php esc_html_e( 'Show/hide content with classes', 'arreluna-countdown-visibility-switcher' ); ?></option>
						<option value="redirect" <?php selected( $meta['action'], 'redirect' ); ?>><?php esc_html_e( 'Redirect immediately to a URL', 'arreluna-countdown-visibility-switcher' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Visibility mode toggles the generated before/after classes. Redirect mode sends expired visitors to the URL below immediately.', 'arreluna-countdown-visibility-switcher' ); ?></p>
				</td>
			</tr>
			<tr class="acvs-field-redirect">
				<th scope="row"><label for="acvs_redirect_url"><?php esc_html_e( 'Redirect URL', 'arreluna-countdown-visibility-switcher' ); ?></label></th>
				<td><input type="url" id="acvs_redirect_url" name="acvs[redirect_url]" value="<?php echo esc_url( $meta['redirect_url'] ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Expired visitors are redirected with no delay.', 'arreluna-countdown-visibility-switcher' ); ?></p>
					<?php if ( 'redirect' === $meta['action'] && empty( $meta['redirect_url'] ) ) : ?>
						<p class="acvs-admin-warning"><?php esc_html_e( 'Redirect mode is selected, but no Redirect URL is set. Visitors will not be redirected until you add a valid URL.', 'arreluna-countdown-visibility-switcher' ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
			<tr class="acvs-field-visibility">
				<th scope="row"><label for="acvs_expired_display"><?php esc_html_e( 'Countdown after expiration', 'arreluna-countdown-visibility-switcher' ); ?></label></th>
				<td>
					<select id="acvs_expired_display" name="acvs[expired_display]">
						<option value="zero" <?php selected( $meta['expired_display'], 'zero' ); ?>><?php esc_html_e( 'Keep visible at 00:00:00', 'arreluna-countdown-visibility-switcher' ); ?></option>
						<option value="hide" <?php selected( $meta['expired_display'], 'hide' ); ?>><?php esc_html_e( 'Hide countdown', 'arreluna-countdown-visibility-switcher' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'This option only affects the countdown display itself. Before/after content and redirects still run normally.', 'arreluna-countdown-visibility-switcher' ); ?></p>
				</td>
			</tr>
			<tr class="acvs-section-row"><th colspan="2"><h3 class="acvs-admin-section-title"><?php esc_html_e( 'Display', 'arreluna-countdown-visibility-switcher' ); ?></h3></th></tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Units', 'arreluna-countdown-visibility-switcher' ); ?></th>
				<td>
					<div class="acvs-admin-unit-grid">
						<label><input type="checkbox" name="acvs[show_days]" value="1" <?php checked( $meta['show_days'], 1 ); ?>> <?php esc_html_e( 'Days', 'arreluna-countdown-visibility-switcher' ); ?></label>
						<label><input type="checkbox" name="acvs[show_hours]" value="1" <?php checked( $meta['show_hours'], 1 ); ?>> <?php esc_html_e( 'Hours', 'arreluna-countdown-visibility-switcher' ); ?></label>
						<label><input type="checkbox" name="acvs[show_minutes]" value="1" <?php checked( $meta['show_minutes'], 1 ); ?>> <?php esc_html_e( 'Minutes', 'arreluna-countdown-visibility-switcher' ); ?></label>
						<label><input type="checkbox" name="acvs[show_seconds]" value="1" <?php checked( $meta['show_seconds'], 1 ); ?>> <?php esc_html_e( 'Seconds', 'arreluna-countdown-visibility-switcher' ); ?></label>
					</div>
					<p class="description"><?php esc_html_e( 'At least one unit must remain enabled. If all are unchecked, hours, minutes, and seconds are restored automatically.', 'arreluna-countdown-visibility-switcher' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Labels', 'arreluna-countdown-visibility-switcher' ); ?></th>
				<td>
					<div class="acvs-admin-label-grid">
						<input type="text" name="acvs[label_days]" value="<?php echo esc_attr( $meta['label_days'] ); ?>" placeholder="<?php esc_attr_e( 'Days', 'arreluna-countdown-visibility-switcher' ); ?>" aria-label="<?php esc_attr_e( 'Days label', 'arreluna-countdown-visibility-switcher' ); ?>">
						<input type="text" name="acvs[label_hours]" value="<?php echo esc_attr( $meta['label_hours'] ); ?>" placeholder="<?php esc_attr_e( 'Hours', 'arreluna-countdown-visibility-switcher' ); ?>" aria-label="<?php esc_attr_e( 'Hours label', 'arreluna-countdown-visibility-switcher' ); ?>">
						<input type="text" name="acvs[label_minutes]" value="<?php echo esc_attr( $meta['label_minutes'] ); ?>" placeholder="<?php esc_attr_e( 'Minutes', 'arreluna-countdown-visibility-switcher' ); ?>" aria-label="<?php esc_attr_e( 'Minutes label', 'arreluna-countdown-visibility-switcher' ); ?>">
						<input type="text" name="acvs[label_seconds]" value="<?php echo esc_attr( $meta['label_seconds'] ); ?>" placeholder="<?php esc_attr_e( 'Seconds', 'arreluna-countdown-visibility-switcher' ); ?>" aria-label="<?php esc_attr_e( 'Seconds label', 'arreluna-countdown-visibility-switcher' ); ?>">
					</div>
					<p class="description"><?php esc_html_e( 'Labels are specific to this countdown, so each campaign can use its own language or wording.', 'arreluna-countdown-visibility-switcher' ); ?></p>
				</td>
			</tr>
		</table>
		<?php
	}

	public static function render_usage_box( $post ) {
		if ( 'auto-draft' === $post->post_status ) {
			echo '<p>' . esc_html__( 'Save this countdown to generate its shortcode and classes.', 'arreluna-countdown-visibility-switcher' ) . '</p>';
			return;
		}

		$countdown_id    = absint( $post->ID );
		$shortcode       = '[acvs_countdown id="' . $countdown_id . '"]';
		$before          = 'acvs-before-' . $countdown_id;
		$after           = 'acvs-after-' . $countdown_id;
		$meta            = self::get_meta( $countdown_id );
		$reset_url       = wp_nonce_url( admin_url( 'admin-post.php?action=acvs_reset_countdown&post_id=' . $countdown_id ), 'acvs_reset_' . $countdown_id );
		$before_html     = '<div class="' . $before . '">This content is visible before the countdown ends.</div>';
		$after_html      = '<div class="' . $after . '">This content is visible after the countdown ends.</div>';
		$show_visibility = 'visibility' === $meta['action'];
		$show_redirect   = 'redirect' === $meta['action'];
		$show_reset      = 'evergreen' === $meta['mode'];
		?>
		<div class="acvs-usage-box">
			<p><strong><?php esc_html_e( '1. Add the countdown shortcode', 'arreluna-countdown-visibility-switcher' ); ?></strong></p>
			<input type="text" readonly value="<?php echo esc_attr( $shortcode ); ?>" class="widefat code" onclick="this.select();">
			<p class="description"><?php esc_html_e( 'Paste this shortcode wherever you want the timer to appear.', 'arreluna-countdown-visibility-switcher' ); ?></p>

			<div class="acvs-usage-visibility" style="display: <?php echo $show_visibility ? 'block' : 'none'; ?>;">
				<hr>

				<p><strong><?php esc_html_e( '2. Show content before expiration', 'arreluna-countdown-visibility-switcher' ); ?></strong></p>
				<input type="text" readonly value="<?php echo esc_attr( $before ); ?>" class="widefat code" onclick="this.select();">
				<p class="description"><?php esc_html_e( 'Add this class to buttons, sections, rows, columns, or blocks that should be visible before the countdown ends.', 'arreluna-countdown-visibility-switcher' ); ?></p>

				<p><strong><?php esc_html_e( '3. Show content after expiration', 'arreluna-countdown-visibility-switcher' ); ?></strong></p>
				<input type="text" readonly value="<?php echo esc_attr( $after ); ?>" class="widefat code" onclick="this.select();">
				<p class="description"><?php esc_html_e( 'Add this class to the alternative content that should appear after the countdown ends.', 'arreluna-countdown-visibility-switcher' ); ?></p>

				<hr>

				<p><strong><?php esc_html_e( 'HTML example', 'arreluna-countdown-visibility-switcher' ); ?></strong></p>
				<textarea readonly class="widefat code" rows="5" onclick="this.select();"><?php echo esc_textarea( $shortcode . "\n\n" . $before_html . "\n" . $after_html ); ?></textarea>
				<p class="description"><?php esc_html_e( 'In page builders, use only the class names. You do not need to paste this HTML unless you are editing custom markup.', 'arreluna-countdown-visibility-switcher' ); ?></p>
			</div>

			<div class="acvs-usage-redirect" style="display: <?php echo $show_redirect ? 'block' : 'none'; ?>;">
				<hr>
				<p><strong><?php esc_html_e( 'Expiration action', 'arreluna-countdown-visibility-switcher' ); ?></strong></p>
				<p class="acvs-admin-info"><?php esc_html_e( 'This countdown is set to redirect expired visitors immediately. CSS visibility classes are only needed when using the show/hide content action.', 'arreluna-countdown-visibility-switcher' ); ?></p>
				<?php if ( ! empty( $meta['redirect_url'] ) ) : ?>
					<p class="description"><strong><?php esc_html_e( 'Redirect URL:', 'arreluna-countdown-visibility-switcher' ); ?></strong><br><code><?php echo esc_html( $meta['redirect_url'] ); ?></code></p>
				<?php else : ?>
					<p class="acvs-admin-warning"><?php esc_html_e( 'Redirect mode is selected, but no Redirect URL is set yet.', 'arreluna-countdown-visibility-switcher' ); ?></p>
				<?php endif; ?>
			</div>

			<div class="acvs-usage-evergreen-reset" style="display: <?php echo $show_reset ? 'block' : 'none'; ?>;">
				<hr>

				<p><strong><?php esc_html_e( 'Evergreen reset', 'arreluna-countdown-visibility-switcher' ); ?></strong></p>
				<p class="description"><?php esc_html_e( 'Use this only when you want every visitor to start a new evergreen countdown. It changes the internal browser storage key version immediately; you do not need to save the countdown afterwards.', 'arreluna-countdown-visibility-switcher' ); ?></p>
				<p class="description"><strong><?php esc_html_e( 'Important:', 'arreluna-countdown-visibility-switcher' ); ?></strong> <?php esc_html_e( 'Save your changes before resetting. Resetting does not save the countdown settings.', 'arreluna-countdown-visibility-switcher' ); ?></p>
				<p class="description"><strong><?php esc_html_e( 'Current version:', 'arreluna-countdown-visibility-switcher' ); ?></strong> <?php echo esc_html( absint( $meta['storage_version'] ) ); ?></p>
				<p><a href="<?php echo esc_url( $reset_url ); ?>" class="button" onclick="return confirm('<?php echo esc_js( __( 'This will reset the evergreen countdown immediately. Unsaved changes on this edit screen will not be saved. Save the countdown first if you changed any settings. Continue?', 'arreluna-countdown-visibility-switcher' ) ); ?>');"><?php esc_html_e( 'Reset evergreen timer now', 'arreluna-countdown-visibility-switcher' ); ?></a></p>
			</div>
		</div>
		<?php
	}

	protected static function sanitize_fixed_datetime( $value ) {
		$value = sanitize_text_field( wp_unslash( $value ) );
		return preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value ) ? $value : '';
	}

	protected static function sanitize_redirect_url( $value ) {
		$url = esc_url_raw( wp_unslash( $value ), array( 'http', 'https' ) );
		if ( empty( $url ) ) {
			return '';
		}

		$scheme = wp_parse_url( $url, PHP_URL_SCHEME );
		return in_array( $scheme, array( 'http', 'https' ), true ) ? $url : '';
	}

	public static function save_meta( $post_id, $post ) {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$old = self::get_meta( $post_id );
		$raw = array();

		if ( isset( $_POST['acvs'] ) && is_array( $_POST['acvs'] ) ) {
			$raw = map_deep( wp_unslash( $_POST['acvs'] ), 'sanitize_text_field' );
		}

		$data = array();
		$data['status']          = isset( $raw['status'] ) && in_array( $raw['status'], array( 'active', 'inactive' ), true ) ? $raw['status'] : 'active';
		$data['mode']            = isset( $raw['mode'] ) && in_array( $raw['mode'], array( 'evergreen', 'fixed' ), true ) ? $raw['mode'] : 'evergreen';
		$data['days']            = isset( $raw['days'] ) ? max( 0, absint( $raw['days'] ) ) : 0;
		$data['hours']           = isset( $raw['hours'] ) ? max( 0, absint( $raw['hours'] ) ) : 0;
		$data['minutes']         = isset( $raw['minutes'] ) ? max( 0, absint( $raw['minutes'] ) ) : 0;
		$data['seconds']         = isset( $raw['seconds'] ) ? max( 0, absint( $raw['seconds'] ) ) : 0;
		$data['fixed_datetime']  = isset( $raw['fixed_datetime'] ) ? self::sanitize_fixed_datetime( $raw['fixed_datetime'] ) : '';
		$data['action']          = isset( $raw['action'] ) && in_array( $raw['action'], array( 'visibility', 'redirect' ), true ) ? $raw['action'] : 'visibility';
		$data['redirect_url']    = isset( $raw['redirect_url'] ) ? self::sanitize_redirect_url( $raw['redirect_url'] ) : '';
		$data['expired_display'] = isset( $raw['expired_display'] ) && in_array( $raw['expired_display'], array( 'zero', 'hide' ), true ) ? $raw['expired_display'] : 'zero';
		$data['show_days']       = ! empty( $raw['show_days'] ) ? 1 : 0;
		$data['show_hours']      = ! empty( $raw['show_hours'] ) ? 1 : 0;
		$data['show_minutes']    = ! empty( $raw['show_minutes'] ) ? 1 : 0;
		$data['show_seconds']    = ! empty( $raw['show_seconds'] ) ? 1 : 0;
		$data['label_days']      = isset( $raw['label_days'] ) ? sanitize_text_field( $raw['label_days'] ) : __( 'Days', 'arreluna-countdown-visibility-switcher' );
		$data['label_hours']     = isset( $raw['label_hours'] ) ? sanitize_text_field( $raw['label_hours'] ) : __( 'Hours', 'arreluna-countdown-visibility-switcher' );
		$data['label_minutes']   = isset( $raw['label_minutes'] ) ? sanitize_text_field( $raw['label_minutes'] ) : __( 'Minutes', 'arreluna-countdown-visibility-switcher' );
		$data['label_seconds']   = isset( $raw['label_seconds'] ) ? sanitize_text_field( $raw['label_seconds'] ) : __( 'Seconds', 'arreluna-countdown-visibility-switcher' );
		$data['storage_version'] = isset( $old['storage_version'] ) ? absint( $old['storage_version'] ) : 1;

		if ( ! $data['show_days'] && ! $data['show_hours'] && ! $data['show_minutes'] && ! $data['show_seconds'] ) {
			$data['show_hours']   = 1;
			$data['show_minutes'] = 1;
			$data['show_seconds'] = 1;
		}

		update_post_meta( $post_id, '_acvs_settings', $data );
	}

	public static function columns( $columns ) {
		$columns['acvs_shortcode'] = __( 'Shortcode', 'arreluna-countdown-visibility-switcher' );
		$columns['acvs_type']      = __( 'Type', 'arreluna-countdown-visibility-switcher' );
		$columns['acvs_action']    = __( 'Action', 'arreluna-countdown-visibility-switcher' );
		$columns['acvs_status']    = __( 'Status', 'arreluna-countdown-visibility-switcher' );
		return $columns;
	}

	public static function column_content( $column, $post_id ) {
		$meta = self::get_meta( $post_id );
		if ( 'acvs_shortcode' === $column ) {
			echo '<code>[acvs_countdown id="' . esc_html( absint( $post_id ) ) . '"]</code>';
		}
		if ( 'acvs_type' === $column ) {
			echo esc_html( 'fixed' === $meta['mode'] ? __( 'Fixed date', 'arreluna-countdown-visibility-switcher' ) : __( 'Evergreen', 'arreluna-countdown-visibility-switcher' ) );
		}
		if ( 'acvs_action' === $column ) {
			echo esc_html( 'redirect' === $meta['action'] ? __( 'Redirect', 'arreluna-countdown-visibility-switcher' ) : __( 'Show/hide', 'arreluna-countdown-visibility-switcher' ) );
		}
		if ( 'acvs_status' === $column ) {
			echo esc_html( 'inactive' === $meta['status'] ? __( 'Inactive', 'arreluna-countdown-visibility-switcher' ) : __( 'Active', 'arreluna-countdown-visibility-switcher' ) );
		}
	}

	public static function row_actions( $actions, $post ) {
		if ( self::POST_TYPE !== $post->post_type ) {
			return $actions;
		}
		$meta = self::get_meta( $post->ID );
		if ( 'evergreen' === $meta['mode'] ) {
			$url = wp_nonce_url( admin_url( 'admin-post.php?action=acvs_reset_countdown&post_id=' . absint( $post->ID ) ), 'acvs_reset_' . absint( $post->ID ) );
			$actions['acvs_reset'] = '<a href="' . esc_url( $url ) . '" onclick="return confirm(\'' . esc_js( __( 'This will reset the evergreen countdown immediately for all visitors. Continue?', 'arreluna-countdown-visibility-switcher' ) ) . '\')">' . esc_html__( 'Reset evergreen timer', 'arreluna-countdown-visibility-switcher' ) . '</a>';
		}
		return $actions;
	}

	public static function handle_reset() {
		$post_id = isset( $_GET['post_id'] ) ? absint( wp_unslash( $_GET['post_id'] ) ) : 0;

		if ( ! $post_id || self::POST_TYPE !== get_post_type( $post_id ) ) {
			wp_die( esc_html__( 'Invalid countdown.', 'arreluna-countdown-visibility-switcher' ) );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'You are not allowed to reset this countdown.', 'arreluna-countdown-visibility-switcher' ) );
		}

		check_admin_referer( 'acvs_reset_' . $post_id );

		$meta = self::get_meta( $post_id );
		$meta['storage_version'] = isset( $meta['storage_version'] ) ? absint( $meta['storage_version'] ) + 1 : 2;
		update_post_meta( $post_id, '_acvs_settings', $meta );

		$redirect = get_edit_post_link( $post_id, 'url' );
		if ( ! $redirect ) {
			$redirect = admin_url( 'edit.php?post_type=' . self::POST_TYPE );
		}

		wp_safe_redirect( add_query_arg( 'acvs_reset', '1', $redirect ) );
		exit;
	}
}
