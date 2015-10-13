<?php


include ECWID_PLUGIN_DIR . "lib/phpseclib/AES.php";

class Ecwid_OAuth {

	const TOKEN_PLACEMENT_UNDEFINED = 0;
	const TOKEN_PLACEMENT_DB = 1;
	const TOKEN_PLACEMENT_DB_AND_FS = 2;

	const OAUTH_CLIENT_ID = 'RD4o2KQimiGUrFZc';
	const OAUTH_CLIENT_SECRET = 'jEPVdcA3KbzKVrG8FZDgNnsY3wKHDTF8';

	const TEST_FILE_NAME = 'test_fs';
	const TOKEN_FILE_NAME = 'token';

	const TEST_OPTION_NAME = 'ecwid_test_vault';
	const TOKEN_OPTION_NAME = 'ecwid_vault_token';

	protected $_token_placement = 0;

    protected $crypt = null;

	public function __construct()
	{
		add_action('admin_post_ecwid_oauth', array($this, 'process_authorization'));
		add_action('admin_post_ecwid_disconnect', array($this, 'disconnect_store'));
		add_action('admin_post_ecwid_show_reconnect', array($this, 'show_reconnect'));

		$this->_load_params();

        $this->crypt = new Crypt_AES();
        $this->crypt->setIV(substr(md5(SECURE_AUTH_SALT . get_ecwid_store_id()), 0, 16));
        $this->crypt->setKey(SECURE_AUTH_KEY);
	}

	public function show_reconnect()
	{
		$ecwid_oauth = $this;
		require_once(ECWID_PLUGIN_DIR . '/templates/reconnect.php');
	}

	public function test_post()
	{
		$return = wp_remote_post('https://my.ecwid.com/api/oauth/token');

		return is_array($return);
	}

	public function get_auth_dialog_url( $params = null )
	{

		$default_params = array(
			'scopes' => array('read_store_profile', 'read_catalog' ),
			'redirect_uri' => admin_url( 'admin-post.php?action=ecwid_oauth' )
		);

		if ( is_array($params) ) {
			$params = array_merge($default_params, $params);
		}

        if (is_null($params)) {
            $params = $default_params;
        }

		if ( !is_array( $params )
            || !is_array( $params['scopes'] )
            || empty( $params['scopes'] )
        ) {
			return false;
		}

		if (isset($params['returnUrl'])) {
			$params['redirect_uri'] = admin_url( 'admin.php?action=ecwid_oauth' );
//			$params['redirect_uri'] = admin_url( 'admin.php?action=ecwid_oauth&return_url=' . urlencode($params['returnUrl'] ) );
		}

		$url = 'https://my.ecwid.com/api/oauth/authorize';

        $query = array();

		$query['source']        = 'wporg';
		$query['client_id']     = self::OAUTH_CLIENT_ID;
		$query['redirect_uri']  = $params['redirect_uri'];
		$query['response_type'] = 'code';
		$query['scope']         = implode( ' ', $params['scopes'] );

		return $url . '?' . build_query( $query );
	}

	public function process_authorization()
	{
		if ( isset( $_REQUEST['error'] ) || !isset( $_REQUEST['code'] ) ) {
			return $this->trigger_auth_error();
		}

		$params['code'] = $_REQUEST['code'];
		$params['client_id'] = self::OAUTH_CLIENT_ID;
		$params['client_secret'] = self::OAUTH_CLIENT_SECRET;
		$params['redirect_uri'] = admin_url( 'admin-post.php?action=ecwid_oauth' );
		$params['grant_type'] = 'authorization_code';

		$return = wp_remote_post('https://my.ecwid.com/api/oauth/token', array('body' => $params));

		if (is_array($return) && isset($return['body'])) {
			$result = json_decode($return['body']);
		}

		if (
			!is_array($return)
			|| !isset( $result->store_id )
			|| !isset( $result->scope )
			|| !isset( $result->access_token )
			|| ( $result->token_type != 'Bearer' )
		) {
			ecwid_log_error(var_export($return, true));
			return $this->trigger_auth_error();
		}

		update_option( 'ecwid_store_id', $result->store_id );
		$this->_prepare_storage();
		$this->_save_token($result->access_token);

		setcookie('ecwid_create_store_clicked', null, strtotime('-1 day'), ADMIN_COOKIE_PATH, COOKIE_DOMAIN);

		if (isset($_REQUEST['return_url'] ) ) {
			wp_redirect( $_REQUEST['return_url'] );
		} else {
			wp_redirect( 'admin.php?page=ecwid&settings-updated=true' );
		}
	}

	public function disconnect_store()
	{
		update_option( 'ecwid_store_id', '' );
		update_option( 'ecwid_oauth_token', '' );
		update_option( 'ecwid_is_api_enabled', 'off' );
		update_option( 'ecwid_api_check_time', 0 );

		wp_redirect('admin.php?page=ecwid');
	}

    public function get_safe_scopes_array($scopes)
    {
        if (!isset($scopes)) {
            return array();
        }

        $scopes = '';
        if (!empty($scopes)) {
            $scopes_array = explode(' ', $scopes);

            foreach ($scopes_array as $key => $scope) {
                if (!preg_match('/^[a-z_]+$/', $scope)) {
                    unset($scopes_array[$key]);
                }
            }
        }

        return $scopes_array;
    }


	protected function trigger_auth_error()
	{
		update_option('ecwid_last_oauth_fail_time', time());

		$logs = get_option('ecwid_error_log');

		if ($logs) {
			$logs = json_decode($logs);
		}

		if (count($logs) > 0) {
			$entry = $logs[count($logs) - 1];
			if (isset($entry->message)) {
				$last_error = $entry->message;
			}
		}
		if (!$last_error) {
			return;
		}

		$url = 'http://' . APP_ECWID_COM . '/script.js?805056&data_platform=wporg&data_wporg_error=' . urlencode($last_error) . '&url=' . urlencode(get_bloginfo('url'));

		wp_remote_get($url);

		wp_redirect('admin.php?page=ecwid&connection_error=true');
	}


	protected function _prepare_storage()
	{
		$this->_token_placement = $this->_test_fs() ? self::TOKEN_PLACEMENT_DB_AND_FS : self::TOKEN_PLACEMENT_DB;

		$everything_is_ok = $this->_test_vault();
		if (!$everything_is_ok) {
			$this->_token_placement = self::TOKEN_PLACEMENT_DB;
		}

		$this->_store_params();
	}

	public function get_oauth_token()
	{
		if ($this->is_initialized()) {
			return $this->_load_token();
		}

		return null;
	}

	public function is_initialized()
	{
		return in_array(
			$this->_token_placement, array(self::TOKEN_PLACEMENT_DB, self::TOKEN_PLACEMENT_DB_AND_FS)
		) && get_option(self::TOKEN_OPTION_NAME);
	}

	protected function _store_params()
	{
		update_option('ecwid_oauth_placement', $this->_token_placement);
	}

	protected function _load_params()
	{
		$this->_token_placement = get_option('ecwid_oauth_placement');
	}

	protected function _test_fs()
	{
		if (!is_writable(ECWID_PLUGIN_DIR)) return false;

		$testfile = ECWID_PLUGIN_DIR . '/' . self::TEST_FILE_NAME;
		$message = 'this is a test message';

		if (!file_put_contents($testfile, $message)) return false;

		$result = file_get_contents($testfile);

		return $result == $message;
	}

	protected function _test_vault()
	{
		$message = 'this is a test message';

		$this->_save_token($message, true);

		$loaded = $this->_load_token(true);

        $this->_cleanup_test_data();

		return $loaded == $message;
	}

	protected function _save_token($token, $test = false)
	{
		$value = base64_encode($this->crypt->encrypt($token));

		$filename = ECWID_PLUGIN_DIR . '/' . (!$test ? self::TOKEN_FILE_NAME : self::TEST_FILE_NAME);
		$option_name = !$test ? self::TOKEN_OPTION_NAME : self::TEST_OPTION_NAME;

		$fs_value = '';
		$db_value = $value;

		if ($this->_token_placement == self::TOKEN_PLACEMENT_DB_AND_FS) {
			$fs_value = substr($value, 0, strlen($value) / 2);
			$db_value = substr($value, strlen($value) / 2);
			$result = file_put_contents($filename, $fs_value);
		}

		update_option($option_name, $db_value);
	}

	protected function _load_token($test = false)
	{
		$filename = ECWID_PLUGIN_DIR . '/' . (!$test ? self::TOKEN_FILE_NAME : self::TEST_FILE_NAME);
		$option_name = !$test ? self::TOKEN_OPTION_NAME : self::TEST_OPTION_NAME;

		$fs_value = '';
		if ($this->_token_placement == self::TOKEN_PLACEMENT_DB_AND_FS) {
			$fs_value = file_get_contents($filename);

            if (empty($fs_value)) return false;
		}

		$db_value = get_option($option_name);
        if (empty($db_value)) return false;

		$encrypted = base64_decode($fs_value . $db_value);
        if (empty($encrypted)) return false;

		$token = $this->crypt->decrypt($encrypted);

		return $token;
	}

	protected function _cleanup_test_data()
	{
		$filename = ECWID_PLUGIN_DIR . '/' . self::TEST_FILE_NAME;

		$fs_is_available = $this->_test_fs();

		if ($fs_is_available) {
			unlink($filename);
		}

		delete_option(self::TEST_OPTION_NAME);
	}
}

$ecwid_oauth = new Ecwid_OAuth();
