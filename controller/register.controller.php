<?php
global $blog_id, $wpdb, $post;

/*
 * If autosave, we're missing some data, and
 * are not interested in logging the post
*/
if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
    return true;
}
/*
 * If created programatically, 'post_type' will not be set
 * Programatically: i.e. as in import.php-created
*/
if( empty( $post->post_type ) || $post->post_type != 'post' ) {
    return true; // programmatisk skapt innhold har ikke denne
}
/*
 * If user is not to edit this post, stop it
 * Why we'd come here is a mystery
*/
if ( !current_user_can( 'edit_post', $post_id ) ) {
    return true;
}
/*
 * We don't save revisions either (because it'd already be logged)
*/
if( wp_is_post_revision( $post_id ) ) {
    return true;
}
/*
 * We don't waste space on empty post data (sic)
*/
if( empty( $post->ID ) || (int)$post->ID == 0 ) {
    return true;
}
/*
 * When did we log on as an non-existent blog?!
*/
if( (int)$blog_id == 0 ) {
    return true;
}


/*
 * LET'S DO THE MAGIC 
 */


require_once('WPOO/WPOO/Post.php');
require_once('WPOO/WPOO/Author.php');

$WPOO = new WPOO_Post( $post );

$data = [
    'blog_id'   => $blog_id,
    'blog_name' => get_bloginfo( 'name' ),
    'post_id'   => $post->ID,
    'title'     => $WPOO->title,
    'lead'      => $WPOO->lead,
    'uri'       => $WPOO->url,
    'time'      => $WPOO->date,
    'featured_image' => $WPOO->image->url
];


$exists = $wpdb->query ( "SELECT `id`
                        FROM `ukm_network_posts`
                        WHERE `blog_id` = '". $blog_id."'
                        AND `post_id` = '". $post->ID ."'");

/*
 * If post is already logged, update now
*/
if( is_numeric( $exists ) && $exists > 0 ) {
    $where = [
        'blog_id' => $blog_id,
        'post_id' => $post->ID
    ];
    $res = $wpdb->update(
        'ukm_network_posts',
        $data,
        $where
    );
} 
/*
 * We've never seen this post before. Welcome!
*/
else {
    $wpdb->insert (
        'ukm_network_posts', 
        $data
    );
}