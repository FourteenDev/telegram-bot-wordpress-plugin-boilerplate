<input class="regular-text<?php echo !empty($class) ? ' ' . $class : ''; ?>" type="text" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php echo $default; ?>" <?php echo $readonly ? 'readonly' : ''; ?>>
<?php if (!empty($description)) : ?>
	<p><?php echo $description; ?></p>
<?php endif; ?>