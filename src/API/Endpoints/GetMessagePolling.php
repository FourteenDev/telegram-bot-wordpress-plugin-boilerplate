<?php

namespace TelegramPluginBoilerplate\API\Endpoints;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\TelegramLog;
use TelegramPluginBoilerplate\API\BaseEndpoint;
use TelegramPluginBoilerplate\Helpers\TelegramHelper;
use TelegramPluginBoilerplate\Telegram\ExtendedClasses\Telegram;

class GetMessagePolling extends BaseEndpoint
{
	public $namespace = 'fdtbwpb/v1/';
	public $route     = 'get-message-polling';
	public $method    = 'GET';

	/**
	 * Uses `getUpdates` method and fetches updates from Telegram.
	 *
	 * @param	\WP_REST_Request	$request	The current matched request object.
	 *
	 * @return	\WP_REST_Response
	 */
	public function handle($request): \WP_REST_Response
	{
		if (wp_get_environment_type() !== 'local')
			return $this->getRestResponse(401, esc_html__('Not allowed!', 'telegram-plugin-boilerplate'));

		$telegram = TelegramHelper::instantiateTelegram();
		if (!$telegram instanceof Telegram)
			return $this->getRestResponse(502, $telegram);

		try {
			$serverResponse = $telegram->handleGetUpdates();
			if ($serverResponse instanceof ServerResponse && $serverResponse->isOk())
				return $this->getRestResponse(200);

			return $this->getRestResponse(502, $serverResponse->printError(true));
		} catch (\Exception $e) {
			TelegramLog::error($e);

			return $this->getRestResponse(502, esc_html__('Error on handling the updates!', 'telegram-plugin-boilerplate'));
		}
	}
}
