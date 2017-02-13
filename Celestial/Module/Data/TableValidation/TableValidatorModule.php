<?php
namespace Celestial\Module\Data\TableValidation;

class TableValidatorModule
{
	/**
	 * @var DependencyManager
	 */
	private $dependencyManager;

	public function __construct(DependencyManager $dependencyManager)
	{
		$this->dependencyManager = $dependencyManager;
	}

	public function validateNamedTable($tableName)
	{
		$filePath = $this->dependencyManager->getTableModule()->getManifestPath($tableName);
		return $this->dependencyManager->getTableManifestFileValidator()->validate($filePath);
	}

	public function validateManifest(\stdClass $tableManifest)
	{
		return $this->dependencyManager->getTableManifestValidator()->validate($tableManifest);
	}
}
