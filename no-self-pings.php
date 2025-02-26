<?php
/*
Plugin Name: No Self Pings
Plugin URI: https://github.com/dartiss/no-self-ping
Description: Keeps WordPress from sending pings to your own site.
Version: 1.1.2
Author: Michael D. Adams
Author URI: http://blogwaffe.com/
Text Domain: no-self-ping
*/

/**
* No Self Pings
*
* PKeeps WordPress from sending pings to your own site.
*
* @package  no-self-ping
* @since    0.1
*/

/**
* Add meta to plugin details
*
* Add options to plugin meta line
*
* @since    1.0
*
* @param    string  $links  Current links
* @param    string  $file   File in use
* @return   string          Links, now with settings added
*/

function no_self_ping_plugin_meta( $links, $file ) {

	if ( false !== strpos( $file, 'no-self-pings.php' ) ) {

		$links = array_merge( $links, array( '<a href="https://github.com/dartiss/no-self-ping">' . __( 'Github', 'no-self-ping' ) . '</a>' ) );

		$links = array_merge( $links, array( '<a href="https://wordpress.org/support/plugin/no-self-ping">' . __( 'Support', 'no-self-ping' ) . '</a>' ) );
	}

	return $links;
}

add_filter( 'plugin_row_meta', 'no_self_ping_plugin_meta', 10, 2 );

/**
* Process Pings
*
* Before pinging the curated URLs, remove any that belong to this installation
*
* @since    0.1
*/

function no_self_ping( &$links ) {

	$home = esc_url( home_url() );

	// Get any additional URLs and explode into an array

	$extra_urls = sanitize_option( 'ping_sites', get_option( 'no_self_pings_option', '' ) );

	if ( is_array( $extra_urls ) ) {
		$url_array = explode( PHP_EOL, $extra_urls );
	} else {
		$url_array = array();
	}

	// Process each link in the content and remove is it matches the current site URL or one of
	// the additional URLs provided

	foreach ( $links as $l => $link ) {

		if ( 0 === strpos( $link, $home ) ) {
			unset( $links[ $l ] );
		}

		foreach ( $url_array as $url ) {

			$url = trim( $url );
			if ( 0 === strpos( $link, $url ) && '' !== $url ) {
				unset( $links[ $l ] );
			}
		}
	}
}

add_action( 'pre_ping', 'no_self_ping' );

/**
* Add to settings
*
* Add a field to the Discussion settings screens to capture additional URLs
*
* @since    1.1
*/

function no_self_pings_settings_init() {

	add_settings_section( 'no_self_pings_section', __( 'No Self Pings', 'no-self-ping' ), 'no_self_pings_section_callback', 'discussion' );

	add_settings_field( 'no_self_pings_option', __( 'Additional URLs', 'no-self-ping' ), 'no_self_pings_setting_callback', 'discussion', 'no_self_pings_section', array( 'label_for' => 'no_self_pings_option' ) );

	register_setting( 'discussion', 'no_self_pings_option' );
}

add_action( 'admin_init', 'no_self_pings_settings_init' );

/**
* Sectoin callback
*
* Create the new section that we've added to the Discussion settings screen
*
* @since    1.1
*/

function no_self_pings_section_callback() {

	/* translators: %s: URL of website */
	esc_attr( sprintf( __( 'By default, No Self Pings will exclude pings for this site (%s) but you can supply additional URLs below. Separate multiple URLs with line breaks.', 'no-self-ping' ), esc_url( home_url() ) ) );

}

/**
* Settings callback
*
* Output the settings field
*
* @since    1.1
*/

function no_self_pings_setting_callback() {

	$urls = sanitize_option( 'ping_sites', get_option( 'no_self_pings_option', '' ) );

	echo '<label>Additional URLs<textarea name="no_self_pings_option" rows="3" class="large-text code">' . esc_attr( $urls ) . '</textarea></label>';

}
