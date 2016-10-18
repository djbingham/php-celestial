<?php
namespace SlothDemo\Resource;

use Sloth\Module\Data\Resource\Definition\Resource\AttributeList;
use Sloth\Module\Data\Resource\ResourceFactory;

class UserFactory extends ResourceFactory
{
    public function getBy(AttributeList $attributes, array $filters = array())
    {
        header('CustomResourceFactory: User::getBy');
        return parent::getBy($attributes, $filters);
    }

    public function search(AttributeList $attributesToInclude, array $filters)
    {
        header('CustomResourceFactory: User::search');
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
