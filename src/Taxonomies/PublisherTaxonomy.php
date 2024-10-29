<?php

namespace YBooksManager\Taxonomies;

class PublisherTaxonomy
{
	public function register()
	{
		add_action('init', [$this, 'registerPublisherTaxonomy']);
	}

	public function registerPublisherTaxonomy()
	{
		register_taxonomy('publisher', 'book', [
			'label' => __('Publisher', 'y-books-manager'),
			'labels' => [
				'name'              => __('Publishers', 'y-books-manager'),
				'singular_name'     => __('Publisher', 'y-books-manager'),
				'search_items'      => __('Search Publishers', 'y-books-manager'),
				'all_items'         => __('All Publishers', 'y-books-manager'),
				'edit_item'         => __('Edit Publisher', 'y-books-manager'),
				'update_item'       => __('Update Publisher', 'y-books-manager'),
				'add_new_item'      => __('Add New Publisher', 'y-books-manager'),
				'new_item_name'     => __('New Publisher Name', 'y-books-manager'),
				'menu_name'         => __('Publishers', 'y-books-manager'),
			],
			'hierarchical' => true,
			'show_admin_column' => true,
		]);
	}
}