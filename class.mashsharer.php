<?php
/*
 	Class Name: class.mashsharer.php
 	Author: Rene Hermenau
 *      version 1.0.0
 	@scince 1.0.0
 	Description: main class for mashsharer
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;
require_once 'class.debug.php';
    /*
      Sharer inspired by the Mashable one - get shares from the big four FB, Twitter, G+, LinkedIn
      Credit goes to http://www.internoetics.com/2014/02/03/display-total-number-of-social-shares-with-php-and-wordpress-shortcode/
     */

class mashsharer {
    function __construct() {
            
             #global $logme;
             #global $post;
             #global $wpdb;
             #$this->wpdb = $wpdb;
            //self::$instance = $this;
            add_shortcode('mashshare',array( $this, 'mashsharerShow'));
            add_filter('the_content', array( $this, 'mashsharer_filter_content'));
            //add_shortcode('mashshare',array( $this, 'mashsharerGetTotal'));
        } // __construct
        
        

    public function mashsharerGetTotal() {
        global $wpdb;
        global $post;
        global $url;
        $apikey = get_option('mashsharer_apikey');

            if (!$url)
                $url = get_permalink($post->ID);

            // We use curl instead file_get_contents for security purposes
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://api.sharedcount.com/?url=" . rawurlencode($url) . "&apikey=" . $apikey);
            /* For debugging */
            //curl_setopt($ch, CURLOPT_URL, "http://api.sharedcount.com/?url=" . rawurlencode('http://www.google.de') . "&apikey=" . $apikey);
            /* For debugging */
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            curl_close($ch);

            $counts = json_decode($output, true);
            //echo "This page has " . $counts["Twitter"] ." tweets, " . $counts["Facebook"]["like_count"] . " likes, and ". $counts["GooglePlusOne"] . "+1's";
            $total_count = $counts['Twitter'] + $counts['Facebook']['total_count'] + $counts['GooglePlusOne'] + 10; /* we add a fake number here for smaller websites */
            
            $sql = "select TOTAL_SHARES from ".MASHSHARER_TABLE." where URL='".$url."'"; 
            $results = $wpdb->get_results($sql);

            if(count($results) == 0)
		{
			if ($total_count >= 0)
                        {
			$sql =	"INSERT INTO ".MASHSHARER_TABLE." (URL, TOTAL_SHARES, CHECK_TIMESTAMP) values('".$url."'," . $total_count . ",now())";
                        }
                } else {
                        $sql =	"UPDATE ".MASHSHARER_TABLE." SET TOTAL_SHARES=".$total_count.", CHECK_TIMESTAMP=now() where URL='".$url."'";  
           
                }
            $wpdb->query($sql);
            /*echo "SQL ergebnisse" . count($results);
            echo "Anzahl shares" . $total_count;
            echo $sql;*/
    }
    
    public function mashsharerShow($atts) {
        global $wpdb;
        global $post;
        global $url;
        global $cacheexpire;
        $cacheexpire = get_option('mashsharer_cache_expire');
        //$cacheexpire = 1;
        $logme = new mashsharer_debug;
        extract(shortcode_atts(array(
            'cache' => '3600',
            'url' => 0,
            'f' => 1,
            'bgcolor' => '#ffffff',
            'bordercolor' => '#ffffff',
            'borderwidth' => '0',
            'bordertype' => 'solid',
            'fontcolor' => '#7fc04c',
            'fontsize' => '70',
            'fontweight' => 'bold',
            'padding' => '1',
            'fontfamily' => 'Helvetica Neue,Helvetica,Arial,sans-serif'
                        ), $atts));

        $title = "" . get_the_title() . "";


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
            
            if ($f)
                $return = '
                    <div style="line-height:10px;">&nbsp;</div>
                    <div class="mashsharer-box">
                    <div class="mashsharer-count"><font style="padding: ' . $padding . 'px ' . $padding . 'px ' . $padding . 'px ' . $padding . 'px; font-size: ' . $fontsize . 'px; font-weight:' . $fontweight . '; font-family: ' . $fontfamily . '; color: ' . $fontcolor . '; background-color: ' . $bgcolor . '; border: ' . $bordercolor . ' ' . $bordertype . ' ' . $borderwidth . 'px">' . $totalshares . '</font>
	            <br><span class="mashsharer-sharetext">SHARES</span>
	            </div>
                    <div class="mashsharer-buttons">
                      <a class="facebook" href="javascript:mashFbSharrer(\'' . $url . '\', \'' . $title . '\', \'Facebook share popup\', \'http://goo.gl/dS52U\', 520, 350)">Share on Facebook</a>	    
                      <a class="twitter" href="javascript:mashTwSharrer(\'' . $url . '\', \'' . $title . '\', \'Twitter share popup\', \'http://goo.gl/dS52U\', 520, 350)">Tweet on Twitter</a>
                    </div>
                    </div>
                    <div style="clear:both;:"></div>
                    ';
            return $return;
        
    }
    
    public function mashsharer_filter_content($content){
            global $atts;
            global $wp_current_filter;

            $position   = get_option('mashsharer_position');
            $option_posts = get_option('mashsharer_posts');
            $option_pages = get_option('mashsharer_pages');
            $pt         = get_post_type();
            
            if ($option_posts != 1)
                    $posts = 'post';
            if ($option_pages != 1)
                    $pages = 'page';

       
             if( $posts === $pt){
                return $content;
            }
             if( $pages === $pt){
                return $content;
            }

            switch($position){
                case 'manual':
                break;

                case 'both':
                    $content = $this->mashsharerShow($atts) . $content . $this->mashsharerShow($atts);
                break;

                case 'before':
                    $content = $this->mashsharerShow($atts) . $content;
                break;

                case 'after':
                    $content .= $this->mashsharerShow($atts);
                break;
            }

            return $content;

        }
}
new mashsharer;

?>