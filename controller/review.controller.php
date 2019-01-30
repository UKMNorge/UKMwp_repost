<?php

require_once('WPOO/WPOO/Author.php');
require_once('WPOO/WPOO/Post.php');

global $blog_id, $wpdb;

/*
 * COLLECT DATA FROM ORIGINAL BLOG
 */
switch_to_blog( $_GET['repost_blog'] );
$post = get_post( $_GET['repost'] );
$post_id = $post->ID;
setup_postdata($post);
$wpoop = new WPOO_Post( $post );
$image_id = $wpoop->image->ID;
$file_original = get_attached_file( $image_id );

$origin_blog_name = get_bloginfo('name');

$metadata = $wpdb->get_row( 
    "SELECT *
    FROM `ukm_network_posts`
    WHERE `blog_id` = '". (int)$_GET['repost_blog'] ."'
    AND `post_id` = '". (int)$_GET['repost'] ."'",
    ARRAY_A
);
									
	
/*
 * GO HOME FOR PUBLISHING
 */
restore_current_blog();

/*
    * If not able to access original image, we'll not re-publish
*/
if( !file_exists( $file_original ) ) {
    throw new Exception(
        'Klarte ikke å lese bilde-informasjon. For re-publisering må saken ha et fremhevet bilde. '.
        '<br />'.
        '<code>'. $file_original .'</code>'
    );
}
    
/*
    * Upload file to current blog
*/
$filename = basename($file_original);
$upload_file = wp_upload_bits($filename, null, file_get_contents($file_original));

if( $upload_file['error'] ) {
    throw new Exception(
        'Kunne ikke opprette bilde på bloggen. '.
        'Prøv igjen, og '.
        '<a href="mailto:support@ukm.no?subject=UKMrepost error">kontakt UKM Norge</a> '.
        'om feilen vedvarer. '.
        '<br />'.
        '<code>'. $upload_file['error'] .'</code>'
    );
}
    
/*
    * Create image as attachment to post
*/
$wp_filetype = wp_check_filetype($filename, null );
$attachment = [
    'post_mime_type' => $wp_filetype['type'],
    'post_parent'   => $parent_post_id,
    'post_title'    => preg_replace('/\.[^.]+$/', '', $filename),
    'post_content'  => '',
    'post_status'   => 'inherit'
];
$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $parent_post_id );


if( is_wp_error($attachment_id) ) {
    throw new Exception(
        'Kunne ikke opprette bilde på bloggen. '.
        'Prøv igjen, og '.
        '<a href="mailto:support@ukm.no?subject=UKMrepost error">kontakt UKM Norge</a> '.
        'om feilen vedvarer'
    );
}

/*
    * Generate image metadata
*/
require_once(ABSPATH . "wp-admin" . '/includes/image.php');
$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
wp_update_attachment_metadata( $attachment_id,  $attachment_data );

$imagedata = wp_get_attachment_image_src($attachment_id,'large' );

/*
    * Set view data 
*/
UKMrepost::addViewData('repost_img', $imagedata[0]);
UKMrepost::addViewData('image_id', $attachment_id);
UKMrepost::addViewData('post', $wpoop);
UKMrepost::addViewData('origin_blog_name', $origin_blog_name);

/*
// GENERATE OVERLAY IMAGE
$ext = substr($file_original, strrpos($file_original, '.')+1);
$file = '/tmp/imageOverlay/'. (int)$_GET['repost_blog'] .'-'. (int)$_GET['repost'] .'.'. $ext;
// Opprett tmp-dir
if( !file_exists( dirname($file) ) ) {
    mkdir( dirname($file), 0777, true);
}

// Generer bildet
try {
    $convert = image_overlay_for_repost( $file_original, $metadata['blog_name'], $file );
} catch( Exception $e ) {
    $convert = false;
    $TWIGdata['error'] = 'En feil oppsto ved bildegenerering: '. $e->getMessage();
}

if( $convert ) {
    unset($file);
    håndter resten
}
 */   