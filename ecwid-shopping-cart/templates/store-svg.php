<?php
	echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
  echo '<?xml-stylesheet type="text/css" href="http://fonts.googleapis.com/css?family=Open+Sans%3A300italic%2C400italic%2C600italic%2C300%2C400%2C600&subset=latin%2Clatin-ext&ver=3.9.2" ?>';
  echo '<?xml-stylesheet type="text/css" href="http://localhost/2.css" ?>';
?><!-- Generator: Adobe Illustrator 18.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
		 viewBox="0 0 560 181" enable-background="new 0 0 560 181" xml:space="preserve">
<g>
	<path fill="#77B644" d="M242.1,72.2c0-0.8,0.6-1.4,1.4-1.4h74.3c0.8,0,1.4,0.6,1.4,1.4V75c0,0.8-0.6,1.4-1.4,1.4h-74.3
		c-0.8,0-1.4-0.6-1.4-1.4V72.2z"/>
	<path fill="#77B644" d="M319.4,25.6c-3-6-4.3-11.8-6-21.5c0-0.5-1.3-2.4-3-2.4h-59.7c-1.6,0-3,1.9-3,2.4c0,0-3,18.5-6,21.5
		c0,3,0,9,0,9s0,0.1,0.1,0.2h-0.1c0,0,0.3,1.5,2.1,2.3c0.3,0.2,0.5,0.3,0.9,0.4v0.1v27c0,1.6,1.7,2.7,3.3,2.7h23.5V42.7
		c0-1.6,1.6-3,3.2-3h11.9c1.6,0,3,1.3,3,3v24.7h23.8c1.6,0,3-1.1,3-2.7v-27v-0.1c2.4-0.4,3-2.7,3-2.7h-0.1c0-0.1,0.1-0.2,0.1-0.2
		S319.4,28.6,319.4,25.6z M265.2,55.5c0,1.6-0.7,3-2.4,3h-6.2c-1.6,0-2.4-1.3-2.4-3V43.6c0-1.6,0.8-3,2.4-3h6.2c1.6,0,2.4,1.3,2.4,3
		V55.5z M306.9,55.5c0,1.6-0.7,3-2.3,3h-6.1c-1.6,0-2.4-1.3-2.4-3V43.6c0-1.6,0.8-3,2.4-3h6.1c1.6,0,2.3,1.3,2.3,3V55.5z M307.4,7.2
		c0,0,3.2,15.2,5.1,18.4c-3,0-8.1,0-8.1,0l-3-18.4H307.4z M312.8,28.6l0,2.3c-0.3,2.1-2,3.6-4.2,3.6c-2.3,0-4.2-1.4-4.2-3.7
		c0-0.1,0.1-2.2,0.1-2.2H312.8z M290.3,30.2l0-1.6h8.5l0,2.4c-0.3,2-2,3.6-4.2,3.6c-2.3,0-4.2-1.9-4.2-4.2
		C290.4,30.3,290.4,30.2,290.3,30.2L290.3,30.2z M295.4,7.2c0,0,2.2,14.8,3.2,18.4c0.9,0-8.3,0-8.3,0l-0.9-18.4H295.4z M277.5,7.2
		h6.1l1.1,18.4h-8.1L277.5,7.2z M276.4,28.6h8.3l0.1,2.1c-0.2,2.2-2,3.9-4.2,3.9c-2.2,0-4-1.7-4.2-3.9L276.4,28.6z M265.6,7.2h6
		l-0.9,18.4c0,0-5.8,0-8,0C263.6,22.3,265.6,7.2,265.6,7.2z M262.5,28.6h8.3l0.1,1.6h-0.1c0,0.1,0,0.1,0,0.2c0,2.3-1.9,4.2-4.2,4.2
		c-2.1,0-3.9-1.6-4.2-3.6L262.5,28.6z M253.8,7.2h5.8l-2.5,18.4c0,0-5.9,0-8.1,0C251.3,21.5,253.8,7.2,253.8,7.2z M248.4,28.6h8.3
		c0,0,0,1.7,0,1.7c0,2.3-1.9,4.2-4.2,4.2c-2.1,0-3.8-1.5-4.1-3.5L248.4,28.6z"/>
</g>
	<text x="280" y="116" text-anchor="middle" fill="#050303" font-family="Open Sans,Helvetica Neue,sans-serif" font-size="20"><?php _e('Your store will be shown here!', 'ecwid-shopping-cart'); ?></text>
	<text x="280" y="145" text-anchor="middle" fill="#999999" font-family="Open Sans,Helvetica Neue,sans-serif" font-size="14">
		<?php if (get_ecwid_store_id() == ECWID_DEMO_STORE_ID): ?>
			<?php _e('Demo Store', 'ecwid-shopping-cart'); ?>
		<?php else: ?>
			<?php _e('Store ID', 'ecwid-shopping-cart'); ?>: <?php echo esc_attr(get_option('ecwid_store_id')); ?>
		<?php endif; ?>
	</text>
</svg>