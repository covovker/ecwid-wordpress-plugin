<?php

if ( !defined( 'ECWID_DEMO_STORE_ID' ) ) {
	// we are not in ecwid
	return;
}


echo "start:" . ($start = microtime(true)) . "<br />";


require_once ECWID_PLUGIN_DIR . '/includes/class-ecwid-api-v3.php';

class Ecwid_Store {

	protected $api = null;

	public function __construct()
	{
		$this->api = new Ecwid_API_V3( get_ecwid_store_id() );

        add_action( 'init', array($this, 'init_db_tables') );
	}

	public function do_fetch_products_iteration() {
		$page = get_option('ecwid_last_fetched_product_page');
		if (!$page) {
			$page = 0;
		}

		$page_size = 3;

		$result = $this->api->get_products(array(
			'offset' => $page * $page_size,
			'limit' => $page_size,
			'sortBy' => 'ADDED_TIME_ASC'
		));

		global $wpdb;

		foreach($result['items'] as $product) {
			$product_id = $wpdb->insert(
				$wpdb->ecwid_products,
				array(
					'id' => $product['id'],
					'hash_url' => substr($product['url'], strpos($product['url'], '#')),
					'thumb_url' => $product['thumbnailUrl'],
					'name' => $product['name'],
					'raw' => serialize($product)
				)
			);
		}

		update_option('ecwid_last_fetched_product_page', $page + 1);
	}

	public function do_fetch_orders_iteration()
	{
		$date = get_option('ecwid_last_fetched_order_date');

		//$date = '';
		if (!$date) {
			$date = $this->_get_first_order_day();

			if (!$date) {
				// finished
				// @TODO handle this case
				//die(var_dump('123123', $date));
			}
		}

		$last_order_date = $this->_fetch_orders($date);

		if (!$last_order_date) {
			return false;
		}

		$next_date = $last_order_date + 60*60*24;
		update_option('ecwid_last_fetched_order_date', $next_date);
	}

	protected function _get_first_order_day()
	{
		$result = $this->api->get_orders(array(
			'limit' => '1'
		));

		if ($result['total'] == 0) {
			return false;
		}

		$result = $this->api->get_orders(array(
			'limit' => '1',
			'offset' => $result['total'] - 1
		));

		return $this->_get_day_start($result['items'][0]['createDate']);
	}

	/**
	 * Fetches orders since specified date and returns last order date
	 *
	 * @param $since_date int Unix timestamp date to fetch orders from
	 *
	 * @return last processed date
	 */
	protected function _fetch_orders($since_date)
	{
		$limit = 100;
		// Unfortunately, API does not allow to sort results, therefore in order
		// to have a persistent set of orders we have to paginate and rely on date
		// The idea is the following
		// 1) Fetch first 100 orders from the last date
		// 2) get the most recent date
		// 3) fetch all orders at that date
		// The combined result is the set processed in one run
		// It allows to
		// 1) Fetch around 100 orders for shops with fewer orders per day
		// 2) Fetch a single day of orders for larger shops
		// 3) Have an ability to keep track of where did the processing end
		//    in this run
		// In such scheme the overhead is quite significant for smaller shops
		// but they will have the whole process take less runs anyway
		// and larger shops with over 100 orders per day will still have no
		// significant overhead when fetching their orders

        // Fetch first 100 orders
		$result = $this->api->get_orders(array(
			'createdFrom' => $this->api->make_time($since_date),
			'limit' => $limit
		));

		if ($result == false) {
			return false;
		}

        $result = $this->api->get_orders(array(
			'createdFrom' => $this->api->make_time($since_date),
			'limit' => $limit,
			'offset' => $result['total'] - $result['limit']
		));

        // Get the most recent date
		$latest_date = $since_date;
		foreach ($result['items'] as $order) {
            $create_date = $this->api->parse_time($order['createDate']);
			echo "$create_date > $latest_date = " . ($create_date > $latest_date ? 'Y' : 'N') . '<br />';
			if ($create_date > $latest_date) {
				$latest_date = $create_date;
			}
		}

        $orders_sets = array(
			$result['items']
		);

        $latest_date_params = array(
            'createdFrom' => $this->api->make_time($latest_date),
            'createdTo' => $this->api->make_time($latest_date + 60*60*24),
            'limit' => $limit
        );

        // Fetch all orders at latest date
		$result = $this->api->get_orders($latest_date_params);

		$orders_sets[] = $result['items'];
		$offset = 0;
		while ($result['count'] + $result['offset'] < $result['total']) {
			$offset += $limit;
            $latest_date_params['offset'] = $offset;
            $batch = $this->api->get_orders($latest_date_params);

			$orders_sets[] = $batch['items'];
		}

        // Process all fetched orders
		$processed_orders = array();
		foreach($orders_sets as $set) {
			foreach ($set as $order) {
				if (in_array($order['orderNumber'], $processed_orders)) {
					continue;
				}

				$this->process_order($order);

				$processed_orders[] = $order['orderNumber'];
			}
		}

        return $latest_date;
	}

	public function process_order($order)
	{
		global $wpdb;

		$order_id = $wpdb->insert(
				$wpdb->ecwid_orders,
				array(
					'id' => $order['orderNumber'],
					'create_date' => $order['createDate'],
					'payment_status' => $order['paymentStatus'],
					'fulfillment_status' => $order['fulfillmentStatus'],
					'raw' => serialize($order)
				)
		);

		foreach ($order['items'] as $item) {
			$this->_process_order_item($order_id, $item);
		}
	}

	protected function _process_order_item($order_id, $item) {
		global $wpdb;

		$item_id = $wpdb->insert(
			$wpdb->ecwid_order_items,
			array(
				'id' => $item['id'],
				'product_id' => $item['productId'],
				'order_id' => $order_id,
				'quantity' => $item['quantity'],
				'price' => $item['price']
			)
		);
	}

	public function get_product($id)
	{
		$product = null;
		if ($this->is_db_outdated()) {
			$this->update_db();
		}

		return $this->_get_local_product($id);
	}

	public function init_db_tables()
	{
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$entities = array('products', 'orders', 'order_items');

		$allTables = '';
		foreach ($entities as $entity) {

			$entity_def = array_merge(
				array(
					'fields' => array(),
					'keys' => array()
				),
				call_user_func(array($this, '_get_' . $entity . '_db_definition'))
			);

			$def = $this->_get_generic_ecwid_object_db_definition();

			foreach ($def as $entry => $value) {
				$def[$entry] = array_merge($def[$entry], @$entity_def[$entry]);
			}

			$wpdb->{'ecwid_' . $entity} = $wpdb->prefix . 'ecwid_' . $entity;

			$sql = 'CREATE TABLE ' . $wpdb->prefix . 'ecwid_' . $entity . '(';

			foreach ($def['fields'] as $name => $field_definition) {
				$sql .= "$name $field_definition,";
			}

			foreach ($def['keys'] as $name => $key_definition) if ($name != 'PRIMARY') {
				$sql .= "KEY $name ($key_definition),";
			}

			$sql .= 'PRIMARY KEY (' . $def['keys']['PRIMARY'] . ')';

			$sql .= '); ';

			$allTables .= $sql;

		}

		dbDelta($allTables);
	}

	public function is_db_outdated()
	{
		return true;
	}

	protected function _get_products_db_definition()
	{
		$fields = array (
			'name' => 'varchar(255) NOT NULL default ""',
			'hash_url' => 'varchar(255) NOT NULL default ""',
			'thumb_url' => 'varchar(255) NOT NULL default ""',
			'raw' => 'text'
		);

		return array(
			'fields' => $fields
		);
	}

	protected function _get_orders_db_definition()
	{
		$fields = array (
			'create_date' => 'datetime NOT NULL DEFAULT "0000-00-00 00:00:00"',
			'payment_status' => 'varchar(40) NOT NULL default ""',
			'fulfillment_status' => 'varchar(40) NOT NULL default ""',
			'raw' => 'text'
		);

		return array(
			'fields' => $fields
		);
	}

	protected function _get_order_items_db_definition()
	{
		$fields = array (
			'product_id' => 'bigint(20) NOT NULL default 0',
			'order_id' => 'bigint(20) NOT NULL default 0',
			'quantity' => 'int(11) NOT NULL default 1',
            'price' => 'decimal (10, 2) NOT NULL default 0'
		);

		return array(
			'fields' => $fields
		);
	}

	protected function _get_generic_ecwid_object_db_definition()
	{
		return array(
			'fields' => array(
				'wp_id' => 'bigint(20) NOT NULL auto_increment',
				'id' => 'bigint(20) NOT NULL default 0'
			),
			'keys' => array(
				'PRIMARY' => 'wp_id',
				'id' => 'id'
			)
		);
	}

	protected function _get_local_product($id)
	{

	}

    protected function _get_day_start($api_time)
    {
        $day = date_parse($api_time);

        return mktime(0, 0, 0, $day['month'], $day['day'], $day['year']);
    }
}

$ecwid_store = new Ecwid_Store();
