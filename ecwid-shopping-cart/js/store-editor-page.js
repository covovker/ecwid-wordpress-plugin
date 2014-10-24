jQuery(document).ready(function() {
	$ = jQuery;

	$popup = $('#ecwid-store-popup-content');

	/*
	 * Media buttons handlers
	 */
	$('#update-ecwid-button,#insert-ecwid-button').click(ecwid_open_store_popup);

	/*
	 * Close button handler
	 */
	$('.media-modal-close', $popup).click(function() {
		$popup.removeClass('open');
		return false;
	});


	/**
	 * Builds params object from the wp.shortcode
	 *
	 * @param shortcode
	 * @returns {*}
	 */
	buildParams = function(shortcode) {
		if (!shortcode) return {};

		var attributes = $.extend({}, shortcode.shortcode.attrs.named);

		if ($.inArray(attributes.category_view, ['grid', 'list', 'table']) == -1) {
			attributes.category_view = undefined;
		}

		if (!$.inArray(attributes.search_view, ['grid', 'list', 'table']) == -1) {
			attributes.search_view = undefined;
		}

		if (!attributes.grid || attributes.grid.match(/^\d+,\d+$/) === null) {
			attributes.grid = '3,3';
		}

		var grid = attributes.grid.match(/^(\d+),(\d+)$/);
		attributes.grid_rows = grid[1];
		attributes.grid_columns = grid[2];

		for (var i in {'categories_per_row': 3, 'list': 10, 'table': 20, 'grid_rows': 3, 'grid_columns': 3, 'default_category_id': 0}) {
			parsed = parseInt(attributes[i]);
			if (isNaN(parsed) || parsed < 0) {
				attributes[i] = undefined;
			}
		}

		var widgets = attributes.widgets.split(/[^a-z^A-Z^0-9^-^_]/);

		return {
			'show_search': $.inArray('search', widgets) != -1,
			'show_categories': $.inArray('categories', widgets) != -1,
			'show_minicart': $.inArray('minicart', widgets) != -1,
			'categories_per_row': attributes.categories_per_row,
			'category_view': attributes.category_view,
			'search_view': attributes.search_view,
			'list_rows': attributes.list,
			'table_rows': attributes.table,
			'grid_rows': grid[1],
			'grid_columns': grid[2],
			'default_category_id': attributes.default_category_id
		};

	}


	/*
	 * Returns default parameters object
	 */
	getDefaultParams = function() {
		return {
			'show_search': false,
			'show_minicart': true,
			'show_categories': true,
			'categories_per_row': 3,
			'grid_rows': 3,
			'grid_columns': 3,
			'table_rows': 20,
			'list_rows': 10,
			'default_category_id': 0,
			'category_view': 'grid',
			'search_view': 'list'
		}
	}

	/*
	 * Tests whether there is a valid store shortcode
	 */
	checkEcwid = function() {

		var hasEcwid = false;
		if (tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()) {
			content = tinyMCE.activeEditor.getBody();

			hasEcwid = jQuery(content).find('.ecwid-store-editor').length > 0;
		} else {
			hasEcwid = ecwid_get_store_shortcode(jQuery('#content').html());
		}

		if (hasEcwid) {
			$('.wp-media-buttons').addClass('has-ecwid');
		} else {
			$('.wp-media-buttons').removeClass('has-ecwid');
		}
	}

	setInterval(checkEcwid, 800);

	/*
	 * Handles media modal menus
	 */
	$('.media-menu-item', $popup).click(function() {
		$('.media-menu .media-menu-item', $popup).removeClass('active');
		$(this).addClass('active');

		$('.media-modal-content', $popup).attr('data-active-dialog', $(this).attr('data-content'));

		return false;
	});

	/*
	 * Main button click
	 */
	$('.button-primary', $popup).click(function() {

		var result = {}, defaults = getDefaultParams();

		result.widgets = 'productbrowser';
		for (var i in {search:1, categories:1, minicart:1}) {
			if ($('input[name=show_' + i + ']').prop('checked')) {
				result.widgets += ' ' + i;
			}
		}

		getNumber = function(name, fallback) {
			var value = parseInt($('[name=' + name + ']', $popup).val());

			if (isNaN(value) || value < 0) {
				value = fallback;
			}

			return value;
		}

		getString = function(name, values, fallback) {
			var value = $('[name=' + name + ']', $popup).val();

			if ($.inArray(value, values) == -1) {
				value = fallback;
			}

			return value;
		}

		result.categories_per_row = getNumber('categories_per_row', defaults.categories_per_row);
		result.grid = getNumber('grid_rows', defaults.grid_rows) + ',' + getNumber('grid_columns', defaults.grid_columns);
		result.list = getNumber('list_rows', defaults.list_rows);
		result.table = getNumber('table_rows', defaults.table_rows);
		result.default_category_id = getNumber('default_category_id', defaults.default_category_id);
		result.category_view = getString('category_view', ['list', 'grid', 'table'], defaults.category_view);
		result.search_view = getString('search_view', ['list', 'grid', 'table'], defaults.search_view);


		var existingShortcode = ecwid_get_store_shortcode(jQuery('#content').val());
		var shortcode = {};
		if (!existingShortcode) {
			shortcode.shortcode = new wp.shortcode();
			shortcode.shortcode.tag = 'ecwid';
			shortcode.shortcode.type = 'single';
		} else {
			shortcode = existingShortcode;
		}

		for (var i in result) {
			shortcode.shortcode.attrs.named[i] = result[i];
		}

		if (existingShortcode) {
			$('#content').val(
				$('#content').val().replace(existingShortcode.content, shortcode.shortcode.string())
			);
			$(tinymce.activeEditor.getBody()).find('.ecwid-store-wrap').attr('data-ecwid-shortcode', shortcode.shortcode.string());
		} else if (tinymce.activeEditor) {
			tinymce.activeEditor.execCommand('mceInsertContent', false, shortcode.shortcode.string());
		}

		$('#ecwid-store-popup-content').removeClass('open');
	});

	updatePreview = function() {
		$('.store-settings input[type=checkbox]', $popup).each(function(idx, el) {
			var widget = $(el).parent().attr('data-ecwid-widget');
			var preview = $('.store-settings-preview svg path.' + widget, $popup);
			if ($(el).prop('checked')) {
				$('.store-settings-wrapper').addClass('ecwid-' + widget);
			} else {
				$('.store-settings-wrapper').removeClass('ecwid-' + widget);
			}
		});
	}

	$('.store-settings-wrapper label', $popup).hover(
		function() {
			$('.store-settings-wrapper').attr('data-ecwid-widget-hover', $(this).attr('data-ecwid-widget'));
		},
		function() {
			$('.store-settings-wrapper').attr('data-ecwid-widget-hover', '');
		}
	);

	$('.store-settings input[type=checkbox]', $popup).change(updatePreview);
});

ecwid_open_store_popup = function() {

	tinyMCE.activeEditor.save();

	var shortcode = ecwid_get_store_shortcode(
		tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()
		? $(tinyMCE.activeEditor.getBody()).find('.ecwid-store-wrap').attr('data-ecwid-shortcode')
		: jQuery('#content').val()
	);

	$popup.addClass('open');

	params = {};
	$.extend(params, getDefaultParams(), buildParams(shortcode));

	for (var i in params) {
		var el = $('[name=' + i + ']', $popup);
		if (el.attr('type') == 'checkbox') {
			el.prop('checked', params[i]);
		} else {
			el.val(params[i]);
		}
	}

	// mode determines whether it is a new store or not, and active dialog is the current tab
	// in other words, mode = [add-store,store-settings] and active dialog is [add-store|store-settings, appearance]
	// buttons and menu items are for mode, current title and content are for dialog
	var current = !shortcode ? 'add-store' : 'store-settings';
	$('.media-modal-content', $popup).attr('data-mode', current);
	$('.media-modal-content', $popup).attr('data-active-dialog', current);
	$('.media-menu-item')
			.removeClass('active')
			.filter('[data-content=' + current + ']').addClass('active');


	updatePreview();

	return false;
};
