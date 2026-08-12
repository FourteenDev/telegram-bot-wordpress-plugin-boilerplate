<?php

namespace TelegramPluginBoilerplate\Services;

/**
 * Handles all admin notices, warnings, and success messages in a centralized way.
 *
 * Use this class anywhere like this:
 * `\TelegramPluginBoilerplate\Services\NoticeManager::success('Test');`
 */
class NoticeManager
{
	public const ERROR   = 'error';
	public const INFO    = 'info';
	public const SUCCESS = 'success';
	public const WARNING = 'warning';

	/**
	 * Stored notices.
	 *
	 * @var	array<string, array<string, mixed>>
	 */
	private static array $notices = [];

	/**
	 * Initializes the notice manager.
	 *
	 * @return	void
	 */
	public static function init(): void
	{
		add_action('admin_notices', [self::class, 'displayNotices']);
	}

	/**
	 * Adds a notice.
	 *
	 * @param	string	$type			Notice type.
	 * @param	string	$message		Notice message.
	 * @param	bool	$dismissible	Whether the notice is dismissible.
	 *
	 * @return	void
	 *
	 * @todo	Fix `$dismissible` via JavaScript.
	 */
	private static function add(string $type, string $message, bool $dismissible = false): void
	{
		$notice = [
			'id'          => uniqid(FDTBWPB_MENUS_SLUG . '_notice_', true),
			'type'        => sanitize_key($type),
			'message'     => wp_kses_post($message),
			'dismissible' => filter_var($dismissible, FILTER_VALIDATE_BOOLEAN),
			'timestamp'   => time(),
		];
		self::$notices[] = $notice;

		// Also store in WordPress options for persistence across page loads
		$storedNotices = get_option(FDTBWPB_MENUS_SLUG . '_notices', []);
		$storedNotices[] = $notice;
		update_option(FDTBWPB_MENUS_SLUG . '_notices', $storedNotices);
	}

	/**
	 * Adds an error notice.
	 *
	 * @param	string	$message		Notice message.
	 * @param	bool	$dismissible	Whether the notice is dismissible.
	 *
	 * @return	void
	 */
	public static function error(string $message, bool $dismissible = false): void
	{
		self::add(self::ERROR, $message, $dismissible);
	}

	/**
	 * Adds an info notice.
	 *
	 * @param	string	$message		Notice message.
	 * @param	bool	$dismissible	Whether the notice is dismissible.
	 *
	 * @return	void
	 */
	public static function info(string $message, bool $dismissible = false): void
	{
		self::add(self::INFO, $message, $dismissible);
	}

	/**
	 * Adds a success notice.
	 *
	 * @param	string	$message		Notice message.
	 * @param	bool	$dismissible	Whether the notice is dismissible.
	 *
	 * @return	void
	 */
	public static function success(string $message, bool $dismissible = false): void
	{
		self::add(self::SUCCESS, $message, $dismissible);
	}

	/**
	 * Adds a warning notice.
	 *
	 * @param	string	$message		Notice message.
	 * @param	bool	$dismissible	Whether the notice is dismissible.
	 *
	 * @return	void
	 */
	public static function warning(string $message, bool $dismissible = false): void
	{
		self::add(self::WARNING, $message, $dismissible);
	}

	/**
	 * Displays all notices.
	 *
	 * @return	void
	 */
	public static function displayNotices(): void
	{
		$storedNotices = get_option(FDTBWPB_MENUS_SLUG . '_notices', []);
		$allNotices    = array_merge(self::$notices, $storedNotices);

		// Remove duplicates
		$allNotices = array_unique($allNotices, SORT_REGULAR);

		foreach ($allNotices as $notice)
			self::displayNotice($notice);

		self::clearAll();
	}

	/**
	 * Displays a single notice.
	 *
	 * @param	array<string, mixed>	$notice	Notice data.
	 *
	 * @return	void
	 */
	private static function displayNotice(array $notice): void
	{
		?>
		<div class="notice notice-<?php echo esc_attr($notice['type']); ?> <?php echo esc_attr($notice['dismissible'] ? 'is-dismissible' : ''); ?>" <?php echo $notice['dismissible'] ? 'data-notice-id="' . esc_attr($notice['id']) . '"' : ''; ?>>
			<p><?php echo wp_kses_post($notice['message']); ?></p>
			<?php if ($notice['dismissible']) : ?>
				<button type="button" class="notice-dismiss" data-notice-id="<?php echo esc_attr($notice['id']); ?>">
					<span class="screen-reader-text"><?php esc_html_e('Dismiss this notice.', 'telegram-plugin-boilerplate'); ?></span>
				</button>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Dismisses a notice.
	 *
	 * @param	string	$noticeId
	 *
	 * @return	void
	 */
	public static function dismiss(string $noticeId): void
	{
		// Remove from current session
		self::$notices = array_filter(self::$notices, function($notice) use ($noticeId) { return $notice['id'] !== $noticeId; });

		// Remove from stored notices
		$storedNotices = get_option(FDTBWPB_MENUS_SLUG . '_notices', []);
		$storedNotices = array_filter($storedNotices, function($notice) use ($noticeId) { return $notice['id'] !== $noticeId; });
		update_option(FDTBWPB_MENUS_SLUG . '_notices', $storedNotices);
	}

	/**
	 * Clears all notices.
	 *
	 * @return	void
	 */
	public static function clearAll(): void
	{
		self::$notices = [];
		delete_option(FDTBWPB_MENUS_SLUG . '_notices');
	}
}
