<?php
namespace Sloth\Module\Resource\DefinitionBuilder;

use Sloth\Module\Resource\Definition\Resource;
use Sloth\Module\Resource\Definition;
use Sloth\Module\Resource\ResourceManifestValidator;

class ResourceDefinitionBuilder
{
	/**
	 * @var TableDefinitionBuilder
	 */
	private $tableBuilder;

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
	 * @var TableFieldListBuilder
	 */
	private $attributeListBuilder;

	/**
	 * @var ViewListBuilder
	 */
	private $viewListBuilder;

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

	public function setSubBuilders(array $builders)
	{
		$this->tableBuilder = $builders['tableBuilder'];
		$this->validatorListBuilder = $builders['validatorListBuilder'];
		$this->attributeListBuilder = $builders['attributeListBuilder'];
		$this->viewListBuilder = $builders['viewListBuilder'];
		return $this;
	}

	public function buildFromName($tableName)
	{
		$filePathParts = explode(DIRECTORY_SEPARATOR, $tableName);
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
		$manifest = json_decode($fileContents, true);
		$manifest['name'] = ucfirst($fileName);
		return $this->buildFromManifest($manifest);
	}

	public function buildFromManifest(array $manifest)
	{
		$manifest = $this->padManifest($manifest);
		$this->assertManifestIsValid($manifest);

		$resource = new Resource();
		$resource->name = $manifest['name'];
		$resource->attributes = $manifest['attributes'];
		$resource->primaryAttribute = $manifest['primaryAttribute'];
		$resource->table = $this->tableBuilder->buildFromName($manifest['table']);
		$resource->validators = $this->validatorListBuilder->build($manifest['validators']);
		$resource->views = $this->viewListBuilder->build($manifest['views']);

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
		if (!array_key_exists('views', $manifest)) {
			$manifest['views'] = array();
		}
		if (!array_key_exists('validators', $manifest)) {
			$manifest['validators'] = array();
		}
		return $manifest;
	}
}
