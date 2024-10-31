<?php

// Make sure we don't expose any info if called directly
if (!defined('SaFly_INC')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/* Load Options */
if ($GLOBALS['safly_options']['if_homepage_off'] == 'on' && SaFly_is_Home()) { //Homepage
	//Pass
}elseif ($GLOBALS['safly_options']['if_spiders_ua_off'] == 'on' && SaFly_If_Spiders(($GLOBALS['safly_options']['exclude_spiders_ua']))) {
	//Excluded Spiders UA
	//Pass
	$SaFly_If_Footer = '0';
}elseif (SaFly_Exclude_Keyword(base64_decode(SaFly_Current_URL()), $GLOBALS['safly_options']['exclude_url_keyword'])) {
	//Excluded keywords
	//Pass
}elseif ($GLOBALS['safly_options']['if_post_off'] == 'on') {
	if (SaFly_Isset_REQUEST_Keyword($GLOBALS['safly_options']['exclude_post_keyword'])) {
		//Excluded POST Keywords
		//Pass
	}else {
		//Location
		$SaFly_If_Location = '1';
	}
}else {
	//Location
	$SaFly_If_Location = '1';
}
/* Advance Deductions */
//Curl to get the code

$safly_interact_waf_output = SaFly_Get_API_Code();
$safly_code = $safly_interact_waf_output->code;
if ($safly_code == '000104') {
	//Whitelist
	wp_cache_set($GLOBALS['safly_visitor_ip'], '1', '', $GLOBALS['safly_options']['whitelist_expire']);
}elseif ($safly_code == '000105') {
	//Blacklist
	wp_cache_set($GLOBALS['safly_visitor_ip'], '0', '', $GLOBALS['safly_options']['blacklist_expire']);
	exit('SaFly Interact WAF - You have been banned.');
}elseif ($safly_code == '000103') {
	if (isset($SaFly_If_Location) && $SaFly_If_Location == '1') {
		//Location
		header("Location: {$GLOBALS['safly_options']['waf_server']}/waf/SaFly-Mitigate/?url={$GLOBALS['safly_current_url']}&{$GLOBALS['safly_api_signed_url_frames']}");
		exit;
	}
}elseif ($safly_code == '000101') {
	//Pass
}elseif ($safly_code == '000102') {
	//Block
	$safly_block_details = $safly_interact_waf_output->data->details;
	exit("SaFly Interact WAF - Suspectable Request - {$safly_block_details}");
}

/* Pages Adding */
if (isset($SaFly_If_Footer) && $SaFly_If_Footer == '0') {
	//No Footer Added
}else {
	add_action('wp_footer', 'SaFly_add_Footer_Frames');
}

?>