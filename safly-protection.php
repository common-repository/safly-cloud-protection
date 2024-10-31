<?php
/*
Plugin Name: SaFly Cloud Protection
Plugin URI: https://www.safly.org
Description: A secure plug-in which helps you be away from being collected, brute force attack and so on, Based on SaFly Cloud API, Designed by SaFly Organization.
Version: 3.1.0
Author: SaFly Organization
Author URI: https://www.safly.org
License: MPL 2.0
Copyright: 2011-2017 SaFly Organization, Inc.
*/

/*
This Source Code Form is subject to the terms of the Mozilla Public
License, v. 2.0. If a copy of the MPL was not distributed with this
file, You can obtain one at http://mozilla.org/MPL/2.0/.
Copyright 2011-2017 SaFly Organization, Inc.
*/

//Debug
//ini_set('display_errors', 'On');

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

//SaFly Cloud Protection - Processing time and memory
$safly_processing_t1 = microtime(TRUE);
$safly_processing_m1 = memory_get_usage();

define('SaFly_INC', 'safly', TRUE);
define('SaFly_DIR', plugin_dir_path(__FILE__));
define('SaFly_URL', plugin_dir_url(__FILE__));
define('SaFly_Cache', WP_CONTENT_DIR . '/cache/safly');

require_once(ABSPATH . 'wp-includes/pluggable.php');
require_once(SaFly_DIR . 'reset.php');
require_once(SaFly_DIR . 'wrapper.php');
require_once(SaFly_DIR . 'variables.php');

/* Activation & Deactivation */
register_activation_hook(__FILE__, 'SaFly_Activated');
register_deactivation_hook(__FILE__, 'SaFly_Deactivated');

/* SaFly Interact WAF */
if (get_option('safly_interact_waf') == 'on') {
	add_action('plugins_loaded', 'SaFly_Interact_WAF', '1');
}

/* SaFly Guard */
if (get_option('safly_guard') == 'on') {
	require_once(SaFly_DIR . 'core/safly-guard.php');
}

/* SaFly Options */
require_once(SaFly_DIR . 'options.php');

/* SaFly Cache Pretreatment */
SaFly_Create_Dir(SaFly_Cache);
if (!file_exists(SaFly_Cache . '/index.html')) {
	touch(SaFly_Cache . '/index.html');
	touch(SaFly_Cache . '/timelag.txt');
	touch(SaFly_Cache . '/reset.mark');
}

/* SaFly Cloud Protection - Processing time */
$safly_processing_t2 = microtime(TRUE);
$safly_processing_m2 = memory_get_usage();
$GLOBALS['safly_processing_time']   = round($safly_processing_t2 - $safly_processing_t1, 3);
$GLOBALS['safly_processing_memory'] = round(($safly_processing_m2 - $safly_processing_m1) / 1048576, 3);
add_action('wp_footer', 'SaFly_add_Footer_Processing_Info');

?>