var recently_viewed = {products: []};

jQuery.cookie('test_ecwid_shopping_cart_recently_products_cookie', 'test_ecwid_shopping_cart_cookie_value', { path: '/' });
if (jQuery.cookie('test_ecwid_shopping_cart_recently_products_cookie') != 'test_ecwid_shopping_cart_cookie_value') {
	// Cookies do not work, we do nothing
	exit;
}

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
			'price': jQuery('.ecwid-productBrowser-details .ecwid-productBrowser-price:first').text()
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
		}
	}

	function create_recently_viewed(product, widget) {
		var id = jQuery(widget).closest('.widget.widget_ecwid_recently_viewed').attr('id');
		var template = jQuery('#recently-viewed-template-' + id).html();

		template = template.replace(/PRODUCT_ID/, product.id);
		template = template.replace(/LINK/g, product.link);
		template = template.replace(/IMAGE/g, product.image);
		template = template.replace(/PRICE/g, product.price);
		template = template.replace(/NAME/g, product.name);

		jQuery(widget).append(template);
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
}

function recently_viewed_on_resize()
{
	for (var i = 0; i < ecwid_recently_viewed_widgets.length; i++) {
		var parent = jQuery('.ecwid-recently-viewed-products', '#' + ecwid_recently_viewed_widgets[i].parent_id);
		if (parent.width() > 210) {
			parent.addClass('wide');
		} else {
			parent.removeClass('wide');
		}
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
});