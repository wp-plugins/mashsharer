<?php
/**
 * Scripts
 *
 * @package     MASHSB
 * @subpackage  Functions
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Load Scripts
 *
 * Enqueues the required scripts.
 *
 * @since 1.0
 * @global $mashsb_options
 * @global $post
 * @return void
 */
function mashsb_load_scripts() {
	global $mashsb_options, $post;
        $url = urlencode(get_permalink($post->ID));
        $title = addslashes(the_title_attribute('echo=0'));
        $titleclean = str_replace('#' , '', html_entity_decode($title));
        //$titleclean = esc_html($title);
        $image = mashsb_get_image($post->ID);
        $desc = mashsb_get_excerpt_by_id($post->ID);
        /* Load hashshag*/       
            if ($mashsb_options['mashsharer_hashtag'] != '') {
                $hashtag = '&via=' . $mashsb_options['mashsharer_hashtag'];
            } else {
                $hashtag = '';
            }
            
	$js_dir = MASHSB_PLUGIN_URL . 'assets/js/';
	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	wp_enqueue_script( 'mashsb', $js_dir . 'mashsb' . $suffix . '.js', array( 'jquery' ), MASHSB_VERSION );
                $shareresult = getSharedcount($url);
                wp_localize_script( 'mashsb', 'mashsb', array(
			'shares'        => $shareresult,
                        'round_shares'  => isset($mashsb_options['mashsharer_round']),
                        /* Do not animate shares on blog posts. The share count would be wrong there and performance bad */
                        'animate_shares' => isset($mashsb_options['animate_shares']) && is_single() ? 1 : 0,
                        'share_url' => $url,
                        'title' => $titleclean,
                        'image' => $image,
                        'desc' => $desc,
                        'hashtag' => $hashtag,
                        'subscribe' => $mashsb_options['subscribe_behavior'] === 'content' ? 'content' : 'link',
                        'subscribe_url' => isset($mashsb_options['subscribe_link']) ? $mashsb_options['subscribe_link'] : ''
                    ));
                        
}
add_action( 'wp_enqueue_scripts', 'mashsb_load_scripts' );

/**
 * Register Styles
 *
 * Checks the styles option and hooks the required filter.
 *
 * @since 1.0
 * @global $mashsb_options
 * @return void
 */
function mashsb_register_styles() {
	global $mashsb_options;

	if ( isset( $mashsb_options['disable_styles'] ) ) {
		return;
	}

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
	$file          = 'mashsb' . $suffix . '.css';

	$url = trailingslashit( plugins_url(). '/mashshare/templates/'    ) . $file;
	wp_enqueue_style( 'mashsb-styles', $url, array(), MASHSB_VERSION );
}
add_action( 'wp_enqueue_scripts', 'mashsb_register_styles' );

/**
 * Load Admin Scripts
 *
 * Enqueues the required admin scripts.
 *
 * @since 1.0
 * @global $post
 * @param string $hook Page hook
 * @return void
 */

function mashsb_load_admin_scripts( $hook ) {
	if ( ! apply_filters( 'mashsb_load_admin_scripts', mashsb_is_admin_page(), $hook ) ) {
		return;
	}
	global $wp_version;

	$js_dir  = MASHSB_PLUGIN_URL . 'assets/js/';
	$css_dir = MASHSB_PLUGIN_URL . 'assets/css/';

	// Use minified libraries if SCRIPT_DEBUG is turned off
	$suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
        //echo $css_dir . 'mashsb-admin' . $suffix . '.css', MASHSB_VERSION;
	// These have to be global
	wp_enqueue_script( 'mashsb-admin-scripts', $js_dir . 'mashsb-admin' . $suffix . '.js', array( 'jquery' ), MASHSB_VERSION, false );
        wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_style( 'mashsb-admin', $css_dir . 'mashsb-admin' . $suffix . '.css', MASHSB_VERSION );
}
add_action( 'admin_enqueue_scripts', 'mashsb_load_admin_scripts', 100 );