<?php
/**
 * Template Functions
 *
 * @package     MASHSB
 * @subpackage  Functions/Templates
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/* Load Hooks
 * @scince 2.0
 * return void
 */

add_shortcode('mashshare', 'mashshareShortcodeShow');
add_filter('the_content', 'mashshare_filter_content', 1000);
add_filter('widget_text', 'do_shortcode');
add_action('mashshare', 'mashshare');


    /* Get share counts of current page by sharedcount.com and write them in database
     * @scince 1.0
     * @return void
     */    
    function mashshareGetTotal($url) {
        global $wpdb, $post, $mashsb_options;

        $counts['Twitter'] = 0;
        $counts['Facebook']['total_count'] = 0;
        $counts['GooglePlusOne'] = 0;
        $counts['Pinterest'] = 0;
        $counts['LinkedIn'] = 0;
        $counts['StumbleUpon'] = 0;
 
        $apikey = $mashsb_options['mashsharer_apikey'];

            // We use curl instead file_get_contents for security purposes
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://free.sharedcount.com/?url=" . rawurlencode($url) . "&apikey=" . $apikey);
            /* For debugging */
            //curl_setopt($ch, CURLOPT_URL, "http://api.sharedcount.com/?url=" . rawurlencode('http://www.google.de') . "&apikey=" . $apikey);
            /* For debugging */
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            curl_close($ch);

            $counts = json_decode($output, true);
            
            // DEPRECATED: delete include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        if (class_exists('MashshareNetworks')) {
            mashdebug()->info('Mashshare Networks exists');
            $total_count = $counts['Twitter'] + $counts['Facebook']['total_count'] + $counts['GooglePlusOne'] + $counts['Pinterest'] + $counts['LinkedIn'] + $counts['StumbleUpon'];
            mashdebug()->info("Debug: This page has " . $counts["Twitter"] ." Tweeter tweets, " . $counts["Facebook"]["like_count"] . " Facebook likes, and ". $counts["GooglePlusOne"] . " Google +1's and " . $counts['Pinterest']. " Pinterest counts and " . $counts['stumbleupon'] . " StumbleUpon counts and " . $counts['LinkedIn'] . "Linked in counts");      
        } else {
            $total_count = $counts['Twitter'] + $counts['Facebook']['total_count'];
            mashdebug()->info("Debug: This page has " . $counts["Twitter"] ." Tweeter tweets, " . $counts["Facebook"]["like_count"] . " Facebook likes");
        }
        $sql = "select TOTAL_SHARES from " . MASHSB_TABLE . " where URL='" . $url . "'";
        $results = $wpdb->get_results($sql);

        $sharecountdb = $wpdb->get_var("select TOTAL_SHARES from " . MASHSB_TABLE . " where URL='" . $url . "'");

			
        if (count($results) == 0) {
            if ($total_count >= 0) {
                $sql = "INSERT INTO " . MASHSB_TABLE . " (URL, TOTAL_SHARES, CHECK_TIMESTAMP) values('" . $url . "'," . $total_count . ",now())";
            }
        } else {
            /*  We only update sharecountdb if the API total_count is higher than the sharcountdb value
             *  This prevents zero counts when the API should be down
             */
            if ($sharecountdb < $total_count) {
                $sql = "UPDATE " . MASHSB_TABLE . " SET TOTAL_SHARES=" . $total_count . ", CHECK_TIMESTAMP=now() where URL='" . $url . "'";
            }
        }
            $wpdb->query($sql);
            /*echo "Debug: SQL results" . count($results);
            echo "Amount shares" . $total_count;
            echo $sql;*/
    }
    
    /* DEPRECATED: todo delete Check if networks Add-On is present and returns additional networks
     * @scince 1.0
     * @return string
     */

    /*
    function mashload($place){
        global $addons;
        	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		if (class_exists('mashshare_networks')) {
                        include_once(ABSPATH . 'wp-content/plugins/mashshare-networks/mashshare-networks.php');
			$networks = new mashshare_networks();
			$addons = $networks->mashshare_get_networks($place);

		}       
        return apply_filters( 'mashsb_output_networks', $addons );
    }
    add_filter('mashsb_load_addons', 'mashload');
     */
    
    /* Show Subscribe extra button
     * @scince 2.0
     * @return string
     */
    
    function mashsb_subscribe_button(){
        global $mashsb_options;
        if ($mashsb_options['networks'][2]){
            // DEPRECATED todo: remove in later version $subscribebutton = '<a href="javascript:void(0)" class="mashicon-subscribe" id="mash-subscribe-control"><span class="icon"></span><span class="text">' . __('Subscribe', 'mashsb') . '</span></a>' . $addons;
        $subscribebutton = '<a href="javascript:void(0)" class="mashicon-subscribe" id="mash-subscribe-control"><span class="icon"></span><span class="text">' . __('Subscribe', 'mashsb') . '</span></a>';
        } else {
            $subscribebutton = '';    
        }
         return apply_filters('mashsb_filter_subscribe_button', $subscribebutton);
    }
    //add_filter('mashsb_output_networks', 'mashsb_subscribe_button');
    
    /* Put the Subscribe container under the share buttons
     * @scince 2.0.0.
     * @return string
     */
    
    function mashsb_subscribe_content(){
        global $mashsb_options;
        if ($mashsb_options['networks'][2] && $mashsb_options['subscribe_behavior'] === 'content'){ //Subscribe content enabled
            $container = '<div class="mashsb-toggle-container" id="mashsb-toggle">' . $mashsb_options['subscribe_content']. '</div>';
        } else {
            $container = '';    
        }
         return apply_filters('mashsb_toggle_container', $container);
    }
    //add_filter('mashsb_output_buttons', 'mashsb_subscribe_content');
    
    
    /* Get Share count from sharedcount.com and returns share buttons and share counts
     * @scince 2.0.0
     * @returns string
     */
    
    function getSharedcount($url){
        global $wpdb ,$mashsb_options, $post;
        $cacheexpire = $mashsb_options['mashsharer_cache'];
        //$cacheexpire = 1; //only for debugging
        mashdebug()->info( "getSharedcount(): " . $url);
        /* Create transient caching function */
        $mashsharer_transient_key = "mash_".md5($url);
        
            if (isset($mashsb_options['disable_cache'])){
                mashdebug()->warn('getSharedcount() Cache deleted (admin option) ' . $mashsharer_transient_key);
                delete_transient($mashsharer_transient_key); // for debugging  
            }
        
        $results = get_transient($mashsharer_transient_key);

            if ($results === false) {
                mashdebug()->warn ("getSharedcount() not cached");
                /* Get the share counts and write them to database when cache is expired */
                mashshareGetTotal($url);
                $sql = "select TOTAL_SHARES from " . MASHSB_TABLE . " where URL='" . $url . "'";
                $results = $wpdb->get_results($sql);
                set_transient($mashsharer_transient_key, $results, $cacheexpire);
            } else {
                $results = get_transient($mashsharer_transient_key);
                mashdebug()->info('cached results: ' . $results[0]->TOTAL_SHARES);
            }
          //echo $mashsharer_transient_key;
          
          /* Agregate a fakecount */
          if (isset($mashsb_options['fake_count'])){
              $fakecount = $mashsb_options['fake_count'];}
              else{$fakecount = 0;}  
          
              /* return total counts */  
          if (empty($results)) {
                $totalshares = 0 + round($fakecount * mashsb_get_fake_factor(), 0);
            } else {
                $totalshares = $results[0]->TOTAL_SHARES + round($fakecount * mashsb_get_fake_factor(), 0);
            }
            return apply_filters('filter_get_sharedcount', $totalshares);
            
    }
    
    
    /* Round the totalshares
     * @scince 1.0
     * @return string
     */
    
    function roundshares($totalshares){           
         if ($totalshares > 1000000) {
            $totalshares = round($totalshares / 1000000, 1) . 'M';
        } elseif ($totalshares > 1000) {
            $totalshares = round($totalshares / 1000, 1) . 'k';
        }
        return apply_filters('get_rounded_shares', $totalshares);
    }
    
    /* Return the more networks button
     * @scince 2.0
     * @return string
     */
    function onOffSwitch(){
        $output = '<div class="onoffswitch">
                        <input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="myonoffswitch" checked="">
                        <label class="onoffswitch-label" for="myonoffswitch">
                        <div class="onoffswitch-inner"></div>
                        </label>
                        </div>';
        return apply_filters('mashsh_onoffswitch', $output);
    }
    
    /* Return the second more networks button after 
     * last hidden additional service. initial status: hidden
     * Gets visible with click on 'plus'
     * @scince 2.0
     * @return string
     */
    function onOffSwitch2(){
        $output = '<div class="onoffswitch2" style="display:none;">
                        <input type="checkbox" name="onoffswitch2" class="onoffswitch2-checkbox" id="myonoffswitch2" checked="">
                        <label class="onoffswitch2-label" for="myonoffswitch2">
                        <div class="onoffswitch2-inner"></div>
                        </label>
                        </div>';
        return apply_filters('mashsh_onoffswitch2', $output);
    }

    /* Delete all services from array which are not enabled
     * @scince 2.0.0
     * @return callback
     */
    function isStatus($var){
        return (!empty($var["status"]));
        }
    
    /* Returns all available networks
     * @scince 2.0
     * @returns string
     */
    function getNetworks() {
        
        global $mashsb_options;
        $output = '';
        $startsecondaryshares = '';
        $endsecondaryshares = '';
        /* var for more services button */
        $onoffswitch = '';
        /* counter for 'Visible Services' */
        $startcounter = 1;
        $maxcounter = $mashsb_options['visible_services']+1; // plus 1 because our array values start counting from zero
        /* our list of available services, includes disabled ones! 
         * We have to clean this array first!
         */
        $getnetworks = $mashsb_options['networks'];
        /* Delete disabled services from array. Use callback function here */
        if (is_array($getnetworks)){
        $enablednetworks = array_filter($getnetworks, 'isStatus');
        }else{
        $enablednetworks = $getnetworks; 
        }
        //var_dump($enablednetworks);
        //echo "max: " . $maxcounter;
    if (!empty($enablednetworks)) {
        foreach ($enablednetworks as $key => $network):
            if($mashsb_options['visible_services'] !== 'all' && $maxcounter != count($enablednetworks) && $mashsb_options['visible_services'] < count($enablednetworks)){
                //if ($startcounter > $maxcounter){$hiddenclass = 'mashsb-hide';} else {$hiddenclass = '';}
                if ($startcounter === $maxcounter ){ 
                    $onoffswitch = onOffSwitch();
                    //$onoffswitch2 = onOffSwitch2();
                    $startsecondaryshares   = '<div class="secondary-shares" style="display:none;">';} else {$onoffswitch = ''; $onoffswitch2 = ''; $startsecondaryshares   = '';}
                if ($startcounter === (count($enablednetworks))){ 
                    //$onoffswitch2 = onOffSwitch2();
                    $endsecondaryshares     = '</div>'; } else { ;$endsecondaryshares = '';}
                    
                //echo " Debug: Startcounter " . $startcounter . " Hello: " . $maxcounter+1 .
                 //" Debug: Enabled services: " . count($enablednetworks) . "<br>"; 
            }
            if ($enablednetworks[$key]['name'] !='') {
                /* replace all spaces with $nbsp; prevents css content: error on text-intend */
                $name = preg_replace('/\040{1,}/','&nbsp;',$enablednetworks[$key]['name']);
            } else {
                $name = ucfirst($enablednetworks[$key]['id']);
            }
            
            $output .= '<a class="mashicon-' . $enablednetworks[$key]['id'] . '" href="javascript:void(0);"><span class="icon"></span><span class="text">' . $name . '</span></a>';
            $output .= $onoffswitch;
            $output .= $startsecondaryshares;
            
            $startcounter++;
        endforeach;
        $output .= onOffSwitch2();
        $output .= $endsecondaryshares;
    }
    return apply_filters('return_networks', $output);
    
}

    /* Select Share count from database and returns share buttons and share counts
     * @scince 1.0
     * @returns string
     */
    function mashshareShow($atts, $place) {
        global $wpdb, $mashsb_options, $post, $title, $url;
        //$url = get_permalink($post->ID);
        //$title = addslashes(the_title_attribute('echo=0'));
        //$enable_sharecount = isset($mashsb_options['enable_sharecount']) ? $mashsb_options['enable_sharecount'] : null;

        //global $url;
        //global $cacheexpire;
        //$cacheexpire = $mashsb_options['mashsharer_cache'];
        //$cacheexpire = 1;
        
        /*DEPRECATED : todo delete
	 * Load addons
         *       
         * $addons = mashload($place);
         */
       
            /* Load hashshag*/       
            if ($mashsb_options['mashsharer_hashtag'] != '') {
                $hashtag = '&via=' . $mashsb_options['mashsharer_hashtag'];
            } else {
                $hashtag = '';
            }

            if (!isset($mashsb_options['disable_sharecount'])) {
                    /* get totalshares of the current page with sharedcount.com */
                    $totalshares = getSharedcount($url);
                    /* Round total shares when enabled */
                    if (isset($mashsb_options['mashsharer_round'])) {
                        $totalshares = roundshares($totalshares);
                    }  
                 $sharecount = '<div class="mashsb-count"><div class="counts" id="mashsbcount">' . $totalshares . '</div><span class="mashsb-sharetext">' . __('SHARES', 'mashsb') . '</span></div>';    
             } else {
                 $sharecount = '';
             }
                     
                $return = '
                    <aside class="mashsb-container">
                    <div class="mashsb-box">'
                        . $sharecount .
                    '<div class="mashsb-buttons">' 
                        . getNetworks() . 
                        //'<a class="mashicon-facebook" href="javascript:void(0);"><span class="icon"></span><span class="text">' . __('Share&nbsp;on&nbsp;Facebook', 'mashsb') . '</span></a><a class="mashicon-twitter" href="javascript:void(0)"><span class="icon"></span><span class="text">' . __('Tweet&nbsp;on&nbsp;Twitter', 'mashsb') . '</span></a><a class="mashicon-google" href="javascript:void(0)"><span class="icon"></span><span class="text">' . __('Google+', 'mashsb') . '</span></a>' . mashsb_subscribe_button() .                     
                    '</div></div>
                    <div style="clear:both;"></div>'
                    . mashsb_subscribe_content() .
                    '</aside>
                        <!-- Share buttons made by mashshare.net - Version: ' . MASHSB_VERSION . '-->';
            return apply_filters( 'mashsb_output_buttons', $return );
    }
    
    /* Shortcode function
     * Select Share count from database and returns share buttons and share counts
     * @scince 1.0
     * @returns string
     */
    function mashshareShortcodeShow($atts, $place) {
        global $wpdb ,$mashsb_options, $post;
        $url = get_permalink($post->ID);
        $title = addslashes(the_title_attribute('echo=0'));
	/* DEPRECATED todo: delete Load addons */
        /*
         * $addons = mashload($place);	
         */

        extract(shortcode_atts(array(
            'cache' => '3600',
            'shares' => 'true',
            'buttons' => 'true'
                        ), $atts));

            /* Load hashshag*/       
            if ($mashsb_options['mashsharer_hashtag'] != '') {
                $hashtag = '&via=' . $mashsb_options['mashsharer_hashtag'];
            } else {
                $hashtag = '';
            }

            
             if ($shares != 'false') {
                    /* gettotalshares of the current page with sharedcount.com */
                    $totalshares = getSharedcount($url);
                    /* Round total shares when enabled */
                    $roundenabled = isset($mashsb_options['mashsharer_round']) ? $mashsb_options['mashsharer_round'] : null;
                        if ($roundenabled) {
                            $totalshares = roundshares($totalshares);
                        }
                    $sharecount = '<div class="mashsb-count"><div class="counts">' . $totalshares . '</div><span class="mashsb-sharetext">' . __('SHARES', 'mashsb') . '</span></div>';    
                    /*If shortcode [mashshare shares="true" onlyshares="true"]
                     * return shares and exit;
                     */
                    if ($shares === "true" && $buttons === 'false'){
                       return $sharecount; 
                    }
                    if ($shares === "false" && $buttons === 'true'){
                       $sharecount = '';
                }  
             }
             
                        
                $return = '
                    <aside class="mashsb-container">
                    <div class="mashsb-box">'
                        . $sharecount .
                    '<div class="mashsb-buttons">' 
                        . getNetworks() . 
                        //'<a class="mashicon-facebook" href="javascript:void(0);"><span class="icon"></span><span class="text">' . __('Share&nbsp;on&nbsp;Facebook', 'mashsb') . '</span></a><a class="mashicon-twitter" href="javascript:void(0)"><span class="icon"></span><span class="text">' . __('Tweet&nbsp;on&nbsp;Twitter', 'mashsb') . '</span></a><a class="mashicon-google" href="javascript:void(0)"><span class="icon"></span><span class="text">' . __('Google+', 'mashsb') . '</span></a>' . mashsb_subscribe_button() .                     
                    '</div></div>
                    <div style="clear:both;"></div>'
                    . mashsb_subscribe_content() .
                    '</aside>
                        <!-- Share buttons made by mashshare.net - Version: ' . MASHSB_VERSION . '-->';
            return apply_filters( 'mashsb_output_buttons', $return );       
    }
                
    
    /* Returns Share buttons on specific positions
     * Uses the_content filter
     * @scince 1.0
     * @return string
     */
    function mashshare_filter_content($content){
        global $atts, $mashsb_options, $url, $title, $post;
        global $wp_current_filter;
        global $pages;
        /* define some vars here to reduce multiple execution of get_permalink() and addslashes() */
        $url = get_permalink($post->ID);
        $title = addslashes(the_title_attribute('echo=0'));
        
        $position = $mashsb_options['mashsharer_position'];
        $option_posts = isset( $mashsb_options['post_types']['mashsharer_posts'] ) ? $mashsb_options['post_types']['mashsharer_posts'] : null;
        $option_pages = isset( $mashsb_options['post_types']['mashsharer_pages'] ) ? $mashsb_options['post_types']['mashsharer_pages'] : null;

        
        $pt = get_post_type();

        $frontpage = isset( $mashsb_options['frontpage'] ) ? $mashsb_options['frontpage'] : null;


        if ($option_posts === 'Posts')
            $option_posts = 'post';
        if ($option_pages === 'Pages')
            $option_pages = 'page';


        $post_types = array(
            $option_posts,
            $option_pages
        );

        if ($post_types && !in_array($pt, $post_types)) {
            return $content;
        }
	   
          /* if( $posts === $pt){
          return "posts".$content . "content is empty";
          }
          if( $pages === $pt){
          return "pages".$content;
          } */

	if (!is_singular()) {
            // disabled to show mashshare on blog frontpage
            //return $content;
        }
        if (in_array('get_the_excerpt', $wp_current_filter)) {
            return $content;
        }
        if ($frontpage == 0 && is_front_page() == true) {
            return $content;
        }
        if (is_feed()) {
            return $content;
        }
			
            switch($position){
                case 'manual':
                break;

                case 'both':
                    $content = mashshareShow($atts, '') . $content . mashshareShow($atts, "bottom", $url, $title);
                break;

                case 'before':
                    $content = mashshareShow($atts, '', $url, $title) . $content;
                break;

                case 'after':
                    $content .= mashshareShow($atts, '', $url, $title);
                break;
            }

            return $content;

        }


/* Deprecated: Template function mashsharer()
 * @since 1.0
 * @return string
*/ 
function mashsharer(){
    global $content;
    global $atts;
    global $url;
    global $title;
    global $post;
    $url = get_permalink($post->ID);
    $title = addslashes(the_title_attribute('echo=0'));
    echo mashshareShow($atts, '', $url, $title);
}

/* Template function mashshare() 
 * @since 2.0.0
 * @return string
*/ 
function mashshare(){
    global $content;
    global $atts;
    global $url;
    global $title;
    global $post;
    $url = get_permalink($post->ID);
    $title = addslashes(the_title_attribute('echo=0'));
    echo mashshareShow($atts, '', $url, $title);
}


/**
 * Get Thumbnail image if existed
 *
 * @since 1.0
 * @param int $postID
 * @return void
 */
function mashsb_get_image($postID){
            global $post;
            if (has_post_thumbnail( $post->ID )) {
				$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
				return $image[0];
            	}
	}
add_action( 'mashsb_get_image', 'mashsb_get_image' );

/**
 * Get excerpt for Facebook Share
 *
 * @since 1.0
 * @param int $postID
 * @return void
 */
function mashsb_get_excerpt_by_id($post_id){
	$the_post = get_post($post_id); //Gets post ID
	$the_excerpt = $the_post->post_content; //Gets post_content to be used as a basis for the excerpt
	$excerpt_length = 35; //Sets excerpt length by word count
	$the_excerpt = strip_tags(strip_shortcodes($the_excerpt)); //Strips tags and images
	$words = explode(' ', $the_excerpt, $excerpt_length + 1);
	if(count($words) > $excerpt_length) :
	array_pop($words);
	array_push($words, '…');
	$the_excerpt = implode(' ', $words);
	endif;
	$the_excerpt = '<p>' . $the_excerpt . '</p>';
	return wp_strip_all_tags($the_excerpt);
}
add_action( 'mashsb_get_excerpt_by_id', 'mashsb_get_excerpt_by_id' );

/**
 * Creat a factor for calculating individual fake counts 
 * based on the number of word within a page title
 *
 * @since 2.0
 * @return int
 */
function mashsb_get_fake_factor(){
        //global $post;
        //$the_post = get_post($post_id); //Gets post ID

	$wordcount = str_word_count(the_title_attribute('echo=0')); //Gets title to be used as a basis for the count
        $factor = $wordcount / 10;
        return apply_filters('mashsb_fake_factor', $factor);
}

/**
 * Add Custom Styles with WP wp_add_inline_style Method
 *
 * @since 1.0
 * 
 * @return string
 */

function mashsb_styles_method() {
    global $mashsb_options;
    /*trailingslashit( plugins_url(). '/mashshare-likeaftershare/templates/'    ) . $file;
    wp_enqueue_style(
		'custom-style',
		plugins_url() . '/mashshare-likeaftershare/templates/'
	);*/
    
    /* VARS */
    $share_color = $mashsb_options['share_color'];
    $custom_css = $mashsb_options['custom_css'];
    
    /* STYLES */
    $mashsb_custom_css = "
        .mashsb-count {
        color: {$share_color};
       
        }"; 
    if ($mashsb_options['border_radius']  != 'default'){
    $mashsb_custom_css .= '
        [class^="mashicon-"], .onoffswitch-label, .onoffswitch2-label {
            border-radius: ' . $mashsb_options['border_radius'] . 'px;
        }';   
    }
    if ($mashsb_options['mash_style']  == 'shadow'){
    $mashsb_custom_css .= '
        .mashsb-buttons a, .onoffswitch, .onoffswitch2, .onoffswitch-inner:before, .onoffswitch2-inner:before  {
            -webkit-transition: all 0.07s ease-in;
            -moz-transition: all 0.07s ease-in;
            -ms-transition: all 0.07s ease-in;
            -o-transition: all 0.07s ease-in;
            transition: all 0.07s ease-in;
            box-shadow: 0 1px 0 0 rgba(0, 0, 0, 0.2),inset 0 -1px 0 0 rgba(0, 0, 0, 0.3);
            text-shadow: 0 1px 0 rgba(0, 0, 0, 0.25);
            border: none;
            -moz-user-select: none;
            -webkit-font-smoothing: subpixel-antialiased;
            -webkit-transition: all linear .25s;
            -moz-transition: all linear .25s;
            -o-transition: all linear .25s;
            -ms-transition: all linear .25s;
            transition: all linear .25s;
        }';   
    }
    $mashsb_custom_css .= $custom_css;
        // ----------- Hook into existed 'mashsb-style' at /templates/mashsb.min.css -----------
        wp_add_inline_style( 'mashsb-styles', $mashsb_custom_css );
}
add_action( 'wp_enqueue_scripts', 'mashsb_styles_method' );