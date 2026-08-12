<?php

namespace TelegramPluginBoilerplate\Services\Logging;

class Logger
{
	/**
	 * Available log levels.
	 */
	public const EMERGENCY = 'emergency';
	public const ALERT     = 'alert';
	public const CRITICAL  = 'critical';
	public const ERROR     = 'error';
	public const WARNING   = 'warning';
	public const NOTICE    = 'notice';
	public const INFO      = 'info';
	public const DEBUG     = 'debug';

	/**
	 * Log level hierarchy.
	 *
	 * @var	array<string, int>
	 */
	private array $levels = [
		self::EMERGENCY => 0,
		self::ALERT     => 1,
		self::CRITICAL  => 2,
		self::ERROR     => 3,
		self::WARNING   => 4,
		self::NOTICE    => 5,
		self::INFO      => 6,
		self::DEBUG     => 7,
	];

	/**
	 * Current log level threshold.
	 *
	 * @var	string
	 */
	private string $threshold;

	/**
	 * Log handlers.
	 *
	 * @var	array<LogHandlerInterface>
	 */
	private array $handlers = [];

	/**
	 * Constructor.
	 *
	 * @param	string	$threshold	Minimum log level to record.
	 */
	public function __construct($threshold = self::INFO)
	{
		$this->threshold = $threshold;
		$this->addDefaultHandlers();
	}

	/**
	 * Adds default log handlers.
	 *
	 * @return	void
	 */
	private function addDefaultHandlers(): void
	{
		// Add file handler for production
		if (!defined('\WP_DEBUG') || !\WP_DEBUG)
			$this->addHandler(new \TelegramPluginBoilerplate\Services\Logging\Handlers\FileHandler());

		// Add WordPress debug log handler if WP_DEBUG_LOG is enabled
		if (defined('\WP_DEBUG_LOG') && \WP_DEBUG_LOG)
			$this->addHandler(new \TelegramPluginBoilerplate\Services\Logging\Handlers\WordPressHandler());

		// Add error log handler for critical errors
		$this->addHandler(new \TelegramPluginBoilerplate\Services\Logging\Handlers\ErrorLogHandler());
	}

	/**
	 * Adds a log handler.
	 *
	 * @param	LogHandlerInterface	$handler	Log handler instance.
	 *
	 * @return	void
	 */
	public function addHandler(LogHandlerInterface $handler): void
	{
		$this->handlers[] = $handler;
	}

	/**
	 * Logs a message with the given level.
	 *
	 * @param	string	$level		Log level.
	 * @param	string	$message	Log message.
	 * @param	array	$context	Additional context data.
	 *
	 * @return	void
	 */
	public function log(string $level, string $message, array $context = []): void
	{
		if (!$this->shouldLog($level)) return;

		$record = [
			'message'   => $message,
			'level'     => $level,
			'level_num' => $this->levels[$level] ?? 0,
			'channel'   => 'telegram-plugin-boilerplate',
			'datetime'  => new \DateTimeImmutable(),
			'context'   => $context,
			'extra'     => [
				// 'user_id' => get_current_user_id(),
			],
		];

		foreach ($this->handlers as $handler)
		{
			try {
				$handler->handle($record);
			} catch (\Exception $e) {
				error_log("TelegramPluginBoilerplate: Logger handler error: " . $e->getMessage());
			}
		}
	}

	/**
	 * Checks if the given level should be logged.
	 *
	 * @param	string	$level	Log level.
	 *
	 * @return	bool
	 */
	private function shouldLog(string $level): bool
	{
		return isset($this->levels[$level]) && isset($this->levels[$this->threshold]) && $this->levels[$level] <= $this->levels[$this->threshold];
	}

	/**
	 * Logs an emergency message.
	 *
	 * @param	string	$message	Log message.
	 * @param	array	$context	Additional context data.
	 *
	 * @return	void
	 */
	public function emergency(string $message, array $context = []): void
	{
		$this->log(self::EMERGENCY, $message, $context);
	}

	/**
	 * Logs an alert message.
	 *
	 * @param	string	$message	Log message.
	 * @param	array	$context	Additional context data.
	 *
	 * @return	void
	 */
	public function alert(string $message, array $context = []): void
	{
		$this->log(self::ALERT, $message, $context);
	}

	/**
	 * Logs a critical message.
	 *
	 * @param	string	$message	Log message.
	 * @param	array	$context	Additional context data.
	 *
	 * @return	void
	 */
	public function critical(string $message, array $context = []): void
	{
		$this->log(self::CRITICAL, $message, $context);
	}

	/**
	 * Logs an error message.
	 *
	 * @param	string	$message	Log message.
	 * @param	array	$context	Additional context data.
	 *
	 * @return	void
	 */
	public function error(string $message, array $context = []): void
	{
		$this->log(self::ERROR, $message, $context);
	}

	/**
	 * Logs a warning message.
	 *
	 * @param	string	$message	Log message.
	 * @param	array	$context	Additional context data.
	 *
	 * @return	void
	 */
	public function warning(string $message, array $context = []): void
	{
		$this->log(self::WARNING, $message, $context);
	}

	/**
	 * Logs a notice message.
	 *
	 * @param	string	$message	Log message.
	 * @param	array	$context	Additional context data.
	 *
	 * @return	void
	 */
	public function notice(string $message, array $context = []): void
	{
		$this->log(self::NOTICE, $message, $context);
	}

	/**
	 * Logs an info message.
	 *
	 * @param	string	$message	Log message.
	 * @param	array	$context	Additional context data.
	 *
	 * @return	void
	 */
	public function info(string $message, array $context = []): void
	{
		$this->log(self::INFO, $message, $context);
	}

	/**
	 * Logs a debug message.
	 *
	 * @param	string	$message	Log message.
	 * @param	array	$context	Additional context data.
	 *
	 * @return	void
	 */
	public function debug(string $message, array $context = []): void
	{
		$this->log(self::DEBUG, $message, $context);
	}
}
