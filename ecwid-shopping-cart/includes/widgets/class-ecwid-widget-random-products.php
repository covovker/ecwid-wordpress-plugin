<?php

class Ecwid_Widget_Random_Products extends Ecwid_Widget_Products_List_Base {

	public function __construct ( ) {
		$this->_label = 'Random products';
		$this->_name = 'ecwidrandomproducts';
		$this->_description = 'Some description for random products widget';

		parent::__construct( );
	}

	protected function _get_products( $args, $instance ) {
		global $wpdb;

		$source_ids = $wpdb->get_col(
			'SELECT wp_id FROM ' . $wpdb->ecwid_products
		);

		$result_ids = array();

		for ( $i = 0; $i < $instance['number_of_products'] && count($source_ids); $i++ ) {
			$index = rand(0, count($source_ids) - 1);

			$result_ids[] = $source_ids[ $index ];

			array_splice( $source_ids, $index, 1);
		}

		$ids_string = implode ( '","', $result_ids );
		$products = $wpdb->get_results(
			'SELECT id, name, hash_url, thumb_url FROM ' . $wpdb->ecwid_products
			. ' WHERE wp_id IN("' . $ids_string . '")'
		);

		foreach ( $products as $key => $product ) {
			$products[$key]->link = ecwid_get_store_page_url() . $product->hash_url;
		}

		return $products;
	}
}