wpCookies.set('test_ecwid_shopping_cart_recently_products_cookie', 'test_ecwid_shopping_cart_cookie_value', { path: '/' });
if (wpCookies.get('test_ecwid_shopping_cart_recently_products_cookie') != 'test_ecwid_shopping_cart_cookie_value') {
	// Cookies do not work, we do nothing
	exit;
}

jQuery.widget('ecwid.recentlyViewedProducts', jQuery.ecwid.productsList, {
	_justAdded: null,

	_create: function() {
		this._superApply(arguments);

		var self = this;
		Ecwid.OnPageLoaded.add(
			function(page) {

				self._justAdded = null;

				if (page.type == 'PRODUCT') {
					var product = {
						id: page.productId.toString(),
						name: page.name
					}

					setTimeout(function() {
						self.addViewedProduct(product);
					}, 300);
				}
			}
		);
	},

	addViewedProduct: function(product) {
		product.image = jQuery('.ecwid-productBrowser-details-thumbnail .gwt-Image').attr('src');
		product.link = window.location.href;
		if (jQuery('.ecwid-productBrowser-price .ecwid-productBrowser-price-value').length > 0) {
			product.price = jQuery('.ecwid-productBrowser-price .ecwid-productBrowser-price-value').html();
		} else {
			product.price = jQuery('.ecwid-productBrowser-price').html();
		}

		if (typeof this.products[product.id] == 'undefined') {
			this._justAdded = product.id;
			this.addProduct(product);
		} else {
			this.sort.splice(this.sort.indexOf(product.id), 1);
			this._addToSort(product.id);
		}

		this._refreshCookies(product);

		this._render();
	},

	_refreshCookies: function(product)
  {
		var cookieName = 'ecwid-shopping-cart-recently-viewed';

		var cookie = JSON.parse(wpCookies.get(cookieName));

		if (cookie == null || typeof(cookie) != 'object') {
			cookie = {last: 0, products: []};
		}

		var expires = new Date;
		expires.setMonth(expires.getMonth() + 1);

		var src = jQuery('script[src*="app.ecwid.com/script.js?"]').attr('src');
		var re = /app.ecwid.com\/script.js\?(\d*)/;
		cookie.store_id = src.match(re)[1];

		for (var i = 0; i < cookie.products.length; i++) {
			if (cookie.products[i].id == product.id) {
				cookie.products.splice(i, 1);
			}
		}

		cookie.products.unshift({
			id: product.id,
			link: product.link
		});

		wpCookies.set(cookieName, JSON.stringify(cookie), expires.toUTCString() );

  },

	_getProductsToShow: function() {
		// copy array using slice
		var sort = this.sort.slice();

		if (this._justAdded) {
			sort.splice(sort.indexOf(this._justAdded), 1);
		}

		if (sort.length > this.options.max && jQuery('.ecwid-productBrowser-ProductPage').length > 0) {
			var currentProductId = jQuery('.ecwid-productBrowser-ProductPage').attr('class').match(/ecwid-productBrowser-ProductPage-(\d+)/);

			if (sort.indexOf(currentProductId[1]) != -1) {
				sort.splice(
					sort.indexOf(
						currentProductId[1]
					), 1
				);
			}
		}

		return sort.reverse().slice(0, this.options.max);
	}
});

jQuery('.ecwid-recently-viewed-products').recentlyViewedProducts();