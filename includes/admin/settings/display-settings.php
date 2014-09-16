<?php
/**
 * Admin Options Page
 *
 * @package     MASHSB
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Options Page
 *
 * Renders the options page contents.
 *
 * @since 1.0
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_options_page() {
	global $mashsb_options;

	$active_tab = isset( $_GET[ 'tab' ] ) && array_key_exists( $_GET['tab'], mashsb_get_settings_tabs() ) ? $_GET[ 'tab' ] : 'general';

	ob_start();
	?>
	<div class="wrap">
             <h1> <?php echo __('Welcome to Mashshare ', 'mashsb') . MASHSB_VERSION; ?></h1>
            <div class="about-text" style="font-weight: 400;line-height: 1.6em;font-size: 19px;">
                <?php echo __('Thank you for updating to the latest version!', 'mashsb');?>
                <br>
                <?php echo __('Mashshare is ready to increase your Shares!', 'mashsb'); ?>
                <?php if (!function_exists('curl_init')){ echo '<br><span style="color:red;">' . __('php_curl is not working on your server. </span><a href="http://us.informatiweb.net/programmation/32--enable-curl-extension-of-php-on-windows.html" target="_blank">Please enable it.</a>'); } ?>
                <br>
                <iframe src="//www.facebook.com/plugins/like.php?href=https%3A%2F%2Fwww.facebook.com%2Fmashshare.net&amp;width=100&amp;layout=standard&amp;action=like&amp;show_faces=false&amp;share=true&amp;height=35&amp;appId=449277011881884" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:400px; height:20px;" allowTransparency="true"></iframe>
            </div>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( mashsb_get_settings_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'settings-updated' => false,
					'tab' => $tab_id
				) );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
					echo esc_html( $tab_name );
				echo '</a>';
			}
			?>
		</h2>
		<div id="tab_container">
			<form method="post" action="options.php">
				<table class="form-table">
				<?php
				settings_fields( 'mashsb_settings' );
				do_settings_fields( 'mashsb_settings_' . $active_tab, 'mashsb_settings_' . $active_tab );
				?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div><!-- #tab_container-->
	</div><!-- .wrap -->
	<?php
	echo ob_get_clean();
}
