<?php

switch_to_blog( $_POST['blog'] );
	$postdata = ['post_title' => $_POST['title'],
				'post_excerpt' => $_POST['lead'],
				'post_content' => $_POST['content'],
			   ];
	$post = wp_insert_post( $postdata );
	
	set_post_thumbnail( $post, $_POST['image'] );
	$redirect = $_POST['linkto'] == 'original' ? 'redirect' : '_redirect';
	add_post_meta($post, $redirect, $_POST['url']);

restore_current_blog();

$blogdata = get_blog_details( $_POST['blog'] );

$TWIGdata['edit_post_url'] = $blogdata->siteurl .'/wp-admin/post.php?post='. $post .'&action=edit';