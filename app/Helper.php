<?php namespace BoilerplateTelegramPlugin;

class Helper
{
	public static $instance = null;

	public static function get_instance()
	{
		null === self::$instance && self::$instance = new self;
		return self::$instance;
	}
}
