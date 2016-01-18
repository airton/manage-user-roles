<?php
/**
 * @package   Manage User Roles
 * @author    Airton Vancin Junior <airtonvancin@gmail.com>
 * @license   GPL-2.0+
 * @link      https://github.com/airton/manage-user-roles
 * @copyright 2016 Airton
 *
 * @wordpress-plugin
 * Plugin Name:       Manage User Roles
 * Plugin URI:        https://github.com/airton/manage-user-roles
 * Description:       Show only of his own publications
 * Version:           1.0.0
 * Author:            Airton Vancin Junior
 * Author URI:        http://airtonvancin.com
 * Text Domain:       manage-user-roles
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/airton/manage-user-roles
 */

// Previne acesso direto
if( ! defined( 'ABSPATH' ) ){
	exit;
}

/**
 * mur_load_textdomain()
 *
 * Carrega arquivos de traduções.
 */
function mur_load_textdomain() {
	load_plugin_textdomain( 'manage-user-roles', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}

add_action( 'plugins_loaded', 'mur_load_textdomain' );

/**
 * mur_limit_posts_per_user()
 *
 * Limit posts for each user.
 */

function mur_limit_posts_per_user( $query ) {

	$current_user = wp_get_current_user();
	$author_role = $current_user->roles[0];
	$author_id = $current_user->ID;

    if( is_admin() && $query->is_main_query() && $query->is_main_query() && $author_role != 'administrator' ) {
        $query->set( 'author__in', array($author_id) );
    }
}

add_action( 'pre_get_posts', 'mur_limit_posts_per_user' );


/**
 * mur_remove_items_admin_bar()
 *
 * Remove edit post in admin bar
 */

function mur_remove_items_admin_bar() {
	global $wp_admin_bar;

	if( is_singular() ){
		$author_id_post = get_the_author_meta('ID');
		$current_user = wp_get_current_user();
		$author_role = $current_user->roles[0];
		$author_id = $current_user->ID;

		if( $author_id_post != $author_id && $author_role != 'administrator' ){
			//Remove the Edit post
			$wp_admin_bar->remove_menu('edit');
		}
	}
}
add_action( 'wp_before_admin_bar_render', 'mur_remove_items_admin_bar' );


/**
 * mur_block_editing_post()
 *
 * block post editing for different user
 */

function mur_block_editing_post() {
	$author_id_post = get_the_author_meta('ID');
	$current_user = wp_get_current_user();
	$author_role = $current_user->roles[0];
	$author_id = $current_user->ID;
}
add_action( 'load-post.php', 'mur_block_editing_post' );
