<div class="wrap fdtbwpb-wrap">
	<h1><?php esc_html_e('Settings', FDTBWPB_TEXT_DOMAIN); ?></h1>

	<form method="post" action="options.php">
		<?php
		settings_fields(FDTBWPB_SETTINGS_SLUG . '_settings_group');

		do_settings_sections(FDTBWPB_SETTINGS_SLUG . '_settings');

		submit_button();
		?>
	</form>
</div>