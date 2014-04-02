<?php
/*
Plugin Name: Ecwid Developer Tools
Plugin URI: http://www.ecwid.com
Description: DO NOT ADD THIS PLUGIN TO A LIVE SITE
Author: Ecwid Team
Version: 0.1
Author URI: http://www.ecwid.com?source=wporg
*/

if ( is_admin() ){
	add_action('admin_enqueue_scripts', 'edev_admin_script');
	add_action('admin_footer', 'edev_footer');
	add_action('admin_init', 'edev_process_request');
}

function get_locales()
{
	return array(
		'en_US',
		'ru_RU',
		'it_IT',
		'de_DE',
		'fr_FR',
		'es_ES',
		'pt_BR'
	);
}

function edev_process_request() {
	$R = array_merge($_GET, $_POST);
	if (isset($R['edev_submit'])) {
		if ($new_vote = @$R['new_vote']) {
			update_option('ecwid_show_vote_message', $new_vote == 'Y');
		}
		if ($new_date = @$R['new_date']) {
			update_option('ecwid_installation_date', strtotime($new_date));
		}

		if ($new_stats_date = @$R['new_stats_date']) {
			update_option('ecwid_stats_sent_date', strtotime($new_stats_date));
		}
		if (isset($R['new_lang']) && in_array($R['new_lang'], get_locales())) {
			$config = file_get_contents(ABSPATH . '/wp-config.php');
			$config = str_replace(
				"define('WPLANG', '" . WPLANG . "');",
				"define('WPLANG', '" . $R['new_lang'] . "');",
				$config
			);
			file_put_contents(ABSPATH . '/wp-config.php', $config);
		}

		header('Location: ' . $R['back_url']);
		exit;
	}
}

function edev_admin_script() {

	wp_register_script('edev-admin-js', plugins_url('ecwid-dev/js/admin.js'), array(), '', '');
	wp_enqueue_script('edev-admin-js');

}

function edev_footer() {
	include plugin_dir_path(__FILE__) . 'templates/container.php';
}