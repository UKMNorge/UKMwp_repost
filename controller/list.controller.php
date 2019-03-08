<?php

global $wpdb;
global $current_user;
global $blog_id;

// Fetch blogs of user
$blogs = [];
$user_blogs = get_blogs_of_user( $current_user->ID );
if( is_array( $user_blogs ) ) {
    foreach( $user_blogs as $blog_data ) {
        $blogs[] = $blog_data->userblog_id;
    }
}

// Remove current blog from list
if(($key = array_search($blog_id, $blogs)) !== false) {
    unset($blogs[$key]);
}

if( !isset( $_GET['pagination'] ) ) {
	$_GET['pagination'] = 0;
}

$limit = 60;
$start = $limit * (int) $_GET['pagination'];

// Query and add view data
UKMrepost::addViewData(
    'posts', 
    $wpdb->get_results(
        "SELECT *
        FROM `ukm_network_posts`
        WHERE `deleted` = 'false'
        AND `blog_id` IN ( ". implode(',', $blogs) ." )
        ORDER BY `id` DESC
        LIMIT $start, $limit",
        ARRAY_A
    )
);

UKMrepost::addViewData('pagination', $_GET['pagination']);
UKMrepost::addViewData('limit', $limit);
UKMrepost::addViewData('site_type', get_option('site_type'));