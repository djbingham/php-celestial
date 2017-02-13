<?php


namespace Celestial\Module\Data\TableValidation\Validator;

use Celestial\Module\Data\TableValidation\Base\BaseValidator;
use Celestial\Module\Data\TableValidation\DependencyManager;
use Celestial\Module\Data\TableValidation\Validator\TableManifest\TableManifestValidator;

class FileValidator extends BaseValidator
{
	/**
	 * @var TableManifestValidator
	 */
	private $manifestValidator;

	public function __construct(DependencyManager $dependencyManager)
	{
		parent::__construct($dependencyManager);

		$this->manifestValidator = $dependencyManager->getTableManifestValidator();
	}

	public function validateOptions(array $options)
	{
		return $this->validationModule->buildValidationResult(array(
			'validator' => $this
		));
	}

	public function validate($filePath, array $options = array())
	{
		$errors = $this->validationModule->buildValidationErrorList();

		if (file_exists($filePath)) {
			$fileContents = file_get_contents($filePath);
			$manifest = json_decode($fileContents);

			if ($manifest === null) {
				$error = $this->buildError('Manifest is in an invalid format (JSON required)');
				$errors->push($error);
			} else {
				$manifestValidationResult = $this->manifestValidator->validate($manifest);
				if (!$manifestValidationResult->isValid()) {
					$errors->merge($manifestValidationResult->getErrors());
				}
			}
		} else {
			$error = $this->buildError(sprintf('Manifest file not found at `%s`', $filePath));
			$errors->push($error);
		}

		return $this->validationModule->buildValidationResult(array(
			'validator' => $this,
			'errors' => $errors
		));
	}
}
