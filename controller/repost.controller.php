<?php

// WP admin tweak :: recommended fields skal IKKE kjøre nå
define('UKMwpat_ignore', true);
/*
 * CREATE POST 
 */
$postdata = [
    'post_title' => $_POST['title'],
    'post_excerpt' => $_POST['lead'],
    'post_content' => $_POST['content'],
    'post_status' => $_POST['submit']
];
$post_id = wp_insert_post( $postdata );

/*
 * SET FEATURED IMAGE
*/
set_post_thumbnail( $post_id, $_POST['image'] );

/*
 * ADD REDIRECT IF THATS INTENDED FUNCTIONALITY
*/
if( $_POST['linkto'] == 'original' ) {
    add_post_meta($post_id, 'redirect', $_POST['url']);    
}

/*
 * ADD REPOST METADATA
*/
add_post_meta($post_id, 'repost', $_POST['blog_origin_name'] );

/*
 * VIEW DATA
*/
UKMrepost::addViewData(
    'edit_post_url',
    'post.php?post='. $post_id .'&action=edit'
);
UKMrepost::addViewData('post_status', $_POST['submit'] );