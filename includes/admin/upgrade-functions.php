<?php
/**
 * Admin Add-ons
 *
 * @package     MASHSB
 * @subpackage  Admin/Upgrades
 * @copyright   Copyright (c) 2014, Rene Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Upgrade routine for new table and plugin layout
/* Compare installed version with new one 
 * 
 * @since 2.0.0
 * @return void
 */

function mashsharer_upgrade_db() {
        $sql = "CREATE TABLE " . MASHSHARER_TABLE . " (
            ID int(11) NOT NULL AUTO_INCREMENT,
            URL varchar(250) NULL,
            TOTAL_SHARES int(20) NOT NULL,
            CHECK_TIMESTAMP TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (ID))";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
}

/**
 * Triggers all upgrade functions
 *
 * This function is usually triggered via AJAX
 *
 * @since 2.0.0
 * @return void
*/
function mashsb_trigger_upgrades() {
	$mashsb_version = get_option( 'edd_version' );

	if ( version_compare( EDD_VERSION, $mashsb_version, '>' ) ) {
		mashsharer_upgrade_db();
	}

	update_option( 'mashsb_version', MASHSB_VERSION );

	if ( DOING_AJAX )
		die( 'complete' ); // Let AJAX know that the upgrade is complete
}
add_action( 'wp_ajax_mashsb _trigger_upgrades', 'mashsb_trigger_upgrades' );

?>