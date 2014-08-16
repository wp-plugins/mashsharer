<?php
/*
 *	Class Name: class.mashsharer.php
 *	Author: Rene Hermenau
 *  @version 1.1.4
 *	@scince 1.1.1
 *	Description: main class for mashsharer
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
require_once 'class.debug.php';
    /*
      Sharer inspired by the Mashable one - get shares from the big four FB, Twitter, G+, LinkedIn
     */

class mashsharer {
    function __construct() {
            
             #global $logme;
             #global $post;
             #global $wpdb;
             #$this->wpdb = $wpdb;
            //self::$instance = $this;
            add_shortcode('mashshare',array( $this, 'mashsharerShow'));
            add_filter('the_content', array( $this, 'mashsharer_filter_content'), 1000);
            add_filter('widget_text', 'do_shortcode');
        } // __construct
        
        

    public function mashsharerGetTotal() {
        global $wpdb;
        global $post;
        global $url;
        
        $counts['Twitter'] = 0;
        $counts['Facebook']['total_count'] = 0;
        $counts['GooglePlusOne'] = 0;
        $counts['Pinterest'] = 0;
        $counts['LinkedIn'] = 0;
        $counts['StumbleUpon'] = 0;
 
        $apikey = get_option('mashsharer_apikey');

            if (!$url)
                $url = get_permalink($post->ID);

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
            
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
     		if (class_exists('mashshare_networks')) {
			    $total_count = $counts['Twitter'] + $counts['Facebook']['total_count'] + $counts['GooglePlusOne'] + $counts['Pinterest'] + $counts['LinkedIn'] + $counts['StumbleUpon']; /* we can add a fake number here for smaller websites */
		    }else {
		        $total_count = $counts['Twitter'] + $counts['Facebook']['total_count']; /* we can add a fake number here for smaller websites */
		    }
            //echo "This page has " . $counts["Twitter"] ." tweets, " . $counts["Facebook"]["like_count"] . " likes, and ". $counts["GooglePlusOne"] . "+1's";
			$sql = "select TOTAL_SHARES from ".MASHSHARER_TABLE." where URL='".$url."'"; 
            $results = $wpdb->get_results($sql);

			$sharecountdb = $wpdb->get_var("select TOTAL_SHARES from ".MASHSHARER_TABLE." where URL='".$url."'");

			
            if(count($results) == 0)
		    {
					if ($total_count >= 0)
                        {
			             $sql =	"INSERT INTO ".MASHSHARER_TABLE." (URL, TOTAL_SHARES, CHECK_TIMESTAMP) values('".$url."'," . $total_count . ",now())";
                        }
            } else {
						/*  We only update sharecountdb if the API total_count is higher than the sharcountdb value
						 *  This prevents zero counts when the API is down
						 */
						if ($sharecountdb < $total_count) {
                         $sql =	"UPDATE ".MASHSHARER_TABLE." SET TOTAL_SHARES=".$total_count.", CHECK_TIMESTAMP=now() where URL='".$url."'";  
						}
                   }
            $wpdb->query($sql);
            /*echo "SQL ergebnisse" . count($results);
            echo "Anzahl shares" . $total_count;
            echo $sql;*/
    }
    
    /* DEFINE ADDONS */
    public function mashload($place){
        global $addons;
        	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		//if (class_exists('mashshare_networks') && is_plugin_active('mashshare-networks/mashshare-networks.php')) {
		if (class_exists('mashshare_networks')) {
                        include_once(ABSPATH . 'wp-content/plugins/mashshare-networks/mashshare-networks.php');
			$networks = new mashshare_networks();
			$addons = $networks->mashshare_get_networks($place);
                        return $addons;
		}
        return '';
    }
    
    public function mashsharerShow($atts, $place) {
        global $wpdb;
        global $post;
        global $url;
        global $cacheexpire;
        global $addons;
        //global $place;
        $cacheexpire = get_option('mashsharer_cache_expire');
        //$cacheexpire = 1;
        $logme = new mashsharer_debug;
		
	    /* Load addons */
        $addons = $this->mashload($place);	

		
        extract(shortcode_atts(array(
            'cache' => '3600',
            'url' => 0,
            'f' => 1,
            'bgcolor' => '',
            'bordercolor' => '#ffffff',
            'borderwidth' => '0',
            'bordertype' => 'solid',
            'fontcolor' => '#7fc04c',
            'fontsize' => '70',
            'fontweight' => 'bold',
            'padding' => '1',
            'fontfamily' => 'Helvetica Neue,Helvetica,Arial,sans-serif'
                        ), $atts));

      
            $title = addslashes(the_title_attribute('echo=0')); 
			if (get_option('mashsharer_hashtag')!= ''){
		    $hashtag = '&via=' . get_option('mashsharer_hashtag');   
			} else {
			$hashtag = '';
			}
					
            if (!$url)
                $url = get_permalink($post->ID);
  
            /* Create transient caching function */
            //$mashsharer_transient_key = "mashsharer_".$url;
            $mashsharer_transient_key = "mash_".md5($url);
            $results = get_transient($mashsharer_transient_key);
            //echo $mashsharer_transient_key;
            //delete_transient($mashsharer_transient_key);
            if ($results === false) {
                //echo "not cached";
                $logme->log_me('SQL data not cached');      
                /* Get the share counts and write them to database when cache is expired */
                $this->mashsharerGetTotal();
                $sql = "select TOTAL_SHARES from " . MASHSHARER_TABLE . " where URL='" . $url . "'";
                $results = $wpdb->get_results($sql);
                set_transient($mashsharer_transient_key, $results, $cacheexpire);
            } else {
                //echo "its cached";
                $logme->log_me ('sql data cached');
                $results = get_transient($mashsharer_transient_key);
                //echo " cached results: ".$results;
            }
          //echo $mashsharer_transient_key;
            
            if(empty($results))
		{
                        $totalshares = 0;
                } else {
                        $totalshares = $results[0]->TOTAL_SHARES; 
                }
				
				/* 
				Round the totalshares
				*/
			if (get_option('mashsharer_round')){
				if ($totalshares > 1000000) { 
				$totalshares = round($totalshares/1000000,1).'mln'; } 
				elseif ($totalshares > 1000) { 
				$totalshares = round($totalshares/1000,1).'k'; }
			}
            
            if ($f)
                $return = '
                    <div style="line-height:10px;">&nbsp;</div>
                    <div class="mashsharer-box">
                    <div class="mashsharer-count"><font style="display:block;padding-bottom:17px;font-size: ' . $fontsize . 'px; font-weight:' . $fontweight . '; font-family: ' . $fontfamily . '; color: ' . $fontcolor . '; background-color: ' . $bgcolor . '; border: ' . $bordercolor . ' ' . $bordertype . ' ' . $borderwidth . 'px">' . $totalshares . '</font>
	            <span class="mashsharer-sharetext">SHARES</span>
	            </div>
                    <div class="mashsharer-buttons">
<a class="facebook" onclick="javascript:mashFbSharrer(\'' . $url . '\',\'' . esc_html($title) . '\', \'Facebook share popup\',\'http://goo.gl/dS52U\',520,350)" href="javascript:void(0);">Share on Facebook</a>
<a class="twitter" onclick="javascript:mashTwSharrer(\'' . $url . '\', \'' . html_entity_decode(get_the_title()) . $hashtag . '\', \'Twitter share popup\', \'http://goo.gl/dS52U\', 520, 350)" href="javascript:void(0)">Tweet on Twitter</a>
                    </div>'
                    . $addons .
                    '</div>
                    <div style="clear:both;:"></div>
                    ';
            return $return;
        
    }
    
    public function mashsharer_filter_content($content){
            global $atts;
            global $wp_current_filter;
            global $pages;
			
			
            $position   = get_option('mashsharer_position');
            $option_posts = get_option('mashsharer_posts');
            $option_pages = get_option('mashsharer_pages');
            $pt         = get_post_type();
            $frontpage = get_option('mashsharer_frontpage');
            
			
            if ($option_posts == '1')
                    $option_posts = 'post';
            if ($option_pages == '1')
                    $option_pages = 'page';

				$post_types = array(
									$option_posts,
									$option_pages
									);
									
			if( $post_types && !in_array($pt,$post_types)){
                // disabled to show mashshare on blog frontpage
                //return $content;
            }
	   
             /*if( $posts === $pt){
                return "posts".$content . "content is empty";
            }
             if( $pages === $pt){
                return "pages".$content;
            }*/
			
            
			if ( !is_singular() ){
                return $content; 
            }
			
            if( in_array('get_the_excerpt', $wp_current_filter) ) {
                return $content;
            }
            
            if ($frontpage == 0 && is_front_page()== true){
                return $content;
            }
			

			if( is_feed() ) {
                return $content;
            }
			
            switch($position){
                case 'manual':
                break;

                case 'both':
                    $content = $this->mashsharerShow($atts, '') . $content . $this->mashsharerShow($atts, "bottom");
                break;

                case 'before':
                    $content = $this->mashsharerShow($atts, '') . $content;
                break;

                case 'after':
                    $content .= $this->mashsharerShow($atts, '');
                break;
            }

            return $content;

        }
}
new mashsharer;

?>