<?php

namespace TelegramPluginBoilerplate\Services\Logging\Handlers;

use TelegramPluginBoilerplate\Services\Logging\LogHandlerInterface;

/**
 * Writes log records to a file with rotation support.
 */
class FileHandler implements LogHandlerInterface
{
	/**
	 * Log file path.
	 *
	 * @var string
	 */
	private string $logFile;

	/**
	 * Maximum file size in bytes before rotation.
	 *
	 * @var int
	 */
	private int $maxFileSize;

	/**
	 * Maximum number of backup files to keep.
	 *
	 * @var int
	 */
	private int $maxBackups;

	/**
	 * Constructor.
	 *
	 * @param	string	$logFile	Log file path.
	 * @param	int		$maxSize	Maximum file size in bytes.
	 * @param	int		$maxBackups	Maximum number of backup files.
	 */
	public function __construct(string $logFile = '', int $maxSize = 5242880, int $maxBackups = 5)
	{
		$this->logFile     = $logFile ?: WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'fdtbwpb_log.log';
		$this->maxFileSize = $maxSize;
		$this->maxBackups  = $maxBackups;
	}

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

		$this->checkLogDirectory();
		$this->rotateIfNeeded();

		$formattedMessage = $this->formatRecord($record);
		file_put_contents($this->logFile, $formattedMessage . PHP_EOL, FILE_APPEND | LOCK_EX);
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
	 * Ensures the log directory exists.
	 *
	 * @return	void
	 */
	private function checkLogDirectory(): void
	{
		if (!is_dir($logDir = dirname($this->logFile)))
			mkdir($logDir, 0755, true);
	}

	/**
	 * Rotates log file if it exceeds maximum size.
	 *
	 * @return	void
	 */
	private function rotateIfNeeded(): void
	{
		if (!file_exists($this->logFile) || filesize($this->logFile) < $this->maxFileSize) return;

		// Remove oldest backup
		$oldestBackup = $this->logFile . '.' . $this->maxBackups;
		if (file_exists($oldestBackup))
			unlink($oldestBackup);

		// Shift existing backups
		for ($i = $this->maxBackups - 1; $i >= 1; $i--)
		{
			$oldFile = $this->logFile . '.' . $i;
			$newFile = $this->logFile . '.' . ($i + 1);

			if (file_exists($oldFile))
				rename($oldFile, $newFile);
		}

		// Rename current log file
		rename($this->logFile, $this->logFile . '.1');
	}

	/**
	 * Formats a log record for file output.
	 *
	 * @param	array<string, mixed>	$record
	 *
	 * @return	string
	 */
	private function formatRecord(array $record): string
	{
		$datetime = $record['datetime'] instanceof \DateTimeInterface ? $record['datetime']->format('Y-m-d H:i:s') : date('Y-m-d H:i:s');
		$level    = strtoupper($record['level']);
		$context  = !empty($record['context']) ? ' ' . json_encode($record['context']) : '';
		$extra    = !empty($record['extra']) ? ' ' . json_encode($record['extra']) : '';

		return "[{$datetime}] [{$level}] {$record['message']}{$context}{$extra}";
	}
}
