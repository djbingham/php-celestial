<?php
namespace Celestial\Module\DataProvider\Base;

use Celestial\Module\Authentication\AuthenticationModule;
use Celestial\Module\DataProvider\DataProviderModule;
use Celestial\Module\DataProvider\Face\DataProviderInterface;
use Celestial\Module\Data\Resource\ResourceModule;
use Celestial\Module\Request\RequestModule;
use Celestial\Module\Session\SessionModule;

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
