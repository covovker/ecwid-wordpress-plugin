<?php

require_once(ECWID_PLUGIN_DIR . 'lib/ecwid_platform.php');

class Ecwid_API_V3 {

	protected $_store_id;

	public function __construct($store_id)
	{
		$this->_store_id = $store_id;
	}

	public function get_orders($params)
	{
        $def = $this->_explain_get_orders();

		$endpoint = sprintf(
			'https://app.ecwid.com/api/v3/%s/%s',
			$this->_store_id,
			$def['endpoint']
		);


		$params = $this->_prepare_params($def['params'], $params);

        $result = EcwidPlatform::fetch_url($endpoint . '?' . http_build_query($params));

        if ($result['code'] == 200 && isset($result['data'])) {
			$result = json_decode($result['data'], true);
		} else {
			$result = false;
		}

		return $result;
	}

	public function get_api_token()
	{
		return get_option( 'ecwid_oauth_token' );
	}

    public function make_time($time)
    {
        return strftime('%Y-%m-%d %H:%M:%S', $time);
    }

    public function parse_time($api_time)
    {
        return strtotime($api_time);
    }

	/**
	 * Prepares params array according to params definition. Filters redundant
	 * and incorrect data type values
	 *
	 * @param $definition API method parameters definition
	 * @param $params     Parameters passed to function
	 *
	 * @return array      Filtered parameters
	 */
	protected function _prepare_params($definition, $params) {

		$result = array();

		foreach ($definition as $name => $type) {
			if (isset($params[$name])) {
				if ($type == 'number' && !is_numeric($params[$name])) {
					continue;
				}

				$result[$name] = $params[$name];
			}
		}

		$result['token']  = get_option('ecwid_oauth_token');

		return $result;
	}

	protected function _explain_get_orders()
	{
		return array(
			'endpoint' => 'orders',
			'method' => 'GET',
			'params' => array(
				'storeId' => 'number',
				'token' => 'string',
				'offset' => 'number',
				'limit' => 'number',
				'keywords' => 'string',
				'couponCode' => 'number',
				'totalFrom' => 'number',
				'totalTo' => 'number',
				'orderNumber' => 'number',
				'vendorOrderNumber' => 'string',
				'customer' => 'string',
				'createdFrom' => 'string',
				'createdTo' => 'string',
				'paymentMethod' => 'string',
				'shippingMethod' => 'string',
				'paymentStatus' => 'string',
				'fulfillmentStatus' => 'string',
				'updatedFrom' => 'string',
				'updatedTo' => 'string',
			)
		);
	}

    protected function _explain_get_products()
    {

    }
}