<?php
namespace Sloth\Module\Data\TableValidation;

use Helper\InternalCacheTrait;
use Sloth\App;
use Sloth\Module\Data\Table\TableModule;
use Sloth\Module\Validation\ValidationModule;

class DependencyManager
{
	use InternalCacheTrait;

	/**
	 * @var App
	 */
	private $app;

	public function __construct(App $app)
	{
		$this->app = $app;
	}

	/**
	 * @return TableModule
	 */
	public function getTableModule()
	{
		return $this->app->module('table');
	}

	/**
	 * @return ValidationModule
	 */
	public function getValidationModule()
	{
		return $this->app->module('validation');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\TableManifest\TableManifestValidator
	 */
	public function getTableManifestValidator()
	{
		return new Validator\TableManifest\TableManifestValidator($this);
	}

	/**
	 * @return Validator\TableManifest\StructureValidator
	 */
	public function getTableManifestStructureValidator()
	{
		return new Validator\TableManifest\StructureValidator($this);
	}

	/**
	 * @return Validator\FieldList\FieldListValidator
	 */
	public function getFieldListValidator()
	{
		if (!$this->isCached('fieldListValidator')) {
			$this->setCached('fieldListValidator', new Validator\FieldList\FieldListValidator($this));
		}

		return $this->getCached('fieldListValidator');
	}

	/**
	 * @return Validator\FieldList\AliasValidator
	 */
	public function getFieldListAliasValidator()
	{
		if (!$this->isCached('fieldListAliasValidator')) {
			$this->setCached('fieldListAliasValidator', new Validator\FieldList\AliasValidator($this));
		}

		return $this->getCached('fieldListAliasValidator');
	}

	/**
	 * @return Validator\FieldList\StructureValidator
	 */
	public function getFieldListStructureValidator()
	{
		if (!$this->isCached('fieldListStructureValidator')) {
			$this->setCached('fieldListStructureValidator', new Validator\FieldList\StructureValidator($this));
		}

		return $this->getCached('fieldListStructureValidator');
	}

	/**
	 * @return Validator\Field\FieldValidator
	 */
	public function getFieldValidator()
	{
		if (!$this->isCached('fieldValidator')) {
			$this->setCached('fieldValidator', new Validator\Field\FieldValidator($this));
		}

		return $this->getCached('fieldValidator');
	}

	/**
	 * @return Validator\Field\StructureValidator
	 */
	public function getFieldStructureValidator()
	{
		if (!$this->isCached('fieldStructureValidator')) {
			$this->setCached('fieldStructureValidator', new Validator\Field\StructureValidator($this));
		}

		return $this->getCached('fieldStructureValidator');
	}

	/**
	 * @return Validator\Field\Property\AutoIncrementValidator
	 */
	public function getFieldAutoIncrementValidator()
	{
		if (!$this->isCached('fieldAutoIncrementValidator')) {
			$this->setCached('fieldAutoIncrementValidator', new Validator\Field\Property\AutoIncrementValidator($this));
		}

		return $this->getCached('fieldAutoIncrementValidator');
	}

	/**
	 * @return Validator\Field\Property\IsUniqueValidator
	 */
	public function getFieldIsUniqueValidator()
	{
		if (!$this->isCached('fieldIsUniqueValidator')) {
			$this->setCached('fieldIsUniqueValidator', new Validator\Field\Property\IsUniqueValidator($this));
		}

		return $this->getCached('fieldIsUniqueValidator');
	}

	/**
	 * @return Validator\Field\Property\NameValidator
	 */
	public function getFieldNameValidator()
	{
		if (!$this->isCached('fieldNameValidator')) {
			$this->setCached('fieldNameValidator', new Validator\Field\Property\NameValidator($this));
		}

		return $this->getCached('fieldNameValidator');
	}

	/**
	 * @return Validator\Field\Property\TypeValidator
	 */
	public function getFieldTypeValidator()
	{
		if (!$this->isCached('fieldTypeValidator')) {
			$this->setCached('fieldTypeValidator', new Validator\Field\Property\TypeValidator($this));
		}

		return $this->getCached('fieldTypeValidator');
	}

	/**
	 * @return Validator\Field\Property\ValidatorListValidator
	 */
	public function getFieldValidatorListValidator()
	{
		if (!$this->isCached('fieldValidatorsValidator')) {
			$this->setCached('fieldValidatorsValidator', new Validator\Field\Property\ValidatorListValidator($this));
		}

		return $this->getCached('fieldValidatorsValidator');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\JoinList\JoinListValidator
	 */
	public function getJoinListValidator()
	{
		if (!$this->isCached('joinListValidator')) {
			$this->setCached('joinListValidator', new Validator\JoinList\JoinListValidator($this));
		}

		return $this->getCached('joinListValidator');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\JoinList\JoinValidator
	 */
	public function getJoinValidator()
	{
		if (!$this->isCached('joinValidator')) {
			$this->setCached('joinValidator', new Validator\JoinList\JoinValidator($this));
		}

		return $this->getCached('joinValidator');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\ValidatorList\ValidatorListValidator
	 */
	public function getValidatorListValidator()
	{
		if (!$this->isCached('validatorListValidator')) {
			$this->setCached('validatorListValidator', new Validator\ValidatorList\ValidatorListValidator($this));
		}

		return $this->getCached('validatorListValidator');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\DataValidator\ValidatorValidator
	 */
	public function getValidatorValidator()
	{
		if (!$this->isCached('validatorValidator')) {
			$this->setCached('validatorValidator', new Validator\DataValidator\ValidatorValidator($this));
		}

		return $this->getCached('validatorValidator');
	}
}
