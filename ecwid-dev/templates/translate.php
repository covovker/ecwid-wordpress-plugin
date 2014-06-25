<table>
	<tr>
		<th width="20"></th>
		<th>Current</th>
		<?php foreach ($translations as $locale => $items): ?>
		<th><?= $locale ?></th>
		<?php endforeach; ?>
	</tr>
<?php foreach ($labels as $label => $domain): ?>
	<tr>
		<td class="delete"></td>
		<td class="base-text">
			<textarea><?= trim($label, "\"'") ?></textarea>
		</td>

		<?php foreach ($translations as $locale => $items): ?>
		<td class="translation">
			<textarea name="labels[<?= htmlentities($label) ?>][<?= $domain ?>]"><?php if ($items[$label]) echo htmlentities($items[$label]); ?></textarea>
		</td>
		<?php endforeach; ?>
	</tr>
<?php endforeach; ?>

	<tr style="background: #FFCCCC">
		<td colspan="<?= sizeof($translations) + 2 ?>" align="center">
			The ones below are not present in the code itself but found in the translations
		</td>
	</tr>
<?php

$processed_labels = $labels;

?>
<?php foreach ($translations as $items): ?>
	<?php foreach ($items as $orig_label => $translated_label): ?>
	<?php if (!array_key_exists($orig_label, $processed_labels)): ?>
	<tr>
		<td><input type="checkbox" name="to_delete[<?= $label ?>]" /></td>
		<td><textarea><?php $processed_labels[$orig_label] = true; echo $orig_label; ?></textarea></td>
		<?php foreach ($translations as $locale => $other_items): ?>
		<td>
			<?php if (array_key_exists($orig_label, $other_items)): ?>
			<textarea><?= $other_items[$orig_label]; ?></textarea>
			<?php endif; ?>
		</td>
		<?php endforeach; ?>
	</tr>
	<?php endif; ?>
<?php endforeach; ?>
<?php endforeach; ?>

</table>