function ecwid_get_store_shortcode(content) {
	var found = false;
	var index = 0;
	while (found = wp.shortcode.next('ecwid', content, index)) {

		if (found && (!found.shortcode.attrs.named.widgets || found.shortcode.attrs.named.widgets.toLowerCase().indexOf('productbrowser') != -1)) {
			break;
		}
		index = found.index + 1;
		found = false;
	}

	if (typeof found == 'undefined') {
		found = false;
	}

	return found;
}

function ecwid_store_start(shortcode) {

	return '<div class="ecwid-store-wrap" contenteditable="false" data-ecwid-shortcode="' + shortcode.replace(/"/g, '&quot;') + '">';
}

function ecwid_store_end()
{
	return '</div>';
}