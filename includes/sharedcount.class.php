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
        
	$counts = array('shares'=>array(),'total'=>0);
	$counts['shares']['fb'] = $sharecounts['Facebook']['total_count'];;
	$counts['shares']['tw'] = $sharecounts['Twitter'];

	foreach ($counts['shares'] as $mashsbcounts => $sharecount) $counts['total'] += (int)$sharecount;
        mashdebug()->error("sharedcount.com getFBTWCounts: " . $counts['total']);
	return $counts;

}
/* Only used when mashshare-networks is enabled */
function getAllCounts(){

        $sharecounts = $this->get_sharedcount();
        
	$counts = array('shares'=>array(),'total'=>0);
	$counts['shares']['fb'] = $sharecounts['Facebook']['total_count'];
	$counts['shares']['tw'] = $sharecounts['Twitter'];
	$counts['shares']['gp'] = $sharecounts['GooglePlusOne'];
	$counts['shares']['li'] = $sharecounts['LinkedIn'];
	$counts['shares']['st'] = $sharecounts['StumbleUpon'];
	$counts['shares']['pin'] = $sharecounts['Pinterest'];

	foreach ($counts['shares'] as $sbserv => $sbsharecount) $counts['total'] += (int)$sbsharecount;
        mashdebug()->error("sharedcount.com getAllCounts: " . $counts['total']);
	return $counts;
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