<?php
/**
 * Database info page.
 *
 * @package CL_DB_Info
 */

namespace CL\DB_Info;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( 'Code is poetry but I am not a poet, sorry!' );
}

// Check if the user has the capability to manage options.
if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'cl-db-info' ) );
}

/**
 * Load the database information. Internal use only.
 *
 * @return array
 */
function _get_and_save_db_global_info() {
	global $wpdb;
	$database_info = $wpdb->get_results( 'SHOW GLOBAL STATUS' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

	set_transient( 'cl_db_info', $database_info, DAY_IN_SECONDS );

	return $database_info;
}

/**
 * Load the tables information. Internal use only.
 *
 * @return array
 */
function _get_and_save_db_table_info() {
	global $wpdb;
	$tables_info = $wpdb->get_results( 'SHOW TABLE STATUS' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

	set_transient( 'cl_db_tables_info', $tables_info, DAY_IN_SECONDS );

	return $tables_info;
}

/**
 * Load the tables information.
 *
 * @param bool $nocache Whether to avoid cache or not. Default is false.
 *
 * @return array
 */
function get_tables_info( $nocache = false ) {
	if ( $nocache ) {
		$tables_info = _get_and_save_db_table_info();
	} else {
		$tables_info = get_transient( 'cl_db_tables_info' );

		// Check if the cache is empty.
		if ( false === $tables_info ) {
			$tables_info = _get_and_save_db_table_info();
		}
	}

	return $tables_info;
}

/**
 * Load the database information.
 *
 * @param bool $nocache Whether to avoid cache or not. Default is false.
 *
 * @return array
 */
function get_db_info( $nocache = false ) {
	if ( $nocache ) {
		$database_info = _get_and_save_db_global_info();
	} else {
		$database_info = get_transient( 'cl_db_info' );

		// Check if the cache is empty.
		if ( false === $database_info ) {
			$database_info = _get_and_save_db_global_info();
		}
	}

	return $database_info;
}

/**
 * Format bytes.
 *
 * @param int|float $bytes    The bytes to format.
 * @param int       $decimals The number of decimals. Default is 2.
 *
 * @return string
 */
function format_bytes( $bytes, $decimals = 2 ) {
	if ( ! is_numeric( absint( $bytes ) ) || empty( absint( $bytes ) ) ) {
		return '0 B';
	}

	$size   = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' );
	$factor = floor( ( strlen( $bytes ) - 1 ) / 3 );

	if ( isset( $size[ $factor ] ) ) {
		$tam = $bytes / pow( 1024, $factor );

		if ( floor( $tam ) === $tam ) {
			$decimals = 0;
		}

		return number_format_i18n( $tam, $decimals ) . ' ' . $size[ $factor ];
	} else {
		return '0 B';
	}
}

/**
 * Format number.
 *
 * @param int|float $number   The number to format.
 * @param int       $decimals The number of decimals. Default is 0.
 *
 * @return string
 */
function format_number( $number, $decimals = 0 ) {
	if ( ! is_numeric( absint( $number ) ) || empty( absint( $number ) ) ) {
		return '0';
	}
	return number_format_i18n( $number, $decimals );
}

if ( isset( $_GET['nocache'] ) && 1 === $_GET['nocache'] && wp_verify_nonce( absint( wp_unslash( $_GET['nocache'] ) ), 'cl-db-info-nonce' ) ) {
	$tables_info   = get_tables_info( false );
	$database_info = get_db_info( false );
} else {
	$tables_info   = get_tables_info();
	$database_info = get_db_info();
}


echo '<div class="wrap cl-db-info">';
echo '<h2>' . esc_html__( 'CL Database Info', 'cl-db-info' ) . '</h1>';

// Add cl-db-info-nonce nonce to the URL.
$no_cache_url = add_query_arg(
	array(
		'nocache'          => 1,
		'cl-db-info-nonce' => wp_create_nonce( 'cl-db-info-nonce' ),
	),
);
echo '<p><a href="' . esc_url( $no_cache_url ) . '">' . esc_html__( 'Reload the database information without cache', 'cl-db-info' ) . '</a></p>';

// Display the database information on a table.
echo '<table class="widefat">';
echo '<thead>';
echo '<tr>';
echo '<th>' . esc_html__( 'Table Name', 'cl-db-info' ) . '</th>';
echo '<th>' . esc_html__( 'Table Engine', 'cl-db-info' ) . '</th>';
echo '<th>' . esc_html__( 'Rows', 'cl-db-info' ) . '</th>';
echo '<th>' . esc_html__( 'Data Length', 'cl-db-info' ) . '</th>';
echo '<th>' . esc_html__( 'Index Length', 'cl-db-info' ) . '</th>';
echo '<th>' . esc_html__( 'Data Free', 'cl-db-info' ) . '</th>';
echo '<th>' . esc_html__( 'Auto Increment', 'cl-db-info' ) . '</th>';
echo '<th>' . esc_html__( 'Collation', 'cl-db-info' ) . '</th>';
echo '</tr>';
echo '</thead>';

echo '<tbody>';
foreach ( $tables_info as $table ) {
	echo '<tr>';
	echo '<td class="cl-tooltip-box">' . esc_html( $table->Name ) . '<span class="cl-tooltip-text">' . esc_html( $table->Create_time ) . '</span></td>';
	echo '<td>' . esc_html( $table->Engine ) . '</td>';
	echo '<td>' . esc_html( format_number( $table->Rows ) ) . '</td>';
	echo '<td>' . esc_html( format_bytes( $table->Data_length ) ) . '</td>';
	echo '<td>' . esc_html( format_bytes( $table->Index_length ) ) . '</td>';
	echo '<td>' . esc_html( format_bytes( $table->Data_free ) ) . '</td>';
	echo '<td>' . esc_html( format_number( $table->Auto_increment ) ) . '</td>';
	echo '<td>' . esc_html( $table->Collation ) . '</td>';
	echo '</tr>';
}
echo '</tbody>';

echo '</table>';

echo '<pre>';
print_r( $tables_info );
echo '</pre>';

echo '<pre>';
print_r( $database_info );
echo '</pre>';

echo '</div>';
