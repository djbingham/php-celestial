<?php
namespace DemoGraph\Module\Graph\Test\Assertions;

use DemoGraph\Module\Graph\ResourceDefinition;

/**
 * Class ResourceBuilderAssertions
 * @package DemoGraph\Module\Graph\Test\Assertions
 *
 * @method assertEquals
 * @method assertSame
 * @method assertInstanceOf
 * @method assertNull
 */
trait ResourceBuilderAssertions
{
	public function assertBuiltResourceMatchesUserManifest(ResourceDefinition\Resource $resource)
	{
		$this->assertEquals('User', $resource->name);
		$this->assertEquals('User', $resource->table->name);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\AttributeList', $resource->attributes);
		$this->assertEquals(3, $resource->attributes->length());
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $resource->links);
		$this->assertEquals(3, $resource->links->length());
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\ValidatorList', $resource->validators);
		$this->assertEquals(0, $resource->validators->length());
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\ViewList', $resource->views);
		$this->assertEquals(0, $resource->views->length());

		$this->assertEquals('id', $resource->attributes->getByIndex(0)->name);
		$this->assertEquals('integer(11)', $resource->attributes->getByIndex(0)->type);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\TableField', $resource->attributes->getByIndex(0)->field);
		$this->assertSame($resource->table, $resource->attributes->getByIndex(1)->field->resource);
		$this->assertEquals('id', $resource->attributes->getByIndex(0)->field->name);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\ValidatorList', $resource->attributes->getByIndex(0)->validators);
		$this->assertEquals(0, $resource->attributes->getByIndex(0)->validators->length());

		$this->assertEquals('forename', $resource->attributes->getByIndex(1)->name);
		$this->assertEquals('text(50)', $resource->attributes->getByIndex(1)->type);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\TableField', $resource->attributes->getByIndex(1)->field);
		$this->assertSame($resource->table, $resource->attributes->getByIndex(1)->field->resource);
		$this->assertEquals('forename', $resource->attributes->getByIndex(1)->field->name);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\ValidatorList', $resource->attributes->getByIndex(1)->validators);
		$this->assertEquals(0, $resource->attributes->getByIndex(1)->validators->length());

		$this->assertEquals('surname', $resource->attributes->getByIndex(2)->name);
		$this->assertEquals('text(100)', $resource->attributes->getByIndex(2)->type);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\TableField', $resource->attributes->getByIndex(2)->field);
		$this->assertSame($resource->table, $resource->attributes->getByIndex(2)->field->resource);
		$this->assertEquals('surname', $resource->attributes->getByIndex(2)->field->name);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\ValidatorList', $resource->attributes->getByIndex(2)->validators);
		$this->assertEquals(0, $resource->attributes->getByIndex(2)->validators->length());
	}

	public function assertBuiltUserResourceLinksToFriendsSubResource(ResourceDefinition\Resource $resource)
	{
		$linkToFriends = $resource->links->getByName('friends');
		$this->assertEquals('friends', $linkToFriends->name);
		$this->assertSame($resource, $linkToFriends->parentResource);
		$this->assertEquals('User', $linkToFriends->childResourceName);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\TableList', $linkToFriends->intermediaryResources);
		$this->assertEquals(1, $linkToFriends->intermediaryResources->length());
		$this->assertEquals('friendLink', $linkToFriends->intermediaryResources->getByIndex(0)->alias);
		$this->assertEquals('UserFriend', $linkToFriends->intermediaryResources->getByIndex(0)->name);

		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkConstraintList', $linkToFriends->getConstraints());
		$this->assertEquals(1, $linkToFriends->getConstraints()->length());

		$joinToFriends = $linkToFriends->getConstraints()->getByIndex(0);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkConstraint', $joinToFriends);
		$this->assertSame($linkToFriends, $joinToFriends->link);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\Attribute', $joinToFriends->parentAttribute);
		$this->assertEquals('id', $joinToFriends->parentAttribute->name);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\Attribute', $joinToFriends->childAttribute);
		$this->assertEquals('id', $joinToFriends->childAttribute->name);

		$subJoinsToFriends = $linkToFriends->getConstraints()->getByIndex(0)->subJoins;
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\TableJoinList', $subJoinsToFriends);
		$this->assertEquals(2, $subJoinsToFriends->length());

		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\TableJoin', $subJoinsToFriends->getByIndex(0));
		$this->assertSame($resource->table, $subJoinsToFriends->getByIndex(0)->parentResource);
		$this->assertEquals($resource->attributes->getByName('id')->field, $subJoinsToFriends->getByIndex(0)->parentAttribute);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\Table', $subJoinsToFriends->getByIndex(0)->childResource);
		$this->assertEquals('UserFriend', $subJoinsToFriends->getByIndex(0)->childResource->name);
		$this->assertEquals('User_friendLink', $subJoinsToFriends->getByIndex(0)->childResource->alias);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\TableField', $subJoinsToFriends->getByIndex(0)->childAttribute);
		$this->assertEquals('friendId1', $subJoinsToFriends->getByIndex(0)->childAttribute->name);
		$this->assertSame($subJoinsToFriends->getByIndex(0)->childResource, $subJoinsToFriends->getByIndex(0)->childAttribute->resource);
		$this->assertEquals('User_friendLink.friendId1', $subJoinsToFriends->getByIndex(0)->childAttribute->alias);

		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\TableJoin', $subJoinsToFriends->getByIndex(1));
		$this->assertSame($subJoinsToFriends->getByIndex(0)->childResource, $subJoinsToFriends->getByIndex(1)->parentResource);

		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\TableField', $subJoinsToFriends->getByIndex(1)->parentAttribute);
		$this->assertEquals('friendId2', $subJoinsToFriends->getByIndex(1)->parentAttribute->name);
		$this->assertSame($subJoinsToFriends->getByIndex(0)->childResource, $subJoinsToFriends->getByIndex(1)->parentAttribute->resource);
		$this->assertEquals('User_friendLink.friendId2', $subJoinsToFriends->getByIndex(1)->parentAttribute->alias);

		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\Table', $subJoinsToFriends->getByIndex(1)->childResource);
		$this->assertEquals('User', $subJoinsToFriends->getByIndex(1)->childResource->name);
		$this->assertEquals('User_friends', $subJoinsToFriends->getByIndex(1)->childResource->alias);
	}

	public function assertBuiltUserResourceLinksToPostsSubResource(ResourceDefinition\Resource $resource)
	{
		$linkToPosts = $resource->links->getByName('posts');
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\Link', $linkToPosts);
		$this->assertEquals('posts', $linkToPosts->name);
		$this->assertSame($resource, $linkToPosts->parentResource);
		$this->assertEquals('Post', $linkToPosts->childResourceName);
		$this->assertNull($linkToPosts->intermediaryResources);

		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkConstraintList', $linkToPosts->getConstraints());
		$this->assertEquals(1, $linkToPosts->getConstraints()->length());

		$joinToPosts = $linkToPosts->getConstraints()->getByIndex(0);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkConstraint', $joinToPosts);
		$this->assertSame($linkToPosts, $joinToPosts->link);
		$this->assertNull($joinToPosts->subJoins);

		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\Attribute', $joinToPosts->parentAttribute);
		$this->assertSame($resource->attributes->getByName('id'), $joinToPosts->parentAttribute);

		$this->assertEquals('authorId', $joinToPosts->childAttribute->name);
		$this->assertEquals('integer(11)', $joinToPosts->childAttribute->type);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\TableField', $joinToPosts->childAttribute->field);
		$this->assertEquals('authorId', $joinToPosts->childAttribute->field->name);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\Table', $joinToPosts->childAttribute->table);
		$this->assertEquals('Post', $joinToPosts->childAttribute->table->name);
		$this->assertEquals($linkToPosts->getChildResource(), $joinToPosts->childAttribute->resource);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\ValidatorList', $joinToPosts->childAttribute->validators);
		$this->assertEquals(0, $joinToPosts->childAttribute->validators->length());
	}

	public function assertBuiltUserResourceLinksToAddressSubResource(ResourceDefinition\Resource $resource)
	{
        $linkToAddress = $resource->links->getByName('address');
        $this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\Link', $linkToAddress);
        $this->assertEquals('address', $linkToAddress->name);
        $this->assertSame($resource, $linkToAddress->parentResource);
        $this->assertEquals('UserAddress', $linkToAddress->childResourceName);
        $this->assertNull($linkToAddress->intermediaryResources);

        $this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkConstraintList', $linkToAddress->getConstraints());
        $this->assertEquals(1, $linkToAddress->getConstraints()->length());

        $joinToAddress = $linkToAddress->getConstraints()->getByIndex(0);
        $this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkConstraint', $joinToAddress);
        $this->assertSame($linkToAddress, $joinToAddress->link);
        $this->assertNull($joinToAddress->subJoins);

        $this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\Attribute', $joinToAddress->parentAttribute);
        $this->assertSame($resource->attributes->getByName('id'), $joinToAddress->parentAttribute);

        $this->assertEquals('userId', $joinToAddress->childAttribute->name);
        $this->assertEquals('integer(11)', $joinToAddress->childAttribute->type);
        $this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\TableField', $joinToAddress->childAttribute->field);
        $this->assertEquals('userId', $joinToAddress->childAttribute->field->name);
        $this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\Table', $joinToAddress->childAttribute->table);
        $this->assertEquals('UserAddress', $joinToAddress->childAttribute->table->name);
        $this->assertEquals($linkToAddress->getChildResource(), $joinToAddress->childAttribute->resource);
        $this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\ValidatorList', $joinToAddress->childAttribute->validators);
        $this->assertEquals(0, $joinToAddress->childAttribute->validators->length());
	}

	public function assertBuiltResourceMatchesPostManifest(ResourceDefinition\Resource $resource)
	{
		$this->assertEquals('Post', $resource->name);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\Table', $resource->table);
		$this->assertEquals('Post', $resource->table->name);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\AttributeList', $resource->attributes);
		$this->assertEquals(3, $resource->attributes->length());
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkList', $resource->links);
		$this->assertEquals(1, $resource->links->length());
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\ValidatorList', $resource->validators);
		$this->assertEquals(0, $resource->validators->length());
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\ViewList', $resource->views);
		$this->assertEquals(0, $resource->views->length());

		$this->assertSame($resource, $resource->attributes->getByIndex(0)->resource);
		$this->assertSame($resource->table, $resource->attributes->getByIndex(2)->table);
		$this->assertEquals('id', $resource->attributes->getByIndex(0)->name);
		$this->assertEquals('integer(11)', $resource->attributes->getByIndex(0)->type);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\TableField', $resource->attributes->getByIndex(0)->field);
		$this->assertEquals('id', $resource->attributes->getByIndex(0)->field->name);
		$this->assertEquals('Post.id', $resource->attributes->getByIndex(0)->field->alias);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\ValidatorList', $resource->attributes->getByIndex(0)->validators);
		$this->assertEquals(0, $resource->attributes->getByIndex(0)->validators->length());

		$this->assertSame($resource, $resource->attributes->getByIndex(1)->resource);
		$this->assertSame($resource->table, $resource->attributes->getByIndex(2)->table);
		$this->assertEquals('authorId', $resource->attributes->getByIndex(1)->name);
		$this->assertEquals('integer(11)', $resource->attributes->getByIndex(1)->type);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\TableField', $resource->attributes->getByIndex(1)->field);
		$this->assertEquals('authorId', $resource->attributes->getByIndex(1)->field->name);
		$this->assertEquals('Post.authorId', $resource->attributes->getByIndex(1)->field->alias);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\ValidatorList', $resource->attributes->getByIndex(1)->validators);
		$this->assertEquals(0, $resource->attributes->getByIndex(1)->validators->length());

		$this->assertSame($resource, $resource->attributes->getByIndex(2)->resource);
		$this->assertSame($resource->table, $resource->attributes->getByIndex(2)->table);
		$this->assertEquals('content', $resource->attributes->getByIndex(2)->name);
		$this->assertEquals('text', $resource->attributes->getByIndex(2)->type);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\TableField', $resource->attributes->getByIndex(2)->field);
		$this->assertEquals('content', $resource->attributes->getByIndex(2)->field->name);
		$this->assertEquals('Post.content', $resource->attributes->getByIndex(2)->field->alias);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\ValidatorList', $resource->attributes->getByIndex(2)->validators);
		$this->assertEquals(0, $resource->attributes->getByIndex(2)->validators->length());

		$this->assertEquals('author', $resource->links->getByIndex(0)->name);
		$this->assertSame($resource, $resource->links->getByIndex(0)->parentResource);
		$this->assertEquals('User', $resource->links->getByIndex(0)->childResourceName);
	}

	public function assertBuiltPostResourceLinksToAuthorSubResource(ResourceDefinition\Resource $resource)
	{
		$linkToAuthor = $resource->links->getByName('author');
		$this->assertEquals('author', $linkToAuthor->name);
		$this->assertSame($resource, $linkToAuthor->parentResource);
		$this->assertEquals('User', $linkToAuthor->childResourceName);
		$this->assertNull($linkToAuthor->intermediaryResources);

		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkConstraintList', $linkToAuthor->getConstraints());
		$this->assertEquals(1, $linkToAuthor->getConstraints()->length());

		$joinToAuthor = $linkToAuthor->getConstraints()->getByIndex(0);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkConstraint', $joinToAuthor);
		$this->assertSame($linkToAuthor, $joinToAuthor->link);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\Attribute', $joinToAuthor->parentAttribute);
		$this->assertEquals('authorId', $joinToAuthor->parentAttribute->name);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\Attribute', $joinToAuthor->childAttribute);
		$this->assertEquals('id', $joinToAuthor->childAttribute->name);

		$joinToAuthor = $linkToAuthor->getConstraints()->getByIndex(0);
		$this->assertSame($linkToAuthor, $joinToAuthor->link);
		$this->assertNull($joinToAuthor->subJoins);

		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\LinkConstraint', $joinToAuthor);
		$this->assertSame($resource->attributes->getByName('authorId'), $joinToAuthor->parentAttribute);

		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\Attribute', $joinToAuthor->childAttribute);
		$this->assertBuiltResourceMatchesUserManifest($joinToAuthor->childAttribute->resource);
		$this->assertEquals('id', $joinToAuthor->childAttribute->name);
		$this->assertEquals('integer(11)', $joinToAuthor->childAttribute->type);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\Table', $joinToAuthor->childAttribute->table);
		$this->assertEquals('User', $joinToAuthor->childAttribute->table->name);
		$this->assertEquals('author', $joinToAuthor->childAttribute->table->alias);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\TableField', $joinToAuthor->childAttribute->field);
		$this->assertSame($joinToAuthor->childAttribute->table, $joinToAuthor->childAttribute->field->resource);
		$this->assertEquals('id', $joinToAuthor->childAttribute->field->name);
		$this->assertEquals('User.id', $joinToAuthor->childAttribute->field->alias);
		$this->assertInstanceOf('DemoGraph\Module\Graph\ResourceDefinition\ValidatorList', $joinToAuthor->childAttribute->validators);
		$this->assertEquals(0, $joinToAuthor->childAttribute->validators->length());
	}
}
