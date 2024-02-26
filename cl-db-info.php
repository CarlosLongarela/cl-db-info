<?php
/**
 * Database Info Plugin.
 *
 * @package      CL_DB_Info
 * @link         https://tabernawp.com
 * @since        1.0
 *
 * @wordpress-plugin
 * Plugin Name:       CL DB Info
 * Version:           1.0.0
 * Plugin URI:        https://github.com/CarlosLongarela/CL-DB-Info
 * Description:       A plugin to display information about the database and tools to optmize and repair it.
 * Author:            Carlos Longarela
 * Author URI:        https://tabernawp.com
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       cl-db-info
 * Domain Path:       /languages
 */

namespace CL\DB_Info;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Code is poetry but I am not a poet, sorry!' );
}

define( 'CL_DB_INFO_VERSION', '1.0' );
define( 'CL_DB_INFO_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'CL_DB_INFO_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load the text domain for translation.
 *
 * @return void
 */
function load_textdomain() {
	load_plugin_textdomain( 'cl-db-info', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'CL\DB_info\load_textdomain' );

/**
 * Load the plugin scripts, only if the user is on this admin page.
 *
 * @return void
 */
function load_scripts() {
	if ( isset( $_GET['page'] ) && 'cl-db-info' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_enqueue_script( 'cl-db-info', CL_DB_INFO_PLUGIN_URL . 'assets/cl-db-info.js', array(), CL_DB_INFO_VERSION, true );
	}
}
add_action( 'admin_enqueue_scripts', 'CL\DB_info\load_scripts' );

/**
 * Load the plugin styles, only if the user is on this admin page.
 *
 * @return void
 */
function load_styles() {
	if ( isset( $_GET['page'] ) && 'cl-db-info' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_enqueue_style( 'cl-db-info', CL_DB_INFO_PLUGIN_URL . 'assets/cl-db-info.css', array(), CL_DB_INFO_VERSION );
	}
}
add_action( 'admin_enqueue_scripts', 'CL\DB_info\load_styles' );

/**
 * Load the plugin menu.
 *
 * @return void
 */
function plugin_menu() {
	add_management_page(
		__( 'CL Database Info', 'cl-db-info' ),
		__( 'CL DB Info', 'cl-db-info' ),
		'manage_options',
		'cl-db-info',
		'CL\DB_info\info_page'
	);
}
add_action( 'admin_menu', 'CL\DB_info\plugin_menu' );

/**
 * Load the plugin page.
 *
 * @return void
 */
function info_page() {
	require_once CL_DB_INFO_PLUGIN_DIR . 'includes/info-page.php';
}
