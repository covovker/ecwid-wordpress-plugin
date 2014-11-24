jQuery(window).resize(function() {
	if (jQuery(this).width() < 600) {
		jQuery('.ecwid-admin').addClass('width-600');
		var head =
		jQuery('.ecwid-dashboard .box .head')
				.addClass('drop-down')
				.find('ul').removeClass('head-links');
		/*
		head.addClass('drop-down');
		head.find('h2').addClass('drop-down-head');
		head.find('ul').addClass('drop-down-content open').removeClass('head-links');*/
	} else {
		jQuery('.ecwid-admin').removeClass('width-600');

		jQuery('.ecwid-dashboard .box .head')
				.removeClass('drop-down')
				.find('ul').addClass('head-links');
		return;
		var head = jQuery('.ecwid-dashboard .box .head');
		head.removeClass('drop-down');
		head.find('h2').removeClass('drop-down-head');
		head.find('ul').removeClass('drop-down-content open').addClass('head-links');
	}
}).trigger('resize');