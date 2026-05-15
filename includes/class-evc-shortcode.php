<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class EVC_Shortcode {
	public static function init() {
		add_shortcode( 'evc_countdown', array( __CLASS__, 'render' ) );
	}

	public static function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'evc_countdown'
		);

		$post_id = absint( $atts['id'] );
		if ( ! $post_id || EVC_Post_Type::POST_TYPE !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) {
			return '';
		}

		$meta = EVC_Post_Type::get_meta( $post_id );
		if ( isset( $meta['status'] ) && 'inactive' === $meta['status'] ) {
			return current_user_can( 'edit_post', $post_id ) ? '<!-- EVC countdown is inactive. -->' : '';
		}

		EVC_Assets::enqueue_frontend();
		$duration = absint( $meta['days'] ) * DAY_IN_SECONDS + absint( $meta['hours'] ) * HOUR_IN_SECONDS + absint( $meta['minutes'] ) * MINUTE_IN_SECONDS + absint( $meta['seconds'] );
		if ( $duration < 1 ) {
			$duration = HOUR_IN_SECONDS;
		}

		$fixed_ts = 0;
		if ( 'fixed' === $meta['mode'] && ! empty( $meta['fixed_datetime'] ) ) {
			$tz = wp_timezone();
			try {
				$dt = new DateTimeImmutable( $meta['fixed_datetime'], $tz );
				$fixed_ts = $dt->getTimestamp() * 1000;
			} catch ( Exception $e ) {
				$fixed_ts = 0;
			}
		}

		$config = array(
			'id'             => $post_id,
			'mode'           => $meta['mode'],
			'duration'       => $duration,
			'fixedTimestamp' => $fixed_ts,
			'action'         => $meta['action'],
			'redirectUrl'    => $meta['redirect_url'],
			'expiredDisplay' => $meta['expired_display'],
			'storageKey'     => 'evc_expiration_' . $post_id . '_v' . absint( $meta['storage_version'] ),
			'beforeClass'    => 'evc-before-' . $post_id,
			'afterClass'     => 'evc-after-' . $post_id,
		);

		$units = array(
			'days'    => array( 'show' => ! empty( $meta['show_days'] ), 'label' => $meta['label_days'] ),
			'hours'   => array( 'show' => ! empty( $meta['show_hours'] ), 'label' => $meta['label_hours'] ),
			'minutes' => array( 'show' => ! empty( $meta['show_minutes'] ), 'label' => $meta['label_minutes'] ),
			'seconds' => array( 'show' => ! empty( $meta['show_seconds'] ), 'label' => $meta['label_seconds'] ),
		);

		ob_start();
		?>
		<div class="evc-countdown" data-evc-config="<?php echo esc_attr( wp_json_encode( $config ) ); ?>" aria-live="polite">
			<div class="evc-countdown__wrapper">
				<?php foreach ( $units as $key => $unit ) : ?>
					<?php if ( $unit['show'] ) : ?>
						<div class="evc-countdown__block" data-evc-unit="<?php echo esc_attr( $key ); ?>">
							<span class="evc-countdown__number">00</span>
							<small class="evc-countdown__label"><?php echo esc_html( $unit['label'] ); ?></small>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
