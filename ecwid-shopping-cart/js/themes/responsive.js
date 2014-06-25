jQuery(document).ready(function() {
	$ = jQuery;

	function doDefaultLayout()
	{
		$('.ecwid-SearchPanel-button').text('');

		$('.ecwid-shopping-cart-search').css({
			'margin-right': $('.ecwid-productBrowser-auth-mini').width() + 30,
			'display': 'inherit'
		});

		$('.ecwid-shopping-cart-minicart').css({
			'right': $('.ecwid-minicart-mini-rollover').width() - $('.ecwid-shopping-cart-minicart').width() + parseInt($('.ecwid-minicart-mini-rollover').css('padding-left')) + 1,
			'visibility': 'visible'
		});

		$('.ecwid-minicart-mini-rolloverContainer').css({
			'left': $('.ecwid-shopping-cart-minicart').offset().left,
			'top':  $('.ecwid-minicart-mini-rollover').offset().top + 5
		}).css('visibility', 'visible');
	}

	function doMobileLayout()
	{
		$('.ecwid-SearchPanel-button').text('');
	  $('.ecwid-shopping-cart-search').show();
		$('.ecwid-minicart-mini-rolloverContainer').css('visibility', 'hidden');
		$('.ecwid-shopping-cart-minicart').css('visibility', 'hidden');
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
});