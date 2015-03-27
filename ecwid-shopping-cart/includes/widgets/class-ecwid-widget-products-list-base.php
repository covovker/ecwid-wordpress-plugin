<?php

class Ecwid_Widget_Products_List_Base extends Ecwid_Widget_Base {

	protected $_max = 10;
	protected $_min = 1;
	protected $_default = 3;

	public function __construct ( ) {
		parent::__construct( );

		wp_register_script('ecwid-products-list-js', plugins_url('ecwid-shopping-cart/js/products-list.js'), array('jquery-ui-widget'));
		wp_register_style('ecwid-products-list-css', plugins_url('ecwid-shopping-cart/css/products-list.css'));
	}

	public function _get_content( $args, $instance ) {
		wp_enqueue_style('ecwid-products-list-css');
		wp_enqueue_script('ecwid-products-list-js');

		$output = '';

		$products = $this->_get_products( $args, $instance );
		if ($products) {

			$counter = 0;
			for ($i = 0; $i < count($products); $i++) {
				$product = $products[$i];
				$counter++;
				if (isset($product->id) && isset($product->link)) {
					$ids[] = $product->id;
					$hide = $counter > $instance['number_of_products'] ? ' hidden' : '';
					$output .= <<<HTML
	<a class="product$hide" href="$product->link" alt="$product->name" title="$product->name">
		<div class="ecwid ecwid-SingleProduct ecwid-Product ecwid-Product-$product->id" data-single-product-link="$product->link" itemscope itemtype="http://schema.org/Product" data-single-product-id="$product->id">
			<div itemprop="image"></div>
			<div class="ecwid-title" itemprop="name"></div>
			<div itemtype="http://schema.org/Offer" itemscope itemprop="offers"><div class="ecwid-productBrowser-price ecwid-price" itemprop="price"></div></div>
		</div>
		<script type="text/javascript">xSingleProduct();</script>
	</a>
HTML;

				}
			}
		}

		$output .= $this->_get_products_list_init_js();

		return $output;
	}

	protected function _get_products_list_init_js() {
		$code = <<<HTML
<script type="text/javascript">
<!--
jQuery(document).ready(function() {
	jQuery('#$this->id ' . $this->_get_widget_wrapper_class_name()).productsList();
});
-->
</script>
HTML;

		return $code;
	}

	public function update( $new_instance, $old_instance ) {

		$num = intval($new_instance['number_of_products']);
		if ($num > $this->_max || $num < $this->_min) {
			$num = $this->_default;
		}

		$instance['title'] = strip_tags(stripslashes($new_instance['title']));
		$instance['number_of_products'] = $num;

		return $instance;
	}

	protected function _get_form($instance) {

		$output = parent::_get_form($instance);

		$number_of_products = $instance['number_of_products'];

		if (!$number_of_products) {
			$number_of_products = $this->_default;
		}

		if ($number_of_products) {
			$output .= '<p><label for="' . $this->get_field_name('number_of_products') . '">' . __( 'Number of products to show', 'ecwid-shopping-cart' ) . ': <input style="width:100%;" id="' . $this->get_field_id('number_of_products') . '" name="' . $this->get_field_name('number_of_products') . '" type="number" min="' . $this->min . '" max="' . $this->max . '" value="' . $number_of_products . '" /></label></p>';
		}

		return $output;
	}

	protected function _get_products() {
		return false;
	}
}