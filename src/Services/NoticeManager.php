<?php

namespace TelegramPluginBoilerplate\Services;

class NoticeManager
{
	/**
	 * Adds a success notice message.
	 *
	 * @param	string	$message
	 *
	 * @return	void
	 */
	public static function success(string $message)
	{
		add_action('admin_notices', function() use ($message)
		{
			echo "<div class='notice notice-success'><p>{$message}</p></div>";
		});
	}
}
