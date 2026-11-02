<?php

namespace TelegramPluginBoilerplate\PostTypes;

/**
 * Custom Post Type: Book (Example CPT)
 */
class BookPostType
{
	public function __construct()
	{
		// Uncomment if you want to register a custom post type
		// add_action('init', [$this, 'registerPostType']);
	}

	/**
	 * Registers "Book" post type.
	 *
	 * @return	void
	 *
	 * @hooked	action: `init` - 10
	 */
	public function registerPostType(): void
	{
		$args = [
			'labels'                => [
				'name'               => esc_html__('Books', 'telegram-plugin-boilerplate'),
				'singular_name'      => esc_html__('Book', 'telegram-plugin-boilerplate'),
				'add_new'            => esc_html__('Add New', 'telegram-plugin-boilerplate'),
				'add_new_item'       => esc_html__('Add New Book', 'telegram-plugin-boilerplate'),
				'edit_item'          => esc_html__('Edit Book', 'telegram-plugin-boilerplate'),
				'new_item'           => esc_html__('New Book', 'telegram-plugin-boilerplate'),
				'view_item'          => esc_html__('View Book', 'telegram-plugin-boilerplate'),
				'search_items'       => esc_html__('Search Books', 'telegram-plugin-boilerplate'),
				'not_found'          => esc_html__('No books found', 'telegram-plugin-boilerplate'),
				'not_found_in_trash' => esc_html__('No books found in trash', 'telegram-plugin-boilerplate'),
				'all_items'          => esc_html__('All Books', 'telegram-plugin-boilerplate'),
			],
			'description'           => esc_html__('Example CPT', 'telegram-plugin-boilerplate'),
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 10,
			'menu_icon'             => 'dashicons-book',
			'show_in_nav_menus'     => false,
			'show_in_rest'          => true,
			'rest_base'             => 'books',
			'hierarchical'          => false,
			'capability_type'       => 'post',
			'supports'              => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
		];

		register_post_type('fdtbwpb_book', $args);
	}
}
