<?php
if (!defined('ABSPATH')) exit;

$options        = !empty($options) && is_array($options) ? $options : [];
$selectedOption = $value;
?>
<select id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name) . (isset($multiple) && $multiple === true ? '[]' : ''); ?>" <?php echo isset($multiple) && $multiple === true ? 'multiple' : '';?>>
	<?php foreach ($options as $key => $value) : 
		$selected = is_array($selectedOption) ? in_array($key, $selectedOption) : $key == $selectedOption; ?>
		<option value="<?php echo esc_attr($key); ?>" <?php selected($selected); ?>><?php echo esc_html($value); ?></option>
	<?php endforeach; ?>
</select>