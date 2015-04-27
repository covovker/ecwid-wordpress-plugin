<div class="wrap ecwid-admin ecwid-connect">
	<div class="box">
		<div class="head"><?php ecwid_embed_svg('ecwid_logo_symbol_RGB');?>
			<h3>
				<?php _e( 'Ecwid Shopping Cart', 'ecwid-shopping-cart' ); ?>
			</h3>
		</div>
		<div class="greeting-image">
			<img src="<?php echo(esc_attr(ECWID_PLUGIN_URL)); ?>/images/store_inprogress.png" width="142" />
		</div>

		<div class="greeting-message mobile-br">
			<?php _e( 'Connect your store<br /> to this WordPress site', 'ecwid-shopping-cart' ); ?>
		</div>

		<div class="connect-button">
			<a href="<?php echo esc_attr($ecwid_oauth->get_auth_dialog_url()); ?>"><?php _e( 'Connect Ecwid store', 'ecwid-shopping-cart' ); ?></a>
		</div>

		<?php if (!$connection_error): ?>

		<div class="note initial">
			<?php _e( 'After clicking button you need to login and accept permissions to use our plugin', 'ecwid-shopping-cart' ); ?>
		</div>

		<?php else: ?>

		<div class="note auth-error">
			<span>
				<?php _e( 'Connection error - after clicking button you need to login and accept permissions to use our plugin. Please, try again.', 'ecwid-shopping-cart' ); ?>
			</span>
		</div>

		<?php endif; ?>

		<div class="create-account-link">
			<a target="_blank" href="https://my.ecwid.com/cp/?source=wporg&partner=ecwidc26#register">
				<?php _e( "Don't have Ecwid account? Create it here", 'ecwid-shopping-cart' ); ?>
			</a>
		</div>
	</div>
	<p><?php echo sprintf(__('Questions? Visit <a %s>Ecwid support center</a>', 'ecwid-shopping-cart'), 'target="_blank" href="http://help.ecwid.com/?source=wporg"'); ?></p>
</div>
