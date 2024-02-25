<?php namespace TelegramPluginBoilerplate\Telegram\Commands\UserCommands;

use TelegramPluginBoilerplate\Telegram\ExtendedClasses\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use TelegramPluginBoilerplate\Telegram\ExtendedClasses\Request;

/**
 * Generic command.
 */
class GenericCommand extends UserCommand
{
	/**
	 * @var	string
	 */
	protected $name = 'generic';

	/**
	 * @var	string
	 */
	protected $description = 'This will be called when the user message is not a valid command (e.g. regular messages).';

	/**
	 * @var	string
	 */
	protected $usage = '/generic';

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
		$message = $this->getMessage();
		if (!$message) return Request::emptyResponse(); // TODO: Handle edited messages too

		return Request::emptyResponse();
	}
}
