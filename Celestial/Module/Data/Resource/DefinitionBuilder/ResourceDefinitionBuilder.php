<?php
namespace Celestial\Module\Data\Resource\DefinitionBuilder;

use Celestial\Module\Data\Table\TableModule;
use Celestial\Module\Data\Resource\Definition\Resource;
use Celestial\Module\Data\Resource\Definition;
use Celestial\Module\Data\Resource\ResourceManifestValidator;

class ResourceDefinitionBuilder
{
	/**
	 * @var TableModule
	 */
	private $tableModule;

	/**
	 * @var ResourceManifestValidator
	 */
	private $manifestValidator;

	/**
	 * @var string
	 */
	private $manifestDirectory;

	/**
	 * @var ValidatorListBuilder
	 */
	private $validatorListBuilder;

	/**
	 * @var AttributeListBuilder
	 */
	private $attributeListBuilder;

	public function setManifestValidator(ResourceManifestValidator $validator)
	{
		$this->manifestValidator = $validator;
		return $this;
	}

	public function setManifestDirectory($manifestDirectory)
	{
		$this->manifestDirectory = $manifestDirectory;
		return $this;
	}

	public function setTableModule(TableModule $tableModule)
	{
		$this->tableModule = $tableModule;
		return $this;
	}

	public function setSubBuilders(array $builders)
	{
		$this->validatorListBuilder = $builders['validatorListBuilder'];
		$this->attributeListBuilder = $builders['attributeListBuilder'];
		return $this;
	}

	public function buildFromName($tableName)
	{
		$filePathParts = explode('/', $tableName);
		$lastPathPartIndex = count($filePathParts) - 1;
		$filePathParts[$lastPathPartIndex] = ucfirst($filePathParts[$lastPathPartIndex]);

		$lastPathPartIndex = count($filePathParts) - 1;
		$lastPathPart = $filePathParts[$lastPathPartIndex];
		$extensionStartPos = strrpos($lastPathPart, '.');
		if ($extensionStartPos !== false) {
			$filePathParts[$lastPathPartIndex] = substr($lastPathPart, 0, $extensionStartPos);
		}

		$fileName = sprintf('%s.json', implode(DIRECTORY_SEPARATOR, $filePathParts));
		$filePath = $this->manifestDirectory . DIRECTORY_SEPARATOR . $fileName ;

		return $this->buildFromFile($filePath);
	}

	public function buildFromFile($filePath)
	{
		$this->assertManifestFileExists($filePath);
		$fileContents = file_get_contents($filePath);
		$fileName = basename($filePath, '.json');
		$manifest = json_decode($fileContents);
		$manifest->name = ucfirst($fileName);
		return $this->buildFromManifest($manifest);
	}

	public function buildFromManifest(\stdClass $manifest)
	{
		$manifest = $this->padManifest($manifest);
		$this->assertManifestIsValid($manifest);

		$resource = new Resource();
		$resource->name = $manifest->name;
		$resource->attributes = $this->attributeListBuilder->build($resource, $manifest->attributes);
		$resource->primaryAttribute = $manifest->primaryAttribute;
		$resource->table = $this->tableModule->get($manifest->table);
		$resource->validators = $this->validatorListBuilder->build($manifest->validators);

		return $resource;
	}

	private function assertManifestFileExists($filePath)
	{
		if (!is_file($filePath)) {
			throw new \Exception('Manifest file not found: ' . $filePath);
		}
	}

	private function assertManifestIsValid(\stdClass $manifest)
	{
		if (!$this->manifestValidator->validate($manifest)) {
			$errorString = implode('; ', $this->manifestValidator->getErrors());
			throw new \Exception('Manifest file failed validation, with the following errors: ' . $errorString);
		}
	}

	private function padManifest(\stdClass $manifest)
	{
		if (!property_exists($manifest, 'validators')) {
			$manifest->validators = array();
		}
		return $manifest;
	}
}
