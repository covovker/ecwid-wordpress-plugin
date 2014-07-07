(function($) {

function executeWhenTrue(action_function, condition_function, delay)
{
	if (condition_function()) {
		action_function();
		return;
	}
	var interval = null;
	var condition_checker = function() {
		if (condition_function()) {
			clearInterval(interval);
			action_function();
		}
	};
	interval = setInterval(condition_checker, delay);
}

function doDefaultLayout()
{
	$('.ecwid-SearchPanel-button').text('');

	$('.ecwid-minicart-mini-rolloverContainer').show();
	$('.ecwid-shopping-cart-minicart').show();

	if ($('.ecwid-shopping-cart-minicart').length > 0 && $('.ecwid-shopping-cart-minicart').closest('.ecwid-productBrowser-auth-mini').length  == 0) {

	$('.ecwid-search-placeholder').click(function() {
		$('body').addClass('ecwid-search-open');
		$('.ecwid-shopping-cart-search .ecwid-SearchPanel-field').focus();
	});
/*
		executeWhenTrue(
				function() {
					var authTd = $('.ecwid-productBrowser-auth').closest('td');
					$('<td class="cart-cell">').append($('.ecwid-shopping-cart-minicart')).insertAfter(authTd);
					$('.ecwid-minicart-mini-rolloverContainer').show();
					$('<td class="search-cell">').append($('<div class="ecwid-search-placeholder">')).append($('.ecwid-shopping-cart-search')).insertAfter(authTd);
					authTd.get(0).width = "";
					$('.ecwid-search-placeholder').click(function() {
						$('.ecwid-shopping-cart .search-cell').addClass('search-cell-opening').find('.ecwid-SearchPanel-field').focus();
						$('.ecwid-shopping-cart .search-cell').addClass('search-cell-open');
					});
				},
				function() {
					return $('.ecwid-productBrowser-auth').text() != '';
				},
				50
		)
*/	}
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


