jQuery.widget('ecwid.productsList', {
	container: null,

	products: {
	},

	options: {
		max: 3
	},

	sort: [],

	_prefix: 'ecwid-productsList',

	_create: function() {
		this.element.addClass(this._prefix);
		this._removeInitialContent();
		this.container = jQuery('<ul>').appendTo(this.element);
		this._setOption('debug', false);
		this._initFromHtmlData();
		this._readSingleProducts();
		this._onWindowResize();
		this._render();

		var self = this;
		jQuery(window).resize(
			ecwid_debounce(
				function() {
					self._onWindowResize();
				}
			, 200)
		);
	},

	_render: function() {
		var toShow = this._getProductsToShow();

		for (var i in toShow) {
			this._showProduct(this.products[toShow[i]]);
		}

		for (var id in this.products) {
			if (toShow.indexOf(id) == -1) {
				this._hideProduct(this.products[id]);
			}
		}

	},

	_setOption: function(key, value) {
		this._super(key, value);
		if (key == 'max') {
			this.refresh();
		}
	},

	_getProductClass: function(id) {
		return this._prefix + '-product-' + id;
	},

	_getProductElement: function(id) {
		return this.container.find('.' + this._getProductClass(id));
	},

	_showProduct: function(product) {
		var existing = this._getProductElement(product.id);

		if (existing.length == 0) {
			this._renderProduct(product);
		}


		this._getProductElement(product.id)
				.addClass('show')
				.removeClass('hide')
				.prependTo(this.container);
	},

	_hideProduct: function(product) {
		this._getProductElement(product.id)
			.addClass('hide')
			.removeClass('show');
	},

	_renderProduct: function(product) {
		var container = jQuery('<li class="' + this._getProductClass(product.id) + '">').appendTo(this.container);

		if (product.link != '') {
			container = jQuery('<a>')
				.attr('href', product.link)
				.attr('title', product.name)
				.appendTo(container);
		}
		if (product.image) {
			jQuery('<div class="' + this._prefix + '-image">').append('<img src="' + product.image + '">').appendTo(container);
		} else {
			jQuery('<div class="' + this._prefix + '-image ecwid-noimage">').appendTo(container);
		}
		jQuery('<div class="' + this._prefix + '-name">').append(product.name).appendTo(container);
		jQuery('<div class="' + this._prefix + '-price ecwid-productBrowser-price">').append(product.price).appendTo(container);

	},

	_initFromHtmlData: function() {
		for (var option_name in this.options) {
			var data_name = 'ecwid-' + option_name;
			if (typeof(this.element.data(data_name)) != 'undefined') {
				this._setOption(option_name, this.element.data(data_name));
			}
		}
	},

	_removeInitialContent: function() {
		this.originalContentContainer = jQuery('<div class="ecwid-initial-productsList-content">')
				.data('generatedProductsList', this)
				.append(this.element.find('>*'))
				.insertAfter(this.element);
	},

	_readSingleProducts: function() {

		var self = this;
		var singleProductLoaded = function (container) {
			return jQuery('.ecwid-title', container).text() != '';
		}

		jQuery('.ecwid-SingleProduct', this.originalContentContainer).each(function(idx, el) {
			var interval = setInterval(
					function() {
						if (singleProductLoaded(el)) {
							clearInterval(interval);
							self._readSingleProduct(el);
						}
					},
					500
			);
		});
	},

	_readSingleProduct: function(singleProductContainer) {
		var product = {
			name: jQuery('.ecwid-title', singleProductContainer).text(),
			image: jQuery('.ecwid-SingleProduct-picture img', singleProductContainer).attr('src'),
			id: jQuery(singleProductContainer).data('single-product-id'),
			link: jQuery(singleProductContainer).data('single-product-link'),
		}
		if (jQuery('.ecwid-productBrowser-price .gwt-HTML', singleProductContainer).length > 0) {
			product.price = jQuery('.ecwid-productBrowser-price .gwt-HTML', singleProductContainer).html();
		} else {
			product.price = jQuery('.ecwid-price', singleProductContainer).html();
		}
		this.addProduct(product, true);
	},

	_getProductsToShow: function() {
		return this.sort.slice(0, this.options.max);
	},

	_addToSort: function(id) {
		this.sort.push(id.toString());
	},

	_triggerError: function(message) {
		message = 'ecwid.productsList ' + message;
		if (this.options.debug) {
			debugger;
			alert(message);
		}
		console.log(message);
	},

	_destroy: function() {
		this.element.removeClass('.' + this._prefix).find('>*').remove();
		this.element.append(this.originalContentContainer.find('>*'));
		this.originalContentContainer.data('generatedProductsList', null);
		this.originalContentContainer = null;
		this._superApply(arguments);
	},

	refresh: function() {
		this._render();
	},

	addProduct: function(product, forceRender) {
		if (typeof(product.id) == 'undefined') {
			this._triggerError('addProduct error: product must have id');
		}

		if (typeof this.products[product.id] != 'undefined') {
			return;
		}

		this.products[product.id] = jQuery.extend(
				{}, {
					id: 0,
					name: 'no name',
					image: '',
					link: '',
					price: '',
					toString: function() {return this.name;}
				},
				product
		);

		this._addToSort(product.id);

		if (forceRender) {
			this._render();
		}
	},

	_onWindowResize: function() {
		if (this.element.width() < 300) {
			this.element.addClass('narrow').removeClass('wide');
		} else {
			this.element.removeClass('narrow').addClass('wide');
		}
	}
});


// Debounce function from http://unscriptable.com/2009/03/20/debouncing-javascript-methods/
var ecwid_debounce = function (func, threshold, execAsap) {

	var timeout;

	return function debounced () {
		var obj = this, args = arguments;
		function delayed () {
			if (!execAsap) {
				func.apply(obj, args);
			}
			timeout = null;
		};

		if (timeout)
			clearTimeout(timeout);
		else if (execAsap)
			func.apply(obj, args);

		timeout = setTimeout(delayed, threshold || 100);
	};

}

jQuery('.ecwid-productsList').trigger('ecwidOnWindowResize');
