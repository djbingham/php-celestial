<?php
namespace CelestialDemo\Resource;

use Celestial\Module\Data\Resource\Definition\Resource\AttributeList;
use Celestial\Module\Data\Resource\ResourceFactory;
use Celestial\Module\Session\SessionModule;

class UserFactory extends ResourceFactory
{
	/**
	 * @var SessionModule
	 */
	private $session;

	public function initialise()
	{
		$this->session = $this->app->module('session');
		$this->session->set('foo', 'bar');
	}

	public function getBy(AttributeList $attributes, array $filters = array())
    {
        header('CustomResourceFactory: User::getBy');
        return parent::getBy($attributes, $filters);
    }

    public function search(AttributeList $attributesToInclude, array $filters)
    {
        header('CustomResourceFactory: User::search - session: foo = ' . $this->session->get('foo'));
        return parent::search($attributesToInclude, $filters);
    }

    public function create(array $attributes)
    {
        header('CustomResourceFactory: User::create');
        return parent::create($attributes);
    }

    public function update(array $filters, array $attributes)
    {
        header('CustomResourceFactory: User::update');
        return parent::update($filters, $attributes);
    }

    public function delete(array $filters)
    {
        header('CustomResourceFactory: User::delete');
        return parent::delete($filters);
    }
}
