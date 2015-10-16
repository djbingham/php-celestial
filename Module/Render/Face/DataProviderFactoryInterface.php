<?php
namespace Module\Render\Face;

interface DataProviderFactoryInterface
{
	/**
	 * @param array $dependencies
	 */
	public function __construct(array $dependencies);

	/**
	 * @param array $providersManifest
	 * @return array
	 */
	public function buildProviders(array $providersManifest);
}