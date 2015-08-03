<?php
namespace DemoGraph\Module\Graph\DefinitionBuilder;

use DemoGraph\Module\Graph\ResourceDefinition;
use DemoGraph\Module\Graph\ResourceManifestValidator;

class ResourceDefinitionBuilder
{
	/**
	 * @var AttributeListBuilder
	 */
	private $attributeListBuilder;

	/**
	 * @var LinkListBuilder
	 */
	private $linkListBuilder;

	/**
	 * @var ViewListBuilder
	 */
	private $viewListBuilder;

	/**
	 * @var TableBuilder
	 */
	private $tableBuilder;

	/**
	 * @var ValidatorListBuilder
	 */
	private $validatorListBuilder;

	/**
	 * @var ResourceManifestValidator
	 */
	private $manifestValidator;

	/**
	 * @var string
	 */
	private $manifestDirectory;

	public function __construct(ResourceManifestValidator $manifestValidator, $manifestDirectory)
	{
		$this->manifestValidator = $manifestValidator;
		$this->manifestDirectory = $manifestDirectory;
	}

	public function setSubBuilders(array $builders)
	{
		$this->validatorListBuilder = $builders['validatorListBuilder'];
		$this->attributeListBuilder = $builders['attributeListBuilder'];
		$this->linkListBuilder = $builders['linkListBuilder'];
		$this->viewListBuilder = $builders['viewListBuilder'];
		$this->tableBuilder = $builders['tableBuilder'];
		return $this;
	}

	public function buildFromName($resourceName, $alias = null)
	{
		$fileName = sprintf('%s.json', implode(DIRECTORY_SEPARATOR, explode('/', $resourceName)));
		$filePath = $this->manifestDirectory . DIRECTORY_SEPARATOR . $fileName ;
		return $this->buildFromFile($filePath, $alias);
	}

	public function buildFromFile($filePath, $alias = null)
	{
		$this->assertManifestFileExists($filePath);
		$fileContents = file_get_contents($filePath);
		$fileName = basename($filePath, '.json');
		$manifest = json_decode($fileContents, true);
		$manifest['name'] = ucfirst($fileName);
		$manifest = $this->padManifest($manifest);
		$this->assertManifestIsValid($manifest);
		return $this->buildFromManifest($manifest, $alias);
	}

	public function buildFromManifest(array $manifest, $alias = null)
	{
		$resource = new ResourceDefinition\Resource();

		if (!is_null($alias)) {
			$resource->alias = $alias;
			$manifest['table']['alias'] = $alias;
		}

		$resource->name = $manifest['name'];
		$resource->table = $this->tableBuilder->build($manifest['table']);
		$resource->attributes = $this->attributeListBuilder->build($resource, $manifest['attributes']);
		$resource->views = $this->viewListBuilder->build($resource, $manifest['views']);
		$resource->links = $this->linkListBuilder->build($resource, $manifest['links']);
		$resource->validators = $this->validatorListBuilder->build($resource, $manifest['validators']);

		return $resource;
	}

	private function assertManifestFileExists($filePath)
	{
		if (!is_file($filePath)) {
			throw new \Exception('Manifest file not found: ' . $filePath);
		}
	}

	private function assertManifestIsValid(array $manifest)
	{
		if (!$this->manifestValidator->validate($manifest)) {
			$errorString = implode('; ', $this->manifestValidator->getErrors());
			throw new \Exception('Manifest file failed validation, with the following errors: ' . $errorString);
		}
	}

	private function padManifest(array $manifest)
	{
		if (!array_key_exists('links', $manifest)) {
			$manifest['links'] = array();
		}
		if (!array_key_exists('views', $manifest)) {
			$manifest['views'] = array();
		}
		if (!array_key_exists('validators', $manifest)) {
			$manifest['validators'] = array();
		}
		return $manifest;
	}
}
