<?php
namespace DemoGraph\Module\Graph;

interface ResourceFactoryInterface
{
    /**
     * @param ResourceDefinition\Resource $definition
     * @param QuerySetFactory $querySetFactory
     */
    public function __construct(ResourceDefinition\Resource $definition, QuerySetFactory $querySetFactory);

	/**
	 * @return ResourceDefinition\Resource
	 */
	public function getResourceDefinition();

    /**
     * Fetch resources form the database whose attributeList exactly match the supplied values
     * @param array $attributes
     * @param array $filters
     * @return ResourceList
     */
    public function getBy(array $attributes, array $filters);

    /**
     * Search the database for resources matching the supplied filters and options as well as this factory's manifest
     * @param array $filters
     * @return ResourceList
     */
    public function search(array $filters);

    /**
     * Create a new Resource with supplied attribute values
     * @param array $attributes
     * @return Resource
     */
    public function create(array $attributes);

    /**
     * Save updates to a given Resource
     * @param ResourceInterface $resource
     * @return ResourceInterface
     */
    public function update(ResourceInterface $resource);

    /**
     * Delete a given ResourceInterface
     * @param ResourceInterface $resource
     * @return $this
     */
    public function delete(ResourceInterface $resource);
}