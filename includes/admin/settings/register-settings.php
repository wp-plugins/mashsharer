<?php
/**
 * Register Settings
 *
 * @package     MASHSB
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * Get an option
 *
 * Looks to see if the specified setting exists, returns default if not
 *
 * @since 1.0.0
 * @return mixed
 */
function mashsb_get_option( $key = '', $default = false ) {
	global $mashsb_options;
	$value = ! empty( $mashsb_options[ $key ] ) ? $mashsb_options[ $key ] : $default;
	$value = apply_filters( 'mashsb_get_option', $value, $key, $default );
	return apply_filters( 'mashsb_get_option_' . $key, $value, $key, $default );
}

/**
 * Get Settings
 *
 * Retrieves all plugin settings
 *
 * @since 1.0
 * @return array MASHSB settings
 */
function mashsb_get_settings() {
	$settings = get_option( 'mashsb_settings' );
               
        
	if( empty( $settings ) ) {
		// Update old settings with new single option

		$general_settings = is_array( get_option( 'mashsb_settings_general' ) )    ? get_option( 'mashsb_settings_general' )  	: array();
                $visual_settings = is_array( get_option( 'mashsb_settings_visual' ) )   ? get_option( 'mashsb_settings_visual' )   : array();
                $networks = is_array( get_option( 'mashsb_settings_networks' ) )   ? get_option( 'mashsb_settings_networks' )   : array();
		$ext_settings     = is_array( get_option( 'mashsb_settings_extensions' ) ) ? get_option( 'mashsb_settings_extensions' )	: array();
		$license_settings = is_array( get_option( 'mashsb_settings_licenses' ) )   ? get_option( 'mashsb_settings_licenses' )   : array();

		$settings = array_merge( $general_settings, $visual_settings, $networks, $ext_settings, $license_settings);

		update_option( 'mashsb_settings', $settings);
	}
	return apply_filters( 'mashsb_get_settings', $settings );
}

/**
 * Add all settings sections and fields
 *
 * @since 1.0
 * @return void
*/
function mashsb_register_settings() {

	if ( false == get_option( 'mashsb_settings' ) ) {
		add_option( 'mashsb_settings' );
	}

	foreach( mashsb_get_registered_settings() as $tab => $settings ) {

		add_settings_section(
			'mashsb_settings_' . $tab,
			__return_null(),
			'__return_false',
			'mashsb_settings_' . $tab
		);

		foreach ( $settings as $option ) {

			$name = isset( $option['name'] ) ? $option['name'] : '';

			add_settings_field(
				'mashsb_settings[' . $option['id'] . ']',
				$name,
				function_exists( 'mashsb_' . $option['type'] . '_callback' ) ? 'mashsb_' . $option['type'] . '_callback' : 'mashsb_missing_callback',
				'mashsb_settings_' . $tab,
				'mashsb_settings_' . $tab,
				array(
					'id'      => isset( $option['id'] ) ? $option['id'] : null,
					'desc'    => ! empty( $option['desc'] ) ? $option['desc'] : '',
					'name'    => isset( $option['name'] ) ? $option['name'] : null,
					'section' => $tab,
					'size'    => isset( $option['size'] ) ? $option['size'] : null,
					'options' => isset( $option['options'] ) ? $option['options'] : '',
					'std'     => isset( $option['std'] ) ? $option['std'] : '',
                                        'textarea_rows' => isset( $option['textarea_rows']) ? $option['textarea_rows'] : ''
				)
			);
		}

	}

	// Creates our settings in the options table
	register_setting( 'mashsb_settings', 'mashsb_settings', 'mashsb_settings_sanitize' );

}
add_action('admin_init', 'mashsb_register_settings');

/**
 * Retrieve the array of plugin settings
 *
 * @since 1.8
 * @return array
*/
function mashsb_get_registered_settings() {

	/**
	 * 'Whitelisted' MASHSB settings, filters are provided for each settings
	 * section to allow extensions and other plugins to add their own settings
	 */
	$mashsb_settings = array(
		/** General Settings */
		'general' => apply_filters( 'mashsb_settings_general',
			array(
				'mashsharer_cache' => array(
					'id' => 'mashsharer_cache',
					'name' => '<strong>' . __( 'Cache expire', 'mashsb' ) . '</strong>',
					'desc' => __('The amount of shares are updated after time of "cache expire". Notice that Sharedcount.com uses his own cache (30 - 60min) so it does not update immediately when expire time is very low, e.g. 5 minutes.', 'mashsb'),
					'type' => 'select',
					'options' => mashsb_get_expiretimes()
				),
				'mashsharer_apikey' => array(
					'id' => 'mashsharer_apikey',
					'name' => __( 'API Key - Important', 'mashsb' ),
					'desc' => __( 'Get it FREE at <a href="https://admin.sharedcount.com/admin/signup.php" target="_blank">SharedCount.com</a> for 50.000 free daily requests. It´s essential for accurate function of this plugin. Make sure Curl is working on your server.', 'mashsb' ),
					'type' => 'text',
					'size' => 'medium'
				),
				
                                'disable_sharecount' => array(
					'id' => 'disable_sharecount',
					'name' => __( 'Disable share counts', 'mashsb' ),
					'desc' => __( 'Use this when you can not enable curl_exec and share counts stays zero on your site. In this mode the plugin do not calls the databse and no SQL queries are done. (Gives just a very little performance boost because all database requests are cached in any case.)', 'mashsb' ),
					'type' => 'checkbox'
				),
                                'disable_cache' => array(
					'id' => 'disable_cache',
					'name' => __( 'Disable Cache', 'mashsb' ),
					'desc' => __( '<strong>Caution: </strong>Use this only for testing to see if share counts are working! Your page loading performance will drop. Works only when shares are enabled.', 'mashsb' ),
					'type' => 'checkbox'
				),
                                'fake_count' => array(
					'id' => 'fake_count',
					'name' => __( 'Fake Share counts', 'mashsb' ),
					'desc' => __( 'This number will be aggregated to all your share counts multiplied with a post specific factor based on title word count divided 10.', 'mashsb' ),
					'type' => 'text',
                                        'size' => 'medium'
				),
                                'uninstall_on_delete' => array(
					'id' => 'uninstall_on_delete',
					'name' => __( 'Remove Data on Uninstall?', 'edd' ),
					'desc' => __( 'Check this box if you would like Mashshare to completely remove all of its data when the plugin is deleted.', 'mashsb' ),
					'type' => 'checkbox'
				)
			)
		),
                'visual' => apply_filters('mashsb_settings_visual',
			array(
                            'mashsharer_position' => array(
					'id' => 'mashsharer_position',
					'name' => __( 'Position', 'mashsb' ),
					'desc' => __( 'Choose where you would like the social icons to appear, before or after the main content. If set to Manual, you can use this code to place your Social links anywhere you like in your templates files: <strong>&lt;?php mashshare(); ?&gt;</strong> or use the shortcode: [mashshare] in your posts. Optional: <strong>[mashshare shares="off"]</strong> if you like to disable the share number.', 'mashsb' ),
					'type' => 'select',
                                        'options' => array(
						'before' => __( 'Top', 'edd' ),
						'after' => __( 'Bottom', 'edd' ),
                                                'both' => __( 'Top and Bottom', 'edd' ),
						'manual' => __( 'Manual', 'edd' )
					)
					
				),
                                'post_types' => array(
					'id' => 'post_types',
					'name' => __( 'Post Types', 'mashsb' ),
					'desc' => __( 'Select where the the share buttons should appear', 'mashsb' ),
					'type' => 'multicheck',
					'options' => apply_filters('mashsb_active', array(
							'mashsharer_posts' => __('Posts', 'mashsb'),
							'mashsharer_pages' => __('Pages' , 'mashsb')
						)	
					)
				),
				'frontpage' => array(
					'id' => 'frontpage',
					'name' => '<strong>' . __( 'Frontpage', 'mashsb' ) . '</strong>',
					'desc' => __('Enable share buttons on frontpage','mashsb'),
					'type' => 'checkbox'
				),
				'mashsharer_round' => array(
					'id' => 'mashsharer_round',
					'name' => __( 'Round Shares', 'mashsb' ),
					'desc' => __( 'Share counts more than 1000 are shown as 1k. More than 1 Million as 1M', 'mashsb' ),
					'type' => 'checkbox'
				),
                                'animate_shares' => array(
					'id' => 'animate_shares',
					'name' => __( 'Animate Shares', 'mashsb' ),
					'desc' => __( 'Count up the shares on page loading with a nice looking and fast jQuery animation. Does not work on blog pages and with shortcodes.f', 'mashsb' ),
					'type' => 'checkbox'
				),
				'mashsharer_hashtag' => array(
					'id' => 'mashsharer_hashtag',
					'name' => __( 'Twitter Hashtag', 'mashsb' ),
					'desc' => __( 'Optional: Use name of your website or any other Name, e.g. \'Mashshare\' results in via @Mashshare', 'mashsb' ),
					'type' => 'text',
					'size' => 'medium'
				),
                                'share_color' => array(
					'id' => 'share_color',
					'name' => __( 'Share color', 'mashsb' ),
					'desc' => __( 'Choose  color of the share number in hex format, e.g. #7FC04C: ', 'mashsb' ),
					'type' => 'text',
					'size' => 'medium',
                                        'std' => '#cccccc'
				),
                                'border_radius' => array(
					'id' => 'border_radius',
					'name' => __( 'Border Radius', 'mashsb' ),
					'desc' => __( 'Specify the border radius of all buttons in pixel. Default is zero border radius.', 'mashsb' ),
					'type' => 'select',
                                        'options' => array(
						1 => 1,
						2 => 2,
                                                3 => 3,
						4 => 4,
                                                5 => 5,
						6 => 6,
                                                'default' => 'default'
					),
                                        'std' => 'default'
					
				),
                                'mash_style' => array(
					'id' => 'mash_style',
					'name' => __( 'Style', 'mashsb' ),
					'desc' => __( 'Load another style to change visual appearance of the share buttons. <br>if you created a custom style and you want to make it available for the community here, <a href="https://www.mashshare.net/contact-support/" target="_blank"> get in contact with me.</a>', 'mashsb' ),
					'type' => 'select',
                                        'options' => array(
						'shadow' => '"Shadow" Created by Rene Hermenau',
                                                'default' => 'Default'
					),
                                        'std' => 'default'
					
				),
                                'subscribe_behavior' => array(
					'id' => 'subscribe_behavior',
					'name' => __( 'Subscribe behavior', 'mashsb' ),
					'desc' => __( 'Specify behavior of the subscribe button and decide if you like to link the button directly to any content or to have a toggled content slider below the button.', 'mashsb' ),
					'type' => 'select',
                                        'options' => array(
						'content' => 'Content',
                                                'link' => 'Link'
					),
                                        'std' => 'content'
					
				),
                                'subscribe_link' => array(
					'id' => 'subscribe_link',
					'name' => __( 'Subscribe Link', 'mashsb' ),
					'desc' => __( 'The link to any place on your site, e.g. http://yoursite.com/subscribe', 'mashsb' ),
					'type' => 'text',
					'size' => 'regular',
                                        'std' => ''
				),
                                'subscribe_content' => array(
					'id' => 'subscribe_content',
					'name' => __( 'Subscribe content', 'mashsb' ),
					'desc' => __( 'Define your subscribe content here. Forms, like button, links or any other text. Shortcodes are also supported, e.g.: [contact-form-7]', 'mashsb' ),
					'type' => 'rich_editor',
					'textarea_rows' => '6',
                                        'std' => ''
				),                                
                                'custom_css' => array(
					'id' => 'custom_css',
					'name' => __( 'Custom CSS', 'mashsb' ),
					'desc' => __( 'Put in some custom styles here', 'mashsb' ),
					'type' => 'textarea',
					'size' => 15
                                        
				)
                        )
		),
                 'networks' => apply_filters( 'mashsb_settings_networks',
                         array(
                                'visible_services' => array(
					'id' => 'visible_services',
					'name' => __( 'Visible Services', 'mashsb' ),
					'desc' => __( 'Specify how many services and social networks are visible before the "Plus" Button is shown.', 'mashsb' ),
					'type' => 'select',
                                        'options' => numberServices()
					
				),
                                'networks' => array(
					'id' => 'networks',
					'name' => '<strong>' . __( 'Services', 'mashsb' ) . '</strong>',
					'desc' => __('Specify the Social networks and services you like to use. Drag and drop the entries to sort them. If you enable more services than the specified "visible services", the plus sign is automatically added to the last share button.','mashsb'),
					'type' => 'networks',
                                        'options' => mashsb_get_networks_list()
                                        
                                        )
				
                         )
                ),
		'licenses' => apply_filters('mashsb_settings_licenses',
			array(
                            
                        )
		)
	);

	return $mashsb_settings;
}

/**
 * Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * At some point this will validate input
 *
 * @since 1.0.0
 *
 * @param array $input The value input in the field
 *
 * @return string $input Sanitized value
 */
function mashsb_settings_sanitize( $input = array() ) {

	global $mashsb_options;

	if ( empty( $_POST['_wp_http_referer'] ) ) {
		return $input;
	}

	parse_str( $_POST['_wp_http_referer'], $referrer );

	$settings = mashsb_get_registered_settings();
	$tab      = isset( $referrer['tab'] ) ? $referrer['tab'] : 'general';

	$input = $input ? $input : array();
	$input = apply_filters( 'mashsb_settings_' . $tab . '_sanitize', $input );

	// Loop through each setting being saved and pass it through a sanitization filter
	foreach ( $input as $key => $value ) {

		// Get the setting type (checkbox, select, etc)
		$type = isset( $settings[$tab][$key]['type'] ) ? $settings[$tab][$key]['type'] : false;

		if ( $type ) {
			// Field type specific filter
			$input[$key] = apply_filters( 'mashsb_settings_sanitize_' . $type, $value, $key );
		}

		// General filter
		$input[$key] = apply_filters( 'mashsb_settings_sanitize', $value, $key );
	}

	// Loop through the whitelist and unset any that are empty for the tab being saved
	if ( ! empty( $settings[$tab] ) ) {
		foreach ( $settings[$tab] as $key => $value ) {

			// settings used to have numeric keys, now they have keys that match the option ID. This ensures both methods work
			if ( is_numeric( $key ) ) {
				$key = $value['id'];
			}

			if ( empty( $input[$key] ) ) {
				unset( $mashsb_options[$key] );
			}

		}
	}

	// Merge our new settings with the existing
	$output = array_merge( $mashsb_options, $input );

	add_settings_error( 'mashsb-notices', '', __( 'Settings updated.', 'mashsb' ), 'updated' );

	return $output;
}

/**
 * Misc Settings Sanitization
 *
 * @since 1.0
 * @param array $input The value inputted in the field
 * @return string $input Sanitizied value
 */
function mashsb_settings_sanitize_misc( $input ) {

	global $mashsb_options;

	/*if( mashsb_get_file_download_method() != $input['download_method'] || ! mashsb_htaccess_exists() ) {
		// Force the .htaccess files to be updated if the Download method was changed.
		mashsb_create_protection_files( true, $input['download_method'] );
	}*/

	/*if( ! empty( $input['enable_sequential'] ) && ! mashsb_get_option( 'enable_sequential' ) ) {

		// Shows an admin notice about upgrading previous order numbers
		MASHSB()->session->set( 'upgrade_sequential', '1' );

	}*/

	return $input;
}
//add_filter( 'mashsb_settings_misc_sanitize', 'mashsb_settings_sanitize_misc' );

/**
 * Taxes Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * This also saves the networks sort order table
 *
 * @since 2.0
 * @param array $input The value inputted in the field
 * @return string $input Sanitizied value
 */
/*function mashsb_settings_sanitize_networks( $input ) {

	$networks_sort_order = ! empty( $_POST['networks_sort_order'] ) ? array_values( $_POST['networks_sort_order'] ) : array();

	update_option( 'mashsb_networks_sort_order', $networks_sort_order );

	return $input;
}
add_filter( 'mashsb_settings_networks_sanitize', 'mashsb_settings_sanitize_networks' );

 */

/**
 * Sanitize text fields
 *
 * @since 1.8
 * @param array $input The field value
 * @return string $input Sanitizied value
 */
function mashsb_sanitize_text_field( $input ) {
	return trim( $input );
}
add_filter( 'mashsb_settings_sanitize_text', 'mashsb_sanitize_text_field' );

/**
 * Retrieve settings tabs
 *
 * @since 1.8
 * @param array $input The field value
 * @return string $input Sanitizied value
 */
function mashsb_get_settings_tabs() {

	$settings = mashsb_get_registered_settings();

	$tabs             = array();
	$tabs['general']  = __( 'General', 'mashsb' );

        if( ! empty( $settings['visual'] ) ) {
		$tabs['visual'] = __( 'Visual', 'mashsb' );
	} 
        
        if( ! empty( $settings['networks'] ) ) {
		$tabs['networks'] = __( 'Social Networks', 'mashsb' );
	}  
        
	if( ! empty( $settings['extensions'] ) ) {
		$tabs['extensions'] = __( 'Extensions', 'mashsb' );
	}
	
	if( ! empty( $settings['licenses'] ) ) {
		$tabs['licenses'] = __( 'Licenses', 'mashsb' );
	}

	//$tabs['misc']      = __( 'Misc', 'mashsb' );

	return apply_filters( 'mashsb_settings_tabs', $tabs );
}

       /*
	* Retrieve a list of possible expire cache times
	*
	* @since  2.0.0
	* @change 
	*
	* @param  array  $methods  Array mit verfügbaren Arten
	*/

        function mashsb_get_expiretimes()
	{
		/* Defaults */
        $times = array(
        '300' => 'in 5 minutes',
        '600' => 'in 10 minutes',
        '1800' => 'in 30 minutes',
        '3600' => 'in 1 hour',
        '21600' => 'in 6 hours',
        '43200' => 'in 12 hours',
        '86400' => 'in 24 hours'
        );
            return $times;
	}
   

/**
 * Retrieve array of  social networks Facebook / Twitter / Subscribe
 *
 * @since 2.0.0
 * 
 * @return array Defined social networks
 */
function mashsb_get_networks_list() {

        $networks = get_option('mashsb_networks');
	return apply_filters( 'mashsb_get_networks_list', $networks );
}

/**
 * Header Callback
 *
 * Renders the header.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @return void
 */
function mashsb_header_callback( $args ) {
	echo '<hr/>';
}

/**
 * Checkbox Callback
 *
 * Renders checkboxes.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_checkbox_callback( $args ) {
	global $mashsb_options;

	$checked = isset( $mashsb_options[ $args[ 'id' ] ] ) ? checked( 1, $mashsb_options[ $args[ 'id' ] ], false ) : '';
	$html = '<input type="checkbox" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="1" ' . $checked . '/>';
	$html .= '<label for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}


/**
 * Multicheck Callback
 *
 * Renders multiple checkboxes.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_multicheck_callback( $args ) {
	global $mashsb_options;

	if ( ! empty( $args['options'] ) ) {
		foreach( $args['options'] as $key => $option ):
			if( isset( $mashsb_options[$args['id']][$key] ) ) { $enabled = $option; } else { $enabled = NULL; }
			echo '<input name="mashsb_settings[' . $args['id'] . '][' . $key . ']" id="mashsb_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="' . $option . '" ' . checked($option, $enabled, false) . '/>&nbsp;';
			echo '<label for="mashsb_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
		endforeach;
		echo '<p class="description">' . $args['desc'] . '</p>';
	}
}

/**
 * Radio Callback
 *
 * Renders radio boxes.
 *
 * @since 1.3.3
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_radio_callback( $args ) {
	global $mashsb_options;

	foreach ( $args['options'] as $key => $option ) :
		$checked = false;

		if ( isset( $mashsb_options[ $args['id'] ] ) && $mashsb_options[ $args['id'] ] == $key )
			$checked = true;
		elseif( isset( $args['std'] ) && $args['std'] == $key && ! isset( $mashsb_options[ $args['id'] ] ) )
			$checked = true;

		echo '<input name="mashsb_settings[' . $args['id'] . ']"" id="mashsb_settings[' . $args['id'] . '][' . $key . ']" type="radio" value="' . $key . '" ' . checked(true, $checked, false) . '/>&nbsp;';
		echo '<label for="mashsb_settings[' . $args['id'] . '][' . $key . ']">' . $option . '</label><br/>';
	endforeach;

	echo '<p class="description">' . $args['desc'] . '</p>';
}

/**
 * Gateways Callback
 *
 * Renders gateways fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_gateways_callback( $args ) {
	global $mashsb_options;

	foreach ( $args['options'] as $key => $option ) :
		if ( isset( $mashsb_options['gateways'][ $key ] ) )
			$enabled = '1';
		else
			$enabled = null;

		echo '<input name="mashsb_settings[' . $args['id'] . '][' . $key . ']"" id="mashsb_settings[' . $args['id'] . '][' . $key . ']" type="checkbox" value="1" ' . checked('1', $enabled, false) . '/>&nbsp;';
		echo '<label for="mashsb_settings[' . $args['id'] . '][' . $key . ']">' . $option['admin_label'] . '</label><br/>';
	endforeach;
}

/**
 * Gateways Callback (drop down)
 *
 * Renders gateways select menu
 *
 * @since 1.5
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_gateway_select_callback($args) {
	global $mashsb_options;

	echo '<select name="mashsb_settings[' . $args['id'] . ']"" id="mashsb_settings[' . $args['id'] . ']">';

	foreach ( $args['options'] as $key => $option ) :
		$selected = isset( $mashsb_options[ $args['id'] ] ) ? selected( $key, $mashsb_options[$args['id']], false ) : '';
		echo '<option value="' . esc_attr( $key ) . '"' . $selected . '>' . esc_html( $option['admin_label'] ) . '</option>';
	endforeach;

	echo '</select>';
	echo '<label for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';
}

/**
 * Text Callback
 *
 * Renders text fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_text_callback( $args ) {
	global $mashsb_options;

	if ( isset( $mashsb_options[ $args['id'] ] ) )
		$value = $mashsb_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="' . $size . '-text" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<label for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Number Callback
 *
 * Renders number fields.
 *
 * @since 1.9
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_number_callback( $args ) {
	global $mashsb_options;

	if ( isset( $mashsb_options[ $args['id'] ] ) )
		$value = $mashsb_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$max  = isset( $args['max'] ) ? $args['max'] : 999999;
	$min  = isset( $args['min'] ) ? $args['min'] : 0;
	$step = isset( $args['step'] ) ? $args['step'] : 1;

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="number" step="' . esc_attr( $step ) . '" max="' . esc_attr( $max ) . '" min="' . esc_attr( $min ) . '" class="' . $size . '-text" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<label for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Textarea Callback
 *
 * Renders textarea fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_textarea_callback( $args ) {
	global $mashsb_options;

	if ( isset( $mashsb_options[ $args['id'] ] ) )
		$value = $mashsb_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : '40';
	$html = '<textarea class="large-text mashsb-textarea" cols="50" rows="' . $size . '" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	$html .= '<label for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Password Callback
 *
 * Renders password fields.
 *
 * @since 1.3
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_password_callback( $args ) {
	global $mashsb_options;

	if ( isset( $mashsb_options[ $args['id'] ] ) )
		$value = $mashsb_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="password" class="' . $size . '-text" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';
	$html .= '<label for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Missing Callback
 *
 * If a function is missing for settings callbacks alert the user.
 *
 * @since 1.3.1
 * @param array $args Arguments passed by the setting
 * @return void
 */
function mashsb_missing_callback($args) {
	printf( __( 'The callback function used for the <strong>%s</strong> setting is missing.', 'mashsb' ), $args['id'] );
}

/**
 * Select Callback
 *
 * Renders select fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_select_callback($args) {
	global $mashsb_options;

	if ( isset( $mashsb_options[ $args['id'] ] ) )
		$value = $mashsb_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$html = '<select id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']"/>';

	foreach ( $args['options'] as $option => $name ) :
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $name . '</option>';
	endforeach;

	$html .= '</select>';
	$html .= '<label for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Color select Callback
 *
 * Renders color select fields.
 *
 * @since 1.8
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_color_select_callback( $args ) {
	global $mashsb_options;

	if ( isset( $mashsb_options[ $args['id'] ] ) )
		$value = $mashsb_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$html = '<select id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']"/>';

	foreach ( $args['options'] as $option => $color ) :
		$selected = selected( $option, $value, false );
		$html .= '<option value="' . $option . '" ' . $selected . '>' . $color['label'] . '</option>';
	endforeach;

	$html .= '</select>';
	$html .= '<label for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Rich Editor Callback
 *
 * Renders rich editor fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @global $wp_version WordPress Version
 */
function mashsb_rich_editor_callback( $args ) {
	global $mashsb_options, $wp_version;
	if ( isset( $mashsb_options[ $args['id'] ] ) )
		$value = $mashsb_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	if ( $wp_version >= 3.3 && function_exists( 'wp_editor' ) ) {
		ob_start();
		wp_editor( stripslashes( $value ), 'mashsb_settings_' . $args['id'], array( 'textarea_name' => 'mashsb_settings[' . $args['id'] . ']', 'textarea_rows' => $args['textarea_rows'] ) );
		$html = ob_get_clean();
	} else {
		$html = '<textarea class="large-text mashsb-richeditor" rows="10" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']">' . esc_textarea( stripslashes( $value ) ) . '</textarea>';
	}

	$html .= '<br/><label for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}

/**
 * Upload Callback
 *
 * Renders upload fields.
 *
 * @since 1.0
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_upload_callback( $args ) {
	global $mashsb_options;

	if ( isset( $mashsb_options[ $args['id'] ] ) )
		$value = $mashsb_options[$args['id']];
	else
		$value = isset($args['std']) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="' . $size . '-text mashsb_upload_field" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( stripslashes( $value ) ) . '"/>';
	$html .= '<span>&nbsp;<input type="button" class="mashsb_settings_upload_button button-secondary" value="' . __( 'Upload File', 'mashsb' ) . '"/></span>';
	$html .= '<label for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}


/**
 * Color picker Callback
 *
 * Renders color picker fields.
 *
 * @since 1.6
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
function mashsb_color_callback( $args ) {
	global $mashsb_options;

	if ( isset( $mashsb_options[ $args['id'] ] ) )
		$value = $mashsb_options[ $args['id'] ];
	else
		$value = isset( $args['std'] ) ? $args['std'] : '';

	$default = isset( $args['std'] ) ? $args['std'] : '';

	$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
	$html = '<input type="text" class="mashsb-color-picker" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '" data-default-color="' . esc_attr( $default ) . '" />';
	$html .= '<label for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

	echo $html;
}




/**
 * Registers the license field callback for Software Licensing
 *
 * @since 1.5
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the MASHSB Options
 * @return void
 */
if ( ! function_exists( 'mashsb_license_key_callback' ) ) {
	function mashsb_license_key_callback( $args ) {
		global $mashsb_options;

		if ( isset( $mashsb_options[ $args['id'] ] ) )
			$value = $mashsb_options[ $args['id'] ];
		else
			$value = isset( $args['std'] ) ? $args['std'] : '';

		$size = ( isset( $args['size'] ) && ! is_null( $args['size'] ) ) ? $args['size'] : 'regular';
		$html = '<input type="text" class="' . $size . '-text" id="mashsb_settings[' . $args['id'] . ']" name="mashsb_settings[' . $args['id'] . ']" value="' . esc_attr( $value ) . '"/>';

		if ( 'valid' == get_option( $args['options']['is_valid_license_option'] ) ) {
			$html .= '<input type="submit" class="button-secondary" name="' . $args['id'] . '_deactivate" value="' . __( 'Deactivate License',  'mashsb' ) . '"/>';
		}
		$html .= '<label for="mashsb_settings[' . $args['id'] . ']"> '  . $args['desc'] . '</label>';

		echo $html;
	}
}

/**
 * Networks Callback / Facebook and Twitter default
 *
 * Renders network order table. Uses separate option field 'mashsb_networks 
 *
 * @since 2.0.0
 * @param array $args Arguments passed by the setting
 * @global $mashsb_options Array of all the mashsb Options
 * @return void
 */

function mashsb_networks_callback( $args ) {
	global $mashsb_options;
       /* Our array in $mashsb_option['networks']
        * 
        *                                   array(
                                                0 => array (
                                                    'status' => '1',
                                                    'name' => 'Share on Facebook',
                                                    'name2' => 'Share'
                                                ), 
                                                1 => array (
                                                    'status' => '1',
                                                    'name' => 'Tweet on Twitter',
                                                    'name2' => 'Twitter'
                                                ),
                                                2 => array (
                                                    'status' => '1',
                                                    'name' => 'Subscribe to us',
                                                    'name2' => 'Subscribe'
                                                )
                                            )
        */

       ob_start();
        ?>
        <p class="description"><?php echo $args['desc']; ?></p>
        <table id="mashsb_network_list" class="wp-list-table fixed posts">
		<thead>
			<tr>
				<th scope="col" style="padding: 15px 10px;"><?php _e( 'Social Networks', 'mashsb' ); ?></th>
                                <th scope="col" style="padding: 15px 10px;"><?php _e( 'Enable', 'mashsb' ); ?></th>
                                <th scope="col" style="padding: 15px 10px;"><?php _e( 'Custom name', 'mashsb' ); ?></th>
			</tr>
		</thead>        
        <?php

	if ( ! empty( $args['options'] ) ) {
		foreach( $args['options'] as $key => $option ):
                        echo '<tr id="mashsb_list_' . $key . '" class="mashsb_list_item">';
			if( isset( $mashsb_options[$args['id']][$key]['status'] ) ) { $enabled = 1; } else { $enabled = NULL; }
                        if( isset( $mashsb_options[$args['id']][$key]['name'] ) ) { $name = $mashsb_options[$args['id']][$key]['name']; } else { $name = NULL; }

                        echo '<td class="mashicon-' . strtolower($option) . '"><span class="icon"></span><span class="text">' . $option . '</span></td>
                        <td><input type="hidden" name="mashsb_settings[' . $args['id'] . '][' . $key . '][id]" id="mashsb_settings[' . $args['id'] . '][' . $key . '][id]" value="' . strtolower($option) .'"><input name="mashsb_settings[' . $args['id'] . '][' . $key . '][status]" id="mashsb_settings[' . $args['id'] . '][' . $key . '][status]" type="checkbox" value="1" ' . checked(1, $enabled, false) . '/><td>
                        <input type="text" class="medium-text" id="mashsb_settings[' . $args['id'] . '][' . $key . '][name]" name="mashsb_settings[' . $args['id'] . '][' . $key . '][name]" value="' . $name .'"/>
                        </tr>';
                endforeach;
	}
        echo '</table>';
        echo ob_get_clean();
}


/**
 * Hook Callback
 *
 * Adds a do_action() hook in place of the field
 *
 * @since 1.0.8.2
 * @param array $args Arguments passed by the setting
 * @return void
 */
function mashsb_hook_callback( $args ) {
	do_action( 'mashsb_' . $args['id'] );
}

/**
 * Set manage_options as the cap required to save MASHSB settings pages
 *
 * @since 1.9
 * @return string capability required
 */
function mashsb_set_settings_cap() {
	return 'manage_options';
}
add_filter( 'option_page_capability_mashsb_settings', 'mashsb_set_settings_cap' );


/* returns array with amount of available services
 * @scince 2.0
 * @return array
 */

function numberServices(){
    $number = 1;
    $array = array();
    while ($number <= count(mashsb_get_networks_list())){
        $array[] = $number++; 

    }
    $array['all'] = __('All Services');
    return apply_filters('mashsb_return_services', $array);
}