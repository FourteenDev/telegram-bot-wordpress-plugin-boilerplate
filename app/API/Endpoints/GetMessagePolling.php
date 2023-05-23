<?php namespace BoilerplateTelegramPlugin\API\Endpoints;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Exception\TelegramLogException;
use Longman\TelegramBot\TelegramLog;
use BoilerplateTelegramPlugin\API\BaseEndpoint;
use BoilerplateTelegramPlugin\Telegram\ExtendedClasses\Telegram;

class GetMessagePolling extends BaseEndpoint
{
	public static $instance = NULL;

	public $namespace 	= 'btbp/v1/';
	public $route 		= 'get_message_polling';
	public $method 		= 'GET';

	public static function get_instance()
	{
		null === self::$instance && self::$instance = new self;
		return self::$instance;
	}

	/**
	 * Uses `getUpdates` method and fetches updates from Telegram.
	 *
	 * @param	\WP_REST_Request	$request	The current matched request object.
	 *
	 * @return	\WP_REST_Response
	 */
	public function handle($request)
	{
		if (wp_get_environment_type() !== 'local')
			return $this->get_rest_reponse(401, esc_html__('Not allowed!', BTBP_TEXT_DOMAIN));

		$bot_token = BTBP()->option('bot_token');
		if (empty($bot_token))
			return $this->get_rest_reponse(401, esc_html__('Bot token is not defined!', BTBP_TEXT_DOMAIN));

		$bot_username = BTBP()->option('bot_username');
		if (empty($bot_username))
			return $this->get_rest_reponse(401, esc_html__('Bot username is not defined!', BTBP_TEXT_DOMAIN));
		if (stripos($bot_username, '@') === false)
			$bot_username = "@$bot_username";

		try {
			$telegram = new Telegram($bot_token, $bot_username);
			// TODO: $telegram->enableAdmins($bot->get_admin_ids());
			$telegram->addCommandsPaths([BTBP_DIR . '/app/Telegram/Commands']);
			$telegram->enableMySql();
			$telegram->enableLogging();
			$telegram->enableLimiter(['enabled' => true]);

			$serverResponse = $telegram->handleGetUpdates();
			if ($serverResponse->isOk()) return $this->get_rest_reponse(200);

			return $this->get_rest_reponse(502, $serverResponse->printError(true));
		} catch (TelegramException $e) {
			TelegramLog::error($e);

			return $this->get_rest_reponse(502, esc_html__('Error on initializing the bot!', BTBP_TEXT_DOMAIN));
		} catch (TelegramLogException $e) {
			return $this->get_rest_reponse(502, esc_html__('Error on logging the exception!', BTBP_TEXT_DOMAIN));
		}
	}
}
