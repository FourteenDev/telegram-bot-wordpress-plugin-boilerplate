<?php

namespace TelegramPluginBoilerplate\Services\Posts;

class Post
{
	public function __construct()
	{
		add_filter('the_content', [$this, 'addSomeTextToPost']);
	}

	/**
	 * An example empty method to showcase Posts services.
	 *
	 * @param	string	$content
	 *
	 * @return	string
	 *
	 * @hooked	filter: `the_content` - 10
	 */
	function addSomeTextToPost($content)
	{
		// Some action

		return $content;
	}
}
