<div class="wrap ecwid-admin ecwid-connect<?php if ($no_oauth): ?> no-oauth<?php else: ?> with-oauth<?php endif; ?>">
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

		<div class="connect-store-id no-oauth">
			<input type="text" id="ecwid-store-id" placeholder="<?php _e('Enter your Store ID', 'ecwid-shopping-cart'); ?>" />
		</div>
		<div class="connect-button">
			<a href="admin-post.php?action=ecwid_connect&reconnect<?php if($scopes): ?>&scopes=<?php echo urlencode($scopes); ?><?php endif; ?><?php if($returnUrl): ?>&returnUrl=<?php echo urlencode($returnUrl); ?><?php endif; ?>" class="with-oauth"><?php _e( 'Connect Ecwid store', 'ecwid-shopping-cart' ); ?></a>
			<a id="ecwid-connect-no-oauth" href="admin-post.php?action=ecwid_connect" class="no-oauth"><?php _e( 'Save and connect', 'ecwid-shopping-cart' ); ?></a>
		</div>

		<?php if (!$connection_error): ?>

			<?php if (isset($reconnect_message)): ?>
			<div class="note initial with-oauth">
				<?php echo $reconnect_message; ?>
			</div>
			<?php endif; ?>

		<?php else: ?>

			<div class="note auth-error">
				<span>
					<?php _e( 'Looks like your site does not support remote POST requests that are required for Ecwid API to work. Please, contact your hosting provider to enable cURL.', 'ecwid-shopping-cart' ); ?>
				</span>
			</div>

		<?php endif; ?>
	</div>
	<p><?php echo sprintf(__('Questions? Visit <a %s>Ecwid support center</a>', 'ecwid-shopping-cart'), 'target="_blank" href="http://help.ecwid.com/?source=wporg"'); ?></p>
</div>
