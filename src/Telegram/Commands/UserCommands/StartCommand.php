<?php

namespace TelegramPluginBoilerplate\Telegram\Commands\UserCommands;

use Longman\TelegramBot\Entities\Keyboard;
use TelegramPluginBoilerplate\Telegram\ExtendedClasses\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;

/**
 * Start command.
 */
class StartCommand extends UserCommand
{
	/**
	 * @var	string
	 */
	protected $name = 'start';

	/**
	 * @var	string
	 */
	protected $description = 'Start command.';

	/**
	 * @var	string
	 */
	protected $usage = '/start';

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
		// $message = $this->getMessage();
		// $chatId  = $message->getChat()->getId();
		// $data    = ['chat_id' => $chatId, 'reply_to_message_id' => $message->getMessageId()];

		if ($this->telegram->isAdmin())
		{
			return $this->replyToChat(
				esc_html__('Welcome Admin!', FDTBWPB_TEXT_DOMAIN),
				[
					'reply_to_message_id' => $this->getMessage()->getMessageId(),
					'reply_markup'        => $this->getStartButtons(),
				]
			);
		}

		return $this->replyToChat(
			esc_html__('Welcome!', FDTBWPB_TEXT_DOMAIN),
			[
				'reply_to_message_id' => $this->getMessage()->getMessageId(),
				'reply_markup'        => $this->getStartButtons(),
			]
		);
	}

	/**
	 * Returns a reply keyboard markup for /start command.
	 *
	 * @return	Keyboard|null
	 */
	private function getStartButtons()
	{
		$keyboard = new Keyboard(
			esc_html__('Test 1', FDTBWPB_TEXT_DOMAIN),
			esc_html__('Test 2', FDTBWPB_TEXT_DOMAIN)
		);

		$keyboard->setResizeKeyboard(true)
			->setOneTimeKeyboard(true);

		return $keyboard;
	}
}
