<?php
namespace Sloth\Module\Resource\Base;

use Sloth\App;
use Sloth\Module\Resource\Base\Resource as BaseResource;

interface Renderer
{
    /**
     * @param App $app
     */
    public function __construct(App $app);

    /**
     * @param ResourceFactory $resourceFactory
     * @param string $format
     */
	public function renderDefinition(ResourceFactory $resourceFactory, $format);

    /**
     * @param ResourceFactory $resourceFactory
     * @param BaseResource $resource
     * @param string $format
     */
    public function renderResource(ResourceFactory $resourceFactory, Resource $resource, $format);

    /**
     * @param ResourceFactory $resourceFactory
     * @param ResourceList $resourceList
     * @param string $format
     */
    public function renderResourceList(ResourceFactory $resourceFactory, ResourceList $resourceList, $format);

    /**
     * @param ResourceFactory $resourceFactory
     * @param string $format
     */
    public function renderCreateForm(ResourceFactory $resourceFactory, $format);

    /**
     * @param ResourceFactory $resourceFactory
     * @param BaseResource $resource
     * @param string $format
     */
    public function renderUpdateForm(ResourceFactory $resourceFactory, Resource $resource, $format);

    /**
     * @param ResourceFactory $resourceFactory
     * @param string $format
     */
    public function renderSearchForm(ResourceFactory $resourceFactory, $format);

    /**
     * @param ResourceFactory $resourceFactory
     * @param BaseResource $resource
     * @param string $format
     */
    public function renderDeletedResource(ResourceFactory $resourceFactory, Resource $resource, $format);
}
