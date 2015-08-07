<?php
namespace Sloth\Module\Graph;

use Sloth\App;

interface RendererInterface
{
	/**
	 * @param App $app
	 * @param array $engines
	 */
	public function __construct(App $app, array $engines);

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
	 * @param Definition\Resource $resourceDefinition
	 * @param ResourceListInterface $resourceList
	 * @param string $format
	 */
	public function renderResourceList(Definition\Resource $resourceDefinition, ResourceListInterface $resourceList, $format);

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
