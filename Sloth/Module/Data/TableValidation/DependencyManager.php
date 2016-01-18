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
		return $this->app->module('data.table');
	}

	/**
	 * @return ValidationModule
	 */
	public function getValidationModule()
	{
		return $this->app->module('validation');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\FileValidator
	 */
	public function getTableManifestFileValidator()
	{
		return new Validator\FileValidator($this);
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
	 * @return \Sloth\Module\Data\TableValidation\Validator\JoinList\StructureValidator
	 */
	public function getJoinListStructureValidator()
	{
		if (!$this->isCached('joinListStructureValidator')) {
			$this->setCached('joinListStructureValidator', new Validator\JoinList\StructureValidator($this));
		}

		return $this->getCached('joinListStructureValidator');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\JoinList\AliasValidator
	 */
	public function getJoinListAliasValidator()
	{
		if (!$this->isCached('joinListAliasValidator')) {
			$this->setCached('joinListAliasValidator', new Validator\JoinList\AliasValidator($this));
		}

		return $this->getCached('joinListAliasValidator');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\Join\JoinValidator
	 */
	public function getJoinValidator()
	{
		if (!$this->isCached('joinValidator')) {
			$this->setCached('joinValidator', new Validator\Join\JoinValidator($this));
		}

		return $this->getCached('joinValidator');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\Join\StructureValidator
	 */
	public function getJoinStructureValidator()
	{
		if (!$this->isCached('joinStructureValidator')) {
			$this->setCached('joinStructureValidator', new Validator\Join\StructureValidator($this));
		}

		return $this->getCached('joinStructureValidator');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\Join\Property\TypeValidator
	 */
	public function getJoinTypeValidator()
	{
		if (!$this->isCached('joinTypeValidator')) {
			$this->setCached('joinTypeValidator', new Validator\Join\Property\TypeValidator($this));
		}

		return $this->getCached('joinTypeValidator');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\Join\Property\TableValidator
	 */
	public function getJoinTableValidator()
	{
		if (!$this->isCached('joinTableValidator')) {
			$this->setCached('joinTableValidator', new Validator\Join\Property\TableValidator($this));
		}

		return $this->getCached('joinTableValidator');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\Join\Property\JoinsValidator
	 */
	public function getJoinJoinsValidator()
	{
		if (!$this->isCached('joinJoinsValidator')) {
			$this->setCached('joinJoinsValidator', new Validator\Join\Property\JoinsValidator($this));
		}

		return $this->getCached('joinJoinsValidator');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\Join\Property\ViaValidator
	 */
	public function getJoinViaValidator()
	{
		if (!$this->isCached('joinViaValidator')) {
			$this->setCached('joinViaValidator', new Validator\Join\Property\ViaValidator($this));
		}

		return $this->getCached('joinViaValidator');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\Join\Property\Via\TableAliasValidator
	 */
	public function getJoinViaTableAliasValidator()
	{
		if (!$this->isCached('joinViaTableAliasValidator')) {
			$this->setCached('joinViaTableAliasValidator', new Validator\Join\Property\Via\TableAliasValidator($this));
		}

		return $this->getCached('joinViaTableAliasValidator');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\Join\Property\Via\TableNameValidator
	 */
	public function getJoinViaTableNameValidator()
	{
		if (!$this->isCached('joinViaTableNameValidator')) {
			$this->setCached('joinViaTableNameValidator', new Validator\Join\Property\Via\TableNameValidator($this));
		}

		return $this->getCached('joinViaTableNameValidator');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\Join\Property\OnInsertValidator
	 */
	public function getJoinOnInsertValidator()
	{
		if (!$this->isCached('joinOnInsertValidator')) {
			$this->setCached('joinOnInsertValidator', new Validator\Join\Property\OnInsertValidator($this));
		}

		return $this->getCached('joinOnInsertValidator');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\Join\Property\OnUpdateValidator
	 */
	public function getJoinOnUpdateValidator()
	{
		if (!$this->isCached('joinOnUpdateValidator')) {
			$this->setCached('joinOnUpdateValidator', new Validator\Join\Property\OnUpdateValidator($this));
		}

		return $this->getCached('joinOnUpdateValidator');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\Join\Property\OnDeleteValidator
	 */
	public function getJoinOnDeleteValidator()
	{
		if (!$this->isCached('joinOnDeleteValidator')) {
			$this->setCached('joinOnDeleteValidator', new Validator\Join\Property\OnDeleteValidator($this));
		}

		return $this->getCached('joinOnDeleteValidator');
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
	 * @return \Sloth\Module\Data\TableValidation\Validator\ValidatorList\StructureValidator
	 */
	public function getValidatorListStructureValidator()
	{
		if (!$this->isCached('validatorListStructureValidator')) {
			$this->setCached('validatorListStructureValidator', new Validator\ValidatorList\StructureValidator($this));
		}

		return $this->getCached('validatorListStructureValidator');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\Validator\ValidatorValidator
	 */
	public function getValidatorValidator()
	{
		if (!$this->isCached('validatorValidator')) {
			$this->setCached('validatorValidator', new Validator\Validator\ValidatorValidator($this));
		}

		return $this->getCached('validatorValidator');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\Validator\StructureValidator
	 */
	public function getValidatorStructureValidator()
	{
		if (!$this->isCached('validatorStructureValidator')) {
			$this->setCached('validatorStructureValidator', new Validator\Validator\StructureValidator($this));
		}

		return $this->getCached('validatorStructureValidator');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\Validator\Property\FieldsValidator
	 */
	public function getValidatorFieldsValidator()
	{
		if (!$this->isCached('validatorFieldsValidator')) {
			$this->setCached('validatorFieldsValidator', new Validator\Validator\Property\FieldsValidator($this));
		}

		return $this->getCached('validatorFieldsValidator');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\Validator\Property\OptionsValidator
	 */
	public function getValidatorOptionsValidator()
	{
		if (!$this->isCached('validatorOptionsValidator')) {
			$this->setCached('validatorOptionsValidator', new Validator\Validator\Property\OptionsValidator($this));
		}

		return $this->getCached('validatorOptionsValidator');
	}

	/**
	 * @return \Sloth\Module\Data\TableValidation\Validator\Validator\Property\RuleValidator
	 */
	public function getValidatorRuleValidator()
	{
		if (!$this->isCached('validatorRuleValidator')) {
			$this->setCached('validatorRuleValidator', new Validator\Validator\Property\RuleValidator($this));
		}

		return $this->getCached('validatorRuleValidator');
	}
}
