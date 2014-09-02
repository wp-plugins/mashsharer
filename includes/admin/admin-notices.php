<?php
/**
 * Admin Notices
 *
 * @package     MASHSB
 * @subpackage  Admin/Notices
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Admin Messages
 *
 * @since 1.0
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_admin_messages() {
	global $mashsb_options;

	

	/*if ( ( empty( $mashsb_options['purchase_page'] ) || 'trash' == get_post_status( $mashsb_options['purchase_page'] ) ) && current_user_can( 'edit_pages' ) && ! get_user_meta( get_current_user_id(), '_mashsb_set_checkout_dismissed' ) ) {
		echo '<div class="error">';
			echo '<p>' . sprintf( __( 'No checkout page has been configured. Visit <a href="%s">Settings</a> to set one.', 'mashsb' ), admin_url( 'edit.php?post_type=download&page=mashsb-settings' ) ) . '</p>';
			echo '<p><a href="' . add_query_arg( array( 'mashsb_action' => 'dismiss_notices', 'mashsb_notice' => 'set_checkout' ) ) . '">' . __( 'Dismiss Notice', 'mashsb' ) . '</a></p>';
		echo '</div>';
	}*/
 

	//settings_errors( 'mashsb-notices' );
}
add_action( 'admin_notices', 'mashsb_admin_messages' );

/**
 * Admin Add-ons Notices
 *
 * @since 1.0
 * @return void
*/
function mashsb_admin_addons_notices() {
	add_settings_error( 'mashsb-notices', 'mashsb-addons-feed-error', __( 'There seems to be an issue with the server. Please try again in a few minutes.', 'mashsb' ), 'error' );
	settings_errors( 'mashsb-notices' );
}

/**
 * Dismisses admin notices when Dismiss links are clicked
 *
 * @since 1.8
 * @return void
*/
function mashsb_dismiss_notices() {

	$notice = isset( $_GET['mashsb_notice'] ) ? $_GET['mashsb_notice'] : false;
	if( ! $notice )
		return; // No notice, so get out of here

	update_user_meta( get_current_user_id(), '_mashsb_' . $notice . '_dismissed', 1 );
      
	wp_redirect( remove_query_arg( array( 'mashsb_action', 'mashsb_notice' ) ) ); exit;

}
add_action( 'mashsb_dismiss_notices', 'mashsb_dismiss_notices' );