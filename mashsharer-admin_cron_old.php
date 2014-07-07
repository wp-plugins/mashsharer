<?php
/* Quit */
defined('ABSPATH') OR exit;

include_once 'class.mashsharer.php';
include_once 'class.debug.php';

/* Define Constants */
global $wp_version;

add_action( 'admin_menu', 'mashsharer_admin_menu' );

function mashsharer_admin_menu() {
		mashsharer_load_menu();
}

function mashsharer_load_menu() {
	$current_theme = wp_get_theme();
	$pluginpath = MASHSHARER_PLUGIN_INSTALL_FILE;
	$active_plugin = get_option('active_plugins');
	
	$plugin_key = array_keys($active_plugin,"$pluginpath");
	$active_plugin_key = $plugin_key[0];
	
	add_submenu_page('options-general.php', __('Mashsharer'), __('Mashsharer'), 'manage_options', 'mashsharer-config', 'mashsharer_conf');
	
}

	

function mashsharer_admin_init() {
    global $wp_version;
    // all admin functions are disabled in old versions
    if (version_compare( $wp_version, '3.0', '<' ) ) {
        
        function mashsharer_version_warning() {
			echo "<div id='mashsharer-warning' class='updated fade'><p><strong>".sprintf(__('Mashsharer %s requires WordPress 3.0 or higher.'), MASHSHARER_VERSION_NUM) ."</strong> ".sprintf(__('Please <a href="%s">upgrade WordPress</a> to a current version.'), 'http://codex.wordpress.org/Upgrading_WordPress'). "</p></div>";
		}
		add_action('admin_notices', 'mashsharer_version_warning');
		return;    
    }
}
add_action('admin_init', 'mashsharer_admin_init');

/* add some extra schedules for cron jobs */
function mashsharer_extra_reccurences() {
    return array(
        'minute' => array('interval' => 60, 'display' => 'Every Minute'),
        '10minutes' => array('interval' => 600, 'display' => 'Every 10 Minutes'),
        'halfhour' => array('interval' => 1800, 'display' => 'Every half hour'),
    );
}
add_filter('cron_schedules', 'mashsharer_extra_reccurences');


function mashsharer_nonce_field($action = -1) { return wp_nonce_field($action); }
$mashsharer_nonce = 'mashsharer-update-key';

/* Define the cron job */
function mashsharer_cron(){
   $mashsharerclass = new mashsharer();
   $mashsharerclass->mashsharerGetTotal();
   //$mashsharerclass->mashsharerShow($atts);
   //$mashsharerclass->mashsharerShow();
}

/* Update and save configuration */
function mashsharer_conf() {
	global $mashsharer_nonce;
        $check_frequency = get_option('mashsharer_check_frequency');
	if ( isset($_POST['submit']) ) {
		if ( function_exists('current_user_can') && !current_user_can('manage_options') )
			die(__('Cheatin&#8217; uh?'));

		check_admin_referer( $mashsharer_nonce );

                /* Update Cron entry and options */
                //wp_clear_scheduled_hook( 'check_frequency_hook' );
                
                /* fire up our chron job */
                if( $check_frequency ){
                        wp_schedule_event( time(), $check_frequency, 'check_frequency_hook');
                }
                        update_option('mashsharer_check_frequency', $_POST['check_frequency'] );
                        add_action('check_frequency_hook', 'mashsharer_cron');

		if ( isset( $_POST['clickthreshold'] ) )
			update_option( 'cfmonitor_click_threshold', $_POST['clickthreshold'] );
		
		if ( isset( $_POST['cfmonitor_ban_period'] ) )
			update_option( 'cfmonitor_ban_period', $_POST['cfmonitor_ban_period'] );
		
		if ( isset( $_POST['cfmonitor_day_span'] ) )
			update_option( 'cfmonitor_day_span', $_POST['cfmonitor_day_span'] );
                
                if ( isset( $_POST['cfmonitor_email'] ) )
			update_option( 'cfmonitor_email', $_POST['cfmonitor_email'] );
                
                 if ( isset( $_POST['cfmonitor_noads'] ) ){
			update_option( 'cfmonitor_noads', $_POST['cfmonitor_noads'] );
                 }else{
                        update_option( 'cfmonitor_noads', 'false' );
                 }
                  if ( isset( $_POST['cfmonitor_customclass'] ) )
			update_option( 'cfmonitor_customclass', $_POST['cfmonitor_customclass'] );
                  
                  if ( isset( $_POST['cfmonitor_myip'] ) )
			update_option( 'cfmonitor_myip', $_POST['cfmonitor_myip'] );
                  
                  if ( isset( $_POST['cfmonitor_blockfirst'] ) ){
			update_option( 'cfmonitor_blockfirst', $_POST['cfmonitor_blockfirst'] );
                  }else{
                        update_option( 'cfmonitor_blockfirst', 'false' );
                 }
                 
                  if ( isset( $_POST['cfmonitor_disablead'] ) ){
			update_option( 'cfmonitor_disablead', $_POST['cfmonitor_disablead'] );
                  }else{
                        update_option( 'cfmonitor_disablead', 'false' );
                 }


	} 

?>
<?php if ( !empty($_POST['submit'] ) ) : ?>
<div id="message" class="updated fade"><p><strong><?php _e('Options saved.') ?></strong></p></div>
<?php endif; ?>

	<div class="wrap rm_wrap">
            <div>
                <img src="<?php echo plugin_dir_url( __FILE__ );?>./images/logo.png">
            </div>
		<h3><?php _e('Mashsharer'); ?></h3>
		<div class="rm_opts">
			<form action="" method="post" id="mashsharer-conf" style="margin: auto;">
                            <table width="100%"> 
                                    <tr>
                                        <td>
                                            <label for="check_frequency">Update Frequency</label>
                                        </td>
                                        <td>
                                            <select name="check_frequency"  id="check_frequency">
                                                <?php
  
                                                $schedules = wp_get_schedules();
                                                if (is_array($schedules)) {
                                                    foreach ($schedules as $key => $value) {
                                                        echo '<option ' . selected($check_frequency, $key, false) . ' value="' . $key . '">' . $value['display'] . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </td>
                                    </tr>
                                </table>    
                            
				
                          
                           
                            <div style="float:clear;">
                                
                                <p class="submit"><input type="submit" name="submit" value="<?php _e('Save settings &raquo;'); ?>" /></p>
                            </div>
                         
				

				<?php mashsharer_nonce_field($mashsharer_nonce); 
                                
                                mashsharer_cron();
                                ?>
			</form>

		</div>
	</div>
<div style="clear:both;">

</div>
<?php

}



?>