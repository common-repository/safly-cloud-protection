<?php
/*
   +----------------------------------------------------------------------+
   | SaFly Cloud API - SaFly Request Test                                 |
   +----------------------------------------------------------------------+
   | Copyright (c) 2011-2017 The SaFly Group                              |
   +----------------------------------------------------------------------+
   | This source file is subject to version 3.x.x of the SaFly license,   |
   | that is available through the world-wide-web at the following url:   |
   | http://www.safly.org/category/agreements/                            |
   | If you are unable to obtain it through the world-wide-web, please    |
   | send a note to license@safly.org, so we can mail you a copy          |
   | immediately.                                                         |
   +----------------------------------------------------------------------+
   | SaFly Cloud API - SaFly Guard™ on 2017-09-08,                        |
   | API Doc: https://api.safly.org/safly-guard/                          |
   +----------------------------------------------------------------------+
   | Authors: SaFly Abyss.Cong <abyss@safly.org>                          |
   +----------------------------------------------------------------------+
*/

// Make sure we don't expose any info if called directly
if (!defined('SaFly_INC')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

/* SaFly Guard */
function SaFly_Guard()
{

	require_once(dirname(dirname(__FILE__)) . '/wrapper.php');

	if ($GLOBALS['safly_api_signed_url']) {
		//Continue if the API is set
		if (SaFly_Isset_REQUEST_Keyword($GLOBALS['safly_options']['safly_guard_trigger'])) {
			//Meeting the conditions, trigger SaFly Guard
			$safly_guard_api   = "{$GLOBALS['safly_options']['api_server']}/api/saflyguard/?mode=visitor&ip={$GLOBALS['safly_visitor_ip']}&url={$GLOBALS['safly_current_url']}&user_agent={$GLOBALS['safly_visitor_ua']}&attributes={$GLOBALS['safly_options']['safly_guard_attributes']}&{$GLOBALS['safly_api_signed_url']}";
			$safly_guard_array = array_unique(array_merge($_POST, $_GET, $_COOKIE));
			$safly_response    = SaFly_Curl_Post($safly_guard_api, $safly_guard_array);
			$safly_response    = json_decode($safly_response);
			if ($safly_response->code == '000201' && $safly_response->data->visitor_data->code == '0') {
				//Requests contain the suspect features
				//Please modify the return information according to your business logics
				header('HTTP/1.1 400 Bad Request');
				header('Content-Type: text/plain');
				exit('SaFly Guard - Bad Request');
			}
		}
	}

}

add_action('plugins_loaded', 'SaFly_Guard', '1');

?>