<?php

namespace TelegramPluginBoilerplate\Shortcodes;

/**
 * Returns the post modified date in the given format.
 */
class PostModifiedDateShortcode
{
	public static $tag = 'post_modified_date';

	/**
	 * Runs the shortcode.
	 *
	 * @global	\WP_Post	$post
	 *
	 * @param	array	$atts
	 *
	 * @return	string
	 */
	public function run($atts): string
	{
		$atts = shortcode_atts(
			[
				'post_id' => 0,
				'format'  => '',
			],
			$atts,
			self::$tag
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
