<?php

namespace YBooksManager\Providers;

use YBooksManager\PostTypes\BookPostType;
use YBooksManager\Taxonomies\PublisherTaxonomy;
use YBooksManager\Taxonomies\AuthorTaxonomy;
use YBooksManager\Admin\MetaBox\ISBNMetaBox;
use Rabbit\Contracts\BootablePluginProviderInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;

class BookServiceProvider extends AbstractServiceProvider implements BootablePluginProviderInterface
{
	/**
	 * The services provided by this service provider.
	 *
	 * @var array
	 */
	protected $provides = [
		BookPostType::class,
		PublisherTaxonomy::class,
		AuthorTaxonomy::class,
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
	}

}