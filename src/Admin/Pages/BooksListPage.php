<?php

namespace YBooksManager\Admin\Pages;

if (!class_exists('WP_List_Table')) {
	require_once ABSPATH . 'wp-admin/includes/template.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
	require_once ABSPATH . 'wp-admin/includes/screen.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

use WP_List_Table;

class BooksListPage extends WP_List_Table
{
	public function __construct()
	{
		parent::__construct([
			'singular' => __('book', 'y-books-manager'),
			'plural'   => __('books', 'y-books-manager'),
			'ajax'     => false,
		]);
	}

	public function prepare_items()
	{
		global $wpdb;

		$this->process_bulk_action();

		$columns = $this->get_columns();
		$hidden = [];
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = [$columns, $hidden, $sortable];

		$table_name = $wpdb->prefix . 'books_info';
		$per_page = $this->get_items_per_page('books_per_page', 10);
		$current_page = $this->get_pagenum();
		$offset = ($current_page - 1) * $per_page;

		// Search query
		$search = isset($_REQUEST['s']) ? wp_unslash(trim($_REQUEST['s'])) : '';

		// Status and author filters
		$status = isset($_REQUEST['status']) ? sanitize_text_field($_REQUEST['status']) : 'all';
		$author_id = isset($_REQUEST['author']) ? intval($_REQUEST['author']) : 0;

		// Base query with GROUP BY to prevent duplicates
		$query = "SELECT bi.post_id AS ID, 
                        MAX(p.post_title) AS title, 
                        MAX(bi.isbn) AS isbn, 
                        MAX(p.post_status) AS post_status 
                 FROM $table_name bi
                 LEFT JOIN {$wpdb->posts} p ON bi.post_id = p.ID
                 WHERE p.post_type = 'book'";

		// Add search condition
		if (!empty($search)) {
			$query .= $wpdb->prepare(
				" AND (p.post_title LIKE %s OR bi.isbn LIKE %s)",
				'%' . $wpdb->esc_like($search) . '%',
				'%' . $wpdb->esc_like($search) . '%'
			);
		}

		// Add status condition
		if ($status !== 'all') {
			$query .= $wpdb->prepare(" AND p.post_status = %s", $status);
		}

		// Add author condition
		if ($author_id) {
			$query .= $wpdb->prepare(" AND p.post_author = %d", $author_id);
		}

		// Add GROUP BY
		$query .= " GROUP BY bi.post_id";

		// Add ordering
		$orderby = isset($_REQUEST['orderby']) ? sanitize_text_field($_REQUEST['orderby']) : 'title';
		$order = isset($_REQUEST['order']) ? sanitize_text_field($_REQUEST['order']) : 'asc';

		// Adjust ORDER BY clause to use MAX() for sorted columns
		$orderby_clause = match($orderby) {
			'title' => 'MAX(p.post_title)',
			'isbn' => 'MAX(bi.isbn)',
			'post_status' => 'MAX(p.post_status)',
			default => 'MAX(p.post_title)'
		};

		$query .= sprintf(" ORDER BY %s %s", $orderby_clause, esc_sql($order));

		// Add pagination
		$query .= $wpdb->prepare(" LIMIT %d OFFSET %d", $per_page, $offset);

		// Get total items for pagination - adjusted for GROUP BY
		$total_items_query = "SELECT COUNT(DISTINCT bi.post_id) 
                            FROM $table_name bi
                            LEFT JOIN {$wpdb->posts} p ON bi.post_id = p.ID
                            WHERE p.post_type = 'book'";

		if (!empty($search)) {
			$total_items_query .= $wpdb->prepare(
				" AND (p.post_title LIKE %s OR bi.isbn LIKE %s)",
				'%' . $wpdb->esc_like($search) . '%',
				'%' . $wpdb->esc_like($search) . '%'
			);
		}

		if ($status !== 'all') {
			$total_items_query .= $wpdb->prepare(" AND p.post_status = %s", $status);
		}

		if ($author_id) {
			$total_items_query .= $wpdb->prepare(" AND p.post_author = %d", $author_id);
		}

		$total_items = $wpdb->get_var($total_items_query);

		$this->set_pagination_args([
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil($total_items / $per_page),
		]);

		$this->items = $wpdb->get_results($query, ARRAY_A);
	}

	public function get_bulk_actions()
	{
		$actions = [
			'delete'  => __('Delete', 'y-books-manager'),
			'publish' => __('Publish', 'y-books-manager'),
			'draft'   => __('Move to Draft', 'y-books-manager'),
		];

		return current_user_can('delete_posts') ? $actions : [];
	}

	public function process_bulk_action()
	{
		if (empty($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'bulk-' . $this->_args['plural'])) {
			return;
		}

		$action = $this->current_action();
		if (!$action) {
			return;
		}

		$book_ids = isset($_REQUEST['book']) ? array_map('intval', $_REQUEST['book']) : [];
		if (empty($book_ids)) {
			return;
		}

		foreach ($book_ids as $book_id) {
			if (!current_user_can('edit_post', $book_id)) {
				continue;
			}

			switch ($action) {
				case 'delete':
					if (current_user_can('delete_post', $book_id)) {
						wp_delete_post($book_id, true);
					}
					break;

				case 'publish':
					wp_update_post([
						'ID'          => $book_id,
						'post_status' => 'publish',
					]);
					break;

				case 'draft':
					wp_update_post([
						'ID'          => $book_id,
						'post_status' => 'draft',
					]);
					break;
			}
		}

		wp_safe_redirect(add_query_arg());
		exit;
	}

	public function get_columns()
	{
		return [
			'cb'        => '<input type="checkbox" />',
			'title'     => __('Book Title', 'y-books-manager'),
			'isbn'      => __('ISBN', 'y-books-manager'),
			'author'    => __('Author', 'y-books-manager'),
			'publisher' => __('Publisher', 'y-books-manager'),
			'status'    => __('Status', 'y-books-manager'),
		];
	}

	public function get_sortable_columns()
	{
		return [
			'title'     => ['title', true],
			'isbn'      => ['isbn', false],
			'status'    => ['post_status', false],
		];
	}

	public function column_default($item, $column_name)
	{
		return match ($column_name) {
			'title', 'isbn' => esc_html($item[$column_name]),
			'status' => $this->get_status_label($item['post_status']),
			'author', 'publisher' => $item[$column_name] ?? '',
			default => print_r($item, true),
		};
	}

	private function get_status_label($status)
	{
		$status_labels = [
			'publish' => __('Published', 'y-books-manager'),
			'draft'   => __('Draft', 'y-books-manager'),
			'pending' => __('Pending', 'y-books-manager'),
		];

		return isset($status_labels[$status]) ?
			sprintf('<span class="status-label status-%s">%s</span>',
				esc_attr($status),
				esc_html($status_labels[$status])
			) :
			esc_html($status);
	}

	public function column_cb($item)
	{
		return sprintf(
			'<input type="checkbox" name="book[]" value="%s" />',
			esc_attr($item['ID'])
		);
	}

	public function column_title($item)
	{
		$actions = [];

		if (current_user_can('edit_post', $item['ID'])) {
			$actions['edit'] = sprintf(
				'<a href="%s">%s</a>',
				get_edit_post_link($item['ID']),
				__('Edit', 'y-books-manager')
			);
		}

		if (current_user_can('delete_post', $item['ID'])) {
			$actions['delete'] = sprintf(
				'<a href="%s" class="submitdelete" onclick="return confirm(\'%s\');">%s</a>',
				get_delete_post_link($item['ID']),
				__('Are you sure you want to delete this book?', 'y-books-manager'),
				__('Delete', 'y-books-manager')
			);
		}

		return sprintf(
			'<a href="%1$s"><strong>%2$s</strong></a> %3$s',
			get_edit_post_link($item['ID']),
			esc_html(get_the_title($item['ID'])),
			$this->row_actions($actions)
		);
	}

	public function column_author($item)
	{
		return wp_kses_post(get_the_term_list($item['ID'], 'author', '', ', '));
	}

	public function column_publisher($item)
	{
		return wp_kses_post(get_the_term_list($item['ID'], 'publisher', '', ', '));
	}

	protected function extra_tablenav($which)
	{
		if ($which === 'top') {
			$current_status = isset($_REQUEST['status']) ? sanitize_text_field($_REQUEST['status']) : 'all';
			$current_author = isset($_REQUEST['author']) ? intval($_REQUEST['author']) : 0;

			$published_count = $this->get_books_count('publish');
			$draft_count = $this->get_books_count('draft');
			$my_books_count = $this->get_books_count('publish', get_current_user_id());

			$current_url = remove_query_arg(['status', 'author', 'paged']);

			// Display tabs
			echo '<ul class="subsubsub">';

			// All tab
			printf(
				'<li class="all"><a href="%s" class="%s">%s <span class="count">(%d)</span></a></li>',
				esc_url($current_url),
				$current_status === 'all' ? 'current' : '',
				esc_html__('All', 'y-books-manager'),
				$published_count + $draft_count
			);

			echo ' | ';

			// Published tab
			printf(
				'<li class="published"><a href="%s" class="%s">%s <span class="count">(%d)</span></a></li>',
				esc_url(add_query_arg('status', 'publish', $current_url)),
				$current_status === 'publish' && !$current_author ? 'current' : '',
				esc_html__('Published', 'y-books-manager'),
				$published_count
			);

			echo ' | ';

			// My Books tab
			printf(
				'<li class="my-books"><a href="%s" class="%s">%s <span class="count">(%d)</span></a></li>',
				esc_url(add_query_arg(['status' => 'publish', 'author' => get_current_user_id()], $current_url)),
				($current_status === 'publish' && $current_author === get_current_user_id()) ? 'current' : '',
				esc_html__('My Books', 'y-books-manager'),
				$my_books_count
			);

			echo ' | ';

			// Drafts tab
			printf(
				'<li class="draft"><a href="%s" class="%s">%s <span class="count">(%d)</span></a></li>',
				esc_url(add_query_arg('status', 'draft', $current_url)),
				$current_status === 'draft' ? 'current' : '',
				esc_html__('Drafts', 'y-books-manager'),
				$draft_count
			);

			echo '</ul>';
		}
	}

	private function get_books_count($status, $author_id = null)
	{
		global $wpdb;

		$query = "SELECT COUNT(*) FROM {$wpdb->posts} p 
                 WHERE p.post_type = 'book' 
                 AND p.post_status = %s";

		$params = [$status];

		if ($author_id) {
			$query .= " AND p.post_author = %d";
			$params[] = $author_id;
		}

		return $wpdb->get_var($wpdb->prepare($query, $params));
	}

	public function display_books_page()
	{
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e('Books List', 'y-books-manager'); ?></h1>

			<?php $this->add_new_book_button(); ?>

			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>" />
				<?php
				$this->prepare_items();
				$this->search_box(__('Search Books', 'y-books-manager'), 'book-search');
				$this->display();
				?>
			</form>
		</div>
		<?php
	}

	private function add_new_book_button()
	{
		if (current_user_can('publish_posts')) {
			$new_book_url = admin_url('post-new.php?post_type=book');
			printf(
				'<a href="%s" class="page-title-action">%s</a>',
				esc_url($new_book_url),
				esc_html__('Add New Book', 'y-books-manager')
			);
		}
	}
}