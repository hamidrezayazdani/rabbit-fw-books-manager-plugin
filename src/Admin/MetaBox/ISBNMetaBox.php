<?php

namespace YBooksManager\Admin\MetaBox;

use YBooksManager\Models\Book;

class ISBNMetaBox
{
	public function register()
	{
		add_action('add_meta_boxes', function () {
			add_meta_box('isbn_meta_box', __('ISBN', 'y-books-manager'), [$this, 'render'], 'book', 'side');
		});

		add_action('save_post', [$this, 'save']);
	}

	public function render($post)
	{
		$book = Book::getByPostId($post->ID);
		$isbn = $book ? $book->isbn : '';

		wp_nonce_field('save_book_isbn', 'book_isbn_nonce');
		echo '<input type="text" name="isbn" value="' . esc_attr($isbn) . '" />';
	}

	public function save($post_id)
	{
		if (!isset($_POST['book_isbn_nonce']) || !wp_verify_nonce($_POST['book_isbn_nonce'], 'save_book_isbn'))
		{
			return;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		{
			return;
		}

		if (!current_user_can('edit_post', $post_id))
		{
			return;
		}

		if (isset($_POST['isbn']))
		{
			$isbn = sanitize_text_field($_POST['isbn']);
			$book = Book::getByPostId($post_id);

			if ($book) {
				$book->updateBook($post_id,  $isbn);
			} else {
				Book::createBook($post_id,  $isbn);
			}
		}
	}
}