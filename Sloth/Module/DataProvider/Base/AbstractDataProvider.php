<?php
namespace Sloth\Module\DataProvider\Base;

use Sloth\Module\Authentication\AuthenticationModule;
use Sloth\Module\DataProvider\DataProviderModule;
use Sloth\Module\DataProvider\Face\DataProviderInterface;
use Sloth\Module\Data\Resource\ResourceModule;
use Sloth\Module\Request\RequestModule;
use Sloth\Module\Session\SessionModule;

abstract class AbstractDataProvider implements DataProviderInterface
{
	/**
	 * @var ResourceModule
	 */
	protected $resourceModule;

	/**
	 * @var DataProviderModule
	 */
	protected $dataProviderModule;

	/**
	 * @var SessionModule
	 */
	protected $sessionModule;

	/**
	 * @var AuthenticationModule
	 */
	protected $authenticationModule;

	/**
	 * @var RequestModule
	 */
	protected $requestModule;

	abstract protected function validateDependencies(array $dependencies);

	public function __construct(array $dependencies)
	{
		$this->validateDependencies($dependencies);
		$this->resourceModule = $dependencies['resourceModule'];
		$this->dataProviderModule = $dependencies['dataProviderModule'];
		$this->sessionModule = $dependencies['sessionModule'];
		$this->authenticationModule = $dependencies['authenticationModule'];
		$this->requestModule = $dependencies['requestModule'];
	}
}
