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
  add_action('admin_post_edev_get_var_value', 'get_var_value');
	add_action('admin_action_edev_download_pot', 'edev_download_pot');
	add_action('admin_head', 'edev_admin_head');
	add_action('wp_ajax_edev_drag', 'edev_drag');
}

function edev_drag()
{
	update_option('edev_container_x', $_GET['x']);
	update_option('edev_container_y', $_GET['y']);
}

function edev_admin_head()
{
	if (is_null(get_option('edev_crowdin_mode', null))) {
		add_option('edev_crowdin_mode', 'N');
	}

	if (get_option('edev_crowdin_mode') == 'Y') {
		echo <<<HTML
<script type="text/javascript">
  var _jipt = [];
  _jipt.push(['project', 'ecwid-plugin-for-wordpressorg']);
</script>
<script type="text/javascript" src="//cdn.crowdin.com/jipt/jipt.js"></script>
HTML;

	}
}

function get_var_value()
{
	$params = $_REQUEST;
	if ($params['var']) {
		$var = $params['var'];
		$value = get_option($var);

		if (!empty($value)) {
			echo 'option ' . $var . ': ';
			var_export($value);
		}

		else {
			$value = $GLOBALS[$var];
			if (!empty($value)) {
				echo 'global var ' . $var . ': ';
				var_export($value);
			}
		}

		if (empty($value)) {
			echo 'not found ' . $var;
		}
		exit();
	}
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
		'pt_BR',
		'tr_TR',
		'fake'
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

	$dir = realpath(plugin_dir_path(__FILE__) . '../ecwid-shopping-cart');

	$labels = get_labels_in_directory($dir, 'ecwid-shopping-cart');

	require plugin_dir_path(__FILE__) . 'templates/translate.php';
}

function edev_download_pot()
{
	$labels = get_labels_in_directory(realpath(plugin_dir_path(__FILE__) . '../ecwid-shopping-cart'), 'ecwid-shopping-cart');

	header('Content-Type:application/octet-stream');
	header('Content-Disposition: attachment; filename=ecwid-shopping-cart.pot');

	foreach ($labels as $key => $label) {
		echo 'msgid "' . str_replace('"', '\"', $key) .  "\"\n";
		echo 'msgstr "' . ($_GET['mode'] == 'filler' ? preg_replace('![^ ]!', 'x', $key) : '') . '"' . "\n\n";
	}

	die();
}

function edev_load_translations()
{
	$dir = realpath(plugin_dir_path(__FILE__) . '../ecwid-shopping-cart');

	$lang_dir = $dir . '/languages';

	$result = explode("\n", shell_exec($cmd = "find $lang_dir -iregex '.*\\.mo'"));

	$locales = array();
	$translations = array();
	foreach ($result as $filename) {
		if (strlen(trim($filename)) == 0) continue;
		$locale = substr($filename, strlen("$lang_dir/ecwid-shopping-cart-"), 5);
		$locales[] = $locale;
		$parser = new MOParser();
		$translations = array_merge($translations, $parser->loadTranslationData($filename, $locale));
	}

	return $translations;
}

function get_labels_in_directory($directory, $domain)
{
	$files = explode("\n", shell_exec('find ' . $directory . ' | grep ".php"'));

	$labels = array();
	foreach ($files as $ind => $file) {
		if (is_readable($file)) {
			$labels = array_merge($labels, get_translation_labels(file_get_contents($file)));
		}
	}

	$result = array();
	foreach ($labels as $label) {
		if (in_array($label['domain'], array('"' . $domain . '"', "'" . $domain . "'"))) {
			$l = $label['label'];
			if ($l{0} == '"') {
				$l = trim($l, '"');
			} elseif ($l[0] == "'") {
				$l = trim($l, "'");
				$l = str_replace("\\'", "'", $l);
			}
			$result[$l] = trim($label['domain'], "'\"");
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

	$data = get_plugin_data(WP_PLUGIN_DIR . '/ecwid-shopping-cart/ecwid-shopping-cart.php', false, false);

	$results[] = array(
		'label' => "'$data[Name]'",
		'domain' => '"ecwid-shopping-cart"'
	);
	$results[] = array(
		'label' => "'$data[Author]'",
		'domain' => '"ecwid-shopping-cart"'
	);
	$results[] = array(
		'label' => "'$data[Description]'",
		'domain' => '"ecwid-shopping-cart"'
	);

	foreach ($tokens as $token) {
		if (is_array($token) && $token[0] == 375) continue;

		$current_expect = $expect[$expect_ind];

		$string_match = (is_string($current_expect['token']) && $current_expect['token'] == $token)
		  || (is_array($current_expect['value']) && in_array($token[1], $current_expect['value']));


		$type_match = is_int($current_expect['token']) && @$token[0] == $current_expect['token'];

		if ($string_match) {
			$expect_ind++;
			$result['match'][] = $token;
		} elseif ($type_match) {

			$found = false;
			if ($current_expect['save_as']) {
				$result[$current_expect['save_as']] = $token[1];
				$found = true;
			}
			if (@$current_expect['value']) {
				foreach ($current_expect['value'] as $value) {
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
		} elseif (!$string_match && !$type_match && $expect_ind > 0) {
			$expect_ind = 0;
			$result = array();
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
        if ($R['mode'] == 'reset_messages') {
            require_once(plugin_dir_path(__FILE__) . '../ecwid-shopping-cart/includes/class-ecwid-message-manager.php');
            Ecwid_Message_Manager::reset_hidden_messages();
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

		$this->_data[$locale][''] = trim(@$this->_data[$locale]['']);

		unset($this->_data[$locale]['']);
		return $this->_data;
	}

}

function edev_admin_script() {

	wp_register_script('edev-admin-js', plugins_url('ecwid-dev/js/admin.js'), array('jquery-ui-draggable'), '', '');
	wp_enqueue_script('edev-admin-js');

}

function edev_footer() {
	include plugin_dir_path(__FILE__) . 'templates/container.php';

	$left = get_option('edev_container_x') . 'px';
	$top = get_option('edev_container_y') . 'px';
	echo <<<HTML
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery('#edev-container').css({
		'left': '$left',
		'top': '$top'
	});
});
</script>
HTML;

}
