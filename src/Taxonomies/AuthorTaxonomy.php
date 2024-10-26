<?php

namespace YBooksManager\Taxonomies;

class AuthorTaxonomy
{
	public function register()
	{
		add_action('init', function () {
			register_taxonomy('author', 'book', [
				'label' => __('Authors', 'y-books-manager'),
				'labels' => [
					'name'              => __('Authors', 'y-books-manager'),
					'singular_name'     => __('Author', 'y-books-manager'),
					'search_items'      => __('Search Authors', 'y-books-manager'),
					'all_items'         => __('All Authors', 'y-books-manager'),
					'edit_item'         => __('Edit Author', 'y-books-manager'),
					'update_item'       => __('Update Author', 'y-books-manager'),
					'add_new_item'      => __('Add New Author', 'y-books-manager'),
					'new_item_name'     => __('New Author Name', 'y-books-manager'),
					'menu_name'         => __('Authors', 'y-books-manager'),
				],
				'hierarchical' => false,
				'show_admin_column' => true,
			]);
		});
	}
}