<?php namespace BoilerplateTelegramPlugin\Telegram\ExtendedClasses;

use Longman\TelegramBot\DB;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\InvalidBotTokenException;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request as TelegramBotRequest;
use Longman\TelegramBot\TelegramLog;

class Request extends TelegramBotRequest
{
	/**
	 * The current action that is being executed
	 *
	 * @var	string
	 */
	private static $current_action = '';

	/**
	 * Available actions to send.
	 *
	 * This is basically the list of all methods listed on the official API documentation.
	 *
	 * @link	https://core.telegram.org/bots/api
	 *
	 * @var		array
	 */
	private static $actions = [
		'getUpdates',
		'setWebhook',
		'deleteWebhook',
		'getWebhookInfo',
		'getMe',
		'logOut',
		'close',
		'sendMessage',
		'forwardMessage',
		'copyMessage',
		'sendPhoto',
		'sendAudio',
		'sendDocument',
		'sendSticker',
		'sendVideo',
		'sendAnimation',
		'sendVoice',
		'sendVideoNote',
		'sendMediaGroup',
		'sendLocation',
		'editMessageLiveLocation',
		'stopMessageLiveLocation',
		'sendVenue',
		'sendContact',
		'sendPoll',
		'sendDice',
		'sendChatAction',
		'getUserProfilePhotos',
		'getFile',
		'banChatMember',
		'unbanChatMember',
		'restrictChatMember',
		'promoteChatMember',
		'setChatAdministratorCustomTitle',
		'banChatSenderChat',
		'unbanChatSenderChat',
		'setChatPermissions',
		'exportChatInviteLink',
		'createChatInviteLink',
		'editChatInviteLink',
		'revokeChatInviteLink',
		'approveChatJoinRequest',
		'declineChatJoinRequest',
		'setChatPhoto',
		'deleteChatPhoto',
		'setChatTitle',
		'setChatDescription',
		'pinChatMessage',
		'unpinChatMessage',
		'unpinAllChatMessages',
		'leaveChat',
		'getChat',
		'getChatAdministrators',
		'getChatMemberCount',
		'getChatMember',
		'setChatStickerSet',
		'deleteChatStickerSet',
		'getForumTopicIconStickers',
		'createForumTopic',
		'editForumTopic',
		'closeForumTopic',
		'reopenForumTopic',
		'deleteForumTopic',
		'unpinAllForumTopicMessages',
		'editGeneralForumTopic',
		'closeGeneralForumTopic',
		'reopenGeneralForumTopic',
		'hideGeneralForumTopic',
		'unhideGeneralForumTopic',
		'answerCallbackQuery',
		'answerInlineQuery',
		'setMyCommands',
		'deleteMyCommands',
		'getMyCommands',
		'setMyName',
		'getMyName',
		'setMyDescription',
		'getMyDescription',
		'setMyShortDescription',
		'getMyShortDescription',
		'setChatMenuButton',
		'getChatMenuButton',
		'setMyDefaultAdministratorRights',
		'getMyDefaultAdministratorRights',
		'editMessageText',
		'editMessageCaption',
		'editMessageMedia',
		'editMessageReplyMarkup',
		'stopPoll',
		'deleteMessage',
		'getStickerSet',
		'getCustomEmojiStickers',
		'uploadStickerFile',
		'createNewStickerSet',
		'addStickerToSet',
		'setStickerPositionInSet',
		'deleteStickerFromSet',
		'setStickerEmojiList',
		'setStickerKeywords',
		'setStickerMaskPosition',
		'setStickerSetTitle',
		'setStickerSetThumbnail',
		'setCustomEmojiStickerSetThumbnail',
		'deleteStickerSet',
		'answerWebAppQuery',
		'sendInvoice',
		'createInvoiceLink',
		'answerShippingQuery',
		'answerPreCheckoutQuery',
		'setPassportDataErrors',
		'sendGame',
		'setGameScore',
		'getGameHighScores',
	];

	/**
	 * Send command.
	 *
	 * @param	string				$action
	 * @param	array				$data
	 *
	 * @return	ServerResponse
	 * @throws	TelegramException
	 */
	public static function send(string $action, array $data = []): ServerResponse
	{
		self::ensureValidAction($action);
		self::addDummyParamIfNecessary($action, $data);

		$bot_username = ''; // self::$telegram->getBotUsername(); // TODO: Cannot access private property

		if (defined('PHPUNIT_TESTSUITE')) {
			$fake_response = self::generateGeneralFakeServerResponse($data);

			return new ServerResponse($fake_response, $bot_username);
		}

		self::ensureNonEmptyData($data);

		self::limitTelegramRequests($action, $data);

		// Remember which action is currently being executed.
		self::$current_action = $action;

		$raw_response = self::execute($action, $data);
		$response     = json_decode($raw_response, true);

		if (null === $response) {
			TelegramLog::debug($raw_response);
			throw new TelegramException('Telegram returned an invalid response!');
		}

		$response = new ServerResponse($response, $bot_username);

		if (!$response->isOk() && $response->getErrorCode() === 401 && $response->getDescription() === 'Unauthorized')
			throw new InvalidBotTokenException();

		// Special case for sent polls, which need to be saved specially.
		// @todo Take into account if DB gets extracted into separate module.
		if ($response->isOk() && ($message = $response->getResult()) && ($message instanceof Message) && $poll = $message->getPoll())
			DB::insertPollRequest($poll);

		// Reset current action after completion.
		self::$current_action = '';

		return $response;
	}

	/**
	 * Make sure the data isn't empty, else throw an exception.
	 *
	 * @param	array				$data
	 *
	 * @throws	TelegramException
	 */
	private static function ensureNonEmptyData(array $data): void
	{
		if (count($data) === 0)
			throw new TelegramException('Data is empty!');
	}

	/**
	 * Make sure the action is valid, else throw an exception.
	 *
	 * @param	string				$action
	 *
	 * @throws	TelegramException
	 */
	private static function ensureValidAction(string $action): void
	{
		if (!in_array($action, self::$actions, true))
			throw new TelegramException('The action "' . $action . '" doesn\'t exist!');
	}

	/**
	 * Use this method to send text messages. On success, the last sent Message is returned.
	 *
	 * All message responses are saved in `$extras['responses']`.
	 * Custom encoding can be defined in `$extras['encoding']` (default: `mb_internal_encoding()`)
	 * Custom splitting can be defined in `$extras['split']` (default: 4096)
	 * 		`$extras['split'] = null;` // force to not split message at all!
	 * 		`$extras['split'] = 200;`  // split message into 200 character chunks
	 *
	 * @link	https://core.telegram.org/bots/api#sendmessage
	 *
	 * @todo	Splitting formatted text may break the message.
	 *
	 * @param	array		$data
	 * @param	array|null	$extras
	 *
	 * @return	ServerResponse
	 * @throws	TelegramException
	 */
	public static function sendMessage(array $data, ?array &$extras = []): ServerResponse
	{
		$extras = array_merge([
			'split' 	=> 4096,
			'encoding' 	=> mb_internal_encoding(),
		], (array) $extras);

		$text 			= $data['text'];
		$encoding 		= $extras['encoding'];
		$max_length 	= $extras['split'] ?: mb_strlen($text, $encoding);

		$responses = [];

		do {
			// Chop off and send the first message.
			$data['text'] 	= mb_substr($text, 0, $max_length, $encoding);
			$responses[] 	= self::send('sendMessage', $data);

			// Prepare the next message.
			$text = mb_substr($text, $max_length, null, $encoding);
		} while ($text !== '');

		// Add all response objects to referenced variable.
		$extras['responses'] = $responses;

		return end($responses);
	}

	/**
	 * Send message to all active chats.
	 *
	 * @param	string				$callback_function
	 * @param	array				$data
	 * @param	array				$select_chats_params
	 *
	 * @return	array
	 * @throws	TelegramException
	 */
	public static function sendToActiveChats(
		string $callback_function,
		array $data,
		array $select_chats_params
	): array {
		self::ensureValidAction($callback_function);

		$chats = DB::selectChats($select_chats_params);

		$results = [];
		if (is_array($chats)) {
			foreach ($chats as $row) {
				$data['chat_id'] 	= $row['chat_id'];
				$results[] 			= self::send($callback_function, $data);
			}
		}

		return $results;
	}

	/**
	 * This functions delays API requests to prevent reaching Telegram API limits
	 *  Can be disabled while in execution by 'Request::setLimiter(false)'.
	 *
	 * @link	https://core.telegram.org/bots/faq#my-bot-is-hitting-limits-how-do-i-avoid-this
	 *
	 * @param	string				$action
	 * @param	array				$data
	 *
	 * @throws	TelegramException
	 */
	private static function limitTelegramRequests(string $action, array $data = []): void
	{
		if (true /*self::$limiter_enabled*/) {
			$limited_methods = [
				'sendMessage',
				'forwardMessage',
				'copyMessage',
				'sendPhoto',
				'sendAudio',
				'sendDocument',
				'sendSticker',
				'sendVideo',
				'sendAnimation',
				'sendVoice',
				'sendVideoNote',
				'sendMediaGroup',
				'sendLocation',
				'editMessageLiveLocation',
				'stopMessageLiveLocation',
				'sendVenue',
				'sendContact',
				'sendPoll',
				'sendDice',
				'sendInvoice',
				'sendGame',
				'setGameScore',
				'setMyCommands',
				'deleteMyCommands',
				'editMessageText',
				'editMessageCaption',
				'editMessageMedia',
				'editMessageReplyMarkup',
				'stopPoll',
				'setChatTitle',
				'setChatDescription',
				'setChatStickerSet',
				'deleteChatStickerSet',
				'setPassportDataErrors',
			];

			$chat_id 			= $data['chat_id'] ?? null;
			$inline_message_id 	= $data['inline_message_id'] ?? null;

			if (($chat_id || $inline_message_id) && in_array($action, $limited_methods, true)) {
				$timeout = 60;

				while (true) {
					if ($timeout <= 0)
						throw new TelegramException('Timed out while waiting for a request spot!');

					if (!($requests = DB::getTelegramRequestCount($chat_id, $inline_message_id)))
						break;

					// Make sure we're handling integers here.
					$requests = array_map('intval', $requests);

					$chat_per_second 	= ($requests['LIMIT_PER_SEC'] === 0);    // No more than one message per second inside a particular chat
					$global_per_second 	= ($requests['LIMIT_PER_SEC_ALL'] < 30); // No more than 30 messages per second to different chats
					$groups_per_minute 	= (((is_numeric($chat_id) && $chat_id > 0) || $inline_message_id !== null) || ((!is_numeric($chat_id) || $chat_id < 0) && $requests['LIMIT_PER_MINUTE'] < 20));    // No more than 20 messages per minute in groups and channels

					if ($chat_per_second && $global_per_second && $groups_per_minute)
						break;

					$timeout--;
					usleep((int) (1 /*self::$limiter_interval*/ * 1000000));
				}

				DB::insertTelegramRequest($action, $data);
			}
		}
	}
}
