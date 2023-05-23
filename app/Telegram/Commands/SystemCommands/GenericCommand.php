<?php namespace BoilerplateTelegramPlugin\Telegram\Commands\SystemCommands;

use BoilerplateTelegramPlugin\Telegram\ExtendedClasses\Commands\SystemCommand;

/**
 * Generic command.
 */
class GenericCommand extends SystemCommand
{
	/**
	 * @var	string
	 */
	protected $name = 'generic';

	/**
	 * @var	string
	 */
	protected $description = 'Handles generic commands or is executed by default when a command is not found.';

	/**
	 * @var	string
	 */
	protected $version = '1.0.0';
}
