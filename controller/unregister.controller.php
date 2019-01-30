<?php

global $blog_id, $wpdb;

if( (int)$post_id == 0 ) {
    return false;
}

$where = [
    'blog_id' => $blog_id,
    'post_id' => $pid
];
$res = $wpdb->update(
    'ukm_network_posts',
    ['deleted' => 'true'],
    $where
);