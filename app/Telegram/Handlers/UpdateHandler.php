<?php namespace BoilerplateTelegramPlugin\Telegram\Handlers;

use Longman\TelegramBot\Entities\Update;
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
		// add_filter('btbp_before_get_commands_list', [$this, 'edit_update_text'], 10);
		add_filter('btbp_before_execute_command', [$this, 'btbp_before_execute_command'], 10, 2);
	}

	/**
	 * Edits update text if necessary.
	 *
	 * This method will...
	 * 	- Changes update text to the proper command it it's one of the reply buttons in /start (e.g. "Contact admin" > `/contact`).
	 * 	- Replaces the underlines in the command with space (e.g. `/message_123` > `/message 123`).
	 *
	 * @param	Update			$update
	 *
	 * @return	Update|false				Return `false` if you needed to stop executing the update.
	 *
	 * @hooked	filter: `btbp_before_get_commands_list` - 10
	 */
	public function edit_update_text($update)
	{
		$methods 	= ['getMessage', 'getEditedMessage'];
		$fields 	= ['message', 'edited_message']; // Method's field name in Bot API
		$commands 	= ['message'];

		$replaces = [
			'/contact' 	=> esc_html__('✉️ Contact admin', BTBP_TEXT_DOMAIN),
			'/profile' 	=> esc_html__('👤 My profile', BTBP_TEXT_DOMAIN),
		];

		foreach ($methods as $index => $method)
		{
			$object = call_user_func(array($update, $method));
			if ($object !== null && ($text = $object->getText()))
			{
				foreach ($replaces as $replace => $search)
					if ($text === $search)
						$text = $replace;

				foreach ($commands as $command)
				{
					$command 				= "/{$command}_";
					$command_start_index 	= stripos($text, $command);
					if ($command_start_index !== false)
					{
						$underline_index 	= strlen($command) - 1;
						$text 				= substr_replace($text, ' ', $underline_index, 1);
					}
				}

				$fake_update = $update->jsonSerialize();
				$fake_update[$fields[$index]]['text'] = $text;
				$update = new Update($fake_update);

				continue; // It's impossible for an update to have multiple methods
			}
		}

		return $update;
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
