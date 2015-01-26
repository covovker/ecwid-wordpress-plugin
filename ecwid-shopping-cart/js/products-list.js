jQuery.widget('ecwid.productsList', {
	container: null,

	products: {
	},

	options: {
		max: 3
	},

	_prefix: 'ecwid-productsList',

	_create: function() {
		this.element.addClass(this._prefix);
		this._removeInitialContent();
		this.container = jQuery('<ul>').appendTo(this.element);
		this._setOption('debug', false);
		this._initFromHtmlData();
		this._readSingleProducts();
		this._render();
	},

	_render: function() {
		var count = 0;
		for (var id in this.products) {
			this._showProduct(this.products[id]);
			count++;
			if (count >= this.options.max) {
				break;
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
		return this.container.find(this._getProductClass(id));
	},

	_showProduct: function(product) {
		var existing = this._getProductElement(product.id);

		if (existing.length == 0) {
			this._renderProduct(product);
		}

		this._getProductElement(product.id).show();
	},

	_renderProduct: function(product) {
		var container = jQuery('<li class="' + this._getProductClass(product.id) + '">').appendTo(this.container);

		if (product.link != '') {
			container = jQuery('<a href="' + product.link + '">').appendTo(container);
		}
		if (product.image) {
			jQuery('<div class="' + this._prefix + '-image">').append('<img src="' + product.image + '">').appendTo(container);
		} else {
			jQuery('<div class="' + this._prefix + '-image ecwid-noimage">').appendTo(container);
		}
		jQuery('<div class="' + this._prefix + '-name">').append(product.name).appendTo(container);

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
		this.addProduct({
			name: jQuery('.ecwid-title', singleProductContainer).text(),
			image: jQuery('.ecwid-SingleProduct-picture img', singleProductContainer).attr('src'),
			id: jQuery(singleProductContainer).data('single-product-id')
		}, true);
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
					link: ''
				},
				product
		);

		if (forceRender) {
			this._renderProduct(this.products[product.id]);
		}
	}

});
