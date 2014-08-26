<?php
/**
 * Install Function
 *
 * @package     MASHSB
 * @subpackage  Functions/Install
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Install
 *
 * Runs on plugin install to populates the settings fields for those plugin
 * pages. After successful install, the user is redirected to the MASHSB Welcome
 * screen.
 *
 * @since 2.0
 * @global $wpdb
 * @global $mashsb_options
 * @global $wp_version
 * @return void
 */


function mashsb_install() {
	global $wpdb, $mashsb_options, $wp_version;

	// Add Upgraded From Option
	$current_version = get_option( 'mashsb_version' );
	if ( $current_version ) {
		update_option( 'mashsb_version_upgraded_from', $current_version );
	}

	// Bail if activating from network, or bulk
	if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
		return;
	}
        // Add the current version
        update_option( 'mashsb_version', EDD_VERSION );
	// Add the transient to redirect
	set_transient( '_mashsb_activation_redirect', true, 30 );
                /* create database table */
        	$sql = "CREATE TABLE ".MASHSB_TABLE." (
                ID int(11) NOT NULL AUTO_INCREMENT,
                URL varchar(250) NULL,
                TOTAL_SHARES int(20) NOT NULL,
                CHECK_TIMESTAMP TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY  (ID))";
                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                dbDelta( $sql );
                
    /* Setup some default options
     * Store our initial social networks in separate option row.
     * For easier modification and to prevent some trouble
     */
    $networks = array(
        'Facebook',
        'Twitter',
        'Subscribe'
    );

    if (false == get_option('mashsb_networks')) {
        update_option('mashsb_networks', $networks);
        /* Uncomment for debug */
        //update_option('mashsb_networks', $networks);
    }

}
register_activation_hook( MASHSB_PLUGIN_FILE, 'mashsb_install' );

/**
 * Post-installation
 *
 * Runs just after plugin installation and exposes the
 * mashsb_after_install hook.
 *
 * @since 2.0
 * @return void
 */
function mashsb_after_install() {

	if ( ! is_admin() ) {
		return;
	}

	$activation_pages = get_transient( '_mashsb_activation_pages' );

	// Exit if not in admin or the transient doesn't exist
	if ( false === $activation_pages ) {
		return;
	}

	// Delete the transient
	delete_transient( '_mashsb_activation_pages' );

	do_action( 'mashsb_after_install', $activation_pages );
}
add_action( 'admin_init', 'mashsb_after_install' );