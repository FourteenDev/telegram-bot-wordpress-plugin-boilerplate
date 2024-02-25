<?php namespace TelegramPluginBoilerplate\Telegram\ExtendedClasses;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Stream;
use Longman\TelegramBot\DB;
use Longman\TelegramBot\Entities\InputMedia\InputMedia;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\InvalidBotTokenException;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request as TelegramBotRequest;
use Longman\TelegramBot\TelegramLog;

class Request extends TelegramBotRequest
{
	/**
	 * Telegram object.
	 *
	 * @var	Telegram
	 */
	private static $telegram;

	/**
	 * URI of the Telegram API.
	 *
	 * @var	string
	 */
	private static $api_base_uri = 'https://api.telegram.org';

	/**
	 * Guzzle Client object.
	 *
	 * @var	ClientInterface
	 */
	private static $client;

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
	 * Available fields for InputFile helper.
	 *
	 * This is basically the list of all fields that allow InputFile objects
	 * for which input can be simplified by providing local path directly as string.
	 *
	 * @var	array
	 */
	private static $input_file_fields = [
		'setWebhook' 				=> ['certificate'],
		'sendPhoto' 				=> ['photo'],
		'sendAudio' 				=> ['audio', 'thumbnail'],
		'sendDocument' 				=> ['document', 'thumbnail'],
		'sendVideo' 				=> ['video', 'thumbnail'],
		'sendAnimation' 			=> ['animation', 'thumbnail'],
		'sendVoice' 				=> ['voice'],
		'sendVideoNote' 			=> ['video_note', 'thumbnail'],
		'setChatPhoto' 				=> ['photo'],
		'sendSticker' 				=> ['sticker'],
		'uploadStickerFile' 		=> ['sticker'],
		// @todo Look into new InputSticker field and see if we can do the same there.
		// 'createNewStickerSet' 	=> ['png_sticker', 'tgs_sticker', 'webm_sticker'],
		// 'addStickerToSet' 		=> ['png_sticker', 'tgs_sticker', 'webm_sticker'],
		'setStickerSetThumbnail' 	=> ['thumbnail'],
	];

	/**
	 * Initialize.
	 *
	 * @param	Telegram	$telegram
	 */
	public static function initialize($telegram): void
	{
		self::$telegram = $telegram;
		self::setClient(self::$client ?: new Client(['base_uri' => self::$api_base_uri]));
	}

	/**
	 * Set a custom Guzzle HTTP Client object.
	 *
	 * @param	ClientInterface	$client
	 */
	public static function setClient($client): void
	{
		self::$client = $client;
	}

	/**
	 * Properly set up the request params.
	 *
	 * If any item of the array is a resource, reformat it to a multipart request.
	 * Else, just return the passed data as form params.
	 *
	 * @param	array	$data
	 *
	 * @throws	TelegramException
	 * @return	array
	 */
	private static function setUpRequestParams(array $data): array
	{
		$has_resource 	= false;
		$multipart 		= [];

		foreach ($data as $key => &$item)
		{
			if ($key === 'media')
			{
				// Magical media input helper.
				$item = self::mediaInputHelper($item, $has_resource, $multipart);
			} else if (array_key_exists(self::$current_action, self::$input_file_fields) && in_array($key, self::$input_file_fields[self::$current_action], true)) {
				// Allow absolute paths to local files.
				if (is_string($item) && file_exists($item))
					$item = new Stream(self::encodeFile($item));
			} else if (is_array($item) || is_object($item)) {
				// Convert any nested arrays or objects into JSON strings.
				$item = json_encode($item);
			}

			// Reformat data array in multipart way if it contains a resource
			$has_resource 	= $has_resource || is_resource($item) || $item instanceof Stream;
			$multipart[] 	= ['name' => $key, 'contents' => $item];
		}
		unset($item);

		if ($has_resource)
			return ['multipart' => $multipart];

		return ['form_params' => $data];
	}

	/**
	 * Magical input media helper to simplify passing media.
	 *
	 * This allows the following:
	 * Request::editMessageMedia([
	 * 		...
	 * 		'media' => new InputMediaPhoto([
	 * 			'caption' => 'Caption!',
	 * 			'media'   => Request::encodeFile($local_photo),
	 * 		]),
	 * ]);
	 * and
	 * Request::sendMediaGroup([
	 * 		'media' => [
	 * 			new InputMediaPhoto(['media' => Request::encodeFile($local_photo_1)]),
	 * 			new InputMediaPhoto(['media' => Request::encodeFile($local_photo_2)]),
	 * 			new InputMediaVideo(['media' => Request::encodeFile($local_video_1)]),
	 * 		],
	 * ]);
	 * and even
	 * Request::sendMediaGroup([
	 * 		'media' => [
	 * 			new InputMediaPhoto(['media' => $local_photo_1]),
	 * 			new InputMediaPhoto(['media' => $local_photo_2]),
	 * 			new InputMediaVideo(['media' => $local_video_1]),
	 * 		],
	 * ]);
	 *
	 * @param	mixed	$item
	 * @param	bool	$has_resource
	 * @param	array	$multipart
	 *
	 * @throws	TelegramException
	 * @return	mixed
	 */
	private static function mediaInputHelper($item, bool &$has_resource, array &$multipart)
	{
		$was_array 			= is_array($item);
		$was_array || $item = [$item];

		/**
		 * @var	InputMedia|null	$media_item
		 */
		foreach ($item as $media_item)
		{
			if (!($media_item instanceof InputMedia)) continue;

			// Make a list of all possible media that can be handled by the helper.
			$possible_medias = array_filter([
				'media' 	=> $media_item->getMedia(),
				'thumbnail' => $media_item->getThumbnail(),
			]);

			foreach ($possible_medias as $type => $media)
			{
				// Allow absolute paths to local files.
				if (is_string($media) && strpos($media, 'attach://') !== 0 && file_exists($media))
					$media = new Stream(self::encodeFile($media));

				if (is_resource($media) || $media instanceof Stream)
				{
					$has_resource 	= true;
					$unique_key 	= uniqid($type . '_', false);
					$multipart[] 	= ['name' => $unique_key, 'contents' => $media];

					// We're literally overwriting the passed media type data!
					$media_item->$type 				= 'attach://' . $unique_key;
					$media_item->raw_data[$type] 	= 'attach://' . $unique_key;
				}
			}
		}

		$was_array || $item = reset($item);

		return json_encode($item);
	}

	/**
	 * Execute HTTP Request.
	 *
	 * @param	string				$action Action to execute.
	 * @param	array				$data   Data to attach to the execution.
	 *
	 * @throws	TelegramException
	 * @return	string						Result of the HTTP Request.
	 */
	public static function execute(string $action, array $data = []): string
	{
		$request_params 			= self::setUpRequestParams($data);
		$request_params['debug'] 	= TelegramLog::getDebugLogTempStream();

		try {
			if (!empty($proxy_update_receiver = FDTBWPB()->option('proxy_update_receiver')))
			{
				$request_params['form_params']['token'] = FDTBWPB()->option('bot_token');
				$response = self::$client->post(
					"$proxy_update_receiver?action=$action",
					$request_params
				);
			} else {
				$response = self::$client->post(
					'/bot' . self::$telegram->getApiKey() . '/' . $action,
					$request_params
				);
			}
			$result   = (string) $response->getBody();
		} catch (RequestException $e) {
			$response 	= null;
			$result 	= $e->getResponse() ? (string) $e->getResponse()->getBody() : '';
		}

		// Logging verbose debug output
		if (TelegramLog::$always_log_request_and_response || $response === null)
		{
			TelegramLog::debug('Request data:' . PHP_EOL . print_r($data, true));
			TelegramLog::debug('Response data:' . PHP_EOL . $result);
			TelegramLog::endDebugLogTempStream('Verbose HTTP Request output:' . PHP_EOL . '%s' . PHP_EOL);
		}

		return $result;
	}

	/**
	 * Encode file.
	 *
	 * @param	string	$file
	 *
	 * @throws	TelegramException
	 * @return	resource
	 */
	public static function encodeFile(string $file)
	{
		$fp = fopen($file, 'rb');
		if ($fp === false)
			throw new TelegramException('Cannot open "' . $file . '" for reading');

		return $fp;
	}

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

		if (defined('PHPUNIT_TESTSUITE'))
		{
			$fake_response = self::generateGeneralFakeServerResponse($data);

			return new ServerResponse($fake_response, $bot_username);
		}

		self::ensureNonEmptyData($data);

		self::limitTelegramRequests($action, $data);

		// Remember which action is currently being executed.
		self::$current_action = $action;

		$raw_response = self::execute($action, $data);
		$response     = json_decode($raw_response, true);

		if (null === $response)
		{
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
		if (is_array($chats))
		{
			foreach ($chats as $row)
			{
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
		if (true /*self::$limiter_enabled*/)
		{
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

			if (($chat_id || $inline_message_id) && in_array($action, $limited_methods, true))
			{
				$timeout = 60;

				while (true)
				{
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
