<?php
/**
 * Plugin Name:     Y Books Manager
 * Plugin URI:      https://github.com/hamidrezayazdani
 * Plugin Prefix:   YBM
 * Description:     Manage book information using custom post types, taxonomies, and Rabbit Framework.
 * Author:          HamidReza Yazdani
 * Author URI:      https://github.com/hamidrezayazdani
 * Text Domain:     y-books-manager
 * Domain Path:     /languages
 * Version:         1.0.0
 */

namespace YBooksManager;

use Rabbit\Application;
use Rabbit\Database\DatabaseServiceProvider;
use Rabbit\Plugin;
use Rabbit\Redirects\AdminNotice;
use Rabbit\Templates\TemplatesServiceProvider;
use Rabbit\Utils\Singleton;
use Exception;
use YBooksManager\Providers\BookServiceProvider;
use YBooksManager\Admin\Pages\BooksListPage;

if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
	require dirname(__FILE__) . '/vendor/autoload.php';
}

class YBooksManager extends Singleton
{
	private $application;

	public function __construct()
	{
		$this->application = Application::get()->loadPlugin(__DIR__, __FILE__ );
		$this->init();
	}

	public function init()
	{
		try {
			$this->application->addServiceProvider(BookServiceProvider::class);
			$this->application->addServiceProvider(DatabaseServiceProvider::class);
			$this->application->addServiceProvider(TemplatesServiceProvider::class);

			add_action('admin_menu', [$this, 'addAdminMenu']);

			$this->application->onActivation(function () {
				BookServiceProvider::install();
			});

			$this->application->boot(function (Plugin $plugin) {
				$plugin->loadPluginTextDomain();
			});

		} catch (Exception $e) {
			add_action('admin_notices', function () use ($e) {
				AdminNotice::permanent(['type' => 'error', 'message' => $e->getMessage()]);
			});

			add_action('init', function () use ($e) {
				if ($this->application->has('logger')) {
					$this->application->get('logger')->warning($e->getMessage());
				}
			});
		}
	}

	public function addAdminMenu()
	{
		add_menu_page(
			__('Y Books Manager', 'y-books-manager'),
			__('Books Manager', 'y-books-manager'),
			'manage_options',
			'y_books_manager',
			[$this, 'displayBooksManagerPage'],
			'dashicons-book-alt'
		);

	}

	public function displayBooksManagerPage()
	{
		$booksListPage = new BooksListPage();
		$booksListPage->display_books_page();
	}

	public function getApplication()
	{
		return $this->application;
	}
}

function YBooksManager()
{
	return YBooksManager::get();
}

YBooksManager();