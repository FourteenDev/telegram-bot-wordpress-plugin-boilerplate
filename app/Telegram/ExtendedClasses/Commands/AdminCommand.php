<?php

namespace TelegramPluginBoilerplate\Telegram\ExtendedClasses\Commands;

abstract class AdminCommand extends Command
{
	/**
	 * @var	bool
	 */
	protected $private_only = true;
}
