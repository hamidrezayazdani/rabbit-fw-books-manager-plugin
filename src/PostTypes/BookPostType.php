<?php

namespace YBooksManager\PostTypes;

use YBooksManager\Models\Book;

class BookPostType
{
	public function register()
	{
		add_action('init', [$this, 'registerPostType']);
		add_filter('manage_book_posts_columns', [$this, 'addIsbnColumn']);
		add_action('manage_book_posts_custom_column', [$this, 'isbnColumnContent'], 10, 2);
		add_filter('manage_book_posts_columns', [$this, 'addIsbnColumn']);
		add_action('before_delete_post', [$this, 'removeIsbnOnDeleteBook'], 99, 2);
	}

	public function registerPostType()
	{
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
			'show_in_menu' => true
		]);

		$already_flushed = get_option('books_flushed');

		if (empty($already_flushed)) {
			flush_rewrite_rules();
			update_option('books_flushed', 1);
		}
	}

	public function addIsbnColumn($columns)
	{
		$columns['isbn'] = 'ISBN';
		return $columns;
	}

	public function isbnColumnContent($column, $post_id)
	{
		if ($column === 'isbn') {
			$book = Book::getByPostId($post_id);
			$isbn = $book ? $book->isbn : __('N/A', 'y-books-manager');

			echo esc_html($isbn);
		}
	}

	public function removeIsbnOnDeleteBook($post_id, $post)
	{
		if ('book'!==$post->post_type)
		{
			return;
		}

		Book::deleteBook($post_id);
	}
}