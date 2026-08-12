<?php

namespace TelegramPluginBoilerplate\Services\Logging\Handlers;

use TelegramPluginBoilerplate\Services\Logging\LogHandlerInterface;

/**
 * Handles critical and emergency level logs through PHP's error_log function.
 */
class ErrorLogHandler implements LogHandlerInterface
{
	/**
	 * Critical log levels that should be handled by this handler.
	 *
	 * @var	array<string>
	 */
	private array $criticalLevels = [
		'emergency',
		'alert',
		'critical',
		'error',
	];

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
		return isset($record['level']) && isset($record['message']) && in_array($record['level'], $this->criticalLevels, true);
	}

	/**
	 * Formats a log record for error log.
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

		return "[TelegramPluginBoilerplate CRITICAL] [{$level}] {$record['message']}{$context}";
	}
}
