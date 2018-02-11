<?php
/*
Plugin Name: Admin NoReCAPTCHA
Plugin URI: https://github.com/amindeed/YOURLS-Admin-NoReCAPTCHA
Description: Enables Google's No CAPTCHA reCAPTCHA on Admin login screen. A port to Google ReCAPTCHA v2.0 of @armujahid's "Admin reCaptcha" plugin.
Version: 1.0
Author: Amindeed
Author URI: https://github.com/amindeed
*/

if( !defined( 'YOURLS_ABSPATH' ) ) die();

yourls_add_action( 'pre_login_username_password', 'adminnorecaptcha_validatereCaptcha' );

// Validates reCaptcha
function adminnorecaptcha_validatereCaptcha()
{
	include('captcha.php'); 
	if ($resp != null && $resp->isSuccess()) 
	{ 
		//reCaptcha validated
		return true;
	}
	else
	{
		yourls_do_action( 'login_failed' );
		yourls_login_screen( $error_msg = 'reCaptcha validation failed' );
		die();
		return false;
	}
}

// Register plugin on admin page
yourls_add_action( 'plugins_loaded', 'adminnorecaptcha_init' );
function adminnorecaptcha_init() {
    yourls_register_plugin_page( 'adminnorecaptcha', 'Admin NoCaptcha Settings', 'adminnorecaptcha_config_page' );
}

// The function that will draw the config page
function adminnorecaptcha_config_page() {
    	 if( isset( $_POST['adminnorecaptcha_public_key'] ) ) {
	        yourls_verify_nonce( 'adminnorecaptcha_nonce' );
	        adminnorecaptcha_save_admin();
	    }
    
    $nonce = yourls_create_nonce( 'adminnorecaptcha_nonce' );
    $pubkey = yourls_get_option( 'adminnorecaptcha_pub_key', "" );
    $privkey = yourls_get_option( 'adminnorecaptcha_priv_key', "" );
    echo '<h2>Admin NoCaptcha plugin settings</h2>';
    echo '<form method="post">';
    echo '<input type="hidden" name="nonce" value="' . $nonce . '" />';
    echo '<p><label for="adminnorecaptcha_public_key">reCaptcha site key: </label>';
    echo '<input type="text" id="adminnorecaptcha_public_key" name="adminnorecaptcha_public_key" value="' . $pubkey . '"></p>';  
    echo '<p><label for="adminnorecaptcha_private_key">reCaptcha secret key: </label>';
    echo '<input type="text" id="adminnorecaptcha_private_key" name="adminnorecaptcha_private_key" value="' . $privkey . '"></p>';
    echo '<input type="submit" value="Save"/>';
    echo '</form>';

}

// Save reCaptcha keys in database 
function adminnorecaptcha_save_admin()
{
	$pubkey = $_POST['adminnorecaptcha_public_key'];
	$privkey = $_POST['adminnorecaptcha_private_key'];
	if ( yourls_get_option( 'adminnorecaptcha_pub_key' ) !== false ) {
        yourls_update_option( 'adminnorecaptcha_pub_key', $pubkey );
    } 
	else {
        yourls_add_option( 'adminnorecaptcha_pub_key', $pubkey );
    }
	if ( yourls_get_option( 'adminnorecaptcha_priv_key' ) !== false ) {
        yourls_update_option( 'adminnorecaptcha_priv_key', $privkey );
    } 
	else {
        yourls_add_option( 'adminnorecaptcha_priv_key', $privkey );
    }
    echo "Saved";
}

// Add the JavaScript for reCaptcha widget
yourls_add_action( 'html_head', 'adminnorecaptcha_addjs' );
function adminnorecaptcha_addjs() {
	$siteKey = yourls_get_option( 'adminnorecaptcha_pub_key' );
	?>
	<script type="text/javascript">
	//JQuery function to add div for reCaptcha widget and load js only on login screen
	$(document).ready(function() {
		var logindiv = document.getElementById('login');
		if (logindiv != null) { //check if we are on login screen
			//getting reCaptcha script by jquery only on login screen
			$.getScript( "https://www.google.com/recaptcha/api.js?onload=loadCaptcha&render=explicit");
			var form = logindiv.innerHTML;
			var index = form.indexOf('<p style="text-align: right;">'); //finding tag before which reCaptcha widget should appear
			document.getElementById('login').innerHTML = form.slice(0, index) + '<div id="captcha_container"></div>' + form.slice(index);	    
		}
    });
	// JavaScript function to explicitly render the reCAPTCHA widget
	var loadCaptcha = function() {
	  captchaContainer = grecaptcha.render('captcha_container', {
		'sitekey' : '<?php echo $siteKey?>'
	  });
	};
	</script>
	<?php
}
?>
