// Wrap an HTMLElement around each element in an HTMLElement array.
HTMLElement.prototype.wrap = function(elms) {
    if (!elms.length) elms = [elms];
    for (var i = elms.length - 1; i >= 0; i--) {
        var child = (i > 0) ? this.cloneNode(true) : this;
        var el    = elms[i];
        var parent  = el.parentNode;
        var sibling = el.nextSibling;
        child.appendChild(el);
        if (sibling) {
            parent.insertBefore(child, sibling);
        } else {
            parent.appendChild(child);
        }
    }
}

// Get css property
function getStyle(el, styleProp) {
	if (el.currentStyle)
		var prop = el.currentStyle[styleProp];
	else if (window.getComputedStyle)
		var prop = document.defaultView.getComputedStyle(el, null).getPropertyValue(styleProp);
	return prop;
}

// Add css to inherit parent styles
Ecwid.OnPageLoad.add(function(page) {
	jQuery('.ecwid-productBrowser').attr('id', 'ProductBrowser-1');
	var widget_ids = ['ProductBrowser-1', 'Categories-1', 'VCategories-1', 'SearchPanel-1', 'Minicart-1'];
	var parent = document.getElementById('ProductBrowser-1').parentNode;
	var color  = getStyle(parent, 'color');
	var bgColor = getStyle(parent, 'background-color');
	var borderColor = color.replace('rgb', 'rgba').replace(')', ', 0.5)');
    var borderColorCat = color.replace('rgb', 'rgba').replace(')', ', 0.1)');
	var css = '#ProductBrowser-1, #Categories-1, #VCategories-1, #SearchPanel-1 { color: '+ color +' !important; background-color: '+ bgColor +'; border-color:'+ borderColor + '; }\n' + '#ProductBrowser-1 table, #Categories-1 table, #VCategories-1 table, #SearchPanel-1 .ecwid-SearchPanel { border-color: '+ borderColor +' !important; }' + '\n' + 'html#ecwid_html div#VCategories-1-Wrap div#VCategories-1 .ecwid-categories-vertical-table-cell-selected .ecwid-categories-vertical-table-cell-categoryLink, html#ecwid_html div#VCategories-1-Wrap div#VCategories-1 .gwt-MenuItem-current span.ecwid-categories-category { background-color: '+ borderColorCat +'; }\n';

	head = document.getElementsByTagName('head')[0],
	style = document.createElement('style');
	style.type = 'text/css';
	if (style.styleSheet) {
		style.styleSheet.cssText = css;
	} else {
		style.appendChild(document.createTextNode(css));
	}
	head.appendChild(style);

	for (var i in widget_ids) {
		if (document.getElementById(widget_ids[i]) != null) {
    		var w = document.createElement('div');
    		w.id = widget_ids[i] + '-Wrap';
    		w.wrap(document.getElementById(widget_ids[i]));
		}
	}
});
