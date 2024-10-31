<?php

// Make sure we don't expose any info if called directly
if (!defined('SaFly_INC')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

$GLOBALS['safly_options'] = unserialize(get_option('safly_options'));
if (!$GLOBALS['safly_options']) {
	require_once(SaFly_DIR . 'initial-configuration.php');
	$GLOBALS['safly_options'] = $safly_options;
}

//SaFly Time-lag
if ($GLOBALS['safly_options']['time_lag'] == 'on' && is_dir(SaFly_Cache)) {
	if (file_exists(SaFly_Cache . '/timelag.txt')) {
		$GLOBALS['safly_time_lag'] = file_get_contents(SaFly_Cache . '/timelag.txt');
	}
	if ($GLOBALS['safly_time_lag']) {
		if (mt_rand(1, 10000) == 1) {
			$GLOBALS['safly_time_lag'] = SaFly_time_lag();
			file_put_contents(SaFly_Cache . '/timelag.txt', $GLOBALS['safly_time_lag']);
		}
	}else {
		$GLOBALS['safly_time_lag'] = SaFly_time_lag();
		file_put_contents(SaFly_Cache . '/timelag.txt', $GLOBALS['safly_time_lag']);
	}
}

//Visitor's Info
$GLOBALS['safly_visitor_ip'] = SaFly_IP($GLOBALS['safly_options']['ip_getenv']);
$GLOBALS['safly_visitor_ua'] = base64_encode($_SERVER['HTTP_USER_AGENT']);

//Make Sign
SaFly_Make_Sign($GLOBALS['safly_options']['sign_method']);

//Get Current URL
$GLOBALS['safly_current_url'] = SaFly_Current_URL();

?>