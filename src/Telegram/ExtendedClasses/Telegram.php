<?php

namespace TelegramPluginBoilerplate\Telegram\ExtendedClasses;

use Longman\TelegramBot\ConversationDB;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram as TelegramBotTelegram;
use Longman\TelegramBot\TelegramLog;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use TelegramPluginBoilerplate\Telegram\ExtendedClasses\Commands\AdminCommand;
use TelegramPluginBoilerplate\Telegram\ExtendedClasses\Commands\Command;
use TelegramPluginBoilerplate\Telegram\ExtendedClasses\Commands\SystemCommand;
use TelegramPluginBoilerplate\Telegram\ExtendedClasses\Commands\UserCommand;
use TelegramPluginBoilerplate\Telegram\Handlers\UpdateHandler;

class Telegram extends TelegramBotTelegram
{
	/**
	 * Custom command class names.
	 * ```
	 * [
	 * 		'User' => [
	 * 			// command_name => command_class
	 * 			'start' => 'Name\Space\To\StartCommand',
	 * 		],
	 * 		'Admin' => [],
	 * 		//etc
	 * ]
	 * ```
	 *
	 * @var	array
	 */
	protected $command_classes = [
		Command::AUTH_USER   => [],
		Command::AUTH_ADMIN  => [],
		Command::AUTH_SYSTEM => [],
	];

	/**
	 * Telegram constructor.
	 *
	 * @param	string	$apiKey
	 * @param	string	$botUsername
	 *
	 * @throws	TelegramException
	 */
	public function __construct(string $apiKey, string $botUsername = '')
	{
		parent::__construct($apiKey, $botUsername);
		Request::initialize($this);
	}

	/**
	 * Initialize Database connection.
	 *
	 * @param	array	$credentials
	 * @param	string	$tablePrefix
	 * @param	string	$encoding
	 *
	 * @return	Telegram
	 * @throws	TelegramException
	 */
	public function enableMySql(array $credentials = [], string $tablePrefix = '', string $encoding = 'utf8mb4'): Telegram
	{
		global $wpdb;
		$this->pdo = DB::initialize(
			[
				'host'     => DB_HOST,
				'user'     => DB_USER,
				'password' => DB_PASSWORD,
				'database' => DB_NAME,
			],
			$this,
			"{$wpdb->prefix}fdtbwpb_",
			$encoding
		);
		ConversationDB::initializeConversation();
		$this->mysql_enabled = true;

		return $this;
	}

	/**
	 * Get commands list.
	 *
	 * @return	array				$commands
	 * @throws	TelegramException
	 */
	public function getCommandsList(): array
	{
		$commands = [];

		foreach ($this->commands_paths as $path)
		{
			try {
				// Get all "*Command.php" files
				$files = new \RegexIterator(
					new \RecursiveIteratorIterator(
						new \RecursiveDirectoryIterator($path)
					),
					'/^.+Command.php$/'
				);

				foreach ($files as $file)
				{
					// Convert filename to command
					$command = $this->classNameToCommandName(substr($file->getFilename(), 0, -4));

					// Invalid Classname
					if (is_null($command))
						continue;

					// Already registered
					if (array_key_exists($command, $commands))
						continue;

					require_once $file->getPathname();

					$commandObject = $this->getCommandObject($command, $file->getPathname());
					if ($commandObject instanceof Command)
						$commands[$command] = $commandObject;
				}
			} catch (\Exception $e) {
				throw new TelegramException('Error getting commands from path: ' . $path, $e->getCode());
			}
		}

		return $commands;
	}

	/**
	 * Get classname of predefined commands.
	 *
	 * @see	command_classes
	 *
	 * @param	string		$auth		Auth of command.
	 * @param	string		$command	Command name.
	 * @param	string		$filePath	Path to the command file.
	 *
	 * @return	string|null
	 */
	public function getCommandClassName(string $auth, string $command, string $filePath = ''): ?string
	{
		$command = mb_strtolower($command);
		if (empty($command)) return null;

		$auth = $this->ucFirstUnicode($auth);

		// First, check for directly assigned command class.
		if ($commandClass = $this->command_classes[$auth][$command] ?? null)
			return $commandClass;

		// Use the extended namespace. (We can't use __NAMESPACE__ here, because the commands are in another folder)
		$commandNamespace = "\\TelegramPluginBoilerplate\\Telegram\\Commands\\{$auth}Commands";

		// Check if we can get the namespace from the file (if passed).
		if ($filePath && !($commandNamespace = $this->getFileNamespace($filePath)))
			return null;

		$commandClass = $commandNamespace . '\\' . $this->commandNameToClassName($command);
		if (class_exists($commandClass))
			return $commandClass;

		return null;
	}

	/**
	 * Get an object instance of the passed command.
	 *
	 * @param	string			$command
	 * @param	string			$filePath
	 *
	 * @return	Command|null
	 */
	public function getCommandObject(string $command, string $filePath = ''): ?Command
	{
		if (isset($this->commands_objects[$command]))
			return $this->commands_objects[$command];

		$which = [Command::AUTH_SYSTEM];
		$this->isAdmin() && $which[] = Command::AUTH_ADMIN;
		$which[] = Command::AUTH_USER;

		foreach ($which as $auth)
		{
			$commandClass = $this->getCommandClassName($auth, $command, $filePath);

			if ($commandClass)
			{
				$commandObject = new $commandClass($this, $this->update);

				if (($auth === Command::AUTH_SYSTEM && $commandObject instanceof SystemCommand) ||
					($auth === Command::AUTH_ADMIN && $commandObject instanceof AdminCommand) ||
					($auth === Command::AUTH_USER && $commandObject instanceof UserCommand))
					return $commandObject;
			}
		}

		return null;
	}

	/**
	 * Handle getUpdates method.
	 *
	 * @param array|int|null	$data
	 * @param int|null			$timeout
	 *
	 * @throws TelegramException
	 * @return ServerResponse
	 */
	public function handleGetUpdates($data = null, ?int $timeout = null): ServerResponse
	{
		if (empty($this->bot_username))
			throw new TelegramException('Bot Username is not defined!');

		if (!DB::isDbConnected() && !$this->getupdates_without_database)
		{
			return new ServerResponse(
				[
					'ok'          => false,
					'description' => 'getUpdates needs MySQL connection! (This can be overridden - see documentation)',
				],
				$this->bot_username
			);
		}

		$offset = 0;
		$limit  = null;

		// By default, get update types sent by Telegram.
		$allowedUpdates = [];

		$offset         = $data['offset'] ?? $offset;
		$limit          = $data['limit'] ?? $limit;
		$timeout        = $data['timeout'] ?? $timeout;
		$allowedUpdates = $data['allowed_updates'] ?? $allowedUpdates;

		// Take custom input into account.
		if ($customInput = $this->getCustomInput())
		{
			try {
				$input = json_decode($this->input, true, 512, JSON_THROW_ON_ERROR);
				if (empty($input))
					throw new TelegramException('Custom input is empty');
				$response = new ServerResponse($input, $this->bot_username);
			} catch (\Throwable $e) {
				throw new TelegramException('Invalid custom input JSON: ' . $e->getMessage());
			}
		} else {
			if (DB::isDbConnected() && $lastUpdate = DB::selectTelegramUpdate(1))
			{
				// Get last Update id from the database.
				$lastUpdate           = reset($lastUpdate);
				$this->last_update_id = $lastUpdate['id'] ?? null;
			}

			// As explained in the telegram bot API documentation.
			if ($this->last_update_id !== null)
				$offset = $this->last_update_id + 1;

			$response = Request::getUpdates(compact('offset', 'limit', 'timeout', 'allowed_updates'));
		}

		if ($response->isOk())
		{
			// Log update.
			TelegramLog::update($response->toJson());

			// Process all updates
			/** @var Update $update */
			foreach ($response->getResult() as $update)
				$this->processUpdate($update);

			if (!DB::isDbConnected() && !$customInput && $this->last_update_id !== null && $offset === 0)
			{
				// Mark update(s) as read after handling
				$offset = $this->last_update_id + 1;
				$limit  = 1;

				Request::getUpdates(compact('offset', 'limit', 'timeout', 'allowed_updates'));
			}
		}

		return $response;
	}

	/**
	 * Handle bot request from webhook.
	 *
	 * @return	bool
	 *
	 * @throws	TelegramException
	 */
	public function handle(): bool
	{
		if ($this->bot_username === '')
			throw new TelegramException('Bot Username is not defined!');

		$input = Request::getInput();
		if (empty($input))
			throw new TelegramException('Input is empty! The webhook must not be called manually, only by Telegram.');

		// Log update.
		TelegramLog::update($input);

		$post = json_decode($input, true);
		if (empty($post))
			throw new TelegramException('Invalid input JSON! The webhook must not be called manually, only by Telegram.');

		if ($response = $this->processUpdate(new Update($post, $this->bot_username)))
			return $response->isOk();

		return false;
	}

	/**
	 * Process bot Update request.
	 *
	 * @param	Update	$update
	 *
	 * @return	ServerResponse
	 * @throws	TelegramException
	 */
	public function processUpdate(Update $update): ServerResponse
	{
		$this->update         = $update;
		$this->last_update_id = $update->getUpdateId();

		if (is_callable($this->update_filter))
		{
			$reason = 'Update denied by update_filter';
			try {
				$allowed = (bool) call_user_func_array($this->update_filter, [$update, $this, &$reason]);
			} catch (\Exception $e) {
				$allowed = false;
			}

			if (!$allowed)
			{
				TelegramLog::debug($reason);
				return new ServerResponse(['ok' => false, 'description' => 'denied']);
			}
		}

		// Load admin commands
		/* if ($this->isAdmin())
			$this->addCommandsPath(TB_BASE_COMMANDS_PATH . '/AdminCommands', false); */

		UpdateHandler::getInstance();
		/**
		 * Filters the user input before getting the commands list.
		 *
		 * @param	Update	$update		Return `false` if you want to stop executing the update.
		 */
		$this->update = apply_filters('fdtbwpb_before_get_commands_list', $this->update);
		if ($this->update === false)
			return Request::emptyResponse();

		// Make sure we have an up-to-date command list
		// This is necessary to "require" all the necessary command files!
		$this->commands_objects = $this->getCommandsList();

		//If all else fails, it's a generic message.
		$command = self::GENERIC_MESSAGE_COMMAND;

		$updateType = $this->update->getUpdateType();
		if ($updateType === 'message')
		{
			$message = $this->update->getMessage();
			$type    = $message->getType();

			// Let's check if the message object has the type field we're looking for...
			$commandTemp   = $type === 'command' ? $message->getCommand() : $this->getCommandFromType($type);
			// ...and if a fitting command class is available.
			$commandObject = $commandTemp ? $this->getCommandObject($commandTemp) : null;

			// Empty usage string denotes a non-executable command.
			// @see https://github.com/php-telegram-bot/core/issues/772#issuecomment-388616072
			if (($commandObject === null && $type === 'command') ||
				($commandObject !== null && $commandObject->getUsage() !== ''))
				$command = $commandTemp;
		} else if ($updateType !== null) {
			$command = $this->getCommandFromType($updateType);
		}

		// Make sure we don't try to process update that was already processed
		$lastId = DB::selectTelegramUpdate(1, $this->update->getUpdateId());
		if ($lastId && count($lastId) === 1)
		{
			TelegramLog::debug('Duplicate update received, processing aborted!');
			return Request::emptyResponse();
		}

		DB::insertRequest($this->update);

		/**
		 * Filter the user input before executing the command.
		 *
		 * @param	bool		$shouldExecuteCommand
		 * @param	Telegram	$telegram
		 */
		if (apply_filters('fdtbwpb_before_execute_command', true, $this))
			return $this->executeCommand($command);
		else
			return Request::emptyResponse();
	}

	/**
	 * Execute /command.
	 *
	 * @param	string				$command
	 *
	 * @return	ServerResponse
	 * @throws	TelegramException
	 */
	public function executeCommand(string $command): ServerResponse
	{
		$command       = mb_strtolower($command);
		$commandObject = $this->commands_objects[$command] ?? $this->getCommandObject($command);

		if (!$commandObject || !$commandObject->isEnabled())
		{
			// Failsafe in case the Generic command can't be found
			if ($command === self::GENERIC_COMMAND)
				throw new TelegramException('Generic command missing!');

			// Handle a generic command or non existing one
			$this->last_command_response = $this->executeCommand(self::GENERIC_COMMAND);
		} else {
			// execute() method is executed after preExecute()
			// This is to prevent executing a DB query without a valid connection
			if ($this->update)
				$this->last_command_response = $commandObject->setUpdate($this->update)->preExecute();
			else
				$this->last_command_response = $commandObject->preExecute();
		}

		return $this->last_command_response;
	}

	/**
	 * Enable a list of Admin Accounts.
	 *
	 * @param	array		$adminIds List of admin IDs.
	 *
	 * @return	Telegram
	 */
	public function enableAdmins(array $adminIds): Telegram
	{
		foreach ($adminIds as $adminId)
			if (intval(trim($adminId)))
				$this->enableAdmin(intval(trim($adminId)));

		return $this;
	}

	/**
	 * Add a single custom command class.
	 *
	 * @param	string		$commandClass	Full command class name.
	 *
	 * @return	Telegram
	 */
	public function addCommandClass(string $commandClass): Telegram
	{
		if (!$commandClass || !class_exists($commandClass))
		{
			$error = sprintf('Command class "%s" does not exist.', $commandClass);
			TelegramLog::error($error);
			throw new \InvalidArgumentException($error);
		}

		if (!is_a($commandClass, Command::class, true))
		{
			$error = sprintf('Command class "%s" does not extend "%s".', $commandClass, Command::class);
			TelegramLog::error($error);
			throw new \InvalidArgumentException($error);
		}

		// Dummy object to get data from.
		$commandObject = new $commandClass($this);

		$auth = null;
		$commandObject->isSystemCommand() && $auth = Command::AUTH_SYSTEM;
		$commandObject->isAdminCommand() && $auth = Command::AUTH_ADMIN;
		$commandObject->isUserCommand() && $auth = Command::AUTH_USER;

		if ($auth)
		{
			$command = mb_strtolower($commandObject->getName());

			$this->command_classes[$auth][$command] = $commandClass;
		}

		return $this;
	}

	/**
	 * Add multiple custom command classes.
	 *
	 * @param	array		$commandClasses	List of full command class names.
	 *
	 * @return	Telegram
	 */
	public function addCommandClasses(array $commandClasses): Telegram
	{
		foreach ($commandClasses as $commandClass)
			$this->addCommandClass($commandClass);

		return $this;
	}

	/**
	 * Return the update.
	 *
	 * @return	Update
	 */
	public function getUpdate()
	{
		return $this->update;
	}

	/**
	 * Enables the TelegramLog object.
	 *
	 * @return	void
	 */
	public function enableLogging()
	{
		// https://github.com/php-telegram-bot/core/blob/master/doc/01-utils.md#logging
		//
		// (this example requires Monolog: composer require monolog/monolog)
		TelegramLog::initialize(
			new Logger('telegram_bot', [
				(new StreamHandler(FDTBWPB_DIR . 'logs/debug.log', Logger::DEBUG))->setFormatter(new LineFormatter(null, null, true)),
				(new StreamHandler(FDTBWPB_DIR . 'logs/error.log', Logger::ERROR))->setFormatter(new LineFormatter(null, null, true)),
			]),
			new Logger('telegram_bot_updates', [
				(new StreamHandler(FDTBWPB_DIR . 'logs/update.log', Logger::INFO))->setFormatter(new LineFormatter('%message%' . PHP_EOL)),
			])
		);
		TelegramLog::$remove_bot_token = true;
		if (wp_get_environment_type() === 'local') TelegramLog::$always_log_request_and_response = true;
	}

	/**
	 * Run provided commands.
	 *
	 * @param	array				$commands
	 *
	 * @return	ServerResponse[]
	 *
	 * @throws	TelegramException
	 */
	public function runCommands(array $commands): array
	{
		if (empty($commands))
			throw new TelegramException('No command(s) provided!');

		$this->run_commands = true;

		// Check if this request has a user Update / comes from Telegram.
		if ($userUpdate = $this->update)
		{
			$from = $this->update->getMessage()->getFrom();
			$chat = $this->update->getMessage()->getChat();
		} else {
			// Fall back to the Bot user.
			$from = new User([
				'id'         => $this->getBotId(),
				'first_name' => $this->getBotUsername(),
				'username'   => $this->getBotUsername(),
			]);

			// Try to get "live" Bot info.
			$response = Request::getMe();
			if ($response->isOk())
			{
				/** @var	User	$result */
				$result = $response->getResult();

				$from = new User([
					'id'         => $result->getId(),
					'first_name' => $result->getFirstName(),
					'username'   => $result->getUsername(),
				]);
			}

			// Give Bot access to admin commands.
			$this->enableAdmin($from->getId());

			// Lock the bot to a private chat context.
			$chat = new Chat([
				'id'   => $from->getId(),
				'type' => 'private',
			]);
		}

		$newUpdate = static function($text = '') use ($from, $chat)
		{
			return new Update([
				'update_id' => -1,
				'message'   => [
					'message_id' => -1,
					'date'       => time(),
					'from'       => json_decode($from->toJson(), true),
					'chat'       => json_decode($chat->toJson(), true),
					'text'       => $text,
				],
			]);
		};

		$responses = [];

		foreach ($commands as $command)
		{
			$this->update = $newUpdate($command);

			// Refresh commands list for new Update object.
			$this->commands_objects = $this->getCommandsList();

			$responses[] = $this->executeCommand($this->update->getMessage()->getCommand());
		}

		// Reset Update to initial context.
		$this->update = $userUpdate;

		return $responses;
	}

	/**
	 * Cancels all operations.
	 *
	 * @param	int		$chatId
	 *
	 * @return	bool
	 */
	public function cancelOperations($chatId)
	{
		return true;
	}
}
