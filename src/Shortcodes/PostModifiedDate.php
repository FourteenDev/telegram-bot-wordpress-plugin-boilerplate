<?php

namespace TelegramPluginBoilerplate\Shortcodes;

class PostModifiedDate
{
	public function __construct()
	{
		add_shortcode('post_modified_date', [$this, 'run']);
	}

	/**
	 * Runs the shortcode.
	 *
	 * @param	array	$atts
	 *
	 * @return	string
	 */
	public function run($atts)
	{
		$atts = shortcode_atts(
			[
				'post_id' => 0,
				'format'  => '',
			],
			$atts,
			'post_modified_date'
		);

		// Get post ID from user's input or from the current post
		$postId = !empty($atts['post_id']) ? intval($atts['post_id']) : 0;
		if (!intval($postId))
		{
			global $post;
			if ($post instanceof \WP_Post)
				$postId = $post->ID;
		}
		if (!intval($postId))
			return '';

		$format = !empty($atts['format']) ? sanitize_key($atts['format']) : get_option('date_format');

		return get_the_modified_date($format, $post);
	}
}
