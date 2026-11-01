<?php

namespace TelegramPluginBoilerplate;

/**
 * Database query builder.
 */
class Model
{
	/** @var	\wpdb */
	private \wpdb $wpdb;

	/** @var	string */
	private string $charsetCollate;

	/** @var	string */
	private string $operation = '';

	/** @var	string */
	private string $table = '';

	/** @var	string */
	private string $fields = '*';

	/** @var	string */
	private string $whereRelation = 'AND';

	/** @var	array<string> */
	private array $whereClauses = [];

	/** @var	array<mixed> */
	private array $valuesToPrepare = [];

	/** @var	array<string> */
	private array $joinClauses = [];

	/**
	 * Set clauses for `INSERT` query.
	 *
	 * @var	array<array>
	 */
	private array $insertSetClauses = [];

	/**
	 * Set clauses for `UPDATE` query.
	 *
	 * @var	array<string>
	 */
	private array $updateSetClauses = [];

	/** @var	array<string> */
	private array $rawWhereClause = [];

	/** @var	string */
	private string $orderClause = '';

	/** @var	string */
	private string $limitClause = '';

	/** @var	string */
	private string $groupByClause = '';

	/** @var	string */
	private string $havingClause = '';

	/**
	 * Constructor.
	 *
	 * @global	\wpdb	$wpdb
	 */
	public function __construct()
	{
		global $wpdb;

		$this->wpdb           = $wpdb;
		$this->charsetCollate = $wpdb->get_charset_collate();

		add_action('plugins_loaded', [$this, 'initializeTables']);
	}

	/**
	 * Creates all the necessary tables if they haven't been created yet.
	 *
	 * Tables are based on `v0.82.0` of Longman PHP Telegram Bot library.
	 *
	 * @return	void
	 *
	 * @hooked	action: `plugins_loaded` - 10
	 */
	public function initializeTables(): void
	{
		if (trim(get_option(FDTBWPB_OPTIONS_KEY_DB_VERSION, '')) === trim(FDTBWPB_VERSION)) return;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Example query:
		/* dbDelta("CREATE TABLE `{$this->wpdb->prefix}some_table` (
			...
		) {$this->charsetCollate};"); */
		// Note: `dbDelta()` doesn't support `IF NOT EXISTS`

		$this->wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}fdtbwpb_user` (
				`id` BIGINT COMMENT 'Unique identifier for this user or bot',
				`is_bot` TINYINT(1) DEFAULT 0 COMMENT 'True, if this user is a bot',
				`first_name` CHAR(255) NOT NULL DEFAULT '' COMMENT 'User''s or bot''s first name',
				`last_name` CHAR(255) DEFAULT NULL COMMENT 'User''s or bot''s last name',
				`username` CHAR(191) DEFAULT NULL COMMENT 'User''s or bot''s username',
				`language_code` CHAR(10) DEFAULT NULL COMMENT 'IETF language tag of the user''s language',
				`is_premium` TINYINT(1) DEFAULT 0 COMMENT 'True, if this user is a Telegram Premium user',
				`added_to_attachment_menu` TINYINT(1) DEFAULT 0 COMMENT 'True, if this user added the bot to the attachment menu',
				`created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Entry date creation',
				`updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Entry date update',

				PRIMARY KEY (`id`),
				KEY `username` (`username`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"
		);

		$this->wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}fdtbwpb_chat` (
				`id` BIGINT COMMENT 'Unique identifier for this chat',
				`bot_username` CHAR(191) NOT NULL COMMENT 'Current bot''s username',
				`type` ENUM('private', 'group', 'supergroup', 'channel') NOT NULL COMMENT 'Type of chat, can be either private, group, supergroup or channel',
				`title` CHAR(255) DEFAULT '' COMMENT 'Title, for supergroups, channels and group chats',
				`username` CHAR(255) DEFAULT NULL COMMENT 'Username, for private chats, supergroups and channels if available',
				`first_name` CHAR(255) DEFAULT NULL COMMENT 'First name of the other party in a private chat',
				`last_name` CHAR(255) DEFAULT NULL COMMENT 'Last name of the other party in a private chat',
				`is_forum` TINYINT(1) DEFAULT 0 COMMENT 'True, if the supergroup chat is a forum (has topics enabled)',
				`all_members_are_administrators` TINYINT(1) DEFAULT 0 COMMENT 'True if a all members of this group are admins',
				`created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Entry date creation',
				`updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Entry date update',
				`old_id` BIGINT DEFAULT NULL COMMENT 'Unique chat identifier, this is filled when a group is converted to a supergroup',

				PRIMARY KEY (`id`, `bot_username`),
				KEY `old_id` (`old_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"
		);

		$this->wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}fdtbwpb_user_chat` (
				`user_id` BIGINT COMMENT 'Unique user identifier',
				`chat_id` BIGINT COMMENT 'Unique user or chat identifier',
				`bot_username` CHAR(191) NOT NULL COMMENT 'Current bot''s username',

				PRIMARY KEY (`user_id`, `chat_id`, `bot_username`)#,

				# FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
				# FOREIGN KEY (`chat_id`) REFERENCES `chat` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"
		);

		$this->wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}fdtbwpb_inline_query` (
				`id` BIGINT UNSIGNED COMMENT 'Unique identifier for this query',
				`user_id` BIGINT NULL COMMENT 'Unique user identifier',
				`bot_username` CHAR(191) DEFAULT NULL COMMENT 'Current bot''s username',
				`location` CHAR(255) NULL DEFAULT NULL COMMENT 'Location of the user',
				`query` TEXT NOT NULL COMMENT 'Text of the query',
				`offset` CHAR(255) NULL DEFAULT NULL COMMENT 'Offset of the result',
				`chat_type` CHAR(255) NULL DEFAULT NULL COMMENT 'Optional. Type of the chat, from which the inline query was sent.',
				`created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Entry date creation',

				PRIMARY KEY (`id`),
				KEY `user_id` (`user_id`)#,

				# FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"
		);

		$this->wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}fdtbwpb_chosen_inline_result` (
				`id` BIGINT UNSIGNED AUTO_INCREMENT COMMENT 'Unique identifier for this entry',
				`result_id` CHAR(255) NOT NULL DEFAULT '' COMMENT 'The unique identifier for the result that was chosen',
				`user_id` BIGINT NULL COMMENT 'The user that chose the result',
				`bot_username` CHAR(191) DEFAULT NULL COMMENT 'Current bot''s username',
				`location` CHAR(255) NULL DEFAULT NULL COMMENT 'Sender location, only for bots that require user location',
				`inline_message_id` CHAR(255) NULL DEFAULT NULL COMMENT 'Identifier of the sent inline message',
				`query` TEXT NOT NULL COMMENT 'The query that was used to obtain the result',
				`created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Entry date creation',

				PRIMARY KEY (`id`),
				KEY `user_id` (`user_id`)#,

				# FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"
		);

		$this->wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}fdtbwpb_message` (
				`chat_id` BIGINT COMMENT 'Unique chat identifier',
				`sender_chat_id` BIGINT COMMENT 'Sender of the message, sent on behalf of a chat',
				`id` BIGINT UNSIGNED COMMENT 'Unique message identifier',
				`message_thread_id` BIGINT(20) NULL DEFAULT NULL COMMENT 'Unique identifier of a message thread to which the message belongs; for supergroups only',
				`user_id` BIGINT NULL COMMENT 'Unique user identifier',
				`bot_username` CHAR(191) NOT NULL COMMENT 'Current bot''s username',
				`date` TIMESTAMP NULL DEFAULT NULL COMMENT 'Date the message was sent in timestamp format',
				`forward_from` BIGINT NULL DEFAULT NULL COMMENT 'Unique user identifier, sender of the original message',
				`forward_from_chat` BIGINT NULL DEFAULT NULL COMMENT 'Unique chat identifier, chat the original message belongs to',
				`forward_from_message_id` BIGINT NULL DEFAULT NULL COMMENT 'Unique chat identifier of the original message in the channel',
				`forward_signature` TEXT NULL DEFAULT NULL COMMENT 'For messages forwarded from channels, signature of the post author if present',
				`forward_sender_name` TEXT NULL DEFAULT NULL COMMENT 'Sender''s name for messages forwarded from users who disallow adding a link to their account in forwarded messages',
				`forward_date` TIMESTAMP NULL DEFAULT NULL COMMENT 'date the original message was sent in timestamp format',
				`is_topic_message` TINYINT(1) DEFAULT 0 COMMENT 'True, if the message is sent to a forum topic',
				`is_automatic_forward` TINYINT(1) DEFAULT 0 COMMENT 'True, if the message is a channel post that was automatically forwarded to the connected discussion group',
				`reply_to_chat` BIGINT NULL DEFAULT NULL COMMENT 'Unique chat identifier',
				`reply_to_message` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Message that this message is reply to',
				`via_bot` BIGINT NULL DEFAULT NULL COMMENT 'Optional. Bot through which the message was sent',
				`edit_date` TIMESTAMP NULL DEFAULT NULL COMMENT 'Date the message was last edited in Unix time',
				`has_protected_content` TINYINT(1) DEFAULT 0 COMMENT 'True, if the message can''t be forwarded',
				`media_group_id` TEXT COMMENT 'The unique identifier of a media message group this message belongs to',
				`author_signature` TEXT COMMENT 'Signature of the post author for messages in channels',
				`text` TEXT COMMENT 'For text messages, the actual UTF-8 text of the message max message length 4096 char utf8mb4',
				`entities` TEXT COMMENT 'For text messages, special entities like usernames, URLs, bot commands, etc. that appear in the text',
				`caption_entities` TEXT COMMENT 'For messages with a caption, special entities like usernames, URLs, bot commands, etc. that appear in the caption',
				`audio` TEXT COMMENT 'Audio object. Message is an audio file, information about the file',
				`document` TEXT COMMENT 'Document object. Message is a general file, information about the file',
				`animation` TEXT COMMENT 'Message is an animation, information about the animation',
				`game` TEXT COMMENT 'Game object. Message is a game, information about the game',
				`photo` TEXT COMMENT 'Array of PhotoSize objects. Message is a photo, available sizes of the photo',
				`sticker` TEXT COMMENT 'Sticker object. Message is a sticker, information about the sticker',
				`story` TEXT DEFAULT NULL COMMENT 'Story object. Message is a forwarded story',
				`video` TEXT COMMENT 'Video object. Message is a video, information about the video',
				`voice` TEXT COMMENT 'Voice Object. Message is a Voice, information about the Voice',
				`video_note` TEXT COMMENT 'VoiceNote Object. Message is a Video Note, information about the Video Note',
				`caption` TEXT COMMENT	'For message with caption, the actual UTF-8 text of the caption',
				`has_media_spoiler` TINYINT(1) DEFAULT 0 COMMENT 'True, if the message media is covered by a spoiler animation',
				`contact` TEXT COMMENT 'Contact object. Message is a shared contact, information about the contact',
				`location` TEXT COMMENT 'Location object. Message is a shared location, information about the location',
				`venue` TEXT COMMENT 'Venue object. Message is a Venue, information about the Venue',
				`poll` TEXT COMMENT 'Poll object. Message is a native poll, information about the poll',
				`dice` TEXT COMMENT 'Message is a dice with random value from 1 to 6',
				`new_chat_members` TEXT COMMENT 'List of unique user identifiers, new member(s) were added to the group, information about them (one of these members may be the bot itself)',
				`left_chat_member` BIGINT NULL DEFAULT NULL COMMENT 'Unique user identifier, a member was removed from the group, information about them (this member may be the bot itself)',
				`new_chat_title` CHAR(255) DEFAULT NULL COMMENT 'A chat title was changed to this value',
				`new_chat_photo` TEXT COMMENT 'Array of PhotoSize objects. A chat photo was change to this value',
				`delete_chat_photo` TINYINT(1) DEFAULT 0 COMMENT 'Informs that the chat photo was deleted',
				`group_chat_created` TINYINT(1) DEFAULT 0 COMMENT 'Informs that the group has been created',
				`supergroup_chat_created` TINYINT(1) DEFAULT 0 COMMENT 'Informs that the supergroup has been created',
				`channel_chat_created` TINYINT(1) DEFAULT 0 COMMENT 'Informs that the channel chat has been created',
				`message_auto_delete_timer_changed` TEXT COMMENT 'MessageAutoDeleteTimerChanged object. Message is a service message: auto-delete timer settings changed in the chat',
				`migrate_to_chat_id` BIGINT NULL DEFAULT NULL COMMENT 'Migrate to chat identifier. The group has been migrated to a supergroup with the specified identifier',
				`migrate_from_chat_id` BIGINT NULL DEFAULT NULL COMMENT 'Migrate from chat identifier. The supergroup has been migrated from a group with the specified identifier',
				`pinned_message` TEXT NULL COMMENT 'Message object. Specified message was pinned',
				`invoice` TEXT NULL COMMENT 'Message is an invoice for a payment, information about the invoice',
				`successful_payment` TEXT NULL COMMENT 'Message is a service message about a successful payment, information about the payment',
				`user_shared` TEXT DEFAULT NULL COMMENT 'Optional. Service message: a user was shared with the bot',
				`chat_shared` TEXT DEFAULT NULL COMMENT 'Optional. Service message: a chat was shared with the bot',
				`connected_website` TEXT NULL COMMENT 'The domain name of the website on which the user has logged in.',
				`write_access_allowed` TEXT DEFAULT NULL COMMENT 'Service message: the user allowed the bot added to the attachment menu to write messages',
				`passport_data` TEXT NULL COMMENT 'Telegram Passport data',
				`proximity_alert_triggered` TEXT NULL COMMENT 'Service message. A user in the chat triggered another user''s proximity alert while sharing Live Location.',
				`forum_topic_created` TEXT NULL DEFAULT NULL COMMENT 'Service message: forum topic created',
				`forum_topic_edited` TEXT DEFAULT NULL COMMENT 'Service message: forum topic edited',
				`forum_topic_closed` TEXT NULL DEFAULT NULL COMMENT 'Service message: forum topic closed',
				`forum_topic_reopened` TEXT NULL DEFAULT NULL COMMENT 'Service message: forum topic reopened',
				`general_forum_topic_hidden` TEXT DEFAULT NULL COMMENT 'Service message: the General forum topic hidden',
				`general_forum_topic_unhidden` TEXT DEFAULT NULL COMMENT 'Service message: the General forum topic unhidden',
				`video_chat_scheduled` TEXT COMMENT 'Service message: video chat scheduled',
				`video_chat_started` TEXT COMMENT 'Service message: video chat started',
				`video_chat_ended` TEXT COMMENT 'Service message: video chat ended',
				`video_chat_participants_invited` TEXT COMMENT 'Service message: new participants invited to a video chat',
				`web_app_data` TEXT COMMENT 'Service message: data sent by a Web App',
				`reply_markup` TEXT NULL COMMENT 'Inline keyboard attached to the message',

				PRIMARY KEY (`chat_id`, `id`, `bot_username`),
				KEY `user_id` (`user_id`),
				KEY `forward_from` (`forward_from`),
				KEY `forward_from_chat` (`forward_from_chat`),
				KEY `reply_to_chat` (`reply_to_chat`),
				KEY `reply_to_message` (`reply_to_message`),
				KEY `via_bot` (`via_bot`),
				KEY `left_chat_member` (`left_chat_member`),
				KEY `migrate_from_chat_id` (`migrate_from_chat_id`),
				KEY `migrate_to_chat_id` (`migrate_to_chat_id`)#,

				# FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
				# FOREIGN KEY (`chat_id`) REFERENCES `chat` (`id`),
				# FOREIGN KEY (`forward_from`) REFERENCES `user` (`id`),
				# FOREIGN KEY (`forward_from_chat`) REFERENCES `chat` (`id`),
				# FOREIGN KEY (`reply_to_chat`, `reply_to_message`) REFERENCES `message` (`chat_id`, `id`),
				# FOREIGN KEY (`via_bot`) REFERENCES `user` (`id`),
				# FOREIGN KEY (`left_chat_member`) REFERENCES `user` (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"
		);

		$this->wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}fdtbwpb_bot_message` (
				`chat_id` BIGINT COMMENT 'Unique chat identifier',
				`sender_chat_id` BIGINT COMMENT 'Sender of the message, sent on behalf of a chat',
				`id` BIGINT UNSIGNED COMMENT 'Unique message identifier',
				`bot_username` CHAR(191) NOT NULL COMMENT 'Current bot''s username',
				`date` TIMESTAMP NULL DEFAULT NULL COMMENT 'Date the message was sent in timestamp format',
				`forward_from` BIGINT NULL DEFAULT NULL COMMENT 'Unique user identifier, sender of the original message',
				`forward_from_chat` BIGINT NULL DEFAULT NULL COMMENT 'Unique chat identifier, chat the original message belongs to',
				`forward_from_message_id` BIGINT NULL DEFAULT NULL COMMENT 'Unique chat identifier of the original message in the channel',
				`forward_signature` TEXT NULL DEFAULT NULL COMMENT 'For messages forwarded from channels, signature of the post author if present',
				`forward_sender_name` TEXT NULL DEFAULT NULL COMMENT 'Sender''s name for messages forwarded from users who disallow adding a link to their account in forwarded messages',
				`forward_date` TIMESTAMP NULL DEFAULT NULL COMMENT 'date the original message was sent in timestamp format',
				`is_automatic_forward` TINYINT(1) DEFAULT 0 COMMENT 'True, if the message is a channel post that was automatically forwarded to the connected discussion group',
				`reply_to_chat` BIGINT NULL DEFAULT NULL COMMENT 'Unique chat identifier',
				`reply_to_message` BIGINT UNSIGNED DEFAULT NULL COMMENT 'Message that this message is reply to',
				`via_bot` BIGINT NULL DEFAULT NULL COMMENT 'Optional. Bot through which the message was sent',
				`edit_date` TIMESTAMP NULL DEFAULT NULL COMMENT 'Date the message was last edited in Unix time',
				`has_protected_content` TINYINT(1) DEFAULT 0 COMMENT 'True, if the message can''t be forwarded',
				`media_group_id` TEXT COMMENT 'The unique identifier of a media message group this message belongs to',
				`author_signature` TEXT COMMENT 'Signature of the post author for messages in channels',
				`text` TEXT COMMENT 'For text messages, the actual UTF-8 text of the message max message length 4096 char utf8mb4',
				`entities` TEXT COMMENT 'For text messages, special entities like usernames, URLs, bot commands, etc. that appear in the text',
				`caption_entities` TEXT COMMENT 'For messages with a caption, special entities like usernames, URLs, bot commands, etc. that appear in the caption',
				`audio` TEXT COMMENT 'Audio object. Message is an audio file, information about the file',
				`document` TEXT COMMENT 'Document object. Message is a general file, information about the file',
				`animation` TEXT COMMENT 'Message is an animation, information about the animation',
				`game` TEXT COMMENT 'Game object. Message is a game, information about the game',
				`photo` TEXT COMMENT 'Array of PhotoSize objects. Message is a photo, available sizes of the photo',
				`sticker` TEXT COMMENT 'Sticker object. Message is a sticker, information about the sticker',
				`video` TEXT COMMENT 'Video object. Message is a video, information about the video',
				`voice` TEXT COMMENT 'Voice Object. Message is a Voice, information about the Voice',
				`video_note` TEXT COMMENT 'VoiceNote Object. Message is a Video Note, information about the Video Note',
				`caption` TEXT COMMENT	'For message with caption, the actual UTF-8 text of the caption',
				`contact` TEXT COMMENT 'Contact object. Message is a shared contact, information about the contact',
				`location` TEXT COMMENT 'Location object. Message is a shared location, information about the location',
				`venue` TEXT COMMENT 'Venue object. Message is a Venue, information about the Venue',
				`poll` TEXT COMMENT 'Poll object. Message is a native poll, information about the poll',
				`dice` TEXT COMMENT 'Message is a dice with random value from 1 to 6',
				`new_chat_members` TEXT COMMENT 'List of unique user identifiers, new member(s) were added to the group, information about them (one of these members may be the bot itself)',
				`left_chat_member` BIGINT NULL DEFAULT NULL COMMENT 'Unique user identifier, a member was removed from the group, information about them (this member may be the bot itself)',
				`new_chat_title` CHAR(255) DEFAULT NULL COMMENT 'A chat title was changed to this value',
				`new_chat_photo` TEXT COMMENT 'Array of PhotoSize objects. A chat photo was change to this value',
				`delete_chat_photo` TINYINT(1) DEFAULT 0 COMMENT 'Informs that the chat photo was deleted',
				`group_chat_created` TINYINT(1) DEFAULT 0 COMMENT 'Informs that the group has been created',
				`supergroup_chat_created` TINYINT(1) DEFAULT 0 COMMENT 'Informs that the supergroup has been created',
				`channel_chat_created` TINYINT(1) DEFAULT 0 COMMENT 'Informs that the channel chat has been created',
				`message_auto_delete_timer_changed` TEXT COMMENT 'MessageAutoDeleteTimerChanged object. Message is a service message: auto-delete timer settings changed in the chat',
				`migrate_to_chat_id` BIGINT NULL DEFAULT NULL COMMENT 'Migrate to chat identifier. The group has been migrated to a supergroup with the specified identifier',
				`migrate_from_chat_id` BIGINT NULL DEFAULT NULL COMMENT 'Migrate from chat identifier. The supergroup has been migrated from a group with the specified identifier',
				`pinned_message` TEXT NULL COMMENT 'Message object. Specified message was pinned',
				`invoice` TEXT NULL COMMENT 'Message is an invoice for a payment, information about the invoice',
				`successful_payment` TEXT NULL COMMENT 'Message is a service message about a successful payment, information about the payment',
				`connected_website` TEXT NULL COMMENT 'The domain name of the website on which the user has logged in.',
				`passport_data` TEXT NULL COMMENT 'Telegram Passport data',
				`proximity_alert_triggered` TEXT NULL COMMENT 'Service message. A user in the chat triggered another user''s proximity alert while sharing Live Location.',
				`video_chat_scheduled` TEXT COMMENT 'Service message: video chat scheduled',
				`video_chat_started` TEXT COMMENT 'Service message: video chat started',
				`video_chat_ended` TEXT COMMENT 'Service message: video chat ended',
				`video_chat_participants_invited` TEXT COMMENT 'Service message: new participants invited to a video chat',
				`web_app_data` TEXT COMMENT 'Service message: data sent by a Web App',
				`reply_markup` TEXT NULL COMMENT 'Inline keyboard attached to the message',

				PRIMARY KEY (`chat_id`, `id`, `bot_username`),
				KEY `forward_from` (`forward_from`),
				KEY `forward_from_chat` (`forward_from_chat`),
				KEY `reply_to_chat` (`reply_to_chat`),
				KEY `reply_to_message` (`reply_to_message`),
				KEY `via_bot` (`via_bot`),
				KEY `left_chat_member` (`left_chat_member`),
				KEY `migrate_from_chat_id` (`migrate_from_chat_id`),
				KEY `migrate_to_chat_id` (`migrate_to_chat_id`)#,

				# FOREIGN KEY (`chat_id`) REFERENCES `chat` (`id`),
				# FOREIGN KEY (`forward_from`) REFERENCES `user` (`id`),
				# FOREIGN KEY (`forward_from_chat`) REFERENCES `chat` (`id`),
				# FOREIGN KEY (`reply_to_chat`, `reply_to_message`) REFERENCES `message` (`chat_id`, `id`),
				# FOREIGN KEY (`via_bot`) REFERENCES `user` (`id`),
				# FOREIGN KEY (`left_chat_member`) REFERENCES `user` (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"
		);

		$this->wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}fdtbwpb_edited_message` (
				`id` BIGINT UNSIGNED AUTO_INCREMENT COMMENT 'Unique identifier for this entry',
				`chat_id` BIGINT COMMENT 'Unique chat identifier',
				`message_id` BIGINT UNSIGNED COMMENT 'Unique message identifier',
				`user_id` BIGINT NULL COMMENT 'Unique user identifier',
				`bot_username` CHAR(191) DEFAULT NULL COMMENT 'Current bot''s username',
				`edit_date` TIMESTAMP NULL DEFAULT NULL COMMENT 'Date the message was edited in timestamp format',
				`text` TEXT COMMENT 'For text messages, the actual UTF-8 text of the message max message length 4096 char utf8',
				`entities` TEXT COMMENT 'For text messages, special entities like usernames, URLs, bot commands, etc. that appear in the text',
				`caption` TEXT COMMENT	'For message with caption, the actual UTF-8 text of the caption',

				PRIMARY KEY (`id`),
				KEY `chat_id` (`chat_id`),
				KEY `message_id` (`message_id`),
				KEY `user_id` (`user_id`)#,

				# FOREIGN KEY (`chat_id`) REFERENCES `chat` (`id`),
				# FOREIGN KEY (`chat_id`, `message_id`) REFERENCES `message` (`chat_id`, `id`),
				# FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"
		);

		$this->wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}fdtbwpb_callback_query` (
				`id` BIGINT UNSIGNED COMMENT 'Unique identifier for this query',
				`user_id` BIGINT NULL COMMENT 'Unique user identifier',
				`chat_id` BIGINT NULL COMMENT 'Unique chat identifier',
				`message_id` BIGINT UNSIGNED COMMENT 'Unique message identifier',
				`bot_username` CHAR(191) DEFAULT NULL COMMENT 'Current bot''s username',
				`inline_message_id` CHAR(255) NULL DEFAULT NULL COMMENT 'Identifier of the message sent via the bot in inline mode, that originated the query',
				`chat_instance` CHAR(255) NOT NULL DEFAULT '' COMMENT 'Global identifier, uniquely corresponding to the chat to which the message with the callback button was sent',
				`data` CHAR(255) NOT NULL DEFAULT '' COMMENT 'Data associated with the callback button',
				`game_short_name` CHAR(255) NOT NULL DEFAULT '' COMMENT 'Short name of a Game to be returned, serves as the unique identifier for the game',
				`created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Entry date creation',

				PRIMARY KEY (`id`),
				KEY `user_id` (`user_id`),
				KEY `chat_id` (`chat_id`),
				KEY `message_id` (`message_id`)#,

				# FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
				# FOREIGN KEY (`chat_id`, `message_id`) REFERENCES `message` (`chat_id`, `id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"
		);

		$this->wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}fdtbwpb_shipping_query` (
				`id` BIGINT UNSIGNED COMMENT 'Unique query identifier',
				`user_id` BIGINT COMMENT 'User who sent the query',
				`bot_username` CHAR(191) DEFAULT NULL COMMENT 'Current bot''s username',
				`invoice_payload` CHAR(255) NOT NULL DEFAULT '' COMMENT 'Bot specified invoice payload',
				`shipping_address` CHAR(255) NOT NULL DEFAULT '' COMMENT 'User specified shipping address',
				`created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Entry date creation',

				PRIMARY KEY (`id`),
				KEY `user_id` (`user_id`)#,

				# FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"
		);

		$this->wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}fdtbwpb_pre_checkout_query` (
				`id` BIGINT UNSIGNED COMMENT 'Unique query identifier',
				`user_id` BIGINT COMMENT 'User who sent the query',
				`bot_username` CHAR(191) DEFAULT NULL COMMENT 'Current bot''s username',
				`currency` CHAR(3) COMMENT 'Three-letter ISO 4217 currency code',
				`total_amount` BIGINT COMMENT 'Total price in the smallest units of the currency',
				`invoice_payload` CHAR(255) NOT NULL DEFAULT '' COMMENT 'Bot specified invoice payload',
				`shipping_option_id` CHAR(255) NULL COMMENT 'Identifier of the shipping option chosen by the user',
				`order_info` TEXT NULL COMMENT 'Order info provided by the user',
				`created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Entry date creation',

				PRIMARY KEY (`id`),
				KEY `user_id` (`user_id`)#,

				# FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"
		);

		$this->wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}fdtbwpb_poll` (
				`id` BIGINT UNSIGNED COMMENT 'Unique poll identifier',
				`bot_username` CHAR(191) DEFAULT NULL COMMENT 'Current bot''s username',
				`question` text NOT NULL COMMENT 'Poll question',
				`options` text NOT NULL COMMENT 'List of poll options',
				`total_voter_count` int UNSIGNED COMMENT 'Total number of users that voted in the poll',
				`is_closed` TINYINT(1) DEFAULT 0 COMMENT 'True, if the poll is closed',
				`is_anonymous` TINYINT(1) DEFAULT 1 COMMENT 'True, if the poll is anonymous',
				`type` char(255) COMMENT 'Poll type, currently can be “regular” or “quiz”',
				`allows_multiple_answers` TINYINT(1) DEFAULT 0 COMMENT 'True, if the poll allows multiple answers',
				`correct_option_id` int UNSIGNED COMMENT '0-based identifier of the correct answer option. Available only for polls in the quiz mode, which are closed, or was sent (not forwarded) by the bot or to the private chat with the bot.',
				`explanation` varchar(255) DEFAULT NULL COMMENT 'Text that is shown when a user chooses an incorrect answer or taps on the lamp icon in a quiz-style poll, 0-200 characters',
				`explanation_entities` text DEFAULT NULL COMMENT 'Special entities like usernames, URLs, bot commands, etc. that appear in the explanation',
				`open_period` int UNSIGNED DEFAULT NULL COMMENT 'Amount of time in seconds the poll will be active after creation',
				`close_date` TIMESTAMP NULL DEFAULT NULL COMMENT 'Point in time (Unix timestamp) when the poll will be automatically closed',
				`created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Entry date creation',

				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"
		);

		$this->wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}fdtbwpb_poll_answer` (
				`poll_id` BIGINT UNSIGNED COMMENT 'Unique poll identifier',
				`user_id` BIGINT NOT NULL COMMENT 'The user, who changed the answer to the poll',
				`bot_username` CHAR(191) DEFAULT NULL COMMENT 'Current bot''s username',
				`option_ids` text NOT NULL COMMENT '0-based identifiers of answer options, chosen by the user. May be empty if the user retracted their vote.',
				`created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Entry date creation',

				PRIMARY KEY (`poll_id`, `user_id`)#,

				# FOREIGN KEY (`poll_id`) REFERENCES `poll` (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"
		);

		$this->wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}fdtbwpb_chat_member_updated` (
				`id` BIGINT UNSIGNED AUTO_INCREMENT COMMENT 'Unique identifier for this entry',
				`chat_id` BIGINT NOT NULL COMMENT 'Chat the user belongs to',
				`user_id` BIGINT NOT NULL COMMENT 'Performer of the action, which resulted in the change',
				`bot_username` CHAR(191) DEFAULT NULL COMMENT 'Current bot''s username',
				`date` TIMESTAMP NOT NULL COMMENT 'Date the change was done in Unix time',
				`old_chat_member` TEXT NOT NULL COMMENT 'Previous information about the chat member',
				`new_chat_member` TEXT NOT NULL COMMENT 'New information about the chat member',
				`invite_link` TEXT NULL COMMENT 'Chat invite link, which was used by the user to join the chat; for joining by invite link events only',
				`created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Entry date creation',

				PRIMARY KEY (`id`)#,

				# FOREIGN KEY (`chat_id`) REFERENCES `chat` (`id`),
				# FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"
		);

		$this->wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}fdtbwpb_chat_join_request` (
				`id` BIGINT UNSIGNED AUTO_INCREMENT COMMENT 'Unique identifier for this entry',
				`chat_id` BIGINT NOT NULL COMMENT 'Chat to which the request was sent',
				`user_id` BIGINT NOT NULL COMMENT 'User that sent the join request',
				`bot_username` CHAR(191) DEFAULT NULL COMMENT 'Current bot''s username',
				`date` TIMESTAMP NOT NULL COMMENT 'Date the request was sent in Unix time',
				`bio` TEXT NULL COMMENT 'Optional. Bio of the user',
				`invite_link` TEXT NULL COMMENT 'Optional. Chat invite link that was used by the user to send the join request',
				`created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Entry date creation',

				PRIMARY KEY (`id`)#,

				# FOREIGN KEY (`chat_id`) REFERENCES `chat` (`id`),
				# FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"
		);

		$this->wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}fdtbwpb_telegram_update` (
				`id` BIGINT UNSIGNED COMMENT 'Update''s unique identifier',
				`chat_id` BIGINT NULL DEFAULT NULL COMMENT 'Unique chat identifier',
				`bot_username` CHAR(191) DEFAULT NULL COMMENT 'Current bot''s username',
				`message_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'New incoming message of any kind - text, photo, sticker, etc.',
				`edited_message_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'New version of a message that is known to the bot and was edited',
				`channel_post_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'New incoming channel post of any kind - text, photo, sticker, etc.',
				`edited_channel_post_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'New version of a channel post that is known to the bot and was edited',
				`inline_query_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'New incoming inline query',
				`chosen_inline_result_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'The result of an inline query that was chosen by a user and sent to their chat partner',
				`callback_query_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'New incoming callback query',
				`shipping_query_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'New incoming shipping query. Only for invoices with flexible price',
				`pre_checkout_query_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'New incoming pre-checkout query. Contains full information about checkout',
				`poll_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'New poll state. Bots receive only updates about polls, which are sent or stopped by the bot',
				`poll_answer_poll_id` BIGINT UNSIGNED DEFAULT NULL COMMENT 'A user changed their answer in a non-anonymous poll. Bots receive new votes only in polls that were sent by the bot itself.',
				`my_chat_member_updated_id` BIGINT UNSIGNED NULL COMMENT 'The bot''s chat member status was updated in a chat. For private chats, this update is received only when the bot is blocked or unblocked by the user.',
				`chat_member_updated_id` BIGINT UNSIGNED NULL COMMENT 'A chat member''s status was updated in a chat. The bot must be an administrator in the chat and must explicitly specify “chat_member” in the list of allowed_updates to receive these updates.',
				`chat_join_request_id` BIGINT UNSIGNED NULL COMMENT 'A request to join the chat has been sent',

				PRIMARY KEY (`id`),
				KEY `message_id` (`message_id`),
				KEY `chat_message_id` (`chat_id`, `message_id`),
				KEY `edited_message_id` (`edited_message_id`),
				KEY `channel_post_id` (`channel_post_id`),
				KEY `edited_channel_post_id` (`edited_channel_post_id`),
				KEY `inline_query_id` (`inline_query_id`),
				KEY `chosen_inline_result_id` (`chosen_inline_result_id`),
				KEY `callback_query_id` (`callback_query_id`),
				KEY `shipping_query_id` (`shipping_query_id`),
				KEY `pre_checkout_query_id` (`pre_checkout_query_id`),
				KEY `poll_id` (`poll_id`),
				KEY `poll_answer_poll_id` (`poll_answer_poll_id`),
				KEY `my_chat_member_updated_id` (`my_chat_member_updated_id`),
				KEY `chat_member_updated_id` (`chat_member_updated_id`),
				KEY `chat_join_request_id` (`chat_join_request_id`)#,

				# FOREIGN KEY (`chat_id`, `message_id`) REFERENCES `message` (`chat_id`, `id`),
				# FOREIGN KEY (`edited_message_id`) REFERENCES `edited_message` (`id`),
				# FOREIGN KEY (`chat_id`, `channel_post_id`) REFERENCES `message` (`chat_id`, `id`),
				# FOREIGN KEY (`edited_channel_post_id`) REFERENCES `edited_message` (`id`),
				# FOREIGN KEY (`inline_query_id`) REFERENCES `inline_query` (`id`),
				# FOREIGN KEY (`chosen_inline_result_id`) REFERENCES `chosen_inline_result` (`id`),
				# FOREIGN KEY (`callback_query_id`) REFERENCES `callback_query` (`id`),
				# FOREIGN KEY (`shipping_query_id`) REFERENCES `shipping_query` (`id`),
				# FOREIGN KEY (`pre_checkout_query_id`) REFERENCES `pre_checkout_query` (`id`),
				# FOREIGN KEY (`poll_id`) REFERENCES `poll` (`id`),
				# FOREIGN KEY (`poll_answer_poll_id`) REFERENCES `poll_answer` (`poll_id`),
				# FOREIGN KEY (`my_chat_member_updated_id`) REFERENCES `chat_member_updated` (`id`),
				# FOREIGN KEY (`chat_member_updated_id`) REFERENCES `chat_member_updated` (`id`),
				# FOREIGN KEY (`chat_join_request_id`) REFERENCES `chat_join_request` (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"
		);

		$this->wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}fdtbwpb_conversation` (
				`id` BIGINT(20) unsigned AUTO_INCREMENT COMMENT 'Unique identifier for this entry',
				`user_id` BIGINT NULL DEFAULT NULL COMMENT 'Unique user identifier',
				`chat_id` BIGINT NULL DEFAULT NULL COMMENT 'Unique user or chat identifier',
				`bot_username` CHAR(191) DEFAULT NULL COMMENT 'Current bot''s username',
				`status` ENUM('active', 'cancelled', 'stopped') NOT NULL DEFAULT 'active' COMMENT 'Conversation state',
				`command` varchar(160) DEFAULT '' COMMENT 'Default command to execute',
				`notes` text DEFAULT NULL COMMENT 'Data stored from command',
				`created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Entry date creation',
				`updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Entry date update',

				PRIMARY KEY (`id`),
				KEY `user_id` (`user_id`),
				KEY `chat_id` (`chat_id`),
				KEY `status` (`status`)#,

				# FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
				# FOREIGN KEY (`chat_id`) REFERENCES `chat` (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"
		);

		$this->wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}fdtbwpb_request_limiter` (
				`id` BIGINT UNSIGNED AUTO_INCREMENT COMMENT 'Unique identifier for this entry',
				`chat_id` char(255) NULL DEFAULT NULL COMMENT 'Unique chat identifier',
				`bot_username` CHAR(191) DEFAULT NULL COMMENT 'Current bot''s username',
				`inline_message_id` char(255) NULL DEFAULT NULL COMMENT 'Identifier of the sent inline message',
				`method` char(255) DEFAULT NULL COMMENT 'Request method',
				`created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Entry date creation',

				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"
		);

		$this->wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}fdtbwpb_chat_list` (
				`id` BIGINT UNSIGNED AUTO_INCREMENT COMMENT 'Unique identifier for this entry',
				`chat_id` longtext NOT NULL,
				`bot_username` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
				`created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Entry date creation',
				`updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Entry date update',
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"
		);

		$this->wpdb->query(
			"CREATE TABLE IF NOT EXISTS `{$this->wpdb->prefix}fdtbwpb_chat_messages` (
				`id` BIGINT UNSIGNED AUTO_INCREMENT COMMENT 'Unique identifier for this entry',
				`chat_list_id` longtext NOT NULL,
				`telegram_message_id` longtext NOT NULL,
				`reply_to_id` longtext NOT NULL,
				`telegram_id` longtext NOT NULL,
				`bot_username` longtext NOT NULL,
				`type` text NOT NULL,
				`status` text NOT NULL,
				`forward_to` longtext NOT NULL,
				`message` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci NOT NULL,
				`from` text NOT NULL,
				`created_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Entry date creation',
				`updated_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Entry date update',
				PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;"
		);

		update_option(FDTBWPB_OPTIONS_KEY_DB_VERSION, trim(FDTBWPB_VERSION));
	}

	/**
	 * Resets all query states for a new operation.
	 *
	 * @return	void
	 */
	private function resetQueryState(): void
	{
		$this->operation        = '';
		$this->table            = '';
		$this->fields           = '*';
		$this->whereRelation    = 'AND';
		$this->whereClauses     = [];
		$this->valuesToPrepare  = [];
		$this->joinClauses      = [];
		$this->insertSetClauses = [];
		$this->updateSetClauses = [];
		$this->rawWhereClause   = [];
		$this->orderClause      = '';
		$this->limitClause      = '';
		$this->groupByClause    = '';
		$this->havingClause     = '';
	}

	/**
	 * Inserts into table.
	 *
	 * @param	string	$table	Table name.
	 *
	 * @return	$this
	 */
	public function insert(string $table): self
	{
		$this->resetQueryState();

		$this->operation = 'insert';
		$this->table     = "{$this->wpdb->prefix}$table";

		return $this;
	}

	/**
	 * Selects columns.
	 *
	 * @param	string|array<string>	$fields	Columns to select.
	 *
	 * @return	$this
	 */
	public function select(string|array $fields = '*'): self
	{
		$this->resetQueryState();

		$this->operation = 'select';
		$this->fields    = !is_array($fields) ? $fields : implode(', ', $fields);

		return $this;
	}

	/**
	 * Updates table.
	 *
	 * @param	string	$table	Table name.
	 *
	 * @return	$this
	 */
	public function update(string $table): self
	{
		$this->resetQueryState();

		$this->operation = 'update';
		$this->table     = "{$this->wpdb->prefix}$table";

		return $this;
	}

	/**
	 * Deletes from table.
	 *
	 * @param	string	$table	Table name.
	 *
	 * @return	$this
	 */
	public function delete(string $table): self
	{
		$this->resetQueryState();

		$this->operation = 'delete';
		$this->table     = "{$this->wpdb->prefix}$table";

		return $this;
	}

	/**
	 * Sets the table for the `SELECT` query.
	 *
	 * @param	string	$table	Table name.
	 *
	 * @return	$this
	 */
	public function from(string $table): self
	{
		$this->table = "{$this->wpdb->prefix}$table";

		return $this;
	}

	/**
	 * Sets values for `INSERT` or `UPDATE` queries.
	 *
	 * @param	array	$values	`column` => `value` pairs.
	 *
	 * @return	$this
	 */
	public function set(array $values): self
	{
		if (empty($values))
			return $this;

		if ($this->operation === 'update')
		{
			foreach ($values as $field => $value)
			{
				$column = '`' . str_replace('`', '``', $field) . '`';

				if (is_string($value))
				{
					$this->updateSetClauses[] = "$column = %s";
					$this->valuesToPrepare[] = $value;
				} else if (is_numeric($value) || is_bool($value)) {
					$this->updateSetClauses[] = "$column = %d";
					$this->valuesToPrepare[] = $value;
				} else if (is_null($value)) {
					$this->updateSetClauses[] = "$column = NULL";
				}
			}
		} else if ($this->operation === 'insert') {
			$identifiers   = [];
			$placeholders  = [];
			$prepareValues = [];

			foreach ($values as $field => $value)
			{
				$identifiers[] = '`' . str_replace('`', '``', $field) . '`';

				if (is_string($value))
				{
					$placeholders[] = '%s';
					$prepareValues[] = $value;
				} else if (is_numeric($value) || is_bool($value)) {
					$placeholders[] = '%d';
					$prepareValues[] = $value;
				} else if (is_null($value)) {
					$placeholders[] = 'NULL';
				}
			}

			$this->valuesToPrepare                 = array_merge($this->valuesToPrepare, $prepareValues);
			$this->insertSetClauses['identifiers'] = $identifiers;
			$this->insertSetClauses['values']      = $placeholders;
		}

		return $this;
	}

	/**
	 * Adds a `WHERE` condition to the query.
	 *
	 * @param	string	$field		Column name.
	 * @param	string	$operator	Comparison operator. Acceptable values: `=`, `!=`, `>`, `>=`, `<`, `<=`, `LIKE`, `NOT LIKE`, `IN`, `NOT IN` and `BETWEEN`.
	 * @param	mixed	$value		Value to compare.
	 *
	 * @return	$this
	 *
	 * @throws	\InvalidArgumentException	When operator is not supported.
	 */
	public function where(string $field, string $operator, mixed $value): self
	{
		if (is_array($value))
		{
			$value = array_values($value);

			if (empty($value))
				return $this;
		} else if (is_null($value) || $value === '' || $value === false) {
			return $this;
		}

		if (!empty($condition = $this->generateCondition($field, $operator, $value)))
		{
			$this->whereClauses[]  = $condition['condition'];
			$this->valuesToPrepare = array_merge($this->valuesToPrepare, $condition['values']);
		}

		return $this;
	}

	/**
	 * Generates a `WHERE` condition and its values.
	 *
	 * @param	string	$field		Column name.
	 * @param	string	$operator	Comparison operator. Acceptable values: `=`, `!=`, `>`, `>=`, `<`, `<=`, `LIKE`, `NOT LIKE`, `IN`, `NOT IN` and `BETWEEN`.
	 * @param	mixed	$value		Value to compare.
	 *
	 * @return	array|null
	 */
	protected function generateCondition(string $field, string $operator, mixed $value): ?array
	{
		$condition = '';
		$values    = [];

		switch ($operator)
		{
			case '=':
			case '!=':
			case '>':
			case '>=':
			case '<':
			case '<=':
			case 'LIKE':
			case 'NOT LIKE':
				$rawValue = str_replace('%', '', (string) $value);

				if (is_numeric($rawValue) || !empty($rawValue))
				{
					$condition = "$field $operator %s";
					$values[]  = $value;
				}
				break;
			case 'IN':
			case 'NOT IN':
				if (is_string($value))
					$value = explode(',', $value);

				if (!empty($value) && is_array($value) && count($value) === 1)
					return $this->generateCondition($field, $operator === 'IN' ? '=' : '!=', reset($value));

				if (!empty($value) && is_array($value))
				{
					$placeholders = implode(', ', array_fill(0, count($value), '%s'));
					$condition    = "$field $operator ($placeholders)";
					$values       = $value;
				}
				break;
			case 'BETWEEN':
				if (is_array($value) && count($value) === 2)
				{
					$condition = "$field BETWEEN %s AND %s";
					$values    = $value;
				}
				break;
			default:
				throw new \InvalidArgumentException("Unsupported operator: $operator");
		}

		if (empty($condition))
			return null;

		return [
			'condition' => $condition,
			'values'    => $values,
		];
	}

	/**
	 * Adds a raw `WHERE` condition to the query.
	 *
	 * @param	string	$condition	SQL condition.
	 * @param	array	$values		Values to prepare.
	 *
	 * @return	$this
	 */
	public function whereRaw(string $condition, array $values = []): self
	{
		$this->rawWhereClause[] = !empty($values) ? $this->prepareQuery($condition, $values) : $condition;

		return $this;
	}

	/**
	 * Prepares a query with values using \wpdb->prepare.
	 *
	 * @param	string	$query	SQL query.
	 * @param	array	$args	Values to bind.
	 *
	 * @return	string
	 */
	protected function prepareQuery(string $query, array $args = []): string
	{
		if (preg_match('/%[i|s|f|d]/', $query))
			$query = $this->wpdb->prepare($query, $args);

		return $query;
	}

	/**
	 * Sets the relation between `WHERE` clauses.
	 *
	 * @param	string	$relation	Acceptable values: 'AND' or 'OR'.
	 *
	 * @return	$this
	 */
	public function whereRelation(string $relation): self
	{
		if (in_array($relation, ['AND', 'OR']))
			$this->whereRelation = $relation;

		return $this;
	}

	/**
	 * Joins another table.
	 *
	 * @param	string			$table		Table name to join.
	 * @param	array|string	$on			Join keys or condition string.
	 * @param	array[]			$conditions	Extra join conditions. An array exactly like `where()`'s parameters.
	 * @param	string			$joinType	Join type.
	 *
	 * @return	$this
	 *
	 * @throws	\InvalidArgumentException	When join clause is invalid.
	 */
	public function join(string $table, $on, array $conditions = [], string $joinType = 'INNER'): self
	{
		$joinTable = "{$this->wpdb->prefix}$table";

		if ((is_array($on) && count($on) === 2) || is_string($on))
		{
			$joinClause = "$joinType JOIN $joinTable AS $table ON ";
			$joinClause .= is_array($on) ? "$on[0] = $on[1]" : $on;

			if (!empty($conditions))
			{
				foreach ($conditions as $condition)
				{
					$field    = $condition[0];
					$operator = $condition[1];
					$value    = $condition[2];

					if (!empty($condition = $this->generateCondition($field, $operator, $value)))
					{
						$condition   = $this->prepareQuery($condition['condition'], $condition['values']);
						$joinClause .= " AND $condition";
					}
				}
			}

			$this->joinClauses[$joinTable] = $joinClause;
		} else {
			throw new \InvalidArgumentException('Invalid join clause!');
		}

		return $this;
	}

	/**
	 * Sets `ORDER BY` clause to the query.
	 *
	 * @param	string|array<string>	$fields	Columns to order by.
	 * @param	string					$order	Acceptable values: 'ASC' or 'DESC'.
	 *
	 * @return	$this
	 */
	public function orderBy(string|array $fields, string $order = 'DESC'): self
	{
		if (!in_array(strtoupper($order), ['ASC', 'DESC']))
			$order = 'DESC';

		if (!empty($fields))
		{
			if (is_string($fields))
				$fields = array_map('trim', explode(',', $fields));

			if (is_array($fields))
			{
				$orderParts = [];

				foreach ($fields as $field)
				{
					if (preg_match('/^[A-Za-z0-9_]+(\.[A-Za-z0-9_]+)?$/', $field))
					{
						if (strpos($field, '.') !== false)
						{
							list($table, $column) = explode('.', $field);
							$orderParts[]         = "`$table`.`$column` $order";
						} else {
							$orderParts[] = "`$field` $order";
						}
					}
				}

				if (!empty($orderParts))
					$this->orderClause = 'ORDER BY ' . implode(', ', $orderParts);
			}
		}

		return $this;
	}

	/**
	 * Sets `LIMIT` and `OFFSET` to the query.
	 *
	 * @param	int	$limit	Max rows.
	 * @param	int	$offset	Offset.
	 *
	 * @return	$this
	 */
	public function limit(int $limit, int $offset = 0): self
	{
		$limit = intval($limit);
		if ($limit > 0)
		{
			$this->limitClause = "LIMIT $limit";

			$offset = intval($offset);
			if ($offset > 0)
				$this->limitClause .= " OFFSET $offset";
		}

		return $this;
	}

	/**
	 * Sets `LIMIT` and `OFFSET` for pagination.
	 *
	 * @param	int $page		Page number.
	 * @param	int $perPage	Items per page.
	 *
	 * @return	$this
	 */
	public function perPage(int $page = 1, int $perPage = 10): self
	{
		$page    = intval($page);
		$perPage = intval($perPage);

		if ($page > 0 && $perPage > 0)
			$this->limit($perPage, ($page - 1) * $perPage);

		return $this;
	}

	/**
	 * Sets `GROUP BY` clause to the query.
	 *
	 * @param	string|array<string>	$fields	Columns to group by.
	 *
	 * @return	$this
	 */
	public function groupBy(string|array $fields): self
	{
		if (is_array($fields))
			$fields = implode(', ', $fields);

		if (!empty($fields))
			$this->groupByClause = "GROUP BY $fields";

		return $this;
	}

	/**
	 * Sets `HAVING` clause to the query.
	 *
	 * @param	string	$expression	`HAVING` expression.
	 *
	 * @return	$this
	 */
	public function having(string $expression): self
	{
		if (!empty($expression))
			$this->havingClause = "HAVING $expression";

		return $this;
	}

	/**
	 * Builds the SQL query based on the current operation.
	 *
	 * @return	string
	 *
	 * @throws	\InvalidArgumentException	When operation method is missing.
	 */
	protected function buildQuery(): string
	{
		$queryMethod = $this->operation . 'Query';

		if (method_exists($this, $queryMethod))
			return $this->$queryMethod();

		throw new \InvalidArgumentException("$queryMethod method is not defined!");
	}

	/**
	 * Builds an `INSERT` query.
	 *
	 * @return	string
	 */
	protected function insertQuery(): string
	{
		$query = "INSERT INTO `$this->table`";

		if (!empty($this->insertSetClauses))
		{
			$query .= ' (' . implode(', ', $this->insertSetClauses['identifiers']) . ') ';
			$query .= 'VALUES (' . implode(', ', $this->insertSetClauses['values']) . ') ';
		}

		return $query . ';';
	}

	/**
	 * Builds a `SELECT` query.
	 *
	 * @return	string
	 */
	protected function selectQuery(): string
	{
		$query = "SELECT $this->fields FROM `$this->table`";

		if (!empty($joinClauses = array_filter($this->joinClauses)))
			$query .= ' ' . implode(' ', $joinClauses);

		if (!empty($whereClauses = array_filter($this->whereClauses)))
			$query .= ' WHERE ' . implode(" $this->whereRelation ", $whereClauses);

		if (!empty($this->rawWhereClause))
		{
			$query .= empty($this->whereClauses) ? ' WHERE ' : ' ';
			$query .= implode(' ', $this->rawWhereClause);
		}

		if (!empty($this->groupByClause))
			$query .= ' ' . $this->groupByClause;

		if (!empty($this->havingClause))
			$query .= ' ' . $this->havingClause;

		if (!empty($this->orderClause))
			$query .= ' ' . $this->orderClause;

		if (!empty($this->limitClause))
			$query .= ' ' . $this->limitClause;

		return $query . ';';
	}

	/**
	 * Builds an `UPDATE` query.
	 *
	 * @return	string
	 */
	protected function updateQuery(): string
	{
		$query = "UPDATE `$this->table`";

		if (!empty($this->updateSetClauses))
			$query .= ' SET ' . implode(', ', $this->updateSetClauses);

		if (!empty($whereClauses = array_filter($this->whereClauses)))
			$query .= ' WHERE ' . implode(" $this->whereRelation ", $whereClauses);

		if (!empty($this->rawWhereClause))
		{
			$query .= empty($this->whereClauses) ? ' WHERE ' : ' ';
			$query .= implode(' ', $this->rawWhereClause);
		}

		return $query . ';';
	}

	/**
	 * Builds a `DELETE` query.
	 *
	 * @return	string
	 */
	protected function deleteQuery(): string
	{
		$query = "DELETE FROM `$this->table`";

		if (!empty($whereClauses = array_filter($this->whereClauses)))
			$query .= ' WHERE ' . implode(" $this->whereRelation ", $whereClauses);

		if (!empty($this->rawWhereClause))
		{
			$query .= empty($this->whereClauses) ? ' WHERE ' : ' ';
			$query .= implode(' ', $this->rawWhereClause);
		}

		return $query . ';';
	}

	/**
	 * Returns the prepared query string.
	 *
	 * @return	string
	 */
	public function getQuery(): string
	{
		$query         = $this->buildQuery();
		$preparedQuery = $this->prepareQuery($query, $this->valuesToPrepare);

		return $preparedQuery;
	}

	/**
	 * Returns all rows from the built query.
	 *
	 * @return	array|null	Database query results. Empty array when no rows match or on database error. `null` when the query is empty or when the connection is not ready.
	 */
	public function getResults(): ?array
	{
		$query = $this->buildQuery();
		$query = $this->prepareQuery($query, $this->valuesToPrepare);

		return $this->wpdb->get_results($query, ARRAY_A);
	}

	/**
	 * Returns all rows from a raw SQL query.
	 *
	 * @param	string	$query	SQL query.
	 * @param	array	$params	Values to prepare.
	 *
	 * @return	array|null		Database query results. Empty array when no rows match or on database error. `null` when the query is empty or when the connection is not ready.
	 */
	public function getResultsRaw(string $query, array $params = []): ?array
	{
		$query = $this->prepareQuery($query, $params);

		return $this->wpdb->get_results($query, ARRAY_A);
	}

	/**
	 * Returns a single row from the built query.
	 *
	 * @return	array|null	Database query result or `null` on failure.
	 */
	public function getRow(): ?array
	{
		$query = $this->buildQuery();
		$query = $this->prepareQuery($query, $this->valuesToPrepare);

		return $this->wpdb->get_row($query, ARRAY_A);
	}

	/**
	 * Returns a single row from a raw SQL query.
	 *
	 * @param	string	$query	SQL query.
	 * @param	array	$params	Values to prepare.
	 *
	 * @return	array|null		Database query result or `null` on failure.
	 */
	public function getRowRaw(string $query, array $params = []): ?array
	{
		$query = $this->prepareQuery($query, $params);

		return $this->wpdb->get_row($query, ARRAY_A);
	}

	/**
	 * Returns a single value from the built query.
	 *
	 * @return	string|null		Database query result as string, or `null` on failure or when the value is an empty string.
	 */
	public function getVar(): ?string
	{
		$query = $this->buildQuery();
		$query = $this->prepareQuery($query, $this->valuesToPrepare);

		return $this->wpdb->get_var($query);
	}

	/**
	 * Returns a single value from a raw SQL query.
	 *
	 * @param	string	$query	SQL query.
	 * @param	array	$params	Values to prepare.
	 *
	 * @return	string|null		Database query result as string, or `null` on failure or when the value is an empty string.
	 */
	public function getVarRaw(string $query, array $params = []): ?string
	{
		$query = $this->prepareQuery($query, $params);

		return $this->wpdb->get_var($query);
	}

	/**
	 * Returns a single column from the built query.
	 *
	 * @return	array	Database query result. Array indexed from 0 by SQL result row number.
	 */
	public function getCol(): array
	{
		$query = $this->buildQuery();
		$query = $this->prepareQuery($query, $this->valuesToPrepare);

		return $this->wpdb->get_col($query);
	}

	/**
	 * Executes the built query (`INSERT`/`UPDATE`/`DELETE`).
	 *
	 * @return	int|bool	Number of rows affected/selected or `false` on error.
	 */
	public function execute(): int|bool
	{
		$query = $this->buildQuery();
		$query = $this->prepareQuery($query, $this->valuesToPrepare);

		return $this->wpdb->query($query);
	}

	/**
	 * Executes a raw SQL query.
	 *
	 * @param	string	$query	SQL query.
	 * @param	array	$params	Values to prepare.
	 *
	 * @return	int|bool		`true` for `CREATE`, `ALTER`, `TRUNCATE` and `DROP` queries. Number of rows affected/selected for all other queries or `false` on error.
	 */
	public function executeRaw(string $query, array $params = []): int|bool
	{
		$query = $this->prepareQuery($query, $params);

		return $this->wpdb->query($query);
	}

	/**
	 * Returns the last inserted ID.
	 *
	 * @return	int		The integer value of var on success, or 0 on failure. Empty arrays and objects return 0, non-empty arrays and objects return 1.
	 */
	public function getLastInsertId(): int
	{
		return intval($this->wpdb->insert_id);
	}

	/**
	 * Returns the last error message.
	 *
	 * @return	string
	 */
	public function getLastError(): string
	{
		return $this->wpdb->last_error ?? '';
	}
}
