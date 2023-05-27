<?php namespace BoilerplateTelegramPlugin\Telegram\Handlers;

use BoilerplateTelegramPlugin\Telegram\ExtendedClasses\Telegram;

class UpdateHandler
{
	public static $instance = null;

	public static function get_instance()
	{
		null === self::$instance && self::$instance = new self;
		return self::$instance;
	}

	public function __construct()
	{
		add_filter('btbp_before_execute_command', [$this, 'btbp_before_execute_command'], 10, 2);
	}

	/**
	 * Handles the incoming Telegram update before executing the command.
	 *
	 * @param	bool		$should_execute_command
	 * @param	Telegram	$telegram
	 *
	 * @return	bool
	 *
	 * @hooked	filter: `btbp_before_execute_command` - 10
	 */
	public function btbp_before_execute_command($should_execute_command, $telegram)
	{
		if ($telegram->isAdmin()) return true;

		return $should_execute_command;
	}
}
