<?php if (!defined('ABSPATH')) exit; ?>
<div class="wrap fdtbwpb-wrap">
	<div class="fdtbwpb-wrap__main">
		<h1><?php esc_html($title); ?></h1>

		<div class="fdtbwpb-tab-group">
			<ul class="fdtbwpb-tab">
				<?php
				foreach ($tabs as $tabSlug => $tabLabel)
				{
					$tabSlug = sanitize_key($tabSlug);
					$tabUrl  = add_query_arg([
						'settings-updated' => false,
						'tab'              => $tabSlug,
					]);

					echo '<li class="tab-' . $tabSlug . '"><a href="' . esc_url($tabUrl) . '" title="' . esc_attr($tabLabel) . '" class="' . ($activeTab == $tabSlug ? 'active' : '') . '">';
						echo $tabLabel;
					echo '</a></li>';
				} ?>
			</ul>
			<div class="fdtbwpb-tab-content <?php echo sanitize_key($activeTab); ?>-settings-tab">
				<form method="post" action="options.php">
					<table class="form-table">
						<?php
						settings_fields("{$menuSlug}_group");
						do_settings_fields($menuSlug, "{$menuSlug}_$activeTab"); ?>
					</table>

					<?php submit_button(); ?>
				</form>
			</div>
		</div>
	</div>
</div>