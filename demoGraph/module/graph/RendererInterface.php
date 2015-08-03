<?php
namespace DemoGraph\Module\Graph;

use Sloth\App;

interface RendererInterface
{
	/**
	 * @param App $app
	 */
	public function __construct(App $app);

	/**
	 * @param ResourceFactoryInterface $resourceFactory
	 * @param string $format
	 */
	public function renderDefinition(ResourceFactoryInterface $resourceFactory, $format);

	/**
	 * @param ResourceFactoryInterface $resourceFactory
	 * @param ResourceInterface $resource
	 * @param string $format
	 */
	public function renderResource(ResourceFactoryInterface $resourceFactory, ResourceInterface $resource, $format);

	/**
	 * @param ResourceFactoryInterface $resourceFactory
	 * @param ResourceListInterface $resourceList
	 * @param string $format
	 */
	public function renderResourceList(ResourceFactoryInterface $resourceFactory, ResourceListInterface $resourceList, $format);

	/**
	 * @param ResourceFactoryInterface $resourceFactory
	 * @param string $format
	 */
	public function renderCreateForm(ResourceFactoryInterface $resourceFactory, $format);

	/**
	 * @param ResourceFactoryInterface $resourceFactory
	 * @param ResourceInterface $resource
	 * @param string $format
	 */
	public function renderUpdateForm(ResourceFactoryInterface $resourceFactory, ResourceInterface $resource, $format);

	/**
	 * @param ResourceFactoryInterface $resourceFactory
	 * @param string $format
	 */
	public function renderSearchForm(ResourceFactoryInterface $resourceFactory, $format);

	/**
	 * @param ResourceFactoryInterface $resourceFactory
	 * @param ResourceInterface $resource
	 * @param string $format
	 */
	public function renderDeletedResource(ResourceFactoryInterface $resourceFactory, ResourceInterface $resource, $format);
}
