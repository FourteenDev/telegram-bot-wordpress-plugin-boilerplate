<?php

namespace TelegramPluginBoilerplate\Telegram\Commands\UserCommands;

use Longman\TelegramBot\Entities\ServerResponse;
use TelegramPluginBoilerplate\Telegram\ExtendedClasses\Commands\UserCommand;
use TelegramPluginBoilerplate\Telegram\Handlers\CallbackQueryHandler;

/**
 * Callback Query command.
 */
class CallbackQueryCommand extends UserCommand
{
	/**
	 * @var	string
	 */
	protected $name = 'callback_query';

	/**
	 * @var	string
	 */
	protected $description = 'Handles incoming callback queries.';

	/**
	 * @var	string
	 */
	protected $usage = '';

	/**
	 * @var	string
	 */
	protected $version = '1.0.0';

	/**
	 * Command execute method.
	 *
	 * @return	ServerResponse
	 */
	public function execute(): ServerResponse
	{
		return CallbackQueryHandler::handleCallbackQuery($this->telegram, $this->update);
	}
}
