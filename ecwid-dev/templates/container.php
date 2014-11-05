<div id="edev-container" style="display: none; position: absolute; right: 10px; top: 40px; border: 1px solid red; padding: 3px">
	Voting message:
	<?php if (!get_option('ecwid_show_vote_message')): ?>
		<b>Disabled</b>
		<a href="javascript: edev_submit({new_vote:'Y'})">Enable</a>
	<?php else: ?>
		<b>Enabled</b>
	<a href="javascript: edev_submit({new_vote:'N'})">Disable</a>
	<?php endif; ?>
	<br />

	<span style="width:150px">Install date:</span>
		<input
			type="text"
			name="new_date"
			id="edev_new_date"
			value="<?php echo strftime("%d %b %G %H:%M:%S", get_option('ecwid_installation_date')); ?>"
		/>
		<a href="javascript: edev_submit({new_date: jQuery('#edev_new_date').val()})">Update</a>
	<br />

	<span style="width:150px">Stats sent date:</span>
	<input
		type="text"
		name="new_stats_date"
		id="edev_stats_date"
		value="<?php echo strftime("%d %b %G %H:%M:%S", get_option('ecwid_stats_sent_date')); ?>"
		/>
	<a href="javascript: edev_submit({new_stats_date: jQuery('#edev_stats_date').val()})">Update</a>
	<br />

	<?php if (!is_writable(ABSPATH . '/wp-config.php')): ?>
		<?php echo 'Config not writeable!'; ?>
	<?php else: ?>
		Set locale to:
		<?php foreach (get_locales() as $locale): ?>
		<a class="edev_set_lang" href="javascript:void();" rel="<?= $locale ?>"><?= $locale ?></a>
		<?php endforeach; ?>
	<?php endif; ?>
    <br />
    <a href="javascript: edev_submit({mode: 'reset_messages'});">Reset messages</a> 
</div>
