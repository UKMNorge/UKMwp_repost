<?php
$TWIGdata = array();
$VIEW = 'list';

global $wpdb;
$TWIGdata['posts'] = $wpdb->get_results ( "SELECT *
										   FROM `ukm_network_posts`
										   ORDER BY `id` DESC",
										   ARRAY_A
										   );