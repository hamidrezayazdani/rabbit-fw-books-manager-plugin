<?php

namespace YBooksManager\Admin\MetaBox;

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
		global $wpdb;

		$isbn = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT isbn FROM {$wpdb->prefix}books_info WHERE post_id=%d",
				$post->ID
			)
		);

		wp_nonce_field('save_book_isbn', 'book_isbn_nonce');
		echo '<input type="text" name="isbn" value="' . esc_attr($isbn) . '" />';
	}

	public function save($post_id)
	{
		if (!isset($_POST['book_isbn_nonce']) || !wp_verify_nonce($_POST['book_isbn_nonce'], 'save_book_isbn')) {
			return;
		}

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}

		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		if (isset($_POST['isbn'])) {
			$isbn = sanitize_text_field($_POST['isbn']);

			global $wpdb;
			$table_name = $wpdb->prefix . 'books_info';
			$wpdb->replace(
				$table_name,
				[
					'post_id' => $post_id,
					'isbn' => $isbn,
				],
				[
					'%d',
					'%s',
				]
			);
		}
	}
}