<?php
/*
Plugin Name: Admin NoCaptcha
Plugin URI: https://github.com/amindeed/admin-nocaptcha
Description: Enables Google's No CAPTCHA reCAPTCHA on Admin login screen. A port to Google ReCAPTCHA v2.0 of @armujahid's "Admin reCaptcha" plugin.
Version: 1.0
Author: Amindeed
Author URI: https://github.com/amindeed
*/

if( !defined( 'YOURLS_ABSPATH' ) ) die();

yourls_add_action( 'pre_login_username_password', 'adminnocaptcha_validatereCaptcha' );

// Validates reCaptcha
function adminnocaptcha_validatereCaptcha()
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
yourls_add_action( 'plugins_loaded', 'adminnocaptcha_init' );
function adminnocaptcha_init() {
    yourls_register_plugin_page( 'adminnocaptcha', 'Admin NoCaptcha Settings', 'adminnocaptcha_config_page' );
}

// The function that will draw the config page
function adminnocaptcha_config_page() {
    	 if( isset( $_POST['adminnocaptcha_public_key'] ) ) {
	        yourls_verify_nonce( 'adminnocaptcha_nonce' );
	        adminnocaptcha_save_admin();
	    }
    
    $nonce = yourls_create_nonce( 'adminnocaptcha_nonce' );
    $pubkey = yourls_get_option( 'adminnocaptcha_pub_key', "" );
    $privkey = yourls_get_option( 'adminnocaptcha_priv_key', "" );
    echo '<h2>Admin NoCaptcha plugin settings</h2>';
    echo '<form method="post">';
    echo '<input type="hidden" name="nonce" value="' . $nonce . '" />';
    echo '<p><label for="adminnocaptcha_public_key">reCaptcha site key: </label>';
    echo '<input type="text" id="adminnocaptcha_public_key" name="adminnocaptcha_public_key" value="' . $pubkey . '"></p>';  
    echo '<p><label for="adminnocaptcha_private_key">reCaptcha secret key: </label>';
    echo '<input type="text" id="adminnocaptcha_private_key" name="adminnocaptcha_private_key" value="' . $privkey . '"></p>';
    echo '<input type="submit" value="Save"/>';
    echo '</form>';

}

// Save reCaptcha keys in database 
function adminnocaptcha_save_admin()
{
	$pubkey = $_POST['adminnocaptcha_public_key'];
	$privkey = $_POST['adminnocaptcha_private_key'];
	if ( yourls_get_option( 'adminnocaptcha_pub_key' ) !== false ) {
        yourls_update_option( 'adminnocaptcha_pub_key', $pubkey );
    } 
	else {
        yourls_add_option( 'adminnocaptcha_pub_key', $pubkey );
    }
	if ( yourls_get_option( 'adminnocaptcha_priv_key' ) !== false ) {
        yourls_update_option( 'adminnocaptcha_priv_key', $privkey );
    } 
	else {
        yourls_add_option( 'adminnocaptcha_priv_key', $privkey );
    }
    echo "Saved";
}

// Add the JavaScript for reCaptcha widget
yourls_add_action( 'html_head', 'adminnocaptcha_addjs' );
function adminnocaptcha_addjs() {
	$siteKey = yourls_get_option( 'adminnocaptcha_pub_key' );
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
