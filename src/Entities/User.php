<?php

namespace TelegramPluginBoilerplate\Entities;

use Longman\TelegramBot\Entities\User as LongmanUser;

class User
{
	/** @var \WP_User */
	private $wpUser;

	/**
	 * Creates/Retrieves user.
	 *
	 * @param	LongmanUser		$tgUser		Try to find a WordPress user in the database with this Telegram ID.
	 * @param	\WP_User		$wpUser
	 */
	public function __construct($tgUser = null, $wpUser = null)
	{
		if ($wpUser instanceof \WP_User)
		{
			$this->wpUser = $wpUser;
			return;
		}

		if (empty($tgUser)) return;

		$user = reset(get_users([
			'meta_key'   => '_telegram_user_id',
			'meta_value' => intval($tgUser->getId()),
			'number'     => 1,
		]));
		if ($user instanceof \WP_User)
			$this->wpUser = $user;
	}

	/**
	 * Returns `WP_User` object.
	 *
	 * @return	\WP_User
	 */
	public function getUser()
	{
		return $this->wpUser;
	}

	/**
	 * Returns user ID in WordPress.
	 *
	 * @return	int
	 */
	public function getId()
	{
		return $this->wpUser->ID;
	}

	/**
	 * Returns user's username in WordPress.
	 *
	 * @return	string
	 */
	public function getLogin()
	{
		return $this->wpUser->user_login;
	}

	/**
	 * Get user's nice name in WordPress.
	 *
	 * @return	string
	 */
	public function getNiceName()
	{
		return $this->wpUser->user_nicename;
	}

	/**
	 * Get user's display name in WordPress.
	 *
	 * @return	string
	 */
	public function getDisplayName()
	{
		return $this->wpUser->display_name;
	}

	/**
	 * Get user's nickname in WordPress.
	 *
	 * @return	string
	 */
	public function getNickName()
	{
		return $this->wpUser->nickname;
	}

	/**
	 * Get user's first name in WordPress.
	 *
	 * @return	string
	 */
	public function getFirstName()
	{
		return $this->wpUser->first_name;
	}

	/**
	 * Get user's last name in WordPress.
	 *
	 * @return	string
	 */
	public function getLastName()
	{
		return $this->wpUser->last_name;
	}

	/**
	 * Get user's Telegram ID.
	 *
	 * @return	int
	 */
	public function getTelegramId()
	{
		return intval(get_user_meta($this->getId(), '_telegram_user_id', true));
	}

	/**
	 * Get user's Telegram username.
	 *
	 * @return	string
	 */
	public function getTelegramUsername()
	{
		return str_replace('@', '', trim(get_user_meta($this->getId(), '_telegram_username', true)));
	}
}
