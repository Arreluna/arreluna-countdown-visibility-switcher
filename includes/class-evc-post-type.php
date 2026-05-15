<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EVC_Post_Type {
	const POST_TYPE = 'evc_countdown';
	const NONCE_ACTION = 'evc_save_countdown';
	const NONCE_NAME = 'evc_countdown_nonce';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save_meta' ), 10, 2 );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'column_content' ), 10, 2 );
		add_filter( 'post_row_actions', array( __CLASS__, 'row_actions' ), 10, 2 );
		add_action( 'admin_post_evc_reset_countdown', array( __CLASS__, 'handle_reset' ) );
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
		add_action( 'admin_head-post.php', array( __CLASS__, 'admin_css' ) );
		add_action( 'admin_head-post-new.php', array( __CLASS__, 'admin_css' ) );
	}

	public static function register_post_type() {
		$labels = array(
			'name'          => __( 'Countdowns', 'evergreen-countdown-visibility' ),
			'singular_name' => __( 'Countdown', 'evergreen-countdown-visibility' ),
			'add_new_item'  => __( 'Add New Countdown', 'evergreen-countdown-visibility' ),
			'edit_item'     => __( 'Edit Countdown', 'evergreen-countdown-visibility' ),
			'menu_name'     => __( 'Countdowns', 'evergreen-countdown-visibility' ),
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
			'label_days'       => __( 'Days', 'evergreen-countdown-visibility' ),
			'label_hours'      => __( 'Hours', 'evergreen-countdown-visibility' ),
			'label_minutes'    => __( 'Minutes', 'evergreen-countdown-visibility' ),
			'label_seconds'    => __( 'Seconds', 'evergreen-countdown-visibility' ),
			'storage_version'  => 1,
		);
	}

	public static function get_meta( $post_id ) {
		$defaults = self::get_defaults();
		$stored   = get_post_meta( $post_id, '_evc_settings', true );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
		return wp_parse_args( $stored, $defaults );
	}

	public static function add_meta_boxes() {
		add_meta_box( 'evc_settings', __( 'Countdown setup', 'evergreen-countdown-visibility' ), array( __CLASS__, 'render_settings_box' ), self::POST_TYPE, 'normal', 'high' );
		add_meta_box( 'evc_usage', __( 'Shortcode and classes', 'evergreen-countdown-visibility' ), array( __CLASS__, 'render_usage_box' ), self::POST_TYPE, 'side', 'high' );
	}


	public static function admin_notices() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || self::POST_TYPE !== $screen->post_type ) {
			return;
		}
		if ( empty( $_GET['evc_reset'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Evergreen countdown reset applied immediately. You do not need to save this countdown again.', 'evergreen-countdown-visibility' ) . '</p></div>';
	}

	public static function admin_css() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || self::POST_TYPE !== $screen->post_type ) {
			return;
		}
		?>
		<style>
			.evc-admin-table .evc-section-row th,
			.evc-admin-table .evc-section-row td {
				padding-top: 22px;
				padding-bottom: 8px;
				border-top: 1px solid #dcdcde;
			}
			.evc-admin-section-title {
				margin: 0;
				font-size: 14px;
				font-weight: 700;
				text-transform: uppercase;
				letter-spacing: .02em;
			}
			.evc-admin-inline-help {
				max-width: 760px;
				margin: 8px 0 0;
				color: #646970;
			}
			.evc-admin-table input[type="number"].small-text {
				width: 72px;
			}
			.evc-admin-unit-grid,
			.evc-admin-label-grid {
				display: grid;
				grid-template-columns: repeat(4, minmax(110px, 1fr));
				gap: 8px 12px;
				max-width: 760px;
			}
			.evc-admin-label-grid input {
				width: 100%;
			}
			.evc-usage-box input.code,
			.evc-usage-box textarea.code {
				font-size: 12px;
			}
			.evc-usage-box .evc-copy-field {
				margin-bottom: 8px;
			}
			.evc-admin-warning {
				padding: 8px 10px;
				border-left: 4px solid #d63638;
				background: #fcf0f1;
			}
			.evc-admin-info {
				padding: 8px 10px;
				border-left: 4px solid #72aee6;
				background: #f0f6fc;
			}
			@media (max-width: 782px) {
				.evc-admin-unit-grid,
				.evc-admin-label-grid {
					grid-template-columns: 1fr;
				}
			}
		</style>
		<?php
	}

	public static function render_settings_box( $post ) {
		$meta = self::get_meta( $post->ID );
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		?>
		<p class="description"><?php esc_html_e( 'Configure the timer, choose what happens when it expires, and customize the visible units for this countdown.', 'evergreen-countdown-visibility' ); ?></p>
		<table class="form-table evc-admin-table" role="presentation">
			<tr class="evc-section-row"><th colspan="2"><h3 class="evc-admin-section-title"><?php esc_html_e( 'Timer', 'evergreen-countdown-visibility' ); ?></h3></th></tr>
			<tr>
				<th scope="row"><label for="evc_status"><?php esc_html_e( 'Status', 'evergreen-countdown-visibility' ); ?></label></th>
				<td>
					<select id="evc_status" name="evc[status]">
						<option value="active" <?php selected( $meta['status'], 'active' ); ?>><?php esc_html_e( 'Active', 'evergreen-countdown-visibility' ); ?></option>
						<option value="inactive" <?php selected( $meta['status'], 'inactive' ); ?>><?php esc_html_e( 'Inactive', 'evergreen-countdown-visibility' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Inactive countdowns do not render on the frontend, but their settings are kept.', 'evergreen-countdown-visibility' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="evc_mode"><?php esc_html_e( 'Countdown type', 'evergreen-countdown-visibility' ); ?></label></th>
				<td>
					<select id="evc_mode" name="evc[mode]">
						<option value="evergreen" <?php selected( $meta['mode'], 'evergreen' ); ?>><?php esc_html_e( 'Evergreen', 'evergreen-countdown-visibility' ); ?></option>
						<option value="fixed" <?php selected( $meta['mode'], 'fixed' ); ?>><?php esc_html_e( 'Fixed date and time', 'evergreen-countdown-visibility' ); ?></option>
					</select>
					<p class="description evc-mode-help evc-mode-help-evergreen"><?php esc_html_e( 'Evergreen starts individually for each visitor and stores the expiration in that visitor’s browser.', 'evergreen-countdown-visibility' ); ?></p>
					<p class="description evc-mode-help evc-mode-help-fixed"><?php esc_html_e( 'Fixed date and time ends at the same moment for everyone, using the WordPress site timezone.', 'evergreen-countdown-visibility' ); ?></p>
				</td>
			</tr>
			<tr class="evc-field-evergreen">
				<th scope="row"><?php esc_html_e( 'Evergreen duration', 'evergreen-countdown-visibility' ); ?></th>
				<td>
					<input type="number" min="0" name="evc[days]" value="<?php echo esc_attr( $meta['days'] ); ?>" class="small-text"> <?php esc_html_e( 'days', 'evergreen-countdown-visibility' ); ?>
					<input type="number" min="0" name="evc[hours]" value="<?php echo esc_attr( $meta['hours'] ); ?>" class="small-text"> <?php esc_html_e( 'hours', 'evergreen-countdown-visibility' ); ?>
					<input type="number" min="0" name="evc[minutes]" value="<?php echo esc_attr( $meta['minutes'] ); ?>" class="small-text"> <?php esc_html_e( 'minutes', 'evergreen-countdown-visibility' ); ?>
					<input type="number" min="0" name="evc[seconds]" value="<?php echo esc_attr( $meta['seconds'] ); ?>" class="small-text"> <?php esc_html_e( 'seconds', 'evergreen-countdown-visibility' ); ?>
					<p class="description"><?php esc_html_e( 'The private browser storage key is generated automatically and is not editable.', 'evergreen-countdown-visibility' ); ?></p>
				</td>
			</tr>
			<tr class="evc-field-fixed">
				<th scope="row"><label for="evc_fixed_datetime"><?php esc_html_e( 'End date and time', 'evergreen-countdown-visibility' ); ?></label></th>
				<td>
					<input type="datetime-local" id="evc_fixed_datetime" name="evc[fixed_datetime]" value="<?php echo esc_attr( $meta['fixed_datetime'] ); ?>">
					<p class="description"><?php esc_html_e( 'Uses the WordPress site timezone.', 'evergreen-countdown-visibility' ); ?></p>
				</td>
			</tr>
			<tr class="evc-section-row"><th colspan="2"><h3 class="evc-admin-section-title"><?php esc_html_e( 'Expiration behavior', 'evergreen-countdown-visibility' ); ?></h3><p class="evc-admin-inline-help"><?php esc_html_e( 'Use visibility classes when the page should stay accessible after expiration. Use immediate redirect when expired visitors should not stay on the current page.', 'evergreen-countdown-visibility' ); ?></p></th></tr>
			<tr>
				<th scope="row"><label for="evc_action"><?php esc_html_e( 'Action when expired', 'evergreen-countdown-visibility' ); ?></label></th>
				<td>
					<select id="evc_action" name="evc[action]">
						<option value="visibility" <?php selected( $meta['action'], 'visibility' ); ?>><?php esc_html_e( 'Show/hide content with classes', 'evergreen-countdown-visibility' ); ?></option>
						<option value="redirect" <?php selected( $meta['action'], 'redirect' ); ?>><?php esc_html_e( 'Redirect immediately to a URL', 'evergreen-countdown-visibility' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'Visibility mode toggles the generated before/after classes. Redirect mode sends expired visitors to the URL below immediately.', 'evergreen-countdown-visibility' ); ?></p>
				</td>
			</tr>
			<tr class="evc-field-redirect">
				<th scope="row"><label for="evc_redirect_url"><?php esc_html_e( 'Redirect URL', 'evergreen-countdown-visibility' ); ?></label></th>
				<td><input type="url" id="evc_redirect_url" name="evc[redirect_url]" value="<?php echo esc_url( $meta['redirect_url'] ); ?>" class="regular-text">
					<p class="description"><?php esc_html_e( 'Expired visitors are redirected with no delay.', 'evergreen-countdown-visibility' ); ?></p>
					<?php if ( 'redirect' === $meta['action'] && empty( $meta['redirect_url'] ) ) : ?>
						<p class="evc-admin-warning"><?php esc_html_e( 'Redirect mode is selected, but no Redirect URL is set. Visitors will not be redirected until you add a valid URL.', 'evergreen-countdown-visibility' ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
			<tr class="evc-field-visibility">
				<th scope="row"><label for="evc_expired_display"><?php esc_html_e( 'Countdown after expiration', 'evergreen-countdown-visibility' ); ?></label></th>
				<td>
					<select id="evc_expired_display" name="evc[expired_display]">
						<option value="zero" <?php selected( $meta['expired_display'], 'zero' ); ?>><?php esc_html_e( 'Keep visible at 00:00:00', 'evergreen-countdown-visibility' ); ?></option>
						<option value="hide" <?php selected( $meta['expired_display'], 'hide' ); ?>><?php esc_html_e( 'Hide countdown', 'evergreen-countdown-visibility' ); ?></option>
					</select>
					<p class="description"><?php esc_html_e( 'This option only affects the countdown display itself. Before/after content and redirects still run normally.', 'evergreen-countdown-visibility' ); ?></p>
				</td>
			</tr>
			<tr class="evc-section-row"><th colspan="2"><h3 class="evc-admin-section-title"><?php esc_html_e( 'Display', 'evergreen-countdown-visibility' ); ?></h3></th></tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Units', 'evergreen-countdown-visibility' ); ?></th>
				<td>
					<div class="evc-admin-unit-grid">
						<label><input type="checkbox" name="evc[show_days]" value="1" <?php checked( $meta['show_days'], 1 ); ?>> <?php esc_html_e( 'Days', 'evergreen-countdown-visibility' ); ?></label>
						<label><input type="checkbox" name="evc[show_hours]" value="1" <?php checked( $meta['show_hours'], 1 ); ?>> <?php esc_html_e( 'Hours', 'evergreen-countdown-visibility' ); ?></label>
						<label><input type="checkbox" name="evc[show_minutes]" value="1" <?php checked( $meta['show_minutes'], 1 ); ?>> <?php esc_html_e( 'Minutes', 'evergreen-countdown-visibility' ); ?></label>
						<label><input type="checkbox" name="evc[show_seconds]" value="1" <?php checked( $meta['show_seconds'], 1 ); ?>> <?php esc_html_e( 'Seconds', 'evergreen-countdown-visibility' ); ?></label>
					</div>
					<p class="description"><?php esc_html_e( 'At least one unit must remain enabled. If all are unchecked, hours, minutes, and seconds are restored automatically.', 'evergreen-countdown-visibility' ); ?></p>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Labels', 'evergreen-countdown-visibility' ); ?></th>
				<td>
					<div class="evc-admin-label-grid">
						<input type="text" name="evc[label_days]" value="<?php echo esc_attr( $meta['label_days'] ); ?>" placeholder="<?php esc_attr_e( 'Days', 'evergreen-countdown-visibility' ); ?>" aria-label="<?php esc_attr_e( 'Days label', 'evergreen-countdown-visibility' ); ?>">
						<input type="text" name="evc[label_hours]" value="<?php echo esc_attr( $meta['label_hours'] ); ?>" placeholder="<?php esc_attr_e( 'Hours', 'evergreen-countdown-visibility' ); ?>" aria-label="<?php esc_attr_e( 'Hours label', 'evergreen-countdown-visibility' ); ?>">
						<input type="text" name="evc[label_minutes]" value="<?php echo esc_attr( $meta['label_minutes'] ); ?>" placeholder="<?php esc_attr_e( 'Minutes', 'evergreen-countdown-visibility' ); ?>" aria-label="<?php esc_attr_e( 'Minutes label', 'evergreen-countdown-visibility' ); ?>">
						<input type="text" name="evc[label_seconds]" value="<?php echo esc_attr( $meta['label_seconds'] ); ?>" placeholder="<?php esc_attr_e( 'Seconds', 'evergreen-countdown-visibility' ); ?>" aria-label="<?php esc_attr_e( 'Seconds label', 'evergreen-countdown-visibility' ); ?>">
					</div>
					<p class="description"><?php esc_html_e( 'Labels are specific to this countdown, so each campaign can use its own language or wording.', 'evergreen-countdown-visibility' ); ?></p>
				</td>
			</tr>
		</table>
		<script>
		(function(){
			function toggle(){
				var mode=document.getElementById('evc_mode').value;
				var action=document.getElementById('evc_action').value;
				document.querySelectorAll('.evc-field-evergreen').forEach(function(el){el.style.display=mode==='evergreen'?'table-row':'none';});
				document.querySelectorAll('.evc-field-fixed').forEach(function(el){el.style.display=mode==='fixed'?'table-row':'none';});
				document.querySelectorAll('.evc-field-redirect').forEach(function(el){el.style.display=action==='redirect'?'table-row':'none';});
				document.querySelectorAll('.evc-field-visibility').forEach(function(el){el.style.display=action==='visibility'?'table-row':'none';});
				document.querySelectorAll('.evc-mode-help-evergreen').forEach(function(el){el.style.display=mode==='evergreen'?'block':'none';});
				document.querySelectorAll('.evc-mode-help-fixed').forEach(function(el){el.style.display=mode==='fixed'?'block':'none';});
				document.querySelectorAll('.evc-usage-visibility').forEach(function(el){el.style.display=action==='visibility'?'block':'none';});
				document.querySelectorAll('.evc-usage-redirect').forEach(function(el){el.style.display=action==='redirect'?'block':'none';});
				document.querySelectorAll('.evc-usage-evergreen-reset').forEach(function(el){el.style.display=mode==='evergreen'?'block':'none';});
			}
			document.getElementById('evc_mode').addEventListener('change',toggle);
			document.getElementById('evc_action').addEventListener('change',toggle);
			toggle();
		})();
		</script>
		<?php
	}

	public static function render_usage_box( $post ) {
		if ( 'auto-draft' === $post->post_status ) {
			echo '<p>' . esc_html__( 'Save this countdown to generate its shortcode and classes.', 'evergreen-countdown-visibility' ) . '</p>';
			return;
		}

		$countdown_id = absint( $post->ID );
		$shortcode    = '[evc_countdown id="' . $countdown_id . '"]';
		$before       = 'evc-before-' . $countdown_id;
		$after        = 'evc-after-' . $countdown_id;
		$meta         = self::get_meta( $countdown_id );
		$reset_url    = wp_nonce_url( admin_url( 'admin-post.php?action=evc_reset_countdown&post_id=' . $countdown_id ), 'evc_reset_' . $countdown_id );
		$before_html  = '<div class="' . $before . '">This content is visible before the countdown ends.</div>';
		$after_html   = '<div class="' . $after . '">This content is visible after the countdown ends.</div>';
		$show_visibility = 'visibility' === $meta['action'];
		$show_redirect   = 'redirect' === $meta['action'];
		$show_reset      = 'evergreen' === $meta['mode'];
		?>
		<div class="evc-usage-box">
			<p><strong><?php esc_html_e( '1. Add the countdown shortcode', 'evergreen-countdown-visibility' ); ?></strong></p>
			<input type="text" readonly value="<?php echo esc_attr( $shortcode ); ?>" class="widefat code" onclick="this.select();">
			<p class="description"><?php esc_html_e( 'Paste this shortcode wherever you want the timer to appear.', 'evergreen-countdown-visibility' ); ?></p>

			<div class="evc-usage-visibility" style="display: <?php echo $show_visibility ? 'block' : 'none'; ?>;">
				<hr>

				<p><strong><?php esc_html_e( '2. Show content before expiration', 'evergreen-countdown-visibility' ); ?></strong></p>
				<input type="text" readonly value="<?php echo esc_attr( $before ); ?>" class="widefat code" onclick="this.select();">
				<p class="description"><?php esc_html_e( 'Add this class to buttons, sections, rows, columns, or blocks that should be visible before the countdown ends.', 'evergreen-countdown-visibility' ); ?></p>

				<p><strong><?php esc_html_e( '3. Show content after expiration', 'evergreen-countdown-visibility' ); ?></strong></p>
				<input type="text" readonly value="<?php echo esc_attr( $after ); ?>" class="widefat code" onclick="this.select();">
				<p class="description"><?php esc_html_e( 'Add this class to the alternative content that should appear after the countdown ends.', 'evergreen-countdown-visibility' ); ?></p>

				<hr>

				<p><strong><?php esc_html_e( 'HTML example', 'evergreen-countdown-visibility' ); ?></strong></p>
				<textarea readonly class="widefat code" rows="5" onclick="this.select();"><?php echo esc_textarea( $shortcode . "\n\n" . $before_html . "\n" . $after_html ); ?></textarea>
				<p class="description"><?php esc_html_e( 'In page builders, use only the class names. You do not need to paste this HTML unless you are editing custom markup.', 'evergreen-countdown-visibility' ); ?></p>
			</div>

			<div class="evc-usage-redirect" style="display: <?php echo $show_redirect ? 'block' : 'none'; ?>;">
				<hr>
				<p><strong><?php esc_html_e( 'Expiration action', 'evergreen-countdown-visibility' ); ?></strong></p>
				<p class="evc-admin-info"><?php esc_html_e( 'This countdown is set to redirect expired visitors immediately. CSS visibility classes are only needed when using the show/hide content action.', 'evergreen-countdown-visibility' ); ?></p>
				<?php if ( ! empty( $meta['redirect_url'] ) ) : ?>
					<p class="description"><strong><?php esc_html_e( 'Redirect URL:', 'evergreen-countdown-visibility' ); ?></strong><br><code><?php echo esc_html( $meta['redirect_url'] ); ?></code></p>
				<?php else : ?>
					<p class="evc-admin-warning"><?php esc_html_e( 'Redirect mode is selected, but no Redirect URL is set yet.', 'evergreen-countdown-visibility' ); ?></p>
				<?php endif; ?>
			</div>

			<div class="evc-usage-evergreen-reset" style="display: <?php echo $show_reset ? 'block' : 'none'; ?>;">
				<hr>

				<p><strong><?php esc_html_e( 'Evergreen reset', 'evergreen-countdown-visibility' ); ?></strong></p>
				<p class="description"><?php esc_html_e( 'Use this only when you want every visitor to start a new evergreen countdown. It changes the internal browser storage key version immediately; you do not need to save the countdown afterwards.', 'evergreen-countdown-visibility' ); ?></p>
				<p class="description"><strong><?php esc_html_e( 'Important:', 'evergreen-countdown-visibility' ); ?></strong> <?php esc_html_e( 'Save your changes before resetting. Resetting does not save the countdown settings.', 'evergreen-countdown-visibility' ); ?></p>
				<p class="description"><strong><?php esc_html_e( 'Current version:', 'evergreen-countdown-visibility' ); ?></strong> <?php echo esc_html( absint( $meta['storage_version'] ) ); ?></p>
				<p><a href="<?php echo esc_url( $reset_url ); ?>" class="button" onclick="return confirm('<?php echo esc_js( __( 'This will reset the evergreen countdown immediately. Unsaved changes on this edit screen will not be saved. Save the countdown first if you changed any settings. Continue?', 'evergreen-countdown-visibility' ) ); ?>');"><?php esc_html_e( 'Reset evergreen timer now', 'evergreen-countdown-visibility' ); ?></a></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Sanitizes the fixed datetime value from a datetime-local field.
	 *
	 * @param string $value Raw value.
	 * @return string Sanitized datetime value, or empty string when invalid.
	 */
	protected static function sanitize_fixed_datetime( $value ) {
		$value = sanitize_text_field( wp_unslash( $value ) );
		return preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value ) ? $value : '';
	}

	/**
	 * Sanitizes redirect URLs for frontend redirects.
	 *
	 * Only http and https URLs are accepted. Invalid or unsupported URLs are
	 * stored as empty strings so frontend redirects fail closed.
	 *
	 * @param string $value Raw URL.
	 * @return string Sanitized URL or empty string.
	 */
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

		if ( isset( $_POST['evc'] ) && is_array( $_POST['evc'] ) ) {
			$raw = map_deep( wp_unslash( $_POST['evc'] ), 'sanitize_text_field' );
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
		$data['label_days']      = isset( $raw['label_days'] ) ? sanitize_text_field( $raw['label_days'] ) : __( 'Days', 'evergreen-countdown-visibility' );
		$data['label_hours']     = isset( $raw['label_hours'] ) ? sanitize_text_field( $raw['label_hours'] ) : __( 'Hours', 'evergreen-countdown-visibility' );
		$data['label_minutes']   = isset( $raw['label_minutes'] ) ? sanitize_text_field( $raw['label_minutes'] ) : __( 'Minutes', 'evergreen-countdown-visibility' );
		$data['label_seconds']   = isset( $raw['label_seconds'] ) ? sanitize_text_field( $raw['label_seconds'] ) : __( 'Seconds', 'evergreen-countdown-visibility' );
		$data['storage_version'] = isset( $old['storage_version'] ) ? absint( $old['storage_version'] ) : 1;

		if ( ! $data['show_days'] && ! $data['show_hours'] && ! $data['show_minutes'] && ! $data['show_seconds'] ) {
			$data['show_hours']   = 1;
			$data['show_minutes'] = 1;
			$data['show_seconds'] = 1;
		}

		update_post_meta( $post_id, '_evc_settings', $data );
	}

	public static function columns( $columns ) {
		$columns['evc_shortcode'] = __( 'Shortcode', 'evergreen-countdown-visibility' );
		$columns['evc_type']      = __( 'Type', 'evergreen-countdown-visibility' );
		$columns['evc_action']    = __( 'Action', 'evergreen-countdown-visibility' );
		$columns['evc_status']    = __( 'Status', 'evergreen-countdown-visibility' );
		return $columns;
	}

	public static function column_content( $column, $post_id ) {
		$meta = self::get_meta( $post_id );
		if ( 'evc_shortcode' === $column ) {
			echo '<code>[evc_countdown id="' . esc_html( absint( $post_id ) ) . '"]</code>';
		}
		if ( 'evc_type' === $column ) {
			echo esc_html( 'fixed' === $meta['mode'] ? __( 'Fixed date', 'evergreen-countdown-visibility' ) : __( 'Evergreen', 'evergreen-countdown-visibility' ) );
		}
		if ( 'evc_action' === $column ) {
			echo esc_html( 'redirect' === $meta['action'] ? __( 'Redirect', 'evergreen-countdown-visibility' ) : __( 'Show/hide', 'evergreen-countdown-visibility' ) );
		}
		if ( 'evc_status' === $column ) {
			echo esc_html( 'inactive' === $meta['status'] ? __( 'Inactive', 'evergreen-countdown-visibility' ) : __( 'Active', 'evergreen-countdown-visibility' ) );
		}
	}

	public static function row_actions( $actions, $post ) {
		if ( self::POST_TYPE !== $post->post_type ) {
			return $actions;
		}
		$meta = self::get_meta( $post->ID );
		if ( 'evergreen' === $meta['mode'] ) {
			$url = wp_nonce_url( admin_url( 'admin-post.php?action=evc_reset_countdown&post_id=' . absint( $post->ID ) ), 'evc_reset_' . absint( $post->ID ) );
			$actions['evc_reset'] = '<a href="' . esc_url( $url ) . '" onclick="return confirm(\'' . esc_js( __( 'This will reset the evergreen countdown immediately for all visitors. Continue?', 'evergreen-countdown-visibility' ) ) . '\')">' . esc_html__( 'Reset evergreen timer', 'evergreen-countdown-visibility' ) . '</a>';
		}
		return $actions;
	}

	public static function handle_reset() {
		$post_id = isset( $_GET['post_id'] ) ? absint( wp_unslash( $_GET['post_id'] ) ) : 0;

		if ( ! $post_id || self::POST_TYPE !== get_post_type( $post_id ) ) {
			wp_die( esc_html__( 'Invalid countdown.', 'evergreen-countdown-visibility' ) );
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( esc_html__( 'You are not allowed to reset this countdown.', 'evergreen-countdown-visibility' ) );
		}

		check_admin_referer( 'evc_reset_' . $post_id );

		$meta = self::get_meta( $post_id );
		$meta['storage_version'] = isset( $meta['storage_version'] ) ? absint( $meta['storage_version'] ) + 1 : 2;
		update_post_meta( $post_id, '_evc_settings', $meta );

		$redirect = get_edit_post_link( $post_id, 'url' );
		if ( ! $redirect ) {
			$redirect = admin_url( 'edit.php?post_type=' . self::POST_TYPE );
		}

		wp_safe_redirect( add_query_arg( 'evc_reset', '1', $redirect ) );
		exit;
	}
}
