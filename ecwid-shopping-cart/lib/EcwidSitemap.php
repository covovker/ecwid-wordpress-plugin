<?php

class EcwidSitemap {
	var $api;

	var $base_url;

	var $stack;

	var $priority = 0.5;

	var $changefreq = 'monthly';

	var $processed_products = array();

	function __construct($api, $base_url, $priority = 0.5, $changefreq = 'monthly') {
		$this->api = $api;
	}

	function EcwidSitemap($api, $base_url, $priority = 0.5, $changefreq = 'monthly') {
		if(version_compare(PHP_VERSION,"5.0.0","<")) {
			$this->__construct($api, $base_url, $priority, $changefreq);
		}
	}

	function reset() {
		$this->stack = null;
		$this->current_index = 0;
	}

	function get_next_item() {
		if (is_array($this->stack) && empty($this->stack)) {
			return false;
		}

		if (is_null($this->stack)) {
			$this->stack = array($this->fetch_category(0));
		}

		$current_array = &$this->stack[count($this->stack) - 1];

		if (!is_array($current_array) || empty($current_array)) {
			trigger_error('Empty current array error: something went wrong');
			return false;
		}

		$item = $current_array[0];

		$result = array('loc' => $this->base_url . '#!/~/');
		if ($item['type'] == 'product') {
			$product = $item['id'];
			$category = count($this->stack) > 1 ? $this->stack[count($this->stack) - 2][0]['id'] : 0;
			$result['loc'] .= sprintf('product/category=%s&id=%s', $category, $product);
		} elseif ($item['type'] == 'category') {
			$result['loc'] .= sprintf('category/id=%s', $item['id']);
		} else {
			trigger_error('Invalid item type error');
			return false;
		}
		$result['changefreq'] = $this->changefreq;
		$result['priority'] = $this->priority;

		$this->proceed();

		return $result;
  	}

	function proceed() {

		if (empty($this->stack)) {
			return;
		}

		$current_array = &$this->stack[count($this->stack) - 1];

		if (empty($current_array)) {
			array_pop($this->stack);
			$this->proceed();
			return;
		}

		$current_item = &$current_array[0];

		if ('product' == $current_item['type'] || $current_item['processed']) {
			$this->processed_products[$current_item['id']] = true;
			array_shift($current_array);
			if (empty($current_array)) {
				$this->proceed();
			}
			return;
		} else if ('category' == $current_item['type']) {
			$entries = $this->fetch_category($current_item['id']);
			$current_item['processed'] = true;
			if (!empty($entries)) {
				array_push($this->stack, $entries);
			} else {
				$this->proceed();
			}
		}
	}

	function fetch_category($id = 0) {

		$entries = array();
		$categories = $this->api->get_subcategories_by_id($id);
		if (!empty($categories)) {
			foreach ($categories as $category) {
				$entries[] = array(
					'type' => 'category',
					'id'  => $category['id']
				);
			}
		}

		$products = $this->api->get_products_by_category_id($id);
		if (!empty($products)) {
			foreach ($products as $product) {
				if (!array_key_exists($product['id'], $this->processed_products)) {
					$entries[] = array(
						'type' => 'product',
						'id' => $product['id']
					);
				}
			}
		}

		return $entries;
	}
}
