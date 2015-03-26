<?php

require_once ECWID_PLUGIN_DIR . '/includes/class-ecwid-api-v3.php';

class Ecwid_OAuth {

	public function __construct()
	{
		add_action('admin_post_ecwid_oauth', array($this, 'process_authorization'));
		add_action('admin_post_ecwid_disconnect', array($this, 'disconnect_store'));
		add_action('admin_post_ecwid_show_reconnect', array($this, 'show_reconnect'));
	}

	public function show_reconnect()
	{
		$ecwid_oauth = $this;
		require_once(ECWID_PLUGIN_DIR . '/templates/reconnect.php');
	}

	public function get_auth_dialog_url( $scopes = array( 'read_store_profile', 'read_catalog' ) )
	{
		if ( !is_array( $scopes ) ) {
			return false;
		}

		$url = 'https://my.ecwid.com/api/oauth/authorize';

		$params['source']        = 'wporg';
		$params['client_id'] 		 = get_option( 'ecwid_oauth_client_id' );
		$params['redirect_uri']  = admin_url( 'admin-post.php?action=ecwid_oauth' );
		$params['response_type'] = 'code';
		$params['scope']         = implode( ' ', $scopes );

		return $url . '?' . build_query( $params );
	}

	public function process_authorization()
	{
		if ( isset( $_REQUEST['error'] ) || !isset( $_REQUEST['code'] ) ) {
			return $this->trigger_auth_error();
		}

		$params['code'] = $_REQUEST['code'];
		$params['client_id'] = get_option( 'ecwid_oauth_client_id' );
		$params['client_secret'] = get_option( 'ecwid_oauth_client_secret' );
		$params['redirect_uri'] = admin_url( 'admin-post.php?action=ecwid_oauth' );
		$params['grant_type'] = 'authorization_code';

		$return = wp_remote_post('https://my.ecwid.com/api/oauth/token', array('body' => $params));
		$result = json_decode($return['body']);

		if (
			!isset( $result->store_id )
			|| !isset( $result->scope )
			|| !isset( $result->access_token )
			|| ( $result->token_type != 'Bearer' )
		) {
			return $this->trigger_auth_error();
		}

		update_option( 'ecwid_store_id', $result->store_id );
		update_option( 'ecwid_oauth_token', $result->access_token );

		setcookie('ecwid_create_store_clicked', null, strtotime('-1 day'), ADMIN_COOKIE_PATH, COOKIE_DOMAIN);

		wp_redirect('admin.php?page=ecwid&settings-updated=true');
	}

	public function disconnect_store()
	{
		update_option( 'ecwid_store_id', '' );
		update_option( 'ecwid_oauth_token', '' );
		wp_redirect('admin.php?page=ecwid');
	}

	protected function trigger_auth_error()
	{
		wp_redirect('admin.php?page=ecwid&connection_error=true');

	}
}

$ecwid_oauth = new Ecwid_OAuth();