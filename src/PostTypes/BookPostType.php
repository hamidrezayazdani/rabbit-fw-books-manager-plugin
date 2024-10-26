<?php
namespace YBooksManager\PostTypes;

class BookPostType
{
	public function register()
	{
		add_action('init', function () {
			register_post_type('book', [
				'label' => __('Books', 'y-books-manager'),
				'labels' => [
					'name'               => __('Books', 'y-books-manager'),
					'singular_name'      => __('Book', 'y-books-manager'),
					'add_new'            => __('Add New', 'y-books-manager'),
					'add_new_item'       => __('Add New Book', 'y-books-manager'),
					'edit_item'          => __('Edit Book', 'y-books-manager'),
					'new_item'           => __('New Book', 'y-books-manager'),
					'view_item'          => __('View Book', 'y-books-manager'),
					'search_items'       => __('Search Books', 'y-books-manager'),
					'not_found'          => __('No Books found', 'y-books-manager'),
					'not_found_in_trash' => __('No Books found in Trash', 'y-books-manager'),
					'all_items'          => __('All Books', 'y-books-manager'),
					'menu_name'          => __('Books', 'y-books-manager'),
					'name_admin_bar'     => __('Book', 'y-books-manager'),
				],
				'public' => true,
				'supports' => ['title', 'editor', 'thumbnail'],
				'menu_icon' => 'dashicons-book-alt',
				'has_archive' => true,
				'rewrite' => ['slug' => 'books'],
				'show_in_menu' => false
			]);
		});
	}
}