<?php
namespace Sloth\Module\Data\Table;

use Sloth\Helper\InternalCacheTrait;
use Sloth\Base\AbstractModuleFactory;
use Sloth\Exception\InvalidArgumentException;
use Sloth\Module\Data\Table\DefinitionBuilder\LinkListBuilder;
use Sloth\Module\Data\Table\DefinitionBuilder\TableBuilder;
use Sloth\Module\Data\Table\DefinitionBuilder\TableFieldBuilder;
use Sloth\Module\Data\Table\DefinitionBuilder\TableFieldListBuilder;
use Sloth\Module\Data\Table\DefinitionBuilder\ValidatorListBuilder;

class Factory extends AbstractModuleFactory
{
	use InternalCacheTrait;

	public function initialise()
	{
		$module = new TableModule($this->app);

		$module->setTableManifestDirectory($this->options['tableManifestDirectory'])
			->setTableBuilder($this->getTableBuilder());

		return $module;
	}

	protected function validateOptions()
	{
		$required = array(
			'tableManifestDirectory'
		);

		$missing = array_diff($required, array_keys($this->options));
		if (!empty($missing)) {
			throw new InvalidArgumentException(
				'Missing required options for DataTable module: ' . implode(', ', $missing)
			);
		}

		if (!is_dir($this->options['tableManifestDirectory'])) {
			throw new InvalidArgumentException('Invalid table directory given in options for DataTable module');
		}
	}

	protected function getTableBuilder()
	{
		if (!$this->isCached('tableBuilder')) {
			$tableBuilder = new TableBuilder($this->options['tableManifestDirectory']);
			$tableBuilder->setSubBuilders(array(
				'linkListBuilder' => $this->getLinkListBuilder($tableBuilder),
				'tableFieldListBuilder' => $this->getTableFieldListBuilder(),
				'validatorListBuilder' => $this->getValidatorListBuilder()
			));

			$this->setCached('tableBuilder', $tableBuilder);
		}
		return $this->getCached('tableBuilder');
	}

	protected function getLinkListBuilder(TableBuilder $tableBuilder)
	{
		return new LinkListBuilder($tableBuilder);
	}

	protected function getTableFieldListBuilder()
	{
		return new TableFieldListBuilder($this->getTableFieldBuilder());
	}

	protected function getTableFieldBuilder() {
		return new TableFieldBuilder($this->getValidatorListBuilder());
	}

	protected function getValidatorListBuilder()
	{
		return new ValidatorListBuilder();
	}
}
