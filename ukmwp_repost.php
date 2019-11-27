<?php  
/* 
Plugin Name: UKM Wordpress Re-post
Plugin URI: http://www.ukm-norge.no
Description: Viser alle posts fra hele nettverket på en adminside, og gjør det mulig å re-poste disse til en annen blogg
Author: UKM Norge / M Mandal 
Version: 2.0 
Author URI: http://mariusmandal.no
*/

require_once('UKM/wp_modul.class.php');

class UKMrepost extends UKMWPmodul {
    public static $action = 'list';
    public static $path_plugin = null;

    /**
     * Register hooks
     */
    public static function hook() {
        // Menu
        if( get_option('site_type') == 'fylke' || !get_option('site_type') ) {
            add_action('admin_menu', ['UKMrepost', 'meny'], 101);
        }
        // Post save and delete
        add_action('save_post', ['UKMrepost','registerPost'] );
        //add_action('delete_post', ['UKMrepost','deletePost'], 8);
    }
    /**
     * Add menu
     */
    public static function meny() {
        $page = add_submenu_page(
            'edit.php',
            'Re-publisér', 
            'Re-publisér', 
            'administrator', 
            'ukm_repost',
            ['UKMrepost','renderAdmin']
        );
        add_action( 
            'admin_print_styles-' . $page, 
            ['UKMrepost', 'scripts_and_styles']
        );
    }

    public static function scripts_and_styles() {
		wp_enqueue_script('WPbootstrap3_js');
		wp_enqueue_script('UKMrepost_scripts', plugin_dir_url( __FILE__ ) .'ukmwp_repost.js');
        
        wp_enqueue_style('WPbootstrap3_css');
		wp_enqueue_style('UKMrepost_styles', plugin_dir_url( __FILE__ ) .'ukmwp_repost.css');
    }

    public static function registerPost( $post_id ) {
        global $blog_id, $wpdb, $post;
        static::require('controller/register.controller.php');
    }

    public static function unRegisterPost( $post_id ) {
        global $blog_id, $wpdb, $post;
        static::require('controller/unregister.controller.php');
        
    }
}


## HOOK MENU AND SCRIPTS
UKMrepost::init( __DIR__ );
UKMrepost::hook();

/*
add_action('admin_bar_menu', 'sarp_adminbar',500);

## HOOK INTO ADMIN BAR FOR RE-POST BUTTON

function sarp_adminbar() {
	global $wp_admin_bar, $blog_id, $post;
	if ( !is_super_admin() || !is_admin_bar_showing() || is_admin() || $blog_id == 1 || (is_object($post) && $post->post_type == 'page'))
		return;

	$wp_admin_bar->add_menu( array(
	'id' => 'sarp',
	'parent' => '',
	'title' => '<img src="//ico.ukm.no/recycle-menu.png" style="float: left; margin-top: 7px; margin-right: 8px;"  />'
	.'<div style="margin-top: 0px; float: left;">Re-publisér</div>',
	'href' => '/wp-admin/network/admin.php?page=ukmn_network_posts_admin&repost_blog='.$blog_id.'&repost='.get_the_ID() 
	) );
}
*/