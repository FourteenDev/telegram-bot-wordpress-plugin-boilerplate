<?php

namespace TelegramPluginBoilerplate\Telegram\Handlers;

use Longman\TelegramBot\Entities\Update;
use TelegramPluginBoilerplate\Telegram\ExtendedClasses\Telegram;

class UpdateHandler
{
	public static $instance = null;

	public static function getInstance()
	{
		self::$instance === null && self::$instance = new self;
		return self::$instance;
	}

	public function __construct()
	{
		// add_filter('fdtbwpb_before_get_commands_list', [$this, 'editUpdateText'], 10);
		add_filter('fdtbwpb_before_execute_command', [$this, 'handleUpdateBeforeCommandExecute'], 10, 2);
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
	 * @hooked	filter: `fdtbwpb_before_get_commands_list` - 10
	 */
	public function editUpdateText($update)
	{
		$methods  = ['getMessage', 'getEditedMessage'];
		$fields   = ['message', 'edited_message']; // Method's field name in Bot API
		$commands = ['message'];

		$replaces = [
			'/contact' => esc_html__('✉️ Contact admin', FDTBWPB_TEXT_DOMAIN),
			'/profile' => esc_html__('👤 My profile', FDTBWPB_TEXT_DOMAIN),
		];

		foreach ($methods as $index => $method)
		{
			$object = call_user_func([$update, $method]);
			if ($object !== null && ($text = $object->getText()))
			{
				foreach ($replaces as $replace => $search)
					if ($text === $search)
						$text = $replace;

				foreach ($commands as $command)
				{
					$command           = "/{$command}_";
					$commandStartIndex = stripos($text, $command);
					if ($commandStartIndex !== false)
					{
						$underlineIndex = strlen($command) - 1;
						$text           = substr_replace($text, ' ', $underlineIndex, 1);
					}
				}

				$fakeUpdate = $update->jsonSerialize();
				$fakeUpdate[$fields[$index]]['text'] = $text;
				$update = new Update($fakeUpdate);

				continue; // It's impossible for an update to have multiple methods
			}
		}

		return $update;
	}

	/**
	 * Handles the incoming Telegram update before executing the command.
	 *
	 * @param	bool		$shouldExecuteCommand
	 * @param	Telegram	$telegram
	 *
	 * @return	bool
	 *
	 * @hooked	filter: `fdtbwpb_before_execute_command` - 10
	 */
	public function handleUpdateBeforeCommandExecute($shouldExecuteCommand, $telegram)
	{
		if ($telegram->isAdmin()) return true; // Example

		return $shouldExecuteCommand;
	}
}
