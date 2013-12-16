<div class="wrap pure-form ecwid-settings general-settings">
	<h2><?php _e('Ecwid Shopping Cart - General settings', 'ecwid-shopping-cart'); ?></h2>


	<form method="POST" action="options.php" name="settings">
		<?php settings_fields('ecwid_options_page'); ?>
		<fieldset>

			<input type="hidden" name="settings_section" value="general" />

			<div class="greeting-box complete">
				<div class="image-container">
					<img class="greeting-image" src="<?php echo(esc_attr(ECWID_PLUGIN_URL)); ?>/images/store_ready.png" width="142" />
				</div>

				<div class="messages-container">
					<?php if ($_GET['settings-updated']): ?>

					<div class="main-message"><?php _e('Congratulations!', 'ecwid-shopping-cart'); ?></div>
					<div class="secondary-message"?><?php _e('Your Ecwid store is now connected to your WordPress website.', 'ecwid-shopping-cart'); ?></div>

					<?php else: ?>

					<div class="main-message"><?php _e('Greetings!', 'ecwid-shopping-cart'); ?></div>
					<div class="secondary-message"?><?php _e('Your Ecwid store is connected to your WordPress website.', 'ecwid-shopping-cart'); ?></div>
					<?php endif; ?>
				</div>
			</div>
			<hr />
			<div class="section">
				<div class="left">
					<span class="main-info">
						<?php _e('Store ID', 'ecwid-shopping-cart'); ?>: <strong><? echo esc_attr(get_ecwid_store_id()); ?></strong>
					</span>
				</div>
				<div class="right"">
					<a class="pure-button" target="_blank" href="https://my.ecwid.com/cp/?source=wporg#t1=&t2=Dashboard">
						<?php _e('Control panel', 'ecwid-shopping-cart'); ?>
					</a>
					<a class="pure-button" target="_blank" href="<?php echo esc_attr(get_page_link(get_option('ecwid_store_page_id'))); ?>">
						<?php _e('Visit storefront', 'ecwid-shopping-cart'); ?>
					</a>
				</div>
			</div>

			<div class="section">
				<div class="left">
					<span class="main-info">
						<?php _e('Account status', 'ecwid-shopping-cart'); ?>:
						<strong>
							<?php
							if (ecwid_is_paid_account()) {
								_e('Paid', 'ecwid-shopping-cart');
							} else {
								_e('Free', 'ecwid-shopping-cart');
							}
							?>
						</strong>
					</span>
					<div class="secondary-info">
						<?php
						if (ecwid_is_paid_account())
							_e('Thank you for supporting Ecwid!', 'ecwid-shopping-cart');
						else
							_e('Upgrade to get access to cool premium features.', 'ecwid-shopping-cart');
						?>
					</div>
				</div>

				<div class="right">
					<?php if (ecwid_is_api_enabled()): ?>
						<a class="pure-button" target="_blank" href="https://my.ecwid.com/cp/CP.html#profile=Billing&t2=My_Profile">
							<?php _e('Billing and plans', 'ecwid-shopping-cart'); ?>
						</a>
					<?php else: ?>
						<a class="<?php echo ECWID_MAIN_BUTTON_CLASS; ?>" target="_blank" href="http://www.ecwid.com/plans-and-pricing.html">
							<?php _e('Upgrade', 'ecwid-shopping-cart'); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>

			<div class="note grayed-links">
				<?php
					echo sprintf(
						__('If you want to connect another Ecwid store, you can <a %s>disconnect the current one and change Store ID</a>.', 'ecwid-shopping-cart'),
						'href="#" onClick="javascript:document.forms[\'settings\'].submit(); return false;"'
					);
				?>

			</div>

			<hr />
			<p><?php _e('Questions? Visit <a href="http://help.ecwid.com/?source=wporg">Ecwid support center</a>.', 'ecwid-shopping-cart'); ?></p>
		</fieldset>
	</form>
</div>