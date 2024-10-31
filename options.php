<?php

// Make sure we don't expose any info if called directly
if (!defined('SaFly_INC')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

//Dashboard
function safly_plugin_menu_page()
{
	add_options_page('SaFly Cloud Protection', 'SaFly Cloud Protection', 'manage_options', 'safly-protection', 'safly_plugin_menu_page_add');
}
function safly_plugin_menu_page_add()
{
	//Prevent users without the right permissions from accessing things
	if (!current_user_can('manage_options')) {
		wp_die(__('You do not have sufficient permissions to access this page.'));
	}

	//Update Notice
	if (isset($_GET['saflynotice'])) {
		echo '<div class="updated"><p>Settings updated successfully!</p></div>';
	}
	/* SaFly Global Setting */
	//Form Radio
	$safly_sign_method = $GLOBALS['safly_options']['sign_method'];
	if ($safly_sign_method == 'basic') {
		$safly_radio_sign_method = '<input type="radio" checked="checked" name="sign_method" value="basic" />BASIC&nbsp;<input type="radio" name="sign_method" value="time" />TIME&nbsp;&nbsp;&nbsp;';
	}elseif ($safly_sign_method == 'time') {
		$safly_radio_sign_method = '<input type="radio" name="sign_method" value="basic" />BASIC&nbsp;<input type="radio" checked="checked" name="sign_method" value="time" />TIME&nbsp;&nbsp;&nbsp;';
	}else{
		$safly_radio_sign_method = 'Invalid method.&nbsp;&nbsp;&nbsp;';
	}
	$safly_time_lag = $GLOBALS['safly_options']['time_lag'];
	//Form Checked
	if ($safly_time_lag == 'on') {
		$safly_if_time_lag = ' checked="checked"';
	}
	//Server IP
	$safly_server_ip = SaFly_Curl('http://api.oranme.com/developer/saflyip.php');
	if (!$safly_server_ip || !filter_var($safly_server_ip, FILTER_VALIDATE_IP)) {
		$safly_server_ip = 'Undefined';
	}

	/* SaFly Interact WAF */
	if ($GLOBALS['safly_api_signed_url']) {
		$safly_code_t1 = microtime(TRUE);
		$safly_interact_waf_output = SaFly_Get_API_Code('true');
		$safly_block_details       = $safly_interact_waf_output->data->details;
		if (!empty($safly_block_details)) {
			$safly_block_details   = " - {$safly_interact_waf_output->data->details}";
		}
		$safly_code    = $safly_interact_waf_output->code;
		$safly_code_t2 = microtime(TRUE);
		$safly_code_time    = round($safly_code_t2 - $safly_code_t1, 3);
		$safly_interact_api = $GLOBALS['safly_interact_waf_api'];
	}else {
		$safly_code          = 'Undefined';
		$safly_block_details = '';
		$safly_code_time     = 'Undefined';
		$safly_interact_api  = 'Undefined';
	}
	if (wp_cache_get($GLOBALS['safly_visitor_ip'], '') == '1') {
		$safly_if_white_listed = 'Yes';
	}else {
		$safly_if_white_listed = 'No';
	}
	//Form Checked
	if (get_option('safly_interact_waf') == 'on') {
		$safly_if_interacton = ' checked="checked"';
	}
	if ($GLOBALS['safly_options']['if_whitelist_user'] == 'on') {
		$safly_if_whitelist_user = ' checked="checked"';
	}
	if ($GLOBALS['safly_options']['if_homepage_off'] == 'on') {
		$safly_if_homepage_off = ' checked="checked"';
	}
	if ($GLOBALS['safly_options']['if_post_off'] == 'on') {
		$safly_if_post_off = ' checked="checked"';
	}
	if ($GLOBALS['safly_options']['if_spiders_ua_off'] == 'on') {
		$safly_if_spiders_ua_off = ' checked="checked"';
	}
	//Form Radio
	$safly_waf_level = $GLOBALS['safly_options']['waf_level'];
	if ($safly_waf_level == 'low') {
		$safly_radio_waf_level = '<input type="radio" checked="checked" name="waf_level" value="low" />Low&nbsp;<input type="radio" name="waf_level" value="medium" />Medium&nbsp;<input type="radio" name="waf_level" value="high" />High&nbsp;&nbsp;&nbsp;';
	}elseif ($safly_waf_level == 'medium') {
		$safly_radio_waf_level = '<input type="radio" name="waf_level" value="low" />Low&nbsp;<input type="radio" checked="checked" name="waf_level" value="medium" />Medium&nbsp;<input type="radio" name="waf_level" value="high" />High&nbsp;&nbsp;&nbsp;';
	}elseif ($safly_waf_level == 'high') {
		$safly_radio_waf_level = '<input type="radio" checked="checked" name="waf_level" value="low" />Low&nbsp;<input type="radio" name="waf_level" value="medium" />Medium&nbsp;<input type="radio" checked="checked" name="waf_level" value="high" />High&nbsp;&nbsp;&nbsp;';
	}else {
		$safly_radio_waf_level = 'Invalid waf level.';
	}
	/* SaFly Guard */
	if (get_option('safly_guard') == 'on') {
		$safly_if_guard = ' checked="checked"';
	}

	echo '
	<h1>SaFly Cloud Protection 3.1.0</h1>
	<p>Notice:<br>1. 如果您被拦截而无法管理您的网站，请删除文件 ' . SaFly_Cache . '/reset.mark' . ' ，系统将自动重置。<br>2. 建议每次更新插件完毕后 Reset 以获取最新配置。</p>
	<p>Shortcut links:<br>官方网站: <a target=\'_blank\' href="https://www.safly.org/">https://www.safly.org</a><br>客户中心: <a target=\'_blank\' href="https://juice.oranme.com/">https://juice.oranme.com</a><br>API 文档: <a target=\'_blank\' href="https://api.safly.org/">https://api.safly.org</a></p>
	<p>
		<form action="' . $_SERVER['REQUEST_URI'] . '" method="post">
		<table class="form-table">

			<!-- SaFly Global Setting -->
			<br><a target=\'_blank\' href="https://my.safly.org/"><h2>Global Setting</h2></a>
			<tr valign="top"><th scope="row"><label>API Domain</label></th><td><input type="text" name="api_domain" value="' . $GLOBALS['safly_options']['api_domain'] . '" class="regular-text" /><span class="description">请输入您在 <a href="https://juice.oranme.com/" target="_blank">客户中心</a> 注册的 API Domain</span></td></tr>
			<tr valign="top"><th scope="row"><label>API KEY</label></th><td><input type="text" name="api_key" value="' . $GLOBALS['safly_options']['api_key'] . '" class="regular-text" /><span class="description">请输入您在 <a href="https://juice.oranme.com/" target="_blank">客户中心</a> 注册的 API KEY</span></td></tr>
			<tr valign="top"><th scope="row"><label>API Server</label></th><td><input type="text" name="api_server" value="' . $GLOBALS['safly_options']['api_server'] . '" class="regular-text" /><span class="description">使用的 SaFly Cloud API 服务器</span></td></tr>
			<tr valign="top"><th scope="row"><label>API Signing Method</label></th><td>' . $safly_radio_sign_method . '<span class="description"><a target=\'_blank\' href="https://api.safly.org/safly-cloud-api-sign-3-0/">API 签名方式</a></span></td></tr>
			<tr valign="top"><th scope="row"><label>SaFly Time-lag</label></th><td><input type="checkbox" name="time_lag" value="on"' . $safly_if_time_lag . ' /><span class="description">勾选后将随机触发事件与 SaFly API System 对时，执行对时时可能会影响网站加载速度，概率为万分之一。</span></td></tr>
			<tr valign="top"><th scope="row"><label>IP Getenv</label></th><td><input type="text" name="ip_getenv" value="' . $GLOBALS['safly_options']['ip_getenv'] . '" class="regular-text" /><span class="description">从指定全局变量中获取访客 IP, 按填入顺序表示优先级。请根据自身实际情况增减，不正确的设置可能导致 IP 欺诈等事件的发生。使用 \',\' 分隔。注：为降低错误设置的风险，留空即为 \'REMOTE_ADDR\'。</span></td></tr>
			<tr valign="top"><th scope="row"><label>Server IP</label></th><td>' . $safly_server_ip . '<span class="description">&nbsp;&nbsp;&nbsp;当前服务器公网 IP</span></td></tr>
			<tr valign="top"><th scope="row"><label>Visitor IP</label></th><td>' . $GLOBALS['safly_visitor_ip'] . '<span class="description">&nbsp;&nbsp;&nbsp;当前访客 IP</span></td></tr>
	';
			if ($GLOBALS['safly_options']['time_lag'] == 'on') {
				echo '<tr valign="top"><th scope="row"><label>SaFly Server Time-lag</label></th><td>' . $GLOBALS['safly_time_lag'] . '<span class="description">&nbsp;&nbsp;&nbsp;SaFly 文件缓存中的 Time-lag, 用于 Make Sign</span></td></tr>';
			}
	echo '
			<tr valign="top"><th scope="row"><label>Cache Info</label></th><td>' . SaFly_Cache . '<span class="description">&nbsp;&nbsp;&nbsp;SaFly 缓存信息</span></td></tr>
		</table>

		<table class="form-table">	
			<!-- SaFly Interact WAF™ -->
			<br><a target=\'_blank\' href="https://api.safly.org/safly-interact-waf/"><h2>SaFly Interact WAF™</h2></a>
			<tr valign="top"><th scope="row"><label>Enable SaFly Interact WAF™</label></th><td><input type="checkbox" name="if_interacton" value="on"' . $safly_if_interacton . ' /><span class="description">勾选后启用 SaFly Interact WAF™</span></td></tr>
			<tr valign="top"><th scope="row"><label>WAF Server</label></th><td><input type="text" name="waf_server" value="' . $GLOBALS['safly_options']['waf_server'] . '" class="regular-text" /><span class="description">使用的 WAF 服务器</span></td></tr>
			<tr valign="top"><th scope="row"><label>Security Level</label></th><td>' . $safly_radio_waf_level . '<span class="description">防御安全等级</span></td></tr>
			<tr valign="top"><th scope="row"><label>If Whitelist Users</label></th><td><input type="checkbox" name="if_whitelist_user" value="on"' . $safly_if_whitelist_user . ' /><span class="description">勾选后将已登录用户永久加入白名单缓存</span></td></tr>
			<tr valign="top"><th scope="row"><label>If Homepage Off</label></th><td><input type="checkbox" name="if_homepage_off" value="on"' . $safly_if_homepage_off . ' /><span class="description">勾选后首页不会发生 Mitigate 跳转，这对提升用户体验很有帮助。Notice: 请保证网站首页路径为 /(index.php), 或者已定义常量 WP_SITEURL 或 WP_HOME 。</span></td></tr>
			<tr valign="top"><th scope="row"><label>If POST Off</label></th><td><input type="checkbox" name="if_post_off" value="on"' . $safly_if_post_off . ' /><span class="description">勾选后对登录表单、注册表单、评论表单禁用 Mitigate 服务，避免了小概率的无法评论、登录等问题。</span></td></tr>
			<tr valign="top"><th scope="row"><label>If Spiders UA Off</label></th><td><input type="checkbox" name="if_spiders_ua_off" value="on"' . $safly_if_spiders_ua_off . ' /><span class="description">勾选后对指定 Spiders UA  禁用 Mitigate 服务，避免了小概率的误拦搜索引擎的问题。我们不建议您勾选此选项，因为它有被欺骗的安全风险。SaFly Interact WAF™ 会自动加载 <a target=\'_blank\' href="https://api.safly.org/safly-guard/">SaFly Guard™</a>, 本身就可以正确并安全地放行大部分知名搜索引擎。</span></td></tr>
			<tr valign="top"><th scope="row"><label>Whitelist Expiration</label></th><td><input type="number" name="whitelist_expire" value="' . $GLOBALS['safly_options']['whitelist_expire'] . '" /><span class="description">IP 白名单过期时间 (seconds), \'0\' 为永不过期。</span></td></tr>
			<tr valign="top"><th scope="row"><label>Blacklist Expiration</label></th><td><input type="number" name="blacklist_expire" value="' . $GLOBALS['safly_options']['blacklist_expire'] . '" /><span class="description">IP 黑名单过期时间 (seconds), \'0\' 为永不过期。</span></td></tr>
			<tr valign="top"><th scope="row"><label>Excluded Spiders UA</label></th><td><input type="text" name="exclude_spiders_ua" value="' . $GLOBALS['safly_options']['exclude_spiders_ua'] . '" class="regular-text" /><span class="description">当 Excluded Spiders UA 勾选且访客 User Agent 中包含指定参数时，禁用 Mitigate 服务。使用 \',\' 分隔。</span></td></tr>
			<tr valign="top"><th scope="row"><label>Excluded REQUEST Keyword</label></th><td><input type="text" name="exclude_post_keyword" value="' . $GLOBALS['safly_options']['exclude_post_keyword'] . '" class="regular-text" /><span class="description">当 Excluded REQUEST Keyword 勾选且 $_REQUEST 中包含指定参数时，禁用 Mitigate 服务。使用 \',\' 分隔。</span></td></tr>
			<tr valign="top"><th scope="row"><label>Excluded URL Keyword</label></th><td><input type="text" name="exclude_url_keyword" value="' . $GLOBALS['safly_options']['exclude_url_keyword'] . '" class="regular-text" /><span class="description">当 Excluded URL Keyword 勾选且 URL 中包含指定参数时，禁用 Mitigate 服务。使用 \',\' 分隔。</span></td></tr>
			<tr valign="top"><th scope="row"><label>Current Code</label></th><td>' . $safly_code . $safly_block_details . '<span class="description">&nbsp;&nbsp;&nbsp;当前 API 返回值</span></td></tr>
			<tr valign="top"><th scope="row"><label>API Curl Time</label></th><td>' . $safly_code_time . '<span class="description">&nbsp;&nbsp;&nbsp;API Curl 消耗的时间</span></td></tr>
			<tr valign="top"><th scope="row"><label>Whether I am white-listed</label></th><td>' . $safly_if_white_listed . '<span class="description">&nbsp;&nbsp;&nbsp;当前用户是否被白名单</span></td></tr>
			<tr valign="top"><th scope="row"><label>Curl Debug</label></th><td><input type="text" value="' . $safly_interact_api . '" class="regular-text" /><span class="description">&nbsp;&nbsp;&nbsp;API Curl 内容调试</span></td></tr>
		</table>

		<table class="form-table">
			<!-- SaFly Guard™ -->	
			<br><a target=\'_blank\' href="https://api.safly.org/safly-guard/"><h2>SaFly Guard™</h2></a>
			<tr valign="top"><th scope="row"><label>Enable SaFly Guard™</label></th><td><input type="checkbox" name="safly_if_guard" value="on"' . $safly_if_guard . ' /><span class="description">勾选后启用 SaFly Guard™</span></td></tr>
			<tr valign="top"><th scope="row"><label>Triggers</label></th><td><input type="text" name="safly_guard_trigger" value="' . $GLOBALS['safly_options']['safly_guard_trigger'] . '" class="regular-text" /><span class="description">当 $_REQUEST 中包含指定参数时，触发 SaFly Guard™。使用 \',\' 分隔。</span></td></tr>
			<tr valign="top"><th scope="row"><label>Attributes</label></th><td><input type="text" name="safly_guard_attributes" value=\'' . $GLOBALS['safly_options']['safly_guard_attributes'] . '\' class="regular-text" /><span class="description">附加属性，Json 格式，用于自定义功能扩展，详见 <a target=\'_blank\' href="https://api.safly.org/safly-guard/">API 文档</a>。</span></td></tr>
		</table>

			';			
	wp_nonce_field();
	echo'
		<p class="submit">
			<input name="saflysave" type="submit" class="button-primary" value="Save Changes and Empty Cache" />
			<input name="saflyreset" type="submit" class="button-secondary" value="Reset" />
		</p>
		</form>
	</p>
	';
}

function safly_load_options()
{
	/* SaFly Options CSRF */
	if (isset($_POST['saflysave']) || isset($_POST['saflyreset'])) {
		//Prevent users without the right permissions from accessing things
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		//Options Nonces
		check_admin_referer();
	}

	/* SaFly Setting */
	if (isset($_POST['saflysave'])) {
		//Empty Cache
		wp_cache_flush();
		unset($GLOBALS['safly_options']);
		$GLOBALS['safly_options'] = array();
		/* SaFly Global Setting */
		//Update the API KEY
		$GLOBALS['safly_options']['api_domain'] = trim($_POST['api_domain']);
		$GLOBALS['safly_options']['api_key']    = trim($_POST['api_key']);
		SaFly_Options_If_API_Domain($GLOBALS['safly_options']['api_domain']);
		SaFly_API_Key_VALIDATE($GLOBALS['safly_options']['api_key']);
		//Update the API Server
		$GLOBALS['safly_options']['api_server'] = $_POST['api_server'];
		SaFly_Options_If_API_Server($GLOBALS['safly_options']['api_server']);
		//Update the API Signing Method
		$GLOBALS['safly_options']['sign_method'] = $_POST['sign_method'];
		SaFly_API_Method_VALIDATE($GLOBALS['safly_options']['sign_method']);
		//Update the time_lag
		if ($_POST['time_lag'] == 'on') {
			$GLOBALS['safly_options']['time_lag'] = 'on';
		}else {
			$GLOBALS['safly_options']['time_lag'] = 'off';
		}
		//Update the trigger
		$GLOBALS['safly_options']['ip_getenv'] = $_POST['ip_getenv'];
		SaFly_Trigger_VALIDATE($GLOBALS['safly_options']['ip_getenv']);
		/* SaFly Interact WAF Setting Page */
		//If the Interact WAF is on
		if ($_POST['if_interacton'] == 'on') {
			update_option('safly_interact_waf', 'on');
		}else {
			update_option('safly_interact_waf', 'off');
		}
		//WAF Server
		$GLOBALS['safly_options']['waf_server'] = $_POST['waf_server'];
		SaFly_Options_If_API_Server($GLOBALS['safly_options']['waf_server'], 'Invalid WAF Server.');
		//Update the WAF Level
		$GLOBALS['safly_options']['waf_level'] = $_POST['waf_level'];
		SaFly_Level_VALIDATE($GLOBALS['safly_options']['waf_level']);
		//If user white-listing is on
		if ($_POST['if_whitelist_user'] == 'on') {
			$GLOBALS['safly_options']['if_whitelist_user'] = 'on';
		}else {
			//Remove the current white-list
			//wp_cache_flush();
			$GLOBALS['safly_options']['if_whitelist_user'] = 'off';
		}
		//If in homepage turned off
		if ($_POST['if_homepage_off'] == 'on') {
			$GLOBALS['safly_options']['if_homepage_off'] = 'on';
		}else {
			$GLOBALS['safly_options']['if_homepage_off'] = 'off';
		}
		//If POST turned off
		if ($_POST['if_post_off'] == 'on') {
			$GLOBALS['safly_options']['if_post_off'] = 'on';
		}else {
			$GLOBALS['safly_options']['if_post_off'] = 'off';
		}
		//If Spiders UA turned Off
		if ($_POST['if_spiders_ua_off'] == 'on') {
			$GLOBALS['safly_options']['if_spiders_ua_off'] = 'on';
		}else {
			$GLOBALS['safly_options']['if_spiders_ua_off'] = 'off';
		}
		//Some Numbers
		$GLOBALS['safly_options']['whitelist_expire'] = intval($_POST['whitelist_expire']);
		$GLOBALS['safly_options']['blacklist_expire'] = intval($_POST['blacklist_expire']);
		SaFly_Number_VALIDATE($GLOBALS['safly_options']['whitelist_expire'] . $GLOBALS['safly_options']['blacklist_expire']);
		//Excluded Keywords
		$GLOBALS['safly_options']['exclude_url_keyword']  = $_POST['exclude_url_keyword'];
		$GLOBALS['safly_options']['exclude_post_keyword'] = $_POST['exclude_post_keyword'];
		$GLOBALS['safly_options']['exclude_spiders_ua']   = $_POST['exclude_spiders_ua'];
		SaFly_Trigger_VALIDATE($GLOBALS['safly_options']['exclude_url_keyword'] . $GLOBALS['safly_options']['exclude_post_keyword'] . $GLOBALS['safly_options']['exclude_spiders_ua']);

		/* SaFly Guard™ Setting Page */
		if ($_POST['safly_guard'] == 'on') {
			update_option('safly_guard', 'on');
		}else {
			update_option('safly_guard', 'off');
		}
		$GLOBALS['safly_options']['safly_guard_trigger'] = $_POST['safly_guard_trigger'];
		SaFly_Trigger_VALIDATE($GLOBALS['safly_options']['safly_guard_trigger']);
		$GLOBALS['safly_options']['safly_guard_attributes'] = $_POST['safly_guard_attributes'];
		$GLOBALS['safly_options']['safly_guard_attributes'] = str_replace('\\', '', $GLOBALS['safly_options']['safly_guard_attributes']);
		SaFly_Attributes_VALIDATE($GLOBALS['safly_options']['safly_guard_attributes']);
		/* UPDATE SaFly Options */
		$safly_serialize = serialize($GLOBALS['safly_options']);
		update_option('safly_options', $safly_serialize);
	}
	//If Reset Button
	if (isset($_POST['saflyreset'])) {
		SaFly_Options_Reset();
	}
	//Notice
	SaFly_Options_Update_Notice();

	/* SaFly Dashboard */
	add_action('admin_menu', 'safly_plugin_menu_page');

}

function SaFly_Options_Update_Notice()
{
	if (isset($_POST['saflysave']) || isset($_POST['saflyreset'])) {
		//Notice
		ob_start();
		$location = base64_decode(SaFly_Current_URL()) . '&saflynotice=on';
		header("location: $location");
		ob_end_flush();
		exit;
	}
}

add_action('init', 'safly_load_options');

?>