<?php

// Make sure we don't expose any info if called directly
if (!defined('SaFly_INC')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

$safly_options = array();
//SaFly Global Setting
$safly_options['api_domain']  = '';
$safly_options['api_key']     = '';
$safly_options['api_server']  = 'http://api.oranme.com';
$safly_options['sign_method'] = 'basic';
$safly_options['time_lag']    = 'on';
$safly_options['ip_getenv']   = 'HTTP_CF_CONNECTING_IP,HTTP_CDN_REAL_IP,HTTP_X_FORWARDED_FOR,REMOTE_ADDR';
//SaFly Interact WAF
$safly_options['waf_server']           = 'https://ips.waf.name';
$safly_options['waf_level']            = 'medium';
$safly_options['if_whitelist_user']    = 'on';
$safly_options['if_homepage_off']      = 'on';
$safly_options['if_post_off']          = 'on';
$safly_options['if_spiders_ua_off']    = 'off';
$safly_options['whitelist_expire']     = 900;
$safly_options['blacklist_expire']     = 900;
$safly_options['exclude_post_keyword'] = 'comment_parent,log,pwd';	
$safly_options['exclude_url_keyword']  = 'rss';
$safly_options['exclude_spiders_ua']   = 'Baiduspider,Googlebot,HaoSouSpider,360Spider,Sosospider,Yahoo,YodaoBot,YoudaoBot,Sogou,bingbot,ia_archiver';
//SaFly Guard
$safly_options['safly_guard_trigger']    = 'comment_parent,log,pwd';
$safly_options['safly_guard_attributes'] = '{"siw":1,"allow_ip":["1.2.3.4","8.8.8.8","8.8.4.4"],"block_ip":["8.8.8.9","8.8.4.5"]}';

?>