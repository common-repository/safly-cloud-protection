<?php

// Make sure we don't expose any info if called directly
if (!defined('SaFly_INC')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

if (file_exists(SaFly_Cache . '/index.html') && !file_exists(SaFly_Cache . '/reset.mark')) {
	require_once(SaFly_DIR . 'wrapper.php');
	SaFly_Options_Reset('serious');
}

?>