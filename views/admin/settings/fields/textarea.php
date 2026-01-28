<?php if (!defined('ABSPATH')) exit; ?>
<textarea id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" cols="50" rows="10" <?php echo !empty($placeholder) ? 'placeholder="' . esc_attr($placeholder) . '"' : ''; ?>><?php echo esc_html($value); ?></textarea>
<?php if (!empty($description)) : ?>
	<p><?php echo $description; ?></p>
<?php endif; ?>