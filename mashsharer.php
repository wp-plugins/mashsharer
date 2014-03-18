<?php
/*
* Plugin Name: Mashshare Share Buttons
* Version: 1.2
* Plugin URI: http://www.digitalsday.com
* Description: Mashshare is a Share functionality inspired by the the great website Mashable for Facebook and Twitter (Additional services are coming soon)
* Author: Rene Hermenau
* Author URI: http://www.digitalsday.com
  
* Mashshare is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 2 of the License, or
* any later version.
*
* Mashshare is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Click-Fraud Monitoring. If not, see <http://www.gnu.org/licenses/>.
*/

/* Quit */
defined('ABSPATH') OR exit;

/* Define some important constants
 * VERSION_KEY is important for the upgrade routine. Must be updated to current version */
if (!defined('MASHSHARER_VERSION_KEY'))
    define('MASHSHARER_VERSION_KEY', 'mashsharer_version');

if (!defined('MASHSHARER_VERSION_NUM'))
    define('MASHSHARER_VERSION_NUM', '1.2');
add_option(MASHSHARER_VERSION_KEY, MASHSHARER_VERSION_NUM);

global $wpdb;
global $installed_ver;

/* get the current version */
//$installed_ver = get_option('mashsharer_version');

define('MASHSHARER_INSTALLED_VER', get_option('mashsharer_version'));
define('MASHSHARER_TABLE', $wpdb->prefix."mashsharer");
define('MASHSHARER_PLUGIN_URL', plugin_dir_url( __FILE__ )); //production
define('MASHSHARER_PLUGIN_INSTALL_FILE', plugin_basename(__FILE__));

/* include main mashsharer class*/
    include_once 'class.mashsharer.php';
    include_once 'class.debug.php';
    include_once 'class.mashsharer-transients.php';
if ( is_admin() ) {
    require_once dirname( __FILE__ ) . '/includes/admin/add-ons.php';
}


if( is_admin() ) require_once dirname( __FILE__ ) . '/mashsharer-admin.php';


/**
 * Upgrade routine for new table and plugin layout
/* get the installed version number */

function mashsharer_upgrade_db() {

    /* compare installed version number with current version number */
   if (MASHSHARER_INSTALLED_VER != MASHSHARER_VERSION_NUM) {
        $sql = "CREATE TABLE " . MASHSHARER_TABLE . " (
            ID int(11) NOT NULL AUTO_INCREMENT,
            URL varchar(250) NULL,
            TOTAL_SHARES int(20) NOT NULL,
            CHECK_TIMESTAMP TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (ID))";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta($sql);
        /* update version number */
        update_option("mashsharer_version", MASHSHARER_VERSION_NUM);
        add_option('mashsharer_do_activation_redirect', true);
    }
}

function mashsharer_update_db_check() {
   if (isset($installed_ver) != MASHSHARER_VERSION_NUM) {
        mashsharer_upgrade_db();
    }
}
if (is_admin()){
add_action( 'plugins_loaded', 'mashsharer_update_db_check' );
}


/* Initialize and create tables at plugin activation
 * 
 */
function mashsharer_create()
{
	$sql = "CREATE TABLE ".MASHSHARER_TABLE." (
                ID int(11) NOT NULL AUTO_INCREMENT,
                URL varchar(250) NULL,
                TOTAL_SHARES int(20) NOT NULL,
                CHECK_TIMESTAMP TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY  (ID))";
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );			
        // create all option rows
        add_option('mashsharer_version', MASHSHARER_VERSION_NUM);
        add_option('mashsharer_apikey');
        add_option('mashsharer_cache_expire');
        add_option('mashsharer_position');
        add_option('mashsharer_pages');
        add_option('mashsharer_posts');
        add_option('mashsharer_frontpage');
        add_option('mashsharer_do_activation_redirect', true);
        wp_schedule_event( time(), 'daily', 'mashsharer_transients_cron');
}

 
register_activation_hook(__FILE__,'mashsharer_create');
      		

 /* redirect user to settings page after activation */
 
 function mashshare_redirect() {
    if (get_option('mashsharer_do_activation_redirect', false)) {
        delete_option('mashsharer_do_activation_redirect');
        if(!isset($_GET['activate-multi']))
        {
            wp_redirect("options-general.php?page=mashsharer-config");
        }
    }
}
 add_action('admin_init', 'mashshare_redirect');   

/* Disable plugin 
 * 
 */
function mashsharer_uninstall() {
    //global $wpdb;
    $tablename = MASHSHARER_TABLE;
    //$optiontable = $wpdb->prefix . "options";
    $sql = "DROP TABLE " . $tablename;
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $wpdb->query($sql);
    // delete all option rows
    delete_option('mashsharer_version');
    delete_option('mashsharer_apikey');
    delete_option('mashsharer_cache_expire');
    delete_option('mashsharer_position');
    delete_option('mashsharer_pages');
    delete_option('mashsharer_posts');
    delete_option('mashsharer_frontpage');
    delete_option('mashsharer_do_activation_redirect');
    wp_clear_scheduled_hook('mashsharer_transients_cron');
    //delete_option('mashsharer_check_frequency', '');
    //wp_unschedule_event(time(), 'check_frequency_hook');
    //wp_clear_scheduled_hook('check_frequency_hook');
    //$wpdb->show_errors();
}
/* Action when plugin is deleted */
//register_uninstall_hook(__FILE__, 'prefix_on_deactivate');
/* action when plugin is deactivated */
//register_deactivation_hook(__FILE__, 'prefix_on_deactivate');
//$wpdb->show_errors();
//$wpdb->print_error();

/* initialize class 
* check first if option  exist 
*/

register_uninstall_hook(__FILE__, 'mashsharer_uninstall');

/* Add settings link on plugin page
 * 
 */
function mashsharer_settings_link($links) { 
  $settings_link = '<a href="options-general.php?page=mashsharer-config.php">Settings</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
$plugin = plugin_basename(__FILE__); 
add_filter("plugin_action_links_$plugin", 'mashsharer_settings_link' );


/* Embed mashsharer.css and mashsharer.js 
 * 
 */
 if( !is_admin()){
function mashsharrer_add_styles() {
wp_register_style('mashsharer_style', plugins_url('assets/mashsharer.css', __FILE__));
wp_enqueue_style('mashsharer_style');
}
add_action( 'wp_enqueue_scripts', 'mashsharrer_add_styles' );  

function mashsharrer_add_scripts() {
wp_register_script('mashsharer_script', plugins_url('assets/mashsharer.js', __FILE__), array('jquery'),'1.1', true);
wp_enqueue_script('mashsharer_script');
}
add_action( 'wp_enqueue_scripts', 'mashsharrer_add_scripts' );  
 }

 /* Function to clean up expired transients
  * 
  */
 
function mashsharer_do_transients_cron() {
    $mashsharerCache = new mashsharerCache();
    $mashsharerCache->purge_transients();
}
add_action('mashsharer_transients_cron', 'mashsharer_do_transients_cron'); 




/* Start it 
 * 
 */
if (!get_option('mashsharer_version') && is_admin()) {
// nothing here
} else {
    //new mashsharer();
    new mashsharer_debug();   
}
     


/* Template Tags
=================================================================
*/
function mashsharer(){
    global $content;
    global $atts;
    $mashsharer = new mashsharer();
    echo $mashsharer->mashsharerShow($atts, '');
}

?>