<?php namespace BoilerplateTelegramPlugin;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Exception\TelegramLogException;
use Longman\TelegramBot\TelegramLog;
use BoilerplateTelegramPlugin\Telegram\ExtendedClasses\Telegram;

class Helper
{
	public static $instance = null;

	public static function get_instance()
	{
		null === self::$instance && self::$instance = new self;
		return self::$instance;
	}

	/**
	 * Instantiates and returns Telegram object.
	 *
	 * @return	Telegram|string		Returns the error on failure.
	 */
	public function instantiate_telegram()
	{
		if (empty($bot_token = BTBP()->option('bot_token')))
			return esc_html__('Bot token is not defined!', BTBP_TEXT_DOMAIN);

		if (empty($bot_username = BTBP()->option('bot_username')))
			return esc_html__('Bot username is not defined!', BTBP_TEXT_DOMAIN);
		if (stripos($bot_username, '@') === false)
			$bot_username = "@$bot_username";

		try {
			$telegram = new Telegram($bot_token, $bot_username);
			// TODO: $telegram->enableAdmins($bot->get_admin_ids());
			$telegram->addCommandsPaths([BTBP_DIR . '/app/Telegram/Commands']);
			$telegram->enableMySql();
			$telegram->enableLogging();
			$telegram->enableLimiter(['enabled' => true]);
		} catch (TelegramException $e) {
			TelegramLog::error($e);

			return esc_html__('Error on initializing the bot!', BTBP_TEXT_DOMAIN);
		} catch (TelegramLogException $e) {
			return esc_html__('Error on logging the exception!', BTBP_TEXT_DOMAIN);
		}

		return $telegram;
	}
}
