<?php

/**
* Basic PHP code to use Google reCAPTCHA v2 with YOURLS.
* Code borrowed from: https://github.com/armujahid/Admin-reCaptcha/blob/master/captcha.php
*/

require_once "autoload.php";

// Register API keys at https://www.google.com/recaptcha/admin
$siteKey = yourls_get_option( 'adminnocaptcha_pub_key' );
$secret = yourls_get_option( 'adminnocaptcha_priv_key' );

// More languages: https://developers.google.com/recaptcha/docs/language
$lang = "en";

// The response from reCAPTCHA
$resp = null;

// The error code from reCAPTCHA, if any
$error = null;

if ($siteKey == "" || $secret == "") {
	die("To use reCAPTCHA you must get an API key.");
}

elseif (isset($_POST['g-recaptcha-response'])) {
	$recaptcha = new \ReCaptcha\ReCaptcha($secret, new \ReCaptcha\RequestMethod\CurlPost());
	$resp = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
}

?>