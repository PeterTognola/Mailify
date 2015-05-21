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
require_once( ABSPATH . WPINC . '/pluggable.php' );

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

/**
 * Returns the requested form or an error if unable to find.
 *
 * @since  1.0.0
 * @return object MailifyForm_Request
 */
function MailifyForm_Request() {
    $MailifyForm = $_GET['MailifyForm'];
    if (!isset($MailifyForm)) return;
    $filePath = plugin_dir_path( __FILE__ ) . '/includes/forms/' . $MailifyForm . '.txt';
    $file = @file_get_contents($filePath);
    if (!empty($file)) {
        echo stripslashes($file);
    } else {
        echo 'ERROR: FORM NOT FOUND - ' . $filePath;
    }
    exit();
}

add_action( 'plugins_loaded', 'MailifyForm_Submit', 11 );
function MailifyForm_Submit() {
    $keyWord = "_m-";
    $MailifyForm = $_GET['MailifySubmit'];
    $loopFormEle = true;
    $currentFormEle = 0;
    $defaultEmail = "Chris-Crowther@LicenseDashboard.com"; //todo change to get from the settings...
    $defaultSubject = "Contact Form"; //todo change to get from the settings...
    $defaultBody = "There has been an error..."; //todo change to get from the settings...
    $MailifyBody = "";
    if (!isset($MailifyForm)) return;
    
    $MailifySendTo = isset($_POST[$keyWord . "sendto"]) ? $_POST[$keyWord . "sendto"] : $defaultEmail;
    $MailifySubject = isset($_POST[$keyWord . "subject"]) ? $_POST[$keyWord . "subject"] : $defaultSubject;
    
    $MailifyBody .= "Hello!\n"; //todo replace with header...
    
    while ($currentFormEle < 25) { //todo change this approach... Do not do it like this...
        if (isset($_POST[$keyWord . $currentFormEle])) {
            $MailifyBody .= $_POST[$keyWord . $currentFormEle] . "\n";
        }
        
        $currentFormEle += 1;
    }
    
    $MailifyBody .= "\n\nGoodbye!"; //todo replace with footer...
    
    $result = wp_mail($MailifySendTo, $MailifySubject, $MailifyBody);
    //echo $MailifySendTo . " - " . $MailifySubject . " - " . $MailifyBody;
    echo $result == 1 ? "true" : "false";
    exit();
}

MailifyForm_Request();
//MailifyForm_Submit();

?>