<?php
namespace Sloth\Module\Data\TableDataValidator\Result;

use Sloth\Module\Data\Table\Face\ValidatorInterface;
use Sloth\Module\Validation\Face\ValidationResultInterface;

class ExecutedValidator
{
	/**
	 * @var ValidatorInterface
	 */
	private $definition;

	/**
	 * @var ValidationResultInterface
	 */
	private $result;

	public function __construct(array $properties)
	{
		$this->definition = $properties['definition'];
		$this->result = $properties['result'];

		return $this;
	}

	public function getDefinition()
	{
		return $this->definition;
	}

	public function getResult()
	{
		return $this->result;
	}
}
