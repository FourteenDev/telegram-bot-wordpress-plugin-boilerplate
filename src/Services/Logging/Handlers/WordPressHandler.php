<?php

namespace TelegramPluginBoilerplate\Services\Logging\Handlers;

use TelegramPluginBoilerplate\Services\Logging\LogHandlerInterface;

/**
 * Integrates with WordPress's built-in debug.log functionality.
 */
class WordPressHandler implements LogHandlerInterface
{
	/**
	 * Handles a log record.
	 *
	 * @param	array<string, mixed>	$record
	 *
	 * @return	void
	 */
	public function handle(array $record): void
	{
		if (!$this->isEnabled($record)) return;

		if (function_exists('error_log'))
			error_log($this->formatRecord($record));
	}

	/**
	 * Checks if this handler is enabled and can handle the given record.
	 *
	 * @param	array<string, mixed>	$record	Log record.
	 *
	 * @return	bool
	 */
	public function isEnabled(array $record): bool
	{
		return isset($record['level']) && isset($record['message']);
	}

	/**
	 * Formats a log record for WordPress debug log.
	 *
	 * @param	array<string, mixed>	$record
	 *
	 * @return	string
	 */
	private function formatRecord(array $record): string
	{
		// $datetime = $record['datetime'] instanceof \DateTimeInterface ? $record['datetime']->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
		$level    = strtoupper($record['level']);
		$context  = !empty($record['context']) ? ' Context: ' . json_encode($record['context']) : '';

		return "[TelegramPluginBoilerplate] [{$level}] {$record['message']}{$context}";
	}
}
