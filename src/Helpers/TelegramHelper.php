<?php

namespace TelegramPluginBoilerplate\Helpers;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Exception\TelegramLogException;
use Longman\TelegramBot\TelegramLog;
use TelegramPluginBoilerplate\Telegram\ExtendedClasses\Telegram;

class TelegramHelper
{
	/**
	 * Instantiates and returns Telegram object.
	 *
	 * @return	Telegram|string		Returns the error on failure.
	 */
	public static function instantiateTelegram()
	{
		if (empty($botToken = FDTBWPB()->option('bot_token')))
			return esc_html__('Bot token is not defined!', FDTBWPB_TEXT_DOMAIN);

		if (empty($botUsername = FDTBWPB()->option('bot_username')))
			return esc_html__('Bot username is not defined!', FDTBWPB_TEXT_DOMAIN);
		if (stripos($botUsername, '@') === false)
			$botUsername = "@$botUsername";

		try {
			$telegram = new Telegram($botToken, $botUsername);
			// TODO: $telegram->enableAdmins($bot->get_admin_ids());
			$telegram->addCommandsPaths([FDTBWPB_DIR . '/src/Telegram/Commands']);
			$telegram->enableMySql();
			$telegram->enableLogging();
			$telegram->enableLimiter(['enabled' => true]);

			if (!empty($admins = FDTBWPB()->option('admin_ids')))
				$telegram->enableAdmins(explode(',', $admins));
		} catch (TelegramException $e) {
			TelegramLog::error($e);

			return esc_html__('Error on initializing the bot!', FDTBWPB_TEXT_DOMAIN);
		} catch (TelegramLogException $e) {
			return esc_html__('Error on logging the exception!', FDTBWPB_TEXT_DOMAIN);
		}

		return $telegram;
	}
}
