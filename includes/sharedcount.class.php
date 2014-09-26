<?php
/**
 * Sharecount functions
 * Get the share count from the service sharedcount.com
 *
 * @package     MASHSB
 * @subpackage  Functions/sharedcount
 * @copyright   Copyright (c) 2014, René Hermenau
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0.9
 */

class mashsbSharedcount {
    private $url,$timeout;

    function __construct($url,$timeout=10) {
        $this->url=rawurlencode($url);
        $this->timeout=$timeout;
        }

function getFBTWCounts(){
    
        $sharecounts = $this->get_sharedcount();
        
	$ret = array('shares'=>array(),'total'=>0);
	$ret['shares']['fb'] = $sharecounts['Facebook']['total_count'];;
	$ret['shares']['tw'] = $sharecounts['Twitter'];

	foreach ($ret['shares'] as $sbserv => $sbsharecount) $ret['total'] += (int)$sbsharecount;
        mashdebug()->error("sharedcount.com getFBTWCounts: " . $ret['total']);
	return $ret;

}
/* Only used when mashshare-networks is enabled */
function getAllCounts(){

        $sharecounts = $this->get_sharedcount();
        
	$ret = array('shares'=>array(),'total'=>0);
	$ret['shares']['fb'] = $sharecounts['Facebook']['total_count'];
	$ret['shares']['tw'] = $sharecounts['Twitter'];
	$ret['shares']['gp'] = $sharecounts['GooglePlusOne'];
	$ret['shares']['li'] = $sharecounts['LinkedIn'];
	$ret['shares']['st'] = $sharecounts['StumbleUpon'];
	$ret['shares']['pin'] = $sharecounts['Pinterest'];

	foreach ($ret['shares'] as $sbserv => $sbsharecount) $ret['total'] += (int)$sbsharecount;
        mashdebug()->error("sharedcount.com getAllCounts: " . $ret['total']);
	return $ret;
}

function get_sharedcount()  {
    mashdebug()->error("URL: " . $this->url);
    global $mashsb_options;
    $apikey = $mashsb_options['mashsharer_apikey'];

	try {
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, "http://free.sharedcount.com/?url=" . $this->url . "&apikey=" . $apikey);
		//curl_setopt($curl, CURLOPT_POST, true);
		//curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		//curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
		$curl_results = curl_exec ($curl);
		curl_close ($curl);
                $counts = json_decode($curl_results, true);
                
                mashdebug()->error("results: " . $counts['Twitter']);
                return $counts;
	} catch (Exception $e){
                mashdebug()->error("error: " . $counts);
		return 0;
	}
        mashdebug()->error("error2: " . $counts);
	return 0;
}

}
?>