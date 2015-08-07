<?php
namespace Sloth\Module\Graph\Test\Assertions;

use Sloth\Module\Graph\Definition;

/**
 * Class ResourceBuilderAssertions
 * @package Sloth\Module\Graph\Test\Assertions
 *
 * @method assertEquals
 * @method assertSame
 * @method assertInstanceOf
 * @method assertNull
 */
trait ResourceBuilderAssertions
{
	public function assertBuiltResourceMatchesUserManifest(Definition\Table $resource)
	{
		$this->assertEquals('User', $resource->name);
		$this->assertEquals('User', $resource->table->name);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\AttributeList', $resource->fields);
		$this->assertEquals(3, $resource->fields->length());
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\LinkList', $resource->links);
		$this->assertEquals(3, $resource->links->length());
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\ValidatorList', $resource->validators);
		$this->assertEquals(0, $resource->validators->length());
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\ViewList', $resource->views);
		$this->assertEquals(0, $resource->views->length());

		$this->assertEquals('id', $resource->fields->getByIndex(0)->name);
		$this->assertEquals('integer(11)', $resource->fields->getByIndex(0)->type);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\TableField', $resource->fields->getByIndex(0)->field);
		$this->assertSame($resource->table, $resource->fields->getByIndex(1)->field->resource);
		$this->assertEquals('id', $resource->fields->getByIndex(0)->field->name);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\ValidatorList', $resource->fields->getByIndex(0)->validators);
		$this->assertEquals(0, $resource->fields->getByIndex(0)->validators->length());

		$this->assertEquals('forename', $resource->fields->getByIndex(1)->name);
		$this->assertEquals('text(50)', $resource->fields->getByIndex(1)->type);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\TableField', $resource->fields->getByIndex(1)->field);
		$this->assertSame($resource->table, $resource->fields->getByIndex(1)->field->resource);
		$this->assertEquals('forename', $resource->fields->getByIndex(1)->field->name);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\ValidatorList', $resource->fields->getByIndex(1)->validators);
		$this->assertEquals(0, $resource->fields->getByIndex(1)->validators->length());

		$this->assertEquals('surname', $resource->fields->getByIndex(2)->name);
		$this->assertEquals('text(100)', $resource->fields->getByIndex(2)->type);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\TableField', $resource->fields->getByIndex(2)->field);
		$this->assertSame($resource->table, $resource->fields->getByIndex(2)->field->resource);
		$this->assertEquals('surname', $resource->fields->getByIndex(2)->field->name);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\ValidatorList', $resource->fields->getByIndex(2)->validators);
		$this->assertEquals(0, $resource->fields->getByIndex(2)->validators->length());
	}

	public function assertBuiltUserResourceLinksToFriendsSubResource(Definition\Table $resource)
	{
		$linkToFriends = $resource->links->getByName('friends');
		$this->assertEquals('friends', $linkToFriends->name);
		$this->assertSame($resource, $linkToFriends->parentTable);
		$this->assertEquals('User', $linkToFriends->childTableName);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\TableList', $linkToFriends->intermediaryTables);
		$this->assertEquals(1, $linkToFriends->intermediaryTables->length());
		$this->assertEquals('friendLink', $linkToFriends->intermediaryTables->getByIndex(0)->alias);
		$this->assertEquals('UserFriend', $linkToFriends->intermediaryTables->getByIndex(0)->name);

		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\LinkConstraintList', $linkToFriends->getConstraints());
		$this->assertEquals(1, $linkToFriends->getConstraints()->length());

		$joinToFriends = $linkToFriends->getConstraints()->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\LinkConstraint', $joinToFriends);
		$this->assertSame($linkToFriends, $joinToFriends->link);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\Attribute', $joinToFriends->parentAttribute);
		$this->assertEquals('id', $joinToFriends->parentAttribute->name);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\Attribute', $joinToFriends->childAttribute);
		$this->assertEquals('id', $joinToFriends->childAttribute->name);

		$subJoinsToFriends = $linkToFriends->getConstraints()->getByIndex(0)->subJoins;
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\TableJoinList', $subJoinsToFriends);
		$this->assertEquals(2, $subJoinsToFriends->length());

		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\TableJoin', $subJoinsToFriends->getByIndex(0));
		$this->assertSame($resource->table, $subJoinsToFriends->getByIndex(0)->parentTable);
		$this->assertEquals($resource->fields->getByName('id')->field, $subJoinsToFriends->getByIndex(0)->parentAttribute);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\Table', $subJoinsToFriends->getByIndex(0)->childTable);
		$this->assertEquals('UserFriend', $subJoinsToFriends->getByIndex(0)->childTable->name);
		$this->assertEquals('User_friendLink', $subJoinsToFriends->getByIndex(0)->childTable->alias);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\TableField', $subJoinsToFriends->getByIndex(0)->childAttribute);
		$this->assertEquals('friendId1', $subJoinsToFriends->getByIndex(0)->childAttribute->name);
		$this->assertSame($subJoinsToFriends->getByIndex(0)->childTable, $subJoinsToFriends->getByIndex(0)->childAttribute->table);
		$this->assertEquals('User_friendLink.friendId1', $subJoinsToFriends->getByIndex(0)->childAttribute->alias);

		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\TableJoin', $subJoinsToFriends->getByIndex(1));
		$this->assertSame($subJoinsToFriends->getByIndex(0)->childTable, $subJoinsToFriends->getByIndex(1)->parentTable);

		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\TableField', $subJoinsToFriends->getByIndex(1)->parentAttribute);
		$this->assertEquals('friendId2', $subJoinsToFriends->getByIndex(1)->parentAttribute->name);
		$this->assertSame($subJoinsToFriends->getByIndex(0)->childTable, $subJoinsToFriends->getByIndex(1)->parentAttribute->table);
		$this->assertEquals('User_friendLink.friendId2', $subJoinsToFriends->getByIndex(1)->parentAttribute->alias);

		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\Table', $subJoinsToFriends->getByIndex(1)->childTable);
		$this->assertEquals('User', $subJoinsToFriends->getByIndex(1)->childTable->name);
		$this->assertEquals('User_friends', $subJoinsToFriends->getByIndex(1)->childTable->alias);
	}

	public function assertBuiltUserResourceLinksToPostsSubResource(Definition\Table $resource)
	{
		$linkToPosts = $resource->links->getByName('posts');
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\Link', $linkToPosts);
		$this->assertEquals('posts', $linkToPosts->name);
		$this->assertSame($resource, $linkToPosts->parentTable);
		$this->assertEquals('Post', $linkToPosts->childTableName);
		$this->assertNull($linkToPosts->intermediaryTables);

		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\LinkConstraintList', $linkToPosts->getConstraints());
		$this->assertEquals(1, $linkToPosts->getConstraints()->length());

		$joinToPosts = $linkToPosts->getConstraints()->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\LinkConstraint', $joinToPosts);
		$this->assertSame($linkToPosts, $joinToPosts->link);
		$this->assertNull($joinToPosts->subJoins);

		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\Attribute', $joinToPosts->parentAttribute);
		$this->assertSame($resource->fields->getByName('id'), $joinToPosts->parentAttribute);

		$this->assertEquals('authorId', $joinToPosts->childAttribute->name);
		$this->assertEquals('integer(11)', $joinToPosts->childAttribute->type);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\TableField', $joinToPosts->childAttribute->field);
		$this->assertEquals('authorId', $joinToPosts->childAttribute->field->name);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\Table', $joinToPosts->childAttribute->table);
		$this->assertEquals('Post', $joinToPosts->childAttribute->table->name);
		$this->assertEquals($linkToPosts->getChildTable(), $joinToPosts->childAttribute->table);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\ValidatorList', $joinToPosts->childAttribute->validators);
		$this->assertEquals(0, $joinToPosts->childAttribute->validators->length());
	}

	public function assertBuiltUserResourceLinksToAddressSubResource(Definition\Table $resource)
	{
        $linkToAddress = $resource->links->getByName('address');
        $this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\Link', $linkToAddress);
        $this->assertEquals('address', $linkToAddress->name);
        $this->assertSame($resource, $linkToAddress->parentTable);
        $this->assertEquals('UserAddress', $linkToAddress->childTableName);
        $this->assertNull($linkToAddress->intermediaryTables);

        $this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\LinkConstraintList', $linkToAddress->getConstraints());
        $this->assertEquals(1, $linkToAddress->getConstraints()->length());

        $joinToAddress = $linkToAddress->getConstraints()->getByIndex(0);
        $this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\LinkConstraint', $joinToAddress);
        $this->assertSame($linkToAddress, $joinToAddress->link);
        $this->assertNull($joinToAddress->subJoins);

        $this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\Attribute', $joinToAddress->parentAttribute);
        $this->assertSame($resource->fields->getByName('id'), $joinToAddress->parentAttribute);

        $this->assertEquals('userId', $joinToAddress->childAttribute->name);
        $this->assertEquals('integer(11)', $joinToAddress->childAttribute->type);
        $this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\TableField', $joinToAddress->childAttribute->field);
        $this->assertEquals('userId', $joinToAddress->childAttribute->field->name);
        $this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\Table', $joinToAddress->childAttribute->table);
        $this->assertEquals('UserAddress', $joinToAddress->childAttribute->table->name);
        $this->assertEquals($linkToAddress->getChildTable(), $joinToAddress->childAttribute->table);
        $this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\ValidatorList', $joinToAddress->childAttribute->validators);
        $this->assertEquals(0, $joinToAddress->childAttribute->validators->length());
	}

	public function assertBuiltResourceMatchesPostManifest(Definition\Table $resource)
	{
		$this->assertEquals('Post', $resource->name);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\Table', $resource->table);
		$this->assertEquals('Post', $resource->table->name);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\AttributeList', $resource->fields);
		$this->assertEquals(3, $resource->fields->length());
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\LinkList', $resource->links);
		$this->assertEquals(1, $resource->links->length());
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\ValidatorList', $resource->validators);
		$this->assertEquals(0, $resource->validators->length());
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\ViewList', $resource->views);
		$this->assertEquals(0, $resource->views->length());

		$this->assertSame($resource, $resource->fields->getByIndex(0)->table);
		$this->assertSame($resource->table, $resource->fields->getByIndex(2)->table);
		$this->assertEquals('id', $resource->fields->getByIndex(0)->name);
		$this->assertEquals('integer(11)', $resource->fields->getByIndex(0)->type);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\TableField', $resource->fields->getByIndex(0)->field);
		$this->assertEquals('id', $resource->fields->getByIndex(0)->field->name);
		$this->assertEquals('Post.id', $resource->fields->getByIndex(0)->field->alias);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\ValidatorList', $resource->fields->getByIndex(0)->validators);
		$this->assertEquals(0, $resource->fields->getByIndex(0)->validators->length());

		$this->assertSame($resource, $resource->fields->getByIndex(1)->table);
		$this->assertSame($resource->table, $resource->fields->getByIndex(2)->table);
		$this->assertEquals('authorId', $resource->fields->getByIndex(1)->name);
		$this->assertEquals('integer(11)', $resource->fields->getByIndex(1)->type);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\TableField', $resource->fields->getByIndex(1)->field);
		$this->assertEquals('authorId', $resource->fields->getByIndex(1)->field->name);
		$this->assertEquals('Post.authorId', $resource->fields->getByIndex(1)->field->alias);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\ValidatorList', $resource->fields->getByIndex(1)->validators);
		$this->assertEquals(0, $resource->fields->getByIndex(1)->validators->length());

		$this->assertSame($resource, $resource->fields->getByIndex(2)->table);
		$this->assertSame($resource->table, $resource->fields->getByIndex(2)->table);
		$this->assertEquals('content', $resource->fields->getByIndex(2)->name);
		$this->assertEquals('text', $resource->fields->getByIndex(2)->type);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\TableField', $resource->fields->getByIndex(2)->field);
		$this->assertEquals('content', $resource->fields->getByIndex(2)->field->name);
		$this->assertEquals('Post.content', $resource->fields->getByIndex(2)->field->alias);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\ValidatorList', $resource->fields->getByIndex(2)->validators);
		$this->assertEquals(0, $resource->fields->getByIndex(2)->validators->length());

		$this->assertEquals('author', $resource->links->getByIndex(0)->name);
		$this->assertSame($resource, $resource->links->getByIndex(0)->parentTable);
		$this->assertEquals('User', $resource->links->getByIndex(0)->childTableName);
	}

	public function assertBuiltPostResourceLinksToAuthorSubResource(Definition\Table $resource)
	{
		$linkToAuthor = $resource->links->getByName('author');
		$this->assertEquals('author', $linkToAuthor->name);
		$this->assertSame($resource, $linkToAuthor->parentTable);
		$this->assertEquals('User', $linkToAuthor->childTableName);
		$this->assertNull($linkToAuthor->intermediaryTables);

		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\LinkConstraintList', $linkToAuthor->getConstraints());
		$this->assertEquals(1, $linkToAuthor->getConstraints()->length());

		$joinToAuthor = $linkToAuthor->getConstraints()->getByIndex(0);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\LinkConstraint', $joinToAuthor);
		$this->assertSame($linkToAuthor, $joinToAuthor->link);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\Attribute', $joinToAuthor->parentAttribute);
		$this->assertEquals('authorId', $joinToAuthor->parentAttribute->name);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\Attribute', $joinToAuthor->childAttribute);
		$this->assertEquals('id', $joinToAuthor->childAttribute->name);

		$joinToAuthor = $linkToAuthor->getConstraints()->getByIndex(0);
		$this->assertSame($linkToAuthor, $joinToAuthor->link);
		$this->assertNull($joinToAuthor->subJoins);

		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\LinkConstraint', $joinToAuthor);
		$this->assertSame($resource->fields->getByName('authorId'), $joinToAuthor->parentAttribute);

		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\Attribute', $joinToAuthor->childAttribute);
		$this->assertBuiltResourceMatchesUserManifest($joinToAuthor->childAttribute->table);
		$this->assertEquals('id', $joinToAuthor->childAttribute->name);
		$this->assertEquals('integer(11)', $joinToAuthor->childAttribute->type);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\Table', $joinToAuthor->childAttribute->table);
		$this->assertEquals('User', $joinToAuthor->childAttribute->table->name);
		$this->assertEquals('author', $joinToAuthor->childAttribute->table->alias);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\TableField', $joinToAuthor->childAttribute->field);
		$this->assertSame($joinToAuthor->childAttribute->table, $joinToAuthor->childAttribute->field->resource);
		$this->assertEquals('id', $joinToAuthor->childAttribute->field->name);
		$this->assertEquals('User.id', $joinToAuthor->childAttribute->field->alias);
		$this->assertInstanceOf('Sloth\Module\Graph\TableDefinition\ValidatorList', $joinToAuthor->childAttribute->validators);
		$this->assertEquals(0, $joinToAuthor->childAttribute->validators->length());
	}
}
