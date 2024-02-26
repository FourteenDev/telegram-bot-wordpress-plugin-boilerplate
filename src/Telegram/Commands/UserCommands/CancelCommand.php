<?php

namespace TelegramPluginBoilerplate\Telegram\Commands\UserCommands;

use TelegramPluginBoilerplate\Telegram\ExtendedClasses\Commands\UserCommand;
use Longman\TelegramBot\Entities\ServerResponse;
use TelegramPluginBoilerplate\Telegram\ExtendedClasses\Request;

/**
 * Cancel command.
 */
class CancelCommand extends UserCommand
{
	/**
	 * @var	string
	 */
	protected $name = 'cancel';

	/**
	 * @var	string
	 */
	protected $description = 'Cancels the current operation.';

	/**
	 * @var	string
	 */
	protected $usage = '/cancel';

	/**
	 * @var	string
	 */
	protected $version = '1.0.0';

	/**
	 * Executes the command.
	 *
	 * @return	ServerResponse
	 */
	public function execute(): ServerResponse
	{
		$message = $this->getMessage();
		$chat_id = $message->getChat()->getId();

		if (!$this->telegram->cancelOperations($chat_id))
			return Request::emptyResponse();

		return $this->replyToChat(
			esc_html__('Canceled!', FDTBWPB_TEXT_DOMAIN),
			[
				'reply_to_message_id' => $this->getMessage()->getMessageId(),
				'reply_markup'        => $this->get_start_buttons(),
			]
		);
	}
}
