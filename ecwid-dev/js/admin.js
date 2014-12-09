
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

	var notLocked = true;
	$.fn.animateHighlight = function(highlightColor, duration) {
		var highlightBg = highlightColor || "#FFFF9C";
		var animateMs = duration || 1500;
		var originalBg = this.css("backgroundColor");
		if (notLocked) {
			notLocked = false;
			this.stop().css("background-color", highlightBg)
					.animate({backgroundColor: originalBg}, animateMs);
			setTimeout( function() { notLocked = true; }, animateMs);
		}
	};

	$('#edev-container #console').keypress(function(e) {
		if(e.which == 13) {
			jQuery.get('admin-post.php?action=edev_get_var_value&var=' + jQuery('#console').val(), {}, function(result) {
				jQuery('#console-result').prepend(result + "\n").css('outline', '3px solid yellow');
				$('#console-result').animate({outlineWidth: 0}, 500);
			} );
		}
	});

	$('#edev-container .usage-stats a').mouseout( function() {
		$('#usage-hint').remove();
	});
});


})(jQuery);
