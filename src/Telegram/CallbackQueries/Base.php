<?php

namespace TelegramPluginBoilerplate\Telegram\CallbackQueries;

use Longman\TelegramBot\Entities\CallbackQuery;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use TelegramPluginBoilerplate\Telegram\ExtendedClasses\Request;
use TelegramPluginBoilerplate\Telegram\ExtendedClasses\Telegram;

abstract class Base
{
	/**
	 * Telegram object.
	 *
	 * @var	Telegram
	 */
	protected $telegram;

	/**
	 * Update object.
	 *
	 * @var	Update
	 */
	protected $update;

	/**
	 * Current update's callback query.
	 *
	 * @var	CallbackQuery
	 */
	protected $callback_query;

	/**
	 * The name of the callback.
	 *
	 * @var	string
	 */
	protected $name = '';

	/**
	 * What does this callback do?
	 *
	 * @var	string
	 */
	protected $description = '';

	/**
	 * How does the callback data looks like?
	 *
	 * @var	string
	 */
	protected $syntax = '';

	/**
	 * The second part of the callback data (after the `:`).
	 * Will remain empty if the callback data doesn't have any colons.
	 *
	 * @var	string
	 */
	protected $callback_data_without_command = '';

	/**
	 * Constructor.
	 *
	 * @param	Telegram	$telegram
	 * @param	Update		$update
	 * @param	int			$index
	 */
	public function __construct(Telegram $telegram, ?Update $update)
	{
		$this->telegram = $telegram;
		$this->update = $update;

		if (!$this->update->getCallbackQuery())
			throw new \LogicException('Current update does not have a callback query!');
		$this->callback_query = $this->update->getCallbackQuery();

		if ($callback_data = $this->update->getCallbackQuery()->getData())
			if (($colon_index = stripos($callback_data, ':')) !== false)
				$this->callback_data_without_command = substr($callback_data, $colon_index + 1);
	}

	/**
	 * The main logic and functions of this callback.
	 *
	 * @return	ServerResponse
	 * @throws	TelegramException
	 */
	abstract function execute();

	/**
	 * Gets Telegram object.
	 *
	 * @return	Telegram
	 */
	public function getTelegram(): Telegram
	{
		return $this->telegram;
	}

	/**
	 * Gets Update object.
	 *
	 * @return	Update|null
	 */
	public function getUpdate(): ?Update
	{
		return $this->update;
	}

	/**
	 * Helper to answer the callback query.
	 *
	 * @param	string	$text
	 * @param	array	$data
	 *
	 * @return	ServerResponse
	 * @throws	TelegramException
	 */
	public function answer(string $text = '', array $data = []): ServerResponse
	{
		if ($callback_query = $this->update->getCallbackQuery())
		{
			return Request::answerCallbackQuery(array_merge([
				'callback_query_id' => $callback_query->getId(),
				'text'              => $text,
			], $data));
		}

		return Request::emptyResponse();
	}
}
