<?php

require_once('WPOO/WPOO/Author.php');
require_once('WPOO/WPOO/Post.php');

$TWIGdata = array();

$_GET['repost_to_blog'] = 1;

// SWITCH TO SOURCE BLOG TO COLLECT DATA
switch_to_blog( $_GET['repost_blog'] );
	$post = get_post( $_GET['repost'] );
	$post_id = $post->ID;
	setup_postdata($post);
	$wpoop = new WPOO_Post( $post );
	
	$image_id = $wpoop->image->ID;
	$file_original = get_attached_file( $image_id );
	$TWIGdata['post'] = $wpoop;
	
	
	global $wpdb;
	$metadata = $wpdb->get_row( "SELECT *
									  FROM `ukm_network_posts`
									  WHERE `blog_id` = '". (int)$_GET['repost_blog'] ."'
									  AND `post_id` = '". (int)$_GET['repost'] ."'",
									ARRAY_A
									);
									
	
// SWITCH TO TARGET BLOG 
switch_to_blog( $_GET['repost_to_blog'] );

	if( file_exists( $file_original ) ) {
		
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
			$filename = basename($file);
			$upload_file = wp_upload_bits($filename, null, file_get_contents($file));
			if (!$upload_file['error']) {
				$wp_filetype = wp_check_filetype($filename, null );
				$attachment = array(
					'post_mime_type' => $wp_filetype['type'],
					'post_parent' => $parent_post_id,
					'post_title' => preg_replace('/\.[^.]+$/', '', $filename),
					'post_content' => '',
					'post_status' => 'inherit'
				);
				$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], $parent_post_id );
				if (!is_wp_error($attachment_id)) {
					require_once(ABSPATH . "wp-admin" . '/includes/image.php');
					$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
					wp_update_attachment_metadata( $attachment_id,  $attachment_data );
				}
				
				$imagedata = wp_get_attachment_image_src($attachment_id,'large' );
				$TWIGdata['repost_img'] = $imagedata[0];
				$TWIGdata['image_id'] = $attachment_id;
			} else {
				$TWIGdata['error'] = 'En feil oppsto ved opplasting til mediebiblioteket: '. $upload_file['error'];
			}
			
		} else {
			$TWIGdata['error'] = !empty($TWIGdata['error']) ? $TWIGdata['error'] : 'Klarte ikke å finne lage ny bildefil';
		}
	} else {
		$TWIGdata['error'] = 'Klarte ikke å finne bildefilen '. $file;
	}


restore_current_blog();

unset($targetFile);