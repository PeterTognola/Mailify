<?php
/*
 * Plugin Name: Mailify
 * Version: 1.0
 * Plugin URI: http://www.phoenixs.co.uk/
 * Description: Some kind of contact form plugin built for Phoenix Software Ltd.
 * Author: Phoenix Software Ltd / James Tognola
 * Author URI: http://www.phoenixs.co.uk/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: mailify
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author James Tognola
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-mailify.php' );
require_once( 'includes/class-mailify-settings.php' );

// Load plugin libraries
require_once( 'includes/lib/class-mailify-admin-api.php' );
require_once( 'includes/lib/class-mailify-post-type.php' );
require_once( 'includes/lib/class-mailify-taxonomy.php' );

/**
 * Returns the main instance of Mailify to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Mailify
 */
function Mailify () {
    $instance = Mailify::instance( __FILE__, '1.0.0' );

    if ( is_null( $instance->settings ) ) {
        $instance->settings = Mailify_Settings::instance( $instance );
    }

    return $instance;
}

Mailify();

function MailifyForm_Request() {
    $MailifyForm = $_GET['MailifyForm'];
    if (!isset($MailifyForm)) return;
    $file = @file_get_contents('includes/forms' . $MailifyForm);
    if (!empty($file)) {
        echo $file;
    } else {
        echo 'ERROR: FORM NOT FOUND!';
    }
    exit();
}

MailifyForm_Request();

?>