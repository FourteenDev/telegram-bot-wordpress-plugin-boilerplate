<?php namespace BoilerplateTelegramPlugin\Telegram\Commands\UserCommands;

use Longman\TelegramBot\Entities\Keyboard;
use BoilerplateTelegramPlugin\Telegram\ExtendedClasses\Commands\UserCommand;
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
//		$message 	= $this->getMessage();
//		$chat_id 	= $message->getChat()->getId();
//		$data 		= ['chat_id' => $chat_id, 'reply_to_message_id' => $message->getMessageId()];

		return $this->replyToChat(
			esc_html__('Welcome!', BTBP_TEXT_DOMAIN),
			[
				'reply_to_message_id' 	=> $this->getMessage()->getMessageId(),
				'reply_markup' 			=> $this->get_start_buttons(),
			]
		);
	}

	/**
	 * Returns a reply keyboard markup for /start command.
	 *
	 * @return	Keyboard|null
	 */
	private function get_start_buttons()
	{
		$keyboard = new Keyboard(
			esc_html__('Test 1', BTBP_TEXT_DOMAIN),
			esc_html__('Test 2', BTBP_TEXT_DOMAIN)
		);

		$keyboard->setResizeKeyboard(true)
			->setOneTimeKeyboard(true);

		return $keyboard;
	}
}
