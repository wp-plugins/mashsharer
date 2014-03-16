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
	
	add_submenu_page('options-general.php', __('Mashshare'), __('Mashshare'), 'manage_options', 'mashsharer-config', 'mashsharer_conf');
	
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


function mashsharer_nonce_field($action = -1) { return wp_nonce_field($action); }
$mashsharer_nonce = 'mashsharer-update-key';


/**
	* Rückgabe der Optionen
	*
	* @since   1.0.0.
	* @change  
	*
	* @return  array  $diff  Array mit Werten
	*/

	function get_options()
	{
		return wp_parse_args(
                        
			array(
				'apikey'	 	=> get_option('mashsharer_apikey'),
				'cache'		 	=> get_option('mashsharer_cache_expire'),
                                'position'		=> get_option('mashsharer_position'),
                                'pages'                 => get_option('mashsharer_pages'),
                                'posts'                 => get_option('mashsharer_posts'),
                                'frontpage'             => get_option('mashsharer_frontpage')
           			)
		);
	}
        
        /**
	* Position dropdown
	*
	* @since   1.0.0
	* @change  
	*
	* @return  array    Key => value array
	*/

	function position_select()
	{
		return array(
			'before'   => 'Top',
			'after'   => 'Bottom',
			'both'   => 'Top and Bottom',
                        'manual'   => 'Manual'
		);
	}

/**
	* Verfügbare Cache-Methoden
	*
	* @since  2.0.0
	* @change 2.1.3
	*
	* @param  array  $methods  Array mit verfügbaren Arten
	*/

        function method_select()
	{
		/* Defaults */
		$methods = array(
			'300'  => 'in 5 minutes',
			'600' => 'in 10 minutes',
			'1800' => 'in 30 minutes',
                        '3600' => 'in 1 hour',
			'21600' => 'in 6 hours',
                        '43200' => 'in 12 hours',
                        '86400' => 'in 24 hours'
		);

		return $methods;
	}
        
        



/* Update and save configuration */
function mashsharer_conf() {
	global $mashsharer_nonce;
        
	if ( isset($_POST['submit']) ) {
		if ( function_exists('current_user_can') && !current_user_can('manage_options') )
			die(__('Cheatin&#8217; uh?'));

		check_admin_referer( $mashsharer_nonce );

		if ( isset( $_POST['mashsharer_cache'] ) )
			update_option( 'mashsharer_cache_expire', $_POST['mashsharer_cache'] );
		
		if ( isset( $_POST['mashsharer_apikey'] ) )
			update_option( 'mashsharer_apikey', $_POST['mashsharer_apikey'] );
		
		if (isset( $_POST['mashsharer_posts']) == 1){
			update_option( 'mashsharer_posts', 1 ); echo $_POST['mashsharer_posts'];
                } else{ 
                        update_option( 'mashsharer_posts', 0 ); echo $_POST['mashsharer_posts']; 
                }
                
                if ( $_POST['mashsharer_pages'] == 1){
			update_option( 'mashsharer_pages', 1 ); echo $_POST['mashsharer_pages'];
                } else {
                        update_option( 'mashsharer_pages', 0 ); echo $_POST['mashsharer_pages'];
                }

                 if ( isset( $_POST['mashsharer_position'] ) )
			update_option( 'mashsharer_position', $_POST['mashsharer_position'] );
                 
                 if ( $_POST['mashsharer_frontpage'] == 1){
			update_option( 'mashsharer_frontpage', 1 ); echo $_POST['mashsharer_frontpage'];
                } else {
                        update_option( 'mashsharer_frontpage', 0 ); echo $_POST['mashsharer_frontpage'];
                }
                

	} 

?>
<?php if ( !empty($_POST['submit'] ) ) : ?>
<div id="message" class="updated fade"><p><strong><?php _e('Options saved.') ?></strong></p></div>
<?php endif; ?>

	<div class="wrap rm_wrap">
            <div>
                <!-- TO DO <img src="<?php echo plugin_dir_url( __FILE__ );?>./images/logo.png">-->
            </div>
		<h2><?php _e('Mashsharer'); ?></h2>
		<div class="rm_opts">
			<form action="" method="post" id="mashsharer-conf" style="margin: auto;">
                            <?php $options = get_options() ?>
                            <table class="form-table"> 
                                <tr>
                                    <th scope="row">Cache expire</th>
                                        <td>
                                            <label for="mashsharer_cache_method">
                                                    <select name="mashsharer_cache" id="mashsharer_cache_method">
                                                        <?php foreach (method_select() as $k => $v) { ?>
                                                            <option value="<?php echo esc_attr($k) ?>" <?php selected($options['cache'], $k); ?>><?php echo esc_html($v) ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </label>
                                        </td>
                                        <td>
                                         Share count is updated after cache expire time. SharedCount uses his own cache so it does not update immediately when expire time is very very low, e.g. 5 minutes.
                                        </td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row">API KEY</th>
                                        <td>
                                            <fieldset>
                                                    <label for="mashsharer-apikey">
                                                        <input type="text" name="mashsharer_apikey" id="mashsharer-apikey" value="<?php echo $options['apikey'];?>">
                                                    </label>
                                                </fieldset>
                                        </td>
                                        <td>
                                          Optional needed for 50.000 free daily request - Get it at <a href="https://admin.sharedcount.com/admin/signup.php" target="_blank">SharedCount.com</a> 
                                        </td>
                                    </tr>
                                    <tr valign="top">
                                    <th scope="row">Position</th>
                                        <td valign="top">
                                            <label for="mashsharer_position">
                                                    <select name="mashsharer_position" id="mashsharer_position">
                                                        <?php foreach (position_select() as $k => $v) { ?>
                                                            <option value="<?php echo esc_attr($k) ?>" <?php selected($options['position'], $k); ?>><?php echo esc_html($v) ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </label>
                                            
                                        </td>
                                        <td valign="top">
                                             Choose where you would like the social icons to appear, before or after the main content. If set to Manual, you can use this code to place your Social links anywhere you like in your templates files:
                                             &nbsp; <strong>&lt;?php mashsharer(); ?&gt;</strong> or use the shortcode: [mashshare] in your posts.
                                        </td>
                                        <tr valign="top">
						<th scope="row">
							<?php _e('Post Types', 'mashsharer') ?>
						</th>
						<td>
							<fieldset>
								<label for="mashsharer_posts">
									<input type="checkbox" name="mashsharer_posts" id="mashsharer_posts" value="1" <?php checked('1', $options['posts']); ?> />
									
								</label>
                                                                Posts
								<br />

								<label for="mashsharer_pages">
									<input type="checkbox" name="mashsharer_pages" id="mashsharer_pages"  value="1" <?php checked('1', $options['pages']); ?> />
									
								</label>
                                                                Pages
							</fieldset>
						</td>
                                                <td>
                                                    <?php _e('Select where you want the share buttons appear', 'mashsharer') ?>
                                                </td>
					</tr>
                                        <tr valign="top">
						<th scope="row">
							<?php _e('Frontpage', 'mashsharer') ?>
						</th>
						<td>
							<fieldset>
								<label for="mashsharer_frontpage">
									<input type="checkbox" name="mashsharer_frontpage" id="mashsharer_frontpage" value="1" <?php checked('1', $options['frontpage']); ?> />
								</label>
                                                         </fieldset>
						</td>
                                                <td>
                                                    <?php _e('Select when the buttons should appear on the frontpage', 'mashsharer') ?>
                                                </td>
					</tr>
                                        <tr valign="top">
						<th scope="row">
							<?php _e('Support', 'mashsharer') ?>
						</th>
						<td>
							
						</td>
                                                <td>
                                                    <?php _e('Do you have any issues? Write me and i try my best to fix it: <a href="mailto:rene@digitalsday.com">rene@digitalsday.com</a>.<br> Please also rate this plugin at <a href="http://wordpress.org/support/view/plugin-reviews/mashsharer" target="_blank">wordpress.org</a>. That helps me to increase the download rate.', 'mashsharer') ?>
                                                </td>
					</tr>
                                    </tr>
                                </table>    
                            
				
                          
                           
                            <div style="float:clear;">
                                
                                <p class="submit"><input type="submit" name="submit" value="<?php _e('Save settings &raquo;'); ?>" /></p>
                            </div>
                         <?php mashsharer_nonce_field($mashsharer_nonce); ?>
			</form>

		</div>
	</div>
<div style="clear:both;">
    <?php mash_add_ons_page(); ?>
<div style="clear:both;">

</div>
<?php

}



?>