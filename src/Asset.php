<?php

namespace TelegramPluginBoilerplate;

class Asset
{
	public static $instance = null;

	public static function getInstance()
	{
		self::$instance === null && self::$instance = new self;
		return self::$instance;
	}

	public function __construct()
	{
		add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
	}

	/**
	 * Enqueues admin styles and scripts.
	 *
	 * @param	string	$hookSuffix		Current admin page.
	 *
	 * @return	void
	 *
	 * @hooked	action: `admin_enqueue_scripts` - 10
	 */
	public function enqueueAdminScripts($hookSuffix)
	{
		wp_enqueue_style('fdtbwpb_admin', FDTBWPB()->url('assets/admin/css/admin.css'), [], FDTBWPB_VERSION);

		wp_enqueue_script('fdtbwpb_admin', FDTBWPB()->url('assets/admin/js/admin.js'), [], FDTBWPB_VERSION, true);
	}
}
