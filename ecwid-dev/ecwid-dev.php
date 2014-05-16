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
	add_action('admin_menu', 'edev_add_page');
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

function edev_add_page()
{
	add_menu_page(
		__('Translate Ecwid', 'ecwid-dev'),
		__('Translate Ecwid', 'ecwid-dev'),
		'manage_options',
		'edev',
		'edev_show_translate_page'
	);
}

function edev_show_translate_page()
{
	$translations = edev_load_translations();

	require plugin_dir_path(__FILE__) . 'templates/translate.php';
}

function edev_load_translations()
{
	$dir = realpath(plugin_dir_path(__FILE__) . '../ecwid-shopping-cart');

	$lang_dir = $dir . '/languages';

	$result = explode("\n", shell_exec($cmd = "find $lang_dir -iregex '.*\\.mo'"));

	$locales = array();
	foreach ($result as $filename) {
		if (strlen(trim($filename)) == 0) continue;
		$locale = substr($filename, strlen("$lang_dir/ecwid-shopping-cart-"), 5);
		$locales[] = $locale;
	}

	$labels = get_labels_in_directory($dir, 'ecwid-shopping-cart');

	die(var_dump($labels));
}

function get_labels_in_directory($directory, $domain)
{
	$files = explode("\n", shell_exec('find ' . $directory . ' | grep ".php"'));

	$labels = array();
	foreach ($files as $file) {
		if (is_readable($file)) {
			$labels += get_translation_labels(file_get_contents($file));
		}
	}

	die(var_dump($labels));
	$result = array();
	foreach ($labels as $label) {
		if ($label['domain'] == $domain) {
			$result[$label['label']] = $label['domain'];
		}
	}

	return $result;
}

function get_translation_labels($php_code)
{
	$expect = array(
		array(
			'token' => 307,
			'value' => array(
				'_e',
				'__',
				'esc_attr_e',
				'esc_html_e'
			)
		),
		array(
			'token' => '('
		),
		array(
			'token' => 315,
			'save_as' => 'label'
		),
		array(
			'token' => ','
		),
		array(
			'token' => 315,
			'save_as' => 'domain'
		),
		array(
			'token' => ')'
		)
	);

	$tokens = token_get_all($php_code);

	$expect_ind = 0;
	$results = array();
	$result = array();
	foreach ($tokens as $token) {
		if (is_array($token) && $token[0] == 375) continue;

		$current_expect = $expect[$expect_ind];

		$string_match = is_string($current_expect['token']) && $current_expect['token'] == $token;
		$type_match = is_int($current_expect['token']) && @$token[0] == $current_expect['token'];
		if ($string_match) {
			$expect_ind++;
			$result['match'][] = $token;
		} elseif ($type_match) {
			if ($expect[$expect_ind]['save_as']) {
				$result[$expect[$expect_ind]['save_as']] = $token[1];
				$found = true;
			}
			if (@$expect[$expect_ind]['value']) {
				foreach ($expect[$expect_ind]['value'] as $value) {
					if ($value == $token[1]) {
						$found = true;
						break;
					}
				}
				}
			if ($found) {
				$expect_ind++;
				$result['match'][] = $token;
			} else {
				$expect_ind = 0;
				$result = array();
			}
		}

		if ($expect_ind >= count($expect)) {
			$results[] = $result;
			$result = array();
			$expect_ind = 0;
		}
	}

	return $results;
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

class MoParser {

	private $_bigEndian   = false;
	private $_file        = false;
	private $_data        = array();

	private function _readMOData($bytes)
	{
		if ($this->_bigEndian === false) {
			return unpack('V' . $bytes, fread($this->_file, 4 * $bytes));
		} else {
			return unpack('N' . $bytes, fread($this->_file, 4 * $bytes));
		}
	}

	public function loadTranslationData($filename, $locale)
	{
		$this->_data      = array();
		$this->_bigEndian = false;
		$this->_file      = @fopen($filename, 'rb');
		if (!$this->_file) throw new Exception('Error opening translation file \'' . $filename . '\'.');
		if (@filesize($filename) < 10) throw new Exception('\'' . $filename . '\' is not a gettext file');

		// get Endian
		$input = $this->_readMOData(1);
		if (strtolower(substr(dechex($input[1]), -8)) == "950412de") {
			$this->_bigEndian = false;
		} else if (strtolower(substr(dechex($input[1]), -8)) == "de120495") {
			$this->_bigEndian = true;
		} else {
			throw new Exception('\'' . $filename . '\' is not a gettext file');
		}
		// read revision - not supported for now
		$input = $this->_readMOData(1);

		// number of bytes
		$input = $this->_readMOData(1);
		$total = $input[1];

		// number of original strings
		$input = $this->_readMOData(1);
		$OOffset = $input[1];

		// number of translation strings
		$input = $this->_readMOData(1);
		$TOffset = $input[1];

		// fill the original table
		fseek($this->_file, $OOffset);
		$origtemp = $this->_readMOData(2 * $total);
		fseek($this->_file, $TOffset);
		$transtemp = $this->_readMOData(2 * $total);

		for($count = 0; $count < $total; ++$count) {
			if ($origtemp[$count * 2 + 1] != 0) {
				fseek($this->_file, $origtemp[$count * 2 + 2]);
				$original = @fread($this->_file, $origtemp[$count * 2 + 1]);
				$original = explode("\0", $original);
			} else {
				$original[0] = '';
			}

			if ($transtemp[$count * 2 + 1] != 0) {
				fseek($this->_file, $transtemp[$count * 2 + 2]);
				$translate = fread($this->_file, $transtemp[$count * 2 + 1]);
				$translate = explode("\0", $translate);
				if ((count($original) > 1) && (count($translate) > 1)) {
					$this->_data[$locale][$original[0]] = $translate;
					array_shift($original);
					foreach ($original as $orig) {
						$this->_data[$locale][$orig] = '';
					}
				} else {
					$this->_data[$locale][$original[0]] = $translate[0];
				}
			}
		}

		$this->_data[$locale][''] = trim($this->_data[$locale]['']);

		unset($this->_data[$locale]['']);
		return $this->_data;
	}

}

function edev_admin_script() {

	wp_register_script('edev-admin-js', plugins_url('ecwid-dev/js/admin.js'), array(), '', '');
	wp_enqueue_script('edev-admin-js');

}

function edev_footer() {
	include plugin_dir_path(__FILE__) . 'templates/container.php';
}