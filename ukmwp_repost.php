<?php  
/* 
Plugin Name: UKMwp RePost
Plugin URI: http://www.ukm-norge.no
Description: Viser alle posts fra hele nettverket på en adminside, og gjør det mulig å re-poste disse til en annen blogg
Author: UKM Norge / M Mandal 
Version: 1.0 
Author URI: http://mariusmandal.no
*/

add_action( 'save_post', 'ukmn_network_posts' );
add_action('delete_post', 'ukmn_network_posts_delete', 8);
#add_action('UKM_admin_menu', 'ukmn_network_posts_menu');

function ukmn_network_posts_menu() {
	UKM_add_menu_page('content','Re-publish', 'Re-publish', 'administrator', 'UKMN_network_posts_admin', 'UKMN_network_posts_admin', 'http://ico.ukm.no/recycle-menu.png',9.1);    
}

function UKMN_network_posts_admin() {
	echo 'ello';
}

## HANDLE DELETE
// DELETE FROM RELATED-TABLE
function ukmn_network_posts_delete( $pid ) {
	global $blog_id, $wpdb;
	if((int)$pid == 0)
		return false; 
	
	$where = array('blog_id' => $blog_id,
				  'post_id' => $pid);
	$res = $wpdb->update( 'ukm_network_posts', array('deleted'=>'true'), $where );
}

## HANDLE SAVE
function ukmn_network_posts( $post_id ) {
	// verify if this is an auto save routine.  // If it is our form has not been submitted, so we dont want to do anything
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	// Check permissions
	if ( 'page' == $_POST['post_type'] ){ if ( !current_user_can( 'edit_page', $post_id ) ) return;  } 
	else { if ( !current_user_can( 'edit_post', $post_id ) ) return; }

	## IKKE LAGRE HVIS REVISJON, SJEKK OM DET FUNKER!!
#	if( $post->post_type == 'revision' ) return; // Don't store custom data twice
	
 	// OK, we're authenticated: we need to find and save the data
	global $blog_id, $wpdb, $post;
	
	if(empty($post->ID)||(int)$post->ID == 0)
		return false;
	if((int) $blog_id == 0)
		return false;

	// WPOO-object
	require_once('WPOO/WPOO/Post.php');
	require_once('WPOO/WPOO/Author.php');
	
	$WPOO = new WPOO_Post( $post );

	$data = array('blog_id' => $blog_id,
				  'post_id' => $post->ID,
				  'title' => $WPOO->title,
				  'lead' => $WPOO->lead,
				  'uri' => $WPOO->url,
				  'featured_image' => $WPOO->image->url);
	

	$inserted = $wpdb->query ( "SELECT `id`
							   FROM `ukm_network_posts`
							   WHERE `blog_id` = '". $blog_id."'
							   AND `post_id` = '". $post->ID ."'");
	
	
	if( is_numeric( $inserted ) && $inserted > 0 ) {
		$where = array('blog_id' => $blog_id,
					  'post_id' => $post->ID);
		$res = $wpdb->update( 'ukm_network_posts', $data, $where );
	} else {
		$wpdb->insert ( 'ukm_network_posts', $data );
	}
}
