<?php

namespace TelegramPluginBoilerplate\API\Endpoints;

use Longman\TelegramBot\TelegramLog;
use TelegramPluginBoilerplate\API\BaseEndpoint;
use TelegramPluginBoilerplate\Helpers\TelegramHelper;
use TelegramPluginBoilerplate\Telegram\ExtendedClasses\Telegram;

class GetMessage extends BaseEndpoint
{
	public string $namespace = 'fdtbwpb/v1/';
	public string $route     = 'get-message';
	public string $method    = 'POST';

	/**
	 * Handles API request when the authorization was successful.
	 *
	 * @param	\WP_REST_Request	$request	The current matched request object.
	 *
	 * @return	\WP_REST_Response
	 */
	public function handle(\WP_REST_Request $request): \WP_REST_Response
	{
		$telegram = TelegramHelper::instantiateTelegram();
		if (!$telegram instanceof Telegram)
			return $this->getRestResponse(502, $telegram);

		try {
			if ($telegram->handle()) return $this->getRestResponse(200);
			else return $this->getRestResponse(502);
		} catch (\Exception $e) {
			TelegramLog::error($e);

			return $this->getRestResponse(502, esc_html__('Error on handling the updates!', 'telegram-plugin-boilerplate'));
		}
	}
}
