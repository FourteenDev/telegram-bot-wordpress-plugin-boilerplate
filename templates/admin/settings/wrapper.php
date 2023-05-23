<div class="wrap btbp-wrap">
	<h1><?php esc_html_e('Settings', BTBP_TEXT_DOMAIN); ?></h1>

	<form method="post" action="options.php">
		<?php
		settings_fields('btbp_settings_group');

		do_settings_sections('btbp_settings_page');

		submit_button();
		?>
	</form>
</div>