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
add_filter('the_content', 'mashshare_filter_content', getExecutionOrder());
add_filter('widget_text', 'do_shortcode');
add_action('mashshare', 'mashshare');

/* Get Execution order of injected Share Buttons in $content 
 * Set global var $enablescripts to determine if js and css must be loaded in frontend
 * 
 * @scince 2.0.4
 * @return int
 */

function getExecutionOrder(){
    global $mashsb_options, $enablescripts;
    $priority = mashsb_get_option('execution_order');
    if (is_int($priority)){
        return $priority;
    }
    /* return priority*/
    return 1000;
}

    /* Get share counts of current page by sharedcount.com and write them in database
     * @scince 1.0
     * @return void
     */    
    function mashshareGetTotal($url) {
        mashdebug()->timer('mashshareGetTotal');
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
            mashdebug()->info("Debug: This page has " . $counts["Twitter"] ." Tweeter tweets, " . $counts["Facebook"]["like_count"] . " Facebook likes, and ". $counts["GooglePlusOne"] . " Google +1's and " . $counts['Pinterest']. " Pinterest counts and " . $counts['stumbleUpon'] . " StumbleUpon counts and " . $counts['LinkedIn'] . "Linked in counts");      
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
            mashdebug()->timer('mashshareGetTotal', true);
    }

    
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
        mashdebug()->timer('getSharedcount');
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
                mashdebug()->info('cached result value: ' . $results[0]->TOTAL_SHARES);
            }
           mashdebug()->info('transient key: ' . $mashsharer_transient_key);
          
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
            mashdebug()->timer('getSharedcount', true);
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
        mashdebug()->timer('getNetworks');
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
    mashdebug()->timer('getNetworks', true);
    return apply_filters('return_networks', $output);
    
}

    /* Select Share count from database and returns share buttons and share counts
     * @scince 1.0
     * @returns string
     */
    function mashshareShow($atts, $place) {
        mashdebug()->timer('timer');
        global $wpdb, $mashsb_options, $post, $title, $url;
       
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
                        . apply_filters('mashsb_sharecount_filter', $sharecount) .
                    '<div class="mashsb-buttons">' 
                        . getNetworks() . 
                        //'<a class="mashicon-facebook" href="javascript:void(0);"><span class="icon"></span><span class="text">' . __('Share&nbsp;on&nbsp;Facebook', 'mashsb') . '</span></a><a class="mashicon-twitter" href="javascript:void(0)"><span class="icon"></span><span class="text">' . __('Tweet&nbsp;on&nbsp;Twitter', 'mashsb') . '</span></a><a class="mashicon-google" href="javascript:void(0)"><span class="icon"></span><span class="text">' . __('Google+', 'mashsb') . '</span></a>' . mashsb_subscribe_button() .                     
                    '</div></div>
                    <div style="clear:both;"></div>'
                    . mashsb_subscribe_content() .
                    '</aside>
                        <!-- Share buttons made by mashshare.net - Version: ' . MASHSB_VERSION . '-->';
            mashdebug()->timer('timer', true);
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
        $sharecount = '';

        extract(shortcode_atts(array(
            'cache' => '3600',
            'shares' => 'true',
            'buttons' => 'true',
            'align' => 'left'
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
                    $sharecount = '<div class="mashsb-count" style="float:' . $align . ';"><div class="counts">' . $totalshares . '</div><span class="mashsb-sharetext">' . __('SHARES', 'mashsb') . '</span></div>';    
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
    
    /* Returns active status of Mashshare.
     * Used for scripts.php $hook
     * @scince 2.0.3
     * @return bool True if MASHSB is enabled on specific page or post.
     * @TODO: Check if shortcode [mashshare] is used in widget
     */
   
    function mashsbGetActiveStatus(){
       global $mashsb_options, $post;
       $option_posts = isset( $mashsb_options['post_types']['mashsharer_posts'] ) ? $mashsb_options['post_types']['mashsharer_posts'] : null;
       $option_pages = isset( $mashsb_options['post_types']['mashsharer_pages'] ) ? $mashsb_options['post_types']['mashsharer_pages'] : null;
       $frontpage = isset( $mashsb_options['frontpage'] ) ? $frontpage = 1 : $frontpage = 0;
       $currentposttype = get_post_type();
       
       if ($option_posts === 'Posts')
            $option_posts = 'post';
       if ($option_pages === 'Pages')
            $option_pages = 'page';
       
       $post_types = array(
            $option_posts,
            $option_pages
        );

       /* check if is not excluded from current page or post */
       if ($post_types && in_array($currentposttype, $post_types) && mashsb_is_excluded() !== true ) {
           mashdebug()->info("100");
           return true;
       }  
       
       /* Check if post types are allowed */
       mashdebug()->info("var frontpage enabled: " . $frontpage . " is_front_page(): " . is_front_page());
       if ($post_types && in_array($currentposttype, $post_types) && mashsb_is_excluded() !== true) {
           mashdebug()->info("200");
           return true;
       }
       /* Check if frontpage is allowed */
       if ($frontpage == 1 && is_front_page() == 1 && mashsb_is_excluded() !== true) {
           mashdebug()->info("300");
            return true;
       }
       /* Check if shortcode is used */ 
       if( has_shortcode( $post->post_content, 'mashshare' ) ) {
           mashdebug()->info("400");
            return true;
       } 
        
       if(has_action('mashshare') && mashsb_is_excluded() !== true) {
           mashdebug()->info("action");
            return true;    
       }
       
       if(has_action('mashsharer') && mashsb_is_excluded() !== true) {
           mashdebug()->info("action");
            return true;    
       } 
    }
    
    /* Returns true if post or page is not excluded
     * @scince 2.0.6
     * @returns bool
     */
    
function mashsb_is_excluded() {
    global $mashsb_options, $post;
           $excluded = isset( $mashsb_options['excluded_from'] ) ? $mashsb_options['excluded_from'] : null;
    if (strpos($excluded, ',') !== false) {
        mashdebug()->error("hoo");
        $excluded = explode(',', $excluded);
        if (in_array($post->ID, $excluded)) {
            return true;
        }
    } elseif ($post->ID == $excluded) {
        return true;
    } else {
        return false;
    }
}
    
    /* Returns Share buttons on specific positions
     * Uses the_content filter
     * @scince 1.0
     * @return string
     */
    function mashshare_filter_content($content){
   
        global $atts, $mashsb_options, $url, $title, $post;
        global $wp_current_filter;
        
        /* define some vars here to reduce multiple execution of some basic functions */
        $url = get_permalink($post->ID);
        $title = addslashes(the_title_attribute('echo=0'));
        
        $position = $mashsb_options['mashsharer_position'];
        $option_posts = isset( $mashsb_options['post_types']['mashsharer_posts'] ) ? $mashsb_options['post_types']['mashsharer_posts'] : null;
        $option_pages = isset( $mashsb_options['post_types']['mashsharer_pages'] ) ? $mashsb_options['post_types']['mashsharer_pages'] : null;

        $excluded = isset( $mashsb_options['excluded_from'] ) ? $mashsb_options['excluded_from'] : null;
        if (strpos($excluded, ',') !== false) {
             $excluded = explode(',', $excluded);
             if (in_array($post->ID, $excluded)) {
                return $content;
             }  
        }
    
        if ($post->ID == $excluded) {
                return $content;
        }  

        $frontpage = isset( $mashsb_options['frontpage'] ) ? $mashsb_options['frontpage'] : null;

        if ($option_posts === 'Posts')
            $option_posts = 'post';
        if ($option_pages === 'Pages')
            $option_pages = 'page';


        $post_types = array(
            $option_posts,
            $option_pages
        );
        
        $pt = get_post_type();
        if ($post_types && !in_array($pt, $post_types)) {
            return $content;
        }

	if (!is_singular()) {
            /* disabled to show mashshare on non singualar pages to do: allow mashshare on this pages
               We have to hardcode the share links into php source href instead using only js 
            */
            return $content;
        }
        if (in_array('get_the_excerpt', $wp_current_filter)) {
            return $content;
        }
        if ($frontpage == 0 && is_front_page() == 1) {
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
    mashdebug()->timer('mashsb_get_image');
            global $post;
            if (has_post_thumbnail( $post->ID )) {
				$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' );
				return $image[0];
            	}
    mashdebug()->timer('mashsb_get_image', true);
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
    mashdebug()->timer('mashsb_get_exerpt');
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
    mashdebug()->timer('mashsb_get_exerpt', true);
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
