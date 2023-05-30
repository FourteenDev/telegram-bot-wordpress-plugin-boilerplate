<?php namespace BoilerplateTelegramPlugin\API\Endpoints;

use Longman\TelegramBot\TelegramLog;
use BoilerplateTelegramPlugin\API\BaseEndpoint;
use BoilerplateTelegramPlugin\Telegram\ExtendedClasses\Telegram;

class GetMessage extends BaseEndpoint
{
	public static $instance = NULL;

	public $namespace 	= 'btbp/v1/';
	public $route 		= 'get_message';
	public $method 		= 'POST';

	public static function get_instance()
	{
		null === self::$instance && self::$instance = new self;
		return self::$instance;
	}

	/**
	 * Handles API request when the authorization was successful.
	 *
	 * @param	\WP_REST_Request	$request	The current matched request object.
	 *
	 * @return	\WP_REST_Response
	 */
	public function handle($request)
	{
		$telegram = BTBP()->helper()->instantiate_telegram();
		if (!$telegram instanceof Telegram)
			return $this->get_rest_reponse(502, $telegram);

		try {
			if ($telegram->handle()) return $this->get_rest_reponse(200);
			else return $this->get_rest_reponse(502);
		} catch (\Exception $e) {
			TelegramLog::error($e);

			return $this->get_rest_reponse(502, esc_html__('Error on handling the updates!', BTBP_TEXT_DOMAIN));
		}
	}
}
