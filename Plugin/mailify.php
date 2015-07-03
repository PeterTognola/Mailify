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
/**
 * Echo's if submition of form was correct and sends email to proposed person.
 *
 * @since  1.0.0
 * @return object MailifyForm_Submit
 */
add_action( 'plugins_loaded', 'MailifyForm_Submit', 11 );
function MailifyForm_Submit() { //todo check for foul play.
    $keyWord = "_m-";
    $MailifyForm = $_GET['MailifySubmit'];
    $loopFormEle = true;
    $currentFormEle = 0;
    $defaultEmail = "web-requests@phoenixs.co.uk"; //todo change to get from the settings...
    $defaultSubject = "Contact Form"; //todo change to get from the settings...
    $defaultBody = "There has been an error..."; //todo change to get from the settings...
    $MailifyBody = "";
    if (!isset($MailifyForm)) return;
    
    //$MailifySendTo = isset($_POST[$keyWord . "sendto"]) ? $_POST[$keyWord . "sendto"] : $defaultEmail; //this isn't safe. todo get from text file.
    
    $captcha;
    if(isset($_POST['g-recaptcha-response'])) {
        $captcha=$_POST['g-recaptcha-response'];
    }
    
    //todo bring back the checking of all other fields that are required.

    if(!$captcha) {
        echo "Please complete the full form, including the reCaptcha field.";
        exit;
    }
    
    $response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LfKXggTAAAAAMI4kbk877lZSqa19rldQDlnH1-G&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']), true);
    
    if($response['success'] == false) {
        var_dump($response);
        echo "There was an error with the reCaptcha on your form.";
        exit();
    }
    
    $subEmail = explode(";", $_POST[$keyWord . 'sendto'] . ';');
    
    foreach($subEmail as $email) {
        if ($email === "") continue;
        
        if ($_POST[$keyWord . 'sendto'] == "seminars@phoenixs.co.uk" || $_POST[$keyWord . 'sendto'] == "subscriptions@phoenixs.co.uk" || $_POST[$keyWord . 'sendto'] == "info@phoenixs.co.uk" || $_POST[$keyWord . 'sendto'] == "web-requests@phoenixs.co.uk") {
            $MailifySendTo .= $_POST[$keyWord . 'sendto'] . ";";
        } else {
            $MailifySendTo .= $_POST[$keyWord . 'sendto'] . ";";
        }
    }
    
    $MailifySendTo .= "james-tognola@phoenixs.co.uk;";
    
    //todo remove if statement.
    
    $MailifySendTo = explode(";", $MailifySendTo);
    
    //$MailifySendTo = $defaultEmail; //todo get sendto from file!
    
    $MailifySubject = isset($_POST[$keyWord . "subject"]) ? $_POST[$keyWord . "subject"] : $defaultSubject;
    
    $MailifyBody .= "The following information has been submitted via the Phoenix Software website:\n"; //todo replace with header...
    
    //$mail->SMTPOptions = array(
    //    'ssl' => array(
    //        'verify_peer' => false,
    //       'verify_peer_name' => false,
    //       'allow_self_signed' => true
    //    )
    //);
    
    if (!isset($_POST[$keyWord . '1']) || $_POST[$keyWord . '1'] === "" || $_POST[$keyWord . '1'] === null) {
        exit();
    }
    
    while ($currentFormEle < 25) { //todo change this approach... Do not do it like this...
        if (isset($_POST[$keyWord . $currentFormEle])) {
            $MailifyBody .= $_POST[$keyWord . $currentFormEle] . "\n\n";
        }
        
        $currentFormEle += 1;
    }
    
    //$MailifyBody .= "\n\nGoodbye!"; //replace with footer from settings...
    
    $result = wp_mail($MailifySendTo, $MailifySubject, $MailifyBody);
    //$result = wp_mail("james-tognola@phoenixs.co.uk", $MailifySubject, $MailifyBody); For testing.
    echo $result == 1 ? "true" : "false";
    exit();
}

MailifyForm_Request();

?>