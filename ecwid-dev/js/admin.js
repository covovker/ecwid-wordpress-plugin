
(function($) {

	if (location.href.indexOf("page=ecwid") == -1) return;

edev_submit = function(submitted_params) {
	var params = $.extend({
		back_url: encodeURI(location.href),
		edev_submit: 1
	}, submitted_params);
	console.log(params);

	var url = '';
	for (var i in params) {
		url += i + "=" + encodeURI(params[i]) + '&';
	}

	location.href="admin.php?" + url;
}

$(document).ready(function() {
	$('#edev-container').show();
	if ($('.update-nag').length) {
		$('#edev-container').css('top', $('.update-nag:last()').position().top + $('.update-nag:last()').outerHeight(true) + 10);
	}

	$('a.edev_set_lang').click(function() {
		edev_submit({new_lang: this.rel});
	});

	$('#edev-container .usage-stats a').mouseover(
		function() {
			debugger;
			if ($('#usage-hint').length > 0) return;
			$('<div id="usage-hint" style="border:1px solid blue;position:absolute">' + this.title + '</div>').appendTo(document.body).css(
					{
						'background': 'white',
						'top' : '' + ($(this).offset().top + 20) + 'px',
						'left': '' + ($(this).offset().left - 50) + 'px'
					}
			);
		}
	);

	$('#edev-container .usage-stats a').mouseout( function() {
		$('#usage-hint').remove();
	});
});


})(jQuery);
