<?php
/*
Class Name:   Purge Transients
Description:  Purge old mashsharer transients
Version:      1.0.0
@Scince       1.0.0
Author:       René Hermenau
*/


class mashsharerCache {
	public function purge_transients($older_than = '1 days', $safemode = true) {
		global $wpdb;

		$older_than_time = strtotime('-' . $older_than);
		if ($older_than_time > time() || $older_than_time < 1) {
			return false;
		}

		$transients = $wpdb->get_col(
			$wpdb->prepare( "
					SELECT REPLACE(option_name, '_transient_timeout_', '') AS transient_name 
					FROM {$wpdb->options} 
					WHERE option_name LIKE '\_transient\_timeout\_mash_%%'
						AND option_value < %s
			", $older_than_time)
		);
		if ($safemode) {
			foreach($transients as $transient) {
				get_transient($transient);
			}
		} else {
			$options_names = array();
			foreach($transients as $transient) {
				$options_names[] = '_transient_' . $transient;
				$options_names[] = '_transient_timeout_' . $transient;
			}
			if ($options_names) {
				$options_names = array_map(array($wpdb, 'escape'), $options_names);
				$options_names = "'". implode("','", $options_names) ."'";

				$result = $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name IN ({$options_names})" );
				if (!$result) {
					return false;
				}
			}
		}

		return $transients;
	}
}

?>