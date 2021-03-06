<?php
/**
 * @package UR_Connect
 */
/*
Plugin Name: UR_Connect
Plugin URI: https://mitiendaenlinea.com.mx/
Description: Developed for expose custom wordpress endpoints
Version: 1.0.0
Author: aotech
License: GPLv2 or later
Text Domain: ur_connect
*/

/**
 * WP_REST_Categories_Controller class.
 */
if ( ! class_exists( 'WP_REST_Categories_Controller' ) ) {
	require_once dirname( __FILE__ ) . '/lib/endpoints/class-wp-rest-categories-controller.php';
}

/**
 * WP_REST_Articles_Controller class.
 */
if ( ! class_exists( 'WP_REST_Articles_Controller' ) ) {
	require_once dirname( __FILE__ ) . '/lib/endpoints/class-wp-rest-articles-controller.php';
}
