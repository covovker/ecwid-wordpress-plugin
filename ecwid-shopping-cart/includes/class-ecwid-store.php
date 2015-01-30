<?php

if ( !defined( 'ECWID_DEMO_STORE_ID' ) ) {
	// we are not in ecwid
	return;
}

require_once ECWID_PLUGIN_DIR . '/lib/ecwid_product_api.php';

class Ecwid_Store {

	protected $api = null;

	public function __construct()
	{
		$this->api = new EcwidProductApi( get_ecwid_store_id() );

		add_action( 'init', array($this, 'init_db_tables') );
	}

	public function get_product($id)
	{
		if ($this->are_products_up_to_date()) {
			return $this->get_local_product($id);
		} else {
			$this->api->get_product($id);
		}
	}

	public function init_db_tables()
	{
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$entities = array('product', 'order', 'order_item');

		$allTables = '';
		foreach ($entities as $entity) {

			$entity_def = array_merge(
				array(
					'fields' => array(),
					'keys' => array()
				),
				call_user_func(array($this, 'get_' . $entity . '_db_definition'))
			);

			$def = $this->get_generic_ecwid_object_db_definition();

			foreach ($def as $entry => $value) {
				$def[$entry] = array_merge($def[$entry], @$entity_def[$entry]);
			}

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

	protected function get_product_db_definition()
	{
		$def = $this->get_generic_ecwid_object_db_definition();

		$fields = array (
			'name' => 'varchar(255) NOT NULL default ""',
			'product_type' => 'bigint(20) NOT NULL default 0',
		);

		$def['fields'] = array_merge($def['fields'], $fields);

		$def = apply_filters('ecwid_product_db_definition', $def);


		return array(
			'fields' => $fields
		);
	}

	protected function get_order_db_definition()
	{
		$fields = array (
			'create_date' => 'datetime NOT NULL DEFAULT "0000-00-00 00:00:00"',
			'payment_status' => 'varchar(40) NOT NULL default ""',
			'fulfillment_status' => 'varchar(40) NOT NULL default ""'
		);

		return array(
			'fields' => $fields
		);
	}

	protected function get_order_item_db_definition()
	{
		$fields = array (
			'product_id' => 'bigint(20) NOT NULL default 0',
			'order_id' => 'bigint(20) NOT NULL default 0',
			'amount' => 'int(11) NOT NULL default 1'
		);

		return array(
			'fields' => $fields
		);
	}

	protected function get_generic_ecwid_object_db_definition()
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
}

$a = new Ecwid_Store();
$a->init_db_tables();