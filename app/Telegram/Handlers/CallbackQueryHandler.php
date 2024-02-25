<?php namespace TelegramPluginBoilerplate\Telegram\Handlers;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Update;
use TelegramPluginBoilerplate\Telegram\ExtendedClasses\Request;

/**
 * The class that handles the callback queries.
 */
class CallbackQueryHandler
{
	/**
	 * Handles the callback query passed with the update.
	 *
	 * @param	Telegram		$telegram	The Telegram object.
	 * @param	Update			$update		The Update object.
	 *
	 * @return	ServerResponse
	 */
	public static function handleCallbackQuery($telegram, $update)
	{
		if ($invalidReason = self::getInvalidCallbackError($telegram, $update))
		{
			return Request::answerCallbackQuery(
				array(
					'callback_query_id' => $update->getCallbackQuery()->getId(),
					'text' 				=> $invalidReason,
					'show_alert' 		=> true,
				)
			);
		}

		return self::handleCallbackData($telegram, $update);
	}

	/**
	 * Checks if the callback query is valid or not.
	 *
	 * Invalid scenarios:
	 * 	- Nothing yet :D
	 *
	 * @param	Telegram	$telegram	The Telegram object.
	 * @param	Update		$update		The Update object.
	 *
	 * @return	string|null				Why the callback is invalid? (`null` means the callback was valid)
	 */
	private static function getInvalidCallbackError($telegram, $update)
	{
		/* if ($update->getCallbackQuery()->getMessage()->getChat()->getId() !== SOMETHING)
			return esc_html__('Invalid for some reason.', FDTBWPB_TEXT_DOMAIN); */

		// Run any other updates and database queries here

		/* if ($message = intval($update->getCallbackQuery()->getMessage()->getMessageId()))
		{
			// Some shit with the message
		} */

		return null;
	}

	/**
	 * Handles the callback data.
	 *
	 * @param	Telegram		$telegram	The Telegram object.
	 * @param	Update			$update		The Update object.
	 *
	 * @return	ServerResponse
	 */
	private static function handleCallbackData($telegram, $update)
	{
		if (!$update->getCallbackQuery()) return Request::emptyResponse();

		$callbackData = $update->getCallbackQuery()->getData();
		if (empty($callbackData) || stripos($callbackData, ':') === false)
		{
			return Request::answerCallbackQuery(
				array(
					'callback_query_id' => $update->getCallbackQuery()->getId(),
					'text' 				=> esc_html__('Invalid callback!', FDTBWPB_TEXT_DOMAIN),
					'show_alert' 		=> true,
				)
			);
		}

		// Callback syntax: COMMAND:VALUE
		$callbackCommand = explode(':', $callbackData)[0];

		foreach (glob(__DIR__ . '/../CallbackQueries/*Callback.php') as $file)
		{
			// We can't use __NAMESPACE__ here, because the callback queries are in another folder
			$class 				= '\\TelegramPluginBoilerplate\\Telegram\\CallbackQueries\\' . basename($file, '.php');
			$classCommandName 	= substr(basename($file, '.php'), 0, -8);

			if (class_exists($class) && mb_strtolower($classCommandName) === mb_strtolower($callbackCommand))
			{
				$class = new $class($telegram, $update);
				return $class->execute();
			}
		}

		// If we reach here, it means that the callback query wasn't defined

		return Request::answerCallbackQuery(
			array(
				'callback_query_id' => $update->getCallbackQuery()->getId(),
				'text' 				=> esc_html__('Invalid button!', FDTBWPB_TEXT_DOMAIN),
				'show_alert' 		=> true,
			)
		);
	}
}
