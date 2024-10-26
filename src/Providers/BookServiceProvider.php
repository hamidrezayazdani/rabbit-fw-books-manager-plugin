<?php

namespace YBooksManager\Providers;

use YBooksManager\PostTypes\BookPostType;
use YBooksManager\Taxonomies\PublisherTaxonomy;
use YBooksManager\Taxonomies\AuthorTaxonomy;
use YBooksManager\Admin\Pages\BooksListPage;
use YBooksManager\Admin\MetaBox\ISBNMetaBox;
use Rabbit\Contracts\BootablePluginProviderInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;

class BookServiceProvider extends AbstractServiceProvider implements BootablePluginProviderInterface
{
	/**
	 * The provided services.
	 *
	 * @var array
	 */
	protected $provides = [
		BookPostType::class,
		PublisherTaxonomy::class,
		AuthorTaxonomy::class,
		BooksListPage::class,
		ISBNMetaBox::class,
	];

	/**
	 * Register services in the container.
	 */
	public function register()
	{
		$this->getContainer()->add(BookPostType::class, function () {
			return new BookPostType();
		});

		$this->getContainer()->add(PublisherTaxonomy::class, function () {
			return new PublisherTaxonomy();
		});

		$this->getContainer()->add(AuthorTaxonomy::class, function () {
			return new AuthorTaxonomy();
		});

		$this->getContainer()->add(BooksListPage::class, function () {
			return new BooksListPage();
		});

		$this->getContainer()->add(ISBNMetaBox::class, function () {
			return new ISBNMetaBox();
		});
	}

	/**
	 * Boot services when the plugin is loaded.
	 */
	public function bootPlugin()
	{
		$this->getContainer()->get(BookPostType::class)->register();
		$this->getContainer()->get(PublisherTaxonomy::class)->register();
		$this->getContainer()->get(AuthorTaxonomy::class)->register();
		$this->getContainer()->get(ISBNMetaBox::class)->register();
		$this->getContainer()->get(BooksListPage::class)->register();
	}

	/**
	 * Called during plugin activation.
	 */
	public static function install()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . 'books_info';

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
            ID BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            isbn VARCHAR(255) NOT NULL,
            PRIMARY KEY (ID)
        ) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}