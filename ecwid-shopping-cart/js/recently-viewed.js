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

/*var recently_viewed = {products: []};

jQuery(document).ready(function() {
	var cookieName = 'ecwid-shopping-cart-recently-viewed';

	Ecwid.OnPageLoaded.add(function(page) {
		if (page.type == 'PRODUCT' && jQuery('.ecwid-productBrowser-details').length > 0) {
			var img = jQuery('.ecwid-productBrowser-details-thumbnail .gwt-Image');
			if (img.attr('src') == '') {

				img.on('load', function() {
					addRecentlyViewedProduct(page.productId, location.href);
				});
			} else {
				addRecentlyViewedProduct(page.productId, location.href);
			}
		}

		updateRecentlyViewed(page);
	});

	function addRecentlyViewedProduct(id, link) {
		var cookie = JSON.parse(wpCookies.get(cookieName));

		if (cookie == null || typeof(cookie) != 'object') {
			cookie = {last: 0, products: []};
		}

		for (var i in cookie.products) {
			if (cookie.products[i].id == id) {
				cookie.products.splice(i, 1);
				break;
			}
 		}

		for (var i in recently_viewed.products) {
			if (recently_viewed.products[i].id == id) {
				recently_viewed.products.splice(i, 1);
				break;
			}
		}

		cookie.products.unshift({'id': id, 'link': link});
		recently_viewed.products.unshift({
			'id': id,
			'link': link,
			'name': jQuery('.ecwid-productBrowser-head').text(),
			'image': jQuery('.ecwid-productBrowser-details .ecwid-productBrowser-details-thumbnail img.gwt-Image').attr('src'),
			'price': jQuery('.ecwid-productBrowser-details .ecwid-productBrowser-price-value').text()
		});

		if (cookie.products.length > 11) {
			cookie.products.pop();
		}

		var expires = new Date;
		expires.setMonth(expires.getMonth() + 1);

		var src = jQuery('script[src*="app.ecwid.com/script.js?"]').attr('src');
		var re = /app.ecwid.com\/script.js\?(\d*)/;
		cookie.store_id = src.match(re)[1];

		wpCookies.set(cookieName, JSON.stringify(cookie), expires.toUTCString() );
	}

	function updateRecentlyViewed(page)
	{
		// An array of items that should be hidden first
		hideable = [];
		if (page.type == 'PRODUCT') {
			hideable.push(page.productId);
		}

		for (var i = 0; i < ecwid_recently_viewed_widgets.length; i++) {
			var items = recently_viewed.products;
			var parent = jQuery('#' + ecwid_recently_viewed_widgets[i].parent_id);
			parent = jQuery('.ecwid-recently-viewed-products', parent);
			if (parent.length == 0) {
				// this might happen if no recently viewed products existed
				parent= jQuery('<div class="ecwid-recently-viewed-products">').appendTo(jQuery('#' + ecwid_recently_viewed_widgets[i].parent_id));
			}

			// restore full array from widget contents
			for (var j = 0; j < items.length; j++) {
				if (items[j].initial && jQuery('.ecwid-Product-' + items[j].id + ' .ecwid-title', parent).text() != '') {
					items[j] = {
						id: items[j].id,
						name: jQuery('.ecwid-Product-' + items[j].id + ' .ecwid-title', parent).text(),
						image: jQuery('.ecwid-Product-' + items[j].id + ' img', parent).attr('src'),
						price: jQuery('.ecwid-Product-' + items[j].id + ' .ecwid-price:first', parent).text(),
						link: jQuery('.ecwid-Product-' + items[j].id, parent).closest('a.product').attr('href')
					}

					jQuery('.ecwid-Product-' + items[j].id + ' .ecwid-title', parent)
							.data('initial-text', jQuery('.ecwid-Product-' + items[j].id + ' .ecwid-title', parent).text())
				}
			}

			// create all missing widgets & reset default visibility
			for (var j = 0; j < items.length; j++) {
				if (jQuery('.ecwid-Product-' + items[j].id, parent).length == 0) {
					create_recently_viewed(items[j], parent);
				}
				items[j].show = true;
			}

			// Remove hideable items first
			var visible = items.length;
			for (var j = items.length - 1; j >= 0; j--) {
				if (visible <= ecwid_recently_viewed_widgets[i].max) {
					break;
				}
				if (jQuery.inArray(items[j].id, hideable) != -1) {
					items[j].show = false;
					visible--;
				}
			}

			// Leave the newer ones visible
			for (var j = items.length - 1; j >= 0; j--) {
				if (visible <= ecwid_recently_viewed_widgets[i].max) {
					break;
				}

				items[j].show = false;
				visible--;
			}

			for (var j = items.length - 1; j >= 0; j--) {
				if (items[j].show) {
					jQuery('.ecwid-Product-' + recently_viewed.products[j].id, parent).closest('a.product').removeClass('hidden').prependTo(parent);
				} else {
					jQuery('.ecwid-Product-' + recently_viewed.products[j].id, parent).closest('a.product').addClass('hidden');
				}
			}

			if (items.length > 0) {
				jQuery('#' + ecwid_recently_viewed_widgets[i].parent_id).show();
			} else {
				jQuery('#' + ecwid_recently_viewed_widgets[i].parent_id).hide();
			}
		}
	}

	function create_recently_viewed(product, widget) {
		var id = jQuery(widget).closest('.widget_ecwid_recently_viewed').attr('id');
		var template = jQuery('#recently-viewed-template-' + id).html();

		template = template.replace(/PRODUCT_ID/, product.id);
		template = template.replace(/LINK/g, product.link);
		template = template.replace(/IMAGE/g, product.image);
		template = template.replace(/PRICE/g, product.price);
		template = template.replace(/NAME/g, product.name);

		jQuery(widget).append(template);
		var title_el = jQuery(widget).find('.ecwid-title');
		title_el.data('initial-text', title_el.text());
	}
});

function ecwid_validate_recently_viewed(parent_id, ids)
{
	for (var i = 0; i < ids.length; i++) {
		var parent = jQuery('.ecwid-SingleProduct.ecwid-Product-' + ids[i], jQuery('#' + parent_id));
		var product = null;
		if (jQuery('.ecwid-title', parent).text() != '') {
			product  = {
				id: ids[i],
				name: jQuery('.ecwid-title', parent).text(),
				image: jQuery('img', parent).src,
				price: jQuery('.ecwid-price', parent).text(),
				link: jQuery(parent).closest('a.product').attr('href')
			};
		} else {
			product = {
				id: ids[i],
				link: jQuery(parent).closest('a.product').attr('href'),
				initial: true
			}
		}
		recently_viewed.products.push(product);
	}

	if (jQuery('#' + parent_id).find('a.product').length == 0) {
		jQuery('#' + parent_id).hide();
	}
}

function recently_viewed_on_resize()
{
	for (var i = 0; i < ecwid_recently_viewed_widgets.length; i++) {
		var parent = jQuery('.ecwid-recently-viewed-products', '#' + ecwid_recently_viewed_widgets[i].parent_id);
		if (parent.width() > 250) {
			parent.addClass('wide');
		} else {
			parent.removeClass('wide');
		}

		parent.find('.ecwid-title').each(function(idx, el) {
			jQuery(el).html(jQuery(el).data('initial-text'));
		})
	}
}

// Debounce function from http://unscriptable.com/2009/03/20/debouncing-javascript-methods/
var ecwid_debounce = function (func, threshold, execAsap) {

	var timeout;

	return function debounced () {
		var obj = this, args = arguments;
		function delayed () {
			if (!execAsap)
				func.apply(obj, args);
			timeout = null;
		};

		if (timeout)
			clearTimeout(timeout);
		else if (execAsap)
			func.apply(obj, args);

		timeout = setTimeout(delayed, threshold || 100);
	};

}

jQuery(window).resize(ecwid_debounce(recently_viewed_on_resize, 200));

if (typeof ecwid_recently_viewed_widgets != 'undefined') {
	for (var i = 0; i < ecwid_recently_viewed_widgets.length; i++) {
		ecwid_validate_recently_viewed(ecwid_recently_viewed_widgets[i].parent_id, ecwid_recently_viewed_widgets[i].ids);

	}
}

jQuery(document).ready(function() {
	recently_viewed_on_resize();
});*/