<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'evc_style_settings' );

global $wpdb;
$wpdb->delete( $wpdb->postmeta, array( 'meta_key' => '_evc_settings' ), array( '%s' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Uninstall routine; caching is irrelevant when permanently deleting plugin data.
