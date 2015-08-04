<?php
namespace DemoGraph\Module\Graph\ResourceDefinition;

use DemoGraph\Module\Graph\Exception\InvalidResourceException;
use DemoGraph\Module\Graph\ResourceDefinition\Resource as GraphResource;
use DemoGraph\Module\Graph\DefinitionBuilder\ResourceDefinitionBuilder;
use Sloth\Exception\InvalidArgumentException;

class Link
{
	const MANY_TO_MANY = 'manyToMany';
	const MANY_TO_ONE = 'manyToOne';
	const ONE_TO_MANY = 'oneToMany';
	const ONE_TO_ONE = 'oneToOne';

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var GraphResource
	 */
	public $parentResource;

	/**
	 * @var string
	 */
	public $childResourceName;

	/**
	 * @var string
	 */
	public $type = Link::ONE_TO_ONE;

	/**
	 * @var ResourceList
	 */
	public $intermediaryResources;

	/**
	 * @var array
	 */
	public $joinManifest = array();

	/**
	 * @var LinkConstraintList
	 */
	public $constraints;

	/**
	 * @var GraphResource
	 */
	protected $childResource;

	/**
	 * @var ResourceDefinitionBuilder
	 */
	private $resourceBuilder;

	public function __construct(ResourceDefinitionBuilder $resourceBuilder)
	{
		$this->resourceBuilder = $resourceBuilder;
	}

	public function getConstraints()
	{
		if (!isset($this->constraints)) {
			$this->load();
		}
		return $this->constraints;
	}

	public function getChildResource()
	{
		if (!isset($this->childResource)) {
			$this->load();
		}
		return $this->childResource;
	}

	private function load()
	{
		$childResourceAlias = $this->buildUniqueResourceAlias($this->name, $this->parentResource->getAlias());
		$this->childResource = $this->resourceBuilder->buildFromName($this->childResourceName, $childResourceAlias);
		$this->constraints = $this->buildJoinList();
	}

	private function buildUniqueResourceAlias($defaultAlias, $parentAlias = null)
	{
		if ($parentAlias !== null) {
			$defaultAlias = $parentAlias . '_' . $defaultAlias;
		}
		return $defaultAlias;
	}

	private function buildJoinList()
	{
		if (!empty($this->intermediaryResources)) {
			$joins = new LinkConstraintList();
			$joins->push($this->buildJoinViaIntermediary());
		} else {
			$joins = $this->buildDirectJoins();
		}
		return $joins;
	}

	private function buildJoinViaIntermediary()
	{
		$join = new LinkConstraint();
		$join->link = $this;

		$resources = new ResourceList();
		$resources->push($this->parentResource);
		$parentAlias = $this->parentResource->getAlias();
		foreach ($this->intermediaryResources as $resource) {
			/** @var GraphResource $resource */
			$resource->alias = $this->buildUniqueResourceAlias($resource->alias, $parentAlias);
			$resources->push($resource);
		}
		$resources->push($this->childResource);

		$join->subJoins = new LinkSubJoinList();
		while ($resources->length() >= 2) {
			$resourcePair = $resources->slice(0, 2);
			$resources->shift();

			$subJoin = $this->buildSubJoin($join, $resourcePair);
			$join->subJoins->push($subJoin);
		}

		$parentAttribute = $this->getParentAttributeFromSubJoins($join->subJoins);
		$childAttribute = $this->getChildAttributeFromSubJoins($join->subJoins);

		$join->parentAttribute = $this->parentResource->getAttributeByName($parentAttribute->name);
		$join->childAttribute = $this->childResource->getAttributeByName($childAttribute->name);

		return $join;
	}

	private function getParentAttributeFromSubJoins(LinkSubJoinList $joins)
	{
		$parentAttribute = null;
		/** @var LinkSubJoin $join */
		foreach ($joins as $join) {
			if ($join->parentResource->name === $this->parentResource->name) {
				$parentAttribute = $join->parentAttribute;
				break;
			} elseif ($join->childResource->name === $this->parentResource->name) {
				$parentAttribute = $join->childAttribute;
				break;
			}
		}
		if (is_null($parentAttribute)) {
			throw new InvalidResourceException(
				'No parent attribute from resource found in sub-joins: ' . json_encode($joins)
			);
		}
		return $parentAttribute;
	}

	private function getChildAttributeFromSubJoins(LinkSubJoinList $joins)
	{
		$childAttribute = null;
		/** @var LinkSubJoin $join */
		foreach ($joins as $join) {
			if ($join->parentResource->name === $this->childResource->name) {
				$childAttribute = $join->parentAttribute;
			} elseif ($join->childResource->name === $this->childResource->name) {
				$childAttribute = $join->childAttribute;
			}
		}
		if (is_null($childAttribute)) {
			throw new InvalidResourceException(
				'No child attribute from resource found in sub-joins: ' . json_encode($joins)
			);
		}
		return $childAttribute;
	}

	private function buildSubJoin(LinkConstraint $parentJoin, ResourceList $resources)
	{
		if ($resources->length() !== 2) {
			throw new InvalidArgumentException(
				'Attempted to build a sub-join from an invalid number of resources: ' . json_encode($resources)
			);
		}

		$firstResource = $resources->getByIndex(0);
		$secondResource = $resources->getByIndex(1);

		$join = new LinkSubJoin();
		$join->parentJoin = $parentJoin;

		foreach ($this->joinManifest as $parentAlias => $childAlias) {
			$parentResourceAlias = rtrim(strstr($parentAlias, '.', true), '.');
			$parentResourceAlias = $this->getResourceNameFromAlias($parentResourceAlias);
			$parentAttributeAlias = ltrim(strstr($parentAlias, '.'), '.');

			$childResourceAlias = rtrim(strstr($childAlias, '.', true), '.');
			$childResourceAlias = $this->getResourceNameFromAlias($childResourceAlias);
			$childAttributeAlias = ltrim(strstr($childAlias, '.'), '.');

			if ($parentResourceAlias === $firstResource->getAlias()) {
				$join->parentResource = $firstResource;
				$join->parentAttribute = $this->buildResourceAttribute($firstResource, $parentAttributeAlias);
				$join->childResource = $secondResource;
				$join->childAttribute = $this->buildResourceAttribute($secondResource, $childAttributeAlias);
				$join->childResource->attributes->push($join->childAttribute);
				break;
			} elseif ($childResourceAlias === $secondResource->getAlias()) {
				$join->childResource = $secondResource;
				$join->childAttribute = $this->buildResourceAttribute($secondResource, $childAttributeAlias);
				$join->parentResource = $firstResource;
				$join->parentAttribute = $this->buildResourceAttribute($firstResource, $parentAttributeAlias);
				$join->parentResource->attributes->push($join->parentAttribute);
				break;
			}
		}

		return $join;
	}

	private function buildDirectJoins()
	{
		$joins = new LinkConstraintList();
		foreach ($this->joinManifest as $parentAlias => $childAlias) {
			$parentAttributeName = ltrim(strstr($parentAlias, '.'), '.');
			$parentAttribute = $this->parentResource->attributes->getByName($parentAttributeName);

			$childAttributeName = ltrim(strstr($childAlias, '.'), '.');
			$childAttribute = $this->childResource->attributes->getByName($childAttributeName);

			$join = new LinkConstraint();
			$join->link = $this;
			$join->parentAttribute = $parentAttribute;
			$join->childAttribute = $childAttribute;
			$joins->push($join);
		}
		return $joins;
	}

	private function buildResourceAttribute(GraphResource $resource, $attributeName)
	{
		$attribute = new Attribute();
		$attribute->resource = $resource;
		$attribute->name = $attributeName;
		$attribute->alias = sprintf('%s.%s', $resource->getAlias(), $attributeName);
		return $attribute;
	}

	private function getResourceNameFromAlias($alias)
	{
		if ($alias === 'this') {
			$resourceName = $this->parentResource->getAlias();
		} elseif ($alias === $this->name) {
			$resourceName = $this->childResource->getAlias();
		} else {
			$resourceName = $alias;
		}
		return $resourceName;
	}
}
