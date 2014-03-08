<?php
/*
 	Class Name: class.debug.php
 	Author: Rene Hermenau
 *      version 1.0.0
 	@scince 1.0.0
 	Description: debug class for mashsharer
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class mashsharer_debug {
    /* Log me function for debugging */

    function log_me($message) {
        if (WP_DEBUG === true) {
            if (is_array($message) || is_object($message)) {
                error_log(print_r($message, true));
            } else {
                error_log($message);
            }
        }
    }

}

?>
