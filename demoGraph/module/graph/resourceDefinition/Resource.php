<?php
namespace DemoGraph\Module\Graph\ResourceDefinition;

use Sloth\Exception\InvalidArgumentException;

class Resource
{
	/**
	 * @var string
	 */
	public $alias;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var AttributeList
	 */
	public $attributes;

	/**
	 * @var LinkList
	 */
	public $links;

	/**
	 * @var ViewList
	 */
	public $views;

	/**
	 * @var ValidatorList
	 */
	public $validators;

	public function setAlias($alias)
	{
		$this->alias = $alias;
		return $this;
	}

	public function getAlias()
	{
		if ($this->alias !== null) {
			$alias = $this->alias;
		} else {
			$alias = $this->name;
		}
		return $alias;
	}

	public function getAttributeByName($attributeName)
	{
		$foundAttribute = null;
		foreach ($this->attributes as $attribute) {
			if ($attribute->name === $attributeName) {
				$foundAttribute = $attribute;
			}
		}
		if (is_null($foundAttribute)) {
			throw new InvalidArgumentException(
				sprintf('Failed to find resource attribute with name = `%s`', $attributeName)
			);
		}
		return $foundAttribute;
	}

	public function listDescendants(LinkList $linksToInclude)
	{
		$resourceList = new ResourceList();
		$resourceLinks = $linksToInclude->getByParent($this->getAlias());
		foreach ($resourceLinks as $link) {
			/** @var Link $link */
			$childResource = $link->getChildResource();
			if ($resourceLinks->containsChild($childResource->getAlias())) {
				$resourceList->push($childResource);
				foreach ($childResource->listDescendants($linksToInclude) as $grandchildResource) {
					$resourceList->push($grandchildResource);
				}
			}
		}
		return $resourceList;
	}

	public function view($name)
	{
		$viewIndex = $this->views->indexOfPropertyValue('name', $name);
		if ($viewIndex === -1) {
			throw new InvalidArgumentException(
				sprintf('Unrecognised view requested from resource definition: %s', $name)
			);
		}
		return $this->views->getByIndex($viewIndex);
	}
}
