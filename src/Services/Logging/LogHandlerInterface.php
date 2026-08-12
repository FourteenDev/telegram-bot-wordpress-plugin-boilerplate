<?php

namespace TelegramPluginBoilerplate\Services\Logging;

interface LogHandlerInterface
{
	/**
	 * Handles a log record.
	 *
	 * @param	array<string, mixed>	$record	Log record containing message, level, context, etc.
	 *
	 * @return	void
	 */
	public function handle(array $record): void;

	/**
	 * Checks if this handler is enabled and can handle the given record.
	 *
	 * @param	array<string, mixed>	$record	Log record.
	 *
	 * @return	bool
	 */
	public function isEnabled(array $record): bool;
}
