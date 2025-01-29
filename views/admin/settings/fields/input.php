<?php
$valueAttribute = $type === 'number' ? intval($value) : esc_attr($value);
$valueAttribute = $type === 'checkbox' ? '1' : $valueAttribute;
?>
<input type="<?php echo esc_attr($type); ?>" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo $valueAttribute; ?>" <?php echo $type === 'checkbox' ? checked($value, true, false) : ''; ?> />
<?php if (!empty($description)) : ?>
	<p><?php echo $description; ?></p>
<?php endif; ?>