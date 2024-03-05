<textarea id="<?php echo $id; ?>" name="<?php echo $name; ?>" cols="40"><?php echo $value; ?></textarea>
<?php if (!empty($description)) : ?>
	<p><?php echo $description; ?></p>
<?php endif; ?>