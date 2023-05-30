<?php namespace BoilerplateTelegramPlugin\API\Endpoints;

use Longman\TelegramBot\Entities\ServerResponse;
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

		$telegram = BTBP()->helper()->instantiate_telegram();
		if (!$telegram instanceof Telegram)
			return $this->get_rest_reponse(502, $telegram);

		try {
			$serverResponse = $telegram->handleGetUpdates();
			if ($serverResponse instanceof ServerResponse && $serverResponse->isOk())
				return $this->get_rest_reponse(200);

			return $this->get_rest_reponse(502, $serverResponse->printError(true));
		} catch (\Exception $e) {
			TelegramLog::error($e);

			return $this->get_rest_reponse(502, esc_html__('Error on handling the updates!', BTBP_TEXT_DOMAIN));
		}
	}
}
