<?php

namespace TelegramPluginBoilerplate\API\Endpoints;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\TelegramLog;
use TelegramPluginBoilerplate\API\BaseEndpoint;
use TelegramPluginBoilerplate\Helpers\TelegramHelper;
use TelegramPluginBoilerplate\Telegram\ExtendedClasses\Telegram;

class GetMessagePolling extends BaseEndpoint
{
	public static $instance = null;

	public $namespace = 'fdtbwpb/v1/';
	public $route     = 'get-message-polling';
	public $method    = 'GET';

	public static function getInstance()
	{
		self::$instance === null && self::$instance = new self;
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
			return $this->getRestReponse(401, esc_html__('Not allowed!', FDTBWPB_TEXT_DOMAIN));

		$telegram = TelegramHelper::instantiateTelegram();
		if (!$telegram instanceof Telegram)
			return $this->getRestReponse(502, $telegram);

		try {
			$serverResponse = $telegram->handleGetUpdates();
			if ($serverResponse instanceof ServerResponse && $serverResponse->isOk())
				return $this->getRestReponse(200);

			return $this->getRestReponse(502, $serverResponse->printError(true));
		} catch (\Exception $e) {
			TelegramLog::error($e);

			return $this->getRestReponse(502, esc_html__('Error on handling the updates!', FDTBWPB_TEXT_DOMAIN));
		}
	}
}
