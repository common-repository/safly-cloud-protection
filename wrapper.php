<?php

// Make sure we don't expose any info if called directly
if (!defined('SaFly_INC')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

function SaFly_Activated()
{
	require_once(SaFly_DIR . 'initial-configuration.php');
	update_option('safly_interact_waf', 'on');
	update_option('safly_guard', 'on');
	//Serialize
	$safly_serialize = serialize($safly_options);
	update_option('safly_options', $safly_serialize);
}

function SaFly_Deactivated()
{
	if (is_dir(SaFly_Cache)) {
		//unlink(SaFly_Cache . '/index.html');
		unlink(SaFly_Cache . '/timelag.txt');
		//rmdir(SaFly_Cache);
	}
	wp_cache_flush();
}

function SaFly_Options_Reset($type = '')
{
	//Delete files
	if (is_dir(SaFly_Cache)) {
		//unlink(SaFly_Cache . '/index.html');
		unlink(SaFly_Cache . '/timelag.txt');
		//rmdir(SaFly_Cache);
	}
	//Protect the API Info
	$GLOBALS['safly_options'] = unserialize(get_option('safly_options'));
	$api_domain = $GLOBALS['safly_options']['api_domain'];
	$api_key    = $GLOBALS['safly_options']['api_key'];
	//Reset
	wp_cache_flush();
	delete_option('safly_options');
	delete_option('safly_interact_waf');
	delete_option('safly_guard');
	SaFly_Activated();
	if (empty($type)) {
		$GLOBALS['safly_options'] = unserialize(get_option('safly_options'));
		$GLOBALS['safly_options']['api_domain'] = $api_domain;
		$GLOBALS['safly_options']['api_key']    = $api_key;
		$safly_serialize = serialize($GLOBALS['safly_options']);
		update_option('safly_options', $safly_serialize);
	}
}

function SaFly_IP($ip_getenv = 'REMOTE_ADDR')
{
	if (empty($ip_getenv)) {
		$ip_getenv = 'REMOTE_ADDR';
	}
	$ip_getenv = explode(',', $ip_getenv);
	foreach ($ip_getenv as $value) {
		if (getenv($value)) {
			$safly_ip    = getenv($value);
			$ip_if_comma = strstr($safly_ip, ',');
			if ($ip_if_comma) {
				$ip_if_comma = explode(',', $ip_if_comma);
				$safly_ip    = $ip_if_comma['0'];
			}
			return $safly_ip;
		}
	}
	if (!$safly_ip) {
		exit('SaFly Cloud Protection - Invalid IP.');
	}
}

function SaFly_Curl($url)
{
	$safly_ch = curl_init();
	curl_setopt($safly_ch, CURLOPT_URL, $url);
	curl_setopt($safly_ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($safly_ch, CURLOPT_TIMEOUT, 5);
	curl_setopt($safly_ch, CURLOPT_HEADER, 0);
	$safly_output = curl_exec($safly_ch);
	curl_close($safly_ch);
	return $safly_output;
}

function SaFly_Curl_Post($url, $array)
{
	$safly_ch = curl_init();
	curl_setopt($safly_ch, CURLOPT_URL, $url);
	curl_setopt($safly_ch, CURLOPT_SSL_VERIFYPEER, FALSE); //no-check-certificate
	curl_setopt($safly_ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($safly_ch, CURLOPT_POST, TRUE);
	curl_setopt($safly_ch, CURLOPT_TIMEOUT, 3);
	curl_setopt($safly_ch, CURLOPT_POSTFIELDS, $array);
	$safly_output = curl_exec($safly_ch);
	curl_close($safly_ch);
	return $safly_output;
}

function SaFly_is_SSL()
{
	if (isset($_SERVER['HTTPS'])) {
		if ('on' == strtolower($_SERVER['HTTPS'])) {
			return TRUE;
		}
		if ('1' == $_SERVER['HTTPS']) {
			return TRUE;
		}
	}elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
		return TRUE;
	}
	return FALSE;
}

function SaFly_Create_Dir($path, $permission = 0755)
{
	if (!file_exists($path)) {
		SaFly_Create_Dir(dirname($path));
		mkdir($path, $permission);
	}
}

function SaFly_Current_URL()
{
	if (!SaFly_is_SSL()) {
		$safly_current_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}else {
		$safly_current_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}
	$safly_current_url = base64_encode($safly_current_url);
	return $safly_current_url;
}

function SaFly_is_Home()
{
	$SaFly_Current_URL = base64_decode(SaFly_Current_URL());
	if (defined('WP_SITEURL') && $SaFly_Current_URL = WP_SITEURL) {
		return TRUE;
	}
	if (defined('WP_HOME') && $SaFly_Current_URL = WP_HOME) {
		return TRUE;
	}
	if ($_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == '/index.php') {
		return TRUE;
	}
	return FALSE;
}

function SaFly_time_lag()
{
	$saflytime1 = intval(SaFly_Curl('http://api.oranme.com/developer/saflytime.php'));
	$saflytime2 = intval(SaFly_Curl('http://api.oranme.com/developer/saflytime.php'));
	$saflytime3 = intval(SaFly_Curl('http://api.oranme.com/developer/saflytime.php'));
	$$saflytime = ($saflytime1 + $saflytime2 + $saflytime3) / 3;
	$safly_time_lag = intval(time()) - intval($saflytime);
	if (empty($saflytime) || abs($safly_time_lag) <= 1) {
		$safly_time_lag = 0;
	}
	return $safly_time_lag;
}

function SaFly_Exclude_Keyword($str, $option)
{
	if (!empty($option)) {
		$option = explode(',', $option);
		foreach ($option as $value) {
			$option_tmp = strstr($str, $value);
			if ($option_tmp) {
				return TRUE;
			}
		}
	}
	return FALSE;
}

function SaFly_Isset_REQUEST_Keyword($option)
{
	if (!empty($option)) {
		$option = explode(',', $option);
		foreach ($option as $value) {
			if (isset($_REQUEST[$value])) {
				return TRUE;
			}
		}
	}
	return FALSE;
}

function SaFly_Make_Sign($method)
{
	if ($GLOBALS['safly_options']['api_domain'] && $GLOBALS['safly_options']['api_key']) {
		$api_domain = $GLOBALS['safly_options']['api_domain'];
		$api_key    = $GLOBALS['safly_options']['api_key'];
		//SaFly Cloud API Sign 2017-07-21
		if ($GLOBALS['safly_time_lag']) {
			$time = time() - $GLOBALS['safly_time_lag'];
		}else {
			$time = time();
		}
		$subtime = intval(substr($time, 0, 8));
		$saltstr = str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890');
		$salt    = substr($saltstr, 0, 6);
		$sign    = md5($api_domain . $api_key . $subtime . $salt, FALSE);
		$sign2   = md5($api_domain . $api_key . $subtime . $salt . 'one-off', FALSE);
		//Compatible signing methods - 2016-12-30
		$time_signed_url   = "apidomain={$api_domain}&salt={$salt}&sign={$sign}";
		$basic_signed_url  = "apidomain={$api_domain}&apikey={$api_key}";	
		if ($method == 'basic') {
			$GLOBALS['safly_api_signed_url'] = $basic_signed_url;
		}else {
			$GLOBALS['safly_api_signed_url'] = $time_signed_url;
		}
		$GLOBALS['safly_api_signed_url_frames'] = "apidomain={$api_domain}&salt={$salt}&sign={$sign2}&one-off=enable";
	}
}

function SaFly_Interact_WAF()
{
	if ($GLOBALS['safly_api_signed_url']) {
		//Load SaFly Interact WAF Setting
		//Options: White-list User
		if (function_exists(is_user_logged_in)) {
			if ($GLOBALS['safly_options']['if_whitelist_user'] == 'on' && is_user_logged_in()) {
				//White-list User
				wp_cache_set($GLOBALS['safly_visitor_ip'], '1', '', $GLOBALS['safly_options']['whitelist_expire']);
			}
		}
		SaFly_Interact_WAF_Start();
	}
}

function SaFly_Interact_WAF_Start()
{
	if (wp_cache_get($GLOBALS['safly_visitor_ip'], '')) {
		$safly_ck = wp_cache_get($GLOBALS['safly_visitor_ip'], '');
		if ($safly_ck == '0') {
			//Ban
			exit('SaFly Interact WAF - You have been banned.');
		}elseif ($safly_ck == '1') {
			//Pass
		}
	}else {
		require_once(SaFly_DIR . 'core/safly-interact-waf.php');
	}
}

function SaFly_Get_API_Code($debug = '')
{
	if (!empty($debug)) {
		$debug = '&debug=1';
	}
	$safly_interact_waf = "{$GLOBALS['safly_options']['api_server']}/api/saflyinteractwaf/?user_agent={$GLOBALS['safly_visitor_ua']}&ip={$GLOBALS['safly_visitor_ip']}&level={$GLOBALS['safly_options']['waf_level']}&url={$GLOBALS['safly_current_url']}{$debug}&{$GLOBALS['safly_api_signed_url']}";
	$GLOBALS['safly_interact_waf_api'] = $safly_interact_waf;
	$safly_output = SaFly_Curl($safly_interact_waf);
	$safly_output = json_decode($safly_output);
	return $safly_output;
}

function SaFly_add_Footer_Frames()
{
	echo "<iframe src='{$GLOBALS['safly_options']['waf_server']}/waf/SaFly-Frames/?url={$GLOBALS['safly_current_url']}&{$GLOBALS['safly_api_signed_url_frames']}' style='display:none;'></iframe>";
}

function SaFly_add_Footer_Processing_Info()
{
	echo "<!-- SaFly Cloud Protection - Processing time: {$GLOBALS['safly_processing_time']} seconds with memory used {$GLOBALS['safly_processing_memory']} MBytes. -->";
}

function SaFly_If_Spiders($spiders_ua)
{
	if(empty($_SERVER['HTTP_USER_AGENT'])) {
		$user_agent = 'UNDEFINED_AGENT';
	}else {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
	}
	//$spiders_ua = 'Baiduspider,Googlebot,HaoSouSpider,360Spider,Sosospider,Yahoo,YodaoBot,YoudaoBot,Sogou,bingbot,ia_archiver';
	$user_agent = strtolower($user_agent);
	$spiders_ua = strtolower($spiders_ua);
	$spiders_ua = explode(',', $spiders_ua);
	foreach ($spiders_ua as $value) {
		$spiders_ua_tmp = strstr($user_agent, $value);
		if ($spiders_ua_tmp) {
			return TRUE;
		}
	}
	return FALSE;
}

function SaFly_API_Key_VALIDATE($api_key)
{
	if (!empty($api_key) && !preg_match('/^(\w){32}$/', $api_key)) {
		wp_die('Invalid API KEY.', 'SaFly Cloud Protection');
	}
}

function SaFly_Options_If_API_Server($str, $notice = 'Invalid API Server.')
{
	if (!empty($str)) {
		if (function_exists(esc_url)) {
			$str = esc_url($str);
		}
		$tempu = parse_url($str);
		$str   = $tempu['host'];
		$api_server_tmp  = strstr($str, 'oranme.com');
		$api_server_tmp2 = strstr($str, 'waf.name');
		if (!$api_server_tmp && !$api_server_tmp2) {
			wp_die($notice, 'SaFly Cloud Protection');
		}
	}
	//return $str;
}

function SaFly_Options_If_API_Domain($str, $notice = 'Invalid API Domain.')
{
	if (!empty($str)) {
		if (function_exists(esc_url)) {
			$str   = esc_url($str);
			$tempu = parse_url($str);
			$str   = $tempu['host'];
		}
		if (!preg_match('/^(\w){1,63}(\.(\w){1,63}){1,5}$/', $str)) {
			wp_die($notice, 'SaFly Cloud Protection');
		}elseif (strlen($str) > 50) {
			wp_die($notice, 'SaFly Cloud Protection');
		}
	}
	return $str;
}

function SaFly_Trigger_VALIDATE($trigger)
{
	if (!empty($trigger) && !preg_match('/^(\w)+(,(\w)+)*$/i', $trigger)) {
		wp_die('Invalid Triggers.', 'SaFly Cloud Protection');
	}
}

function SaFly_Level_VALIDATE($level)
{
	if ($level != ('low' || 'medium' || 'high')) {
		wp_die('Level: only low or medium or high.', 'SaFly Cloud Protection');
	}
}

function SaFly_API_Method_VALIDATE($method)
{
	if ($method != ('basic' || 'ip' || 'time')) {
		wp_die('API Signing Method: only BASIC or TIME or TIME.', 'SaFly Cloud Protection');
	}
}

function SaFly_Number_VALIDATE($number)
{
	if (!empty($number) && !preg_match('/^([0-9])+$/', $number)) {
		wp_die('Invalid Numbers.', 'SaFly Cloud Protection');
	}
}

function SaFly_Attributes_VALIDATE($attributes)
{
	if (!empty($attributes)) {
		$attributes = json_decode($attributes, TRUE);
		if (!is_array($attributes)) {
			wp_die('Invalid Attributes.', 'SaFly Cloud Protection');
		}
	}
}

?>