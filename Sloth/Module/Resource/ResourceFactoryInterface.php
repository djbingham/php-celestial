<?php
namespace Sloth\Module\Resource;

use Sloth\Module\Resource\Definition\AttributeList;
use Sloth\Module\Resource\Resource as ResourceModel;

interface ResourceFactoryInterface
{
	/**
	 * @param Definition\Resource $definition
	 * @param QuerySetFactory $querySetFactory
	 * @param DataValidator $dataValidator
	 */
	public function __construct(Definition\Resource $definition, QuerySetFactory $querySetFactory, DataValidator $dataValidator);

	/**
	 * @return Definition\Resource
	 */
	public function getResourceDefinition();

	/**
	 * Fetch resources form the database whose attributeList exactly match the supplied values
	 * @param AttributeList $attributesToInclude
	 * @param array $filters
	 * @return ResourceList
	 */
	public function getBy(AttributeList $attributesToInclude, array $filters);

	/**
	 * Search the database for resources matching the supplied filters and options as well as this factory's manifest
	 * @param AttributeList $attributesToInclude
	 * @param array $filters
	 * @return ResourceList
	 */
	public function search(AttributeList $attributesToInclude, array $filters);

	/**
	 * Create a new Resource with supplied attribute values
	 * @param array $attributes
	 * @return ResourceModel
	 */
	public function create(array $attributes);

	/**
	 * Save updates to a given Resource
	 * @param array $filters
	 * @param array $attributes
	 * @return ResourceInterface
	 */
	public function update(array $filters, array $attributes);

	/**
	 * Delete a given Resource
	 * @param array $filters
	 * @return $this
	 */
	public function delete(array $filters);
}