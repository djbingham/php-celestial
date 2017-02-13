<?php
namespace Celestial\Module\Data\Resource\Face;

use Celestial\App;
use Celestial\Module\Data\ResourceDataValidator\ResourceDataValidatorModule;
use Celestial\Module\Data\TableDataValidator\Result\ExecutedValidatorList;
use Celestial\Module\Data\TableQuery\TableQueryModule;
use Celestial\Module\Data\Resource\Definition\Resource\AttributeList;
use Celestial\Module\Data\Resource\Resource as ResourceModel;
use Celestial\Module\Data\Resource\ResourceList;

interface ResourceFactoryInterface
{
	/**
	 * @param App $app
	 * @param Definition\ResourceInterface $definition
	 * @param TableQueryModule $tableQueryModule
	 * @param ResourceDataValidatorModule $dataValidator
	 */
	public function __construct(
		App $app,
		Definition\ResourceInterface $definition,
		TableQueryModule $tableQueryModule,
		ResourceDataValidatorModule $dataValidator
	);

	/**
	 * @return Definition\ResourceInterface
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
	 * @param array $attributes
	 * @return ExecutedValidatorList
	 */
	public function validateCreateData(array $attributes);

	/**
	 * Create a new Resource with supplied attribute values
	 * @param array $attributes
	 * @return ResourceModel
	 */
	public function create(array $attributes);

	/**
	 * @param array $attributes
	 * @return ExecutedValidatorList
	 */
	public function validateUpdateData(array $attributes);

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