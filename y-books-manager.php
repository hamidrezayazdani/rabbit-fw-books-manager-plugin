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
use Rabbit\Utils\Singleton;
use Exception;
use YBooksManager\Providers\BookServiceProvider;
use YBooksManager\Migrations\BooksInfoDBHandler;



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

			$this->application->onActivation(function () {
				BooksInfoDBHandler::up();
			});

			$this->application->boot(function (Plugin $plugin) {
				$plugin->loadPluginTextDomain();
			});

			/**
			 * Deactivation hooks
			 */
			$this->application->onDeactivation(function () {
				// If site admin added the FORCE_DELETE_BOOKS_DB to the wp-config.php
				if(defined('FORCE_DELETE_BOOKS_DB'))
				{
					BooksInfoDBHandler::down();
				}
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