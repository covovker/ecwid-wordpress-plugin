(function($) {

function doDefaultLayout()
{
	$('.ecwid-shopping-cart-search .ecwid-SearchPanel-button').text('');

	$('.ecwid-minicart-mini-rolloverContainer').show();
	$('.ecwid-shopping-cart-minicart')
			.css({
					'top': '2px'
			})
			.show();

	var topElement = $('.ecwid-shopping-cart-categories');
	if (topElement.length == 0) {
		topElement = $('.ecwid-shopping-cart-product-browser')
	}
	if (topElement.length > 0) {
		$('.ecwid-productBrowser-auth-mini').css({
			'position': 'absolute',
			'top': topElement.prop('offsetTop') - 50
			//'right': '0px'
		});
		$('.ecwid-shopping-cart-search').css({
			'position': 'absolute',
			'top': topElement.prop('offsetTop') - 50 + 8
	  });
	}

	if ($('.ecwid-search-placeholder').length == 0) {
		$('.ecwid-shopping-cart .ecwid-shopping-cart-search .ecwid-SearchPanel').after('<div class="ecwid-search-placeholder"></div>');
	}

	if ($('.ecwid-shopping-cart-minicart').length > 0 && $('.ecwid-shopping-cart-minicart').closest('.ecwid-productBrowser-auth-mini').length  == 0) {

		$('.ecwid-search-placeholder').click(function() {
			$('body').addClass('ecwid-search-open');
			$('.ecwid-shopping-cart-search .ecwid-SearchPanel-field').focus();
		});
	}
}

$('body').click(function(e) {
	if ($('.ecwid-shopping-cart-search').has(e.target).length == 0) {
		$(this).removeClass('ecwid-search-open');
	}
});

function doMobileLayout()
{
	$('.ecwid-minicart-mini-rolloverContainer').hide();
	$('.ecwid-shopping-cart-minicart').hide();
	$('.ecwid-productBrowser-auth-mini').css({
		'position': 'inherit',
		'top': 'auto'
	});
}

Ecwid.OnPageLoaded.add(function(args) {
	if ($(window).width() < 650) {
		doMobileLayout();
	} else {
		doDefaultLayout();
	}
});

$(window).resize(function() {
	if ($(window).width() < 650) {
		doMobileLayout();
	} else {
		doDefaultLayout();
	}
});

})(jQuery);
/*});*/


