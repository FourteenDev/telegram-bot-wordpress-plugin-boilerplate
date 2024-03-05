<textarea class="regular-textarea<?php echo !empty($class) ? ' ' . $class : ''; ?>" name="<?php echo $name; ?>" id="<?php echo $id; ?>" <?php echo $readonly ? 'readonly' : ''; ?> cols="40"><?php echo $default; ?></textarea>
<?php if (!empty($description)) : ?>
	<p><?php echo $description; ?></p>
<?php endif; ?>