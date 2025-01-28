<?php

namespace TelegramPluginBoilerplate\API\Endpoints;

use Longman\TelegramBot\TelegramLog;
use TelegramPluginBoilerplate\API\BaseEndpoint;
use TelegramPluginBoilerplate\Helpers\TelegramHelper;
use TelegramPluginBoilerplate\Telegram\ExtendedClasses\Telegram;

class GetMessage extends BaseEndpoint
{
	public static $instance = null;

	public $namespace = 'fdtbwpb/v1/';
	public $route     = 'get-message';
	public $method    = 'POST';

	public static function getInstance()
	{
		self::$instance === null && self::$instance = new self;
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
		$telegram = TelegramHelper::instantiateTelegram();
		if (!$telegram instanceof Telegram)
			return $this->getRestReponse(502, $telegram);

		try {
			if ($telegram->handle()) return $this->getRestReponse(200);
			else return $this->getRestReponse(502);
		} catch (\Exception $e) {
			TelegramLog::error($e);

			return $this->getRestReponse(502, esc_html__('Error on handling the updates!', FDTBWPB_TEXT_DOMAIN));
		}
	}
}
