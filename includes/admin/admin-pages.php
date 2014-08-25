<?php
/**
 * Admin Pages
 *
 * @package     MASHSB
 * @subpackage  Admin/Pages
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Creates the admin submenu pages under the ShareAfterLike menu and assigns their
 * links to global variables
 *
 * @since 1.0
 * @global $mashsb_discounts_page
 * @global $mashsb_payments_page
 * @global $mashsb_settings_page
 * @global $mashsb_reports_page
 * @global $mashsb_add_ons_page
 * @global $mashsb_settings_export
 * @global $mashsb_upgrades_screen
 * @return void
 */
function mashsb_add_options_link() {
	global $mashsb_parent_page, $mashsb_add_ons_page, $mashsb_settings_export, $mashsb_upgrades_screen;

        //$mashsb_parent_page = add_menu_page( 'Mashshare Welcome Screen' , 'Mashshare' , 'manage_options' , 'mashshare-welcome' , 'mashshare_welcome_conf');   
	$mashsb_parent_page = add_menu_page( 'Mashshare Settings', __( 'Mashshare', 'mashsb' ), 'manage_options', 'mashsb-settings', 'mashsb_options_page' );
        $mashsb_settings_page = add_submenu_page( 'mashsb-settings', __( 'Mashshare Settings', 'mashsb' ), __( 'Settings', 'mashsb' ), 'manage_options', 'mashsb-settings', 'mashsb_options_page' );
        $mashsb_add_ons_page  = add_submenu_page( 'mashsb-settings', __( 'Mashshare Add Ons', 'mashsb' ), __( 'Add Ons', 'mashsb' ), 'manage_options', 'mashsb-addons', 'mashsb_add_ons_page' );        
}
add_action( 'admin_menu', 'mashsb_add_options_link', 10 );

/**
 *  Determines whether the current admin page is an MASHSB admin page.
 *  
 *  Only works after the `wp_loaded` hook, & most effective 
 *  starting on `admin_menu` hook.
 *  
 *  @since 1.9.6
 *  @return bool True if MASHSB admin page.
 */
function mashsb_is_admin_page() {

	if ( ! is_admin() || ! did_action( 'wp_loaded' ) ) {
		return false;
	}
	
	global $mashsb_parent_page, $pagenow, $typenow, $mashsb_settings_page, $mashsb_add_ons_page;

        //echo $pagenow . "typenow" . $typenow . $mashsb_parent_page;
        
	if ( 'toplevel_page_mashsb-settings' == $mashsb_parent_page ) {
		return true;
	}
	
	$mashsb_admin_pages = apply_filters( 'mashsb_admin_pages', array( $mashsb_parent_page, $mashsb_settings_page, $mashsb_add_ons_page, ) );
	
	if ( in_array( $pagenow, $mashsb_admin_pages ) ) {
		return true;
	} else {
		return false;
	}
}
