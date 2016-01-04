<?php
namespace Sloth\Module\Data\TableValidation;

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
		$file = $this->dependencyManager->getTableModule()->getManifestPath($tableName);
		$fileContents = file_get_contents($file);
		$manifest = json_decode($fileContents);

		return $this->validate($manifest);
	}

	public function validate(\stdClass $tableManifest)
	{
		return $this->dependencyManager->getTableManifestValidator()->validate($tableManifest);
	}
}
