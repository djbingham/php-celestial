<?php
namespace Celestial\Module\Data\Table\Test\Assertions;

use Celestial\Module\Data\Table\Definition;

/**
 * Class TableDefinitionAssertions
 * @package Celestial\Module\Data\Table\Test\Assertions
 *
 * @method assertEquals($expected, $actual)
 * @method assertSame($expected, $actual)
 * @method assertInstanceOf($expected, $actual)
 * @method assertNull($actual)
 */
trait TableDefinitionAssertions
{
	public function assertBuiltTableMatchesUserManifest(Definition\Table $table)
	{
		$this->assertEquals('User', $table->name);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\FieldList', $table->fields);
		$this->assertEquals(3, $table->fields->length());
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\JoinList', $table->links);
		$this->assertEquals(3, $table->links->length());
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\ValidatorList', $table->validators);
		$this->assertEquals(0, $table->validators->length());

		$this->assertEquals('id', $table->fields->getByIndex(0)->name);
		$this->assertEquals('integer(11)', $table->fields->getByIndex(0)->type);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Field', $table->fields->getByIndex(0));
		$this->assertSame($table, $table->fields->getByIndex(1)->table);
		$this->assertEquals('id', $table->fields->getByIndex(0)->name);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\ValidatorList', $table->fields->getByIndex(0)->validators);
		$this->assertEquals(0, $table->fields->getByIndex(0)->validators->length());

		$this->assertEquals('forename', $table->fields->getByIndex(1)->name);
		$this->assertEquals('text(50)', $table->fields->getByIndex(1)->type);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Field', $table->fields->getByIndex(1));
		$this->assertSame($table, $table->fields->getByIndex(1)->table);
		$this->assertEquals('forename', $table->fields->getByIndex(1)->name);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\ValidatorList', $table->fields->getByIndex(1)->validators);
		$this->assertEquals(0, $table->fields->getByIndex(1)->validators->length());

		$this->assertEquals('surname', $table->fields->getByIndex(2)->name);
		$this->assertEquals('text(100)', $table->fields->getByIndex(2)->type);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Field', $table->fields->getByIndex(2));
		$this->assertSame($table, $table->fields->getByIndex(2)->table);
		$this->assertEquals('surname', $table->fields->getByIndex(2)->name);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\ValidatorList', $table->fields->getByIndex(2)->validators);
		$this->assertEquals(0, $table->fields->getByIndex(2)->validators->length());
	}

	public function assertBuiltUserTableJoinsToFriendsTable(Definition\Table $table)
	{
		$linkToFriends = $table->links->getByName('friends');
		$this->assertEquals('friends', $linkToFriends->name);
		$this->assertSame($table, $linkToFriends->parentTable);
		$this->assertEquals('User', $linkToFriends->childTableName);
		$this->assertEquals(Definition\Table\Join::MANY_TO_MANY, $linkToFriends->type);
		$this->assertEquals(Definition\Table\Join::ACTION_ASSOCIATE, $linkToFriends->onInsert);
		$this->assertEquals(Definition\Table\Join::ACTION_ASSOCIATE, $linkToFriends->onUpdate);
		$this->assertEquals(Definition\Table\Join::ACTION_ASSOCIATE, $linkToFriends->onDelete);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\TableList', $linkToFriends->intermediaryTables);
		$this->assertEquals(1, $linkToFriends->intermediaryTables->length());
		$this->assertEquals('friendLink', $linkToFriends->intermediaryTables->getByIndex(0)->alias);
		$this->assertEquals('UserFriend', $linkToFriends->intermediaryTables->getByIndex(0)->name);

		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Join\ConstraintList', $linkToFriends->getConstraints());
		$this->assertEquals(1, $linkToFriends->getConstraints()->length());

		$joinToFriends = $linkToFriends->getConstraints()->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Join\Constraint', $joinToFriends);
		$this->assertSame($linkToFriends, $joinToFriends->link);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Field', $joinToFriends->parentField);
		$this->assertEquals('id', $joinToFriends->parentField->name);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Field', $joinToFriends->childField);
		$this->assertEquals('id', $joinToFriends->childField->name);

		$subJoinsToFriends = $linkToFriends->getConstraints()->getByIndex(0)->subJoins;
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Join\SubJoinList', $subJoinsToFriends);
		$this->assertEquals(2, $subJoinsToFriends->length());

		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Join\SubJoin', $subJoinsToFriends->getByIndex(0));
		$this->assertSame($table, $subJoinsToFriends->getByIndex(0)->parentTable);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Field', $subJoinsToFriends->getByIndex(0)->parentField);
		$this->assertSame($table->fields->getByName('id')->table, $subJoinsToFriends->getByIndex(0)->parentField->table);
		$this->assertEquals($table->fields->getByName('id')->name, $subJoinsToFriends->getByIndex(0)->parentField->name);
		$this->assertEquals($table->fields->getByName('id')->alias, $subJoinsToFriends->getByIndex(0)->parentField->alias);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table', $subJoinsToFriends->getByIndex(0)->childTable);
		$this->assertEquals('UserFriend', $subJoinsToFriends->getByIndex(0)->childTable->name);
		$this->assertEquals('User_friendLink', $subJoinsToFriends->getByIndex(0)->childTable->alias);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Field', $subJoinsToFriends->getByIndex(0)->childField);
		$this->assertEquals('friendId1', $subJoinsToFriends->getByIndex(0)->childField->name);
		$this->assertSame($subJoinsToFriends->getByIndex(0)->childTable, $subJoinsToFriends->getByIndex(0)->childField->table);
		$this->assertEquals('User_friendLink.friendId1', $subJoinsToFriends->getByIndex(0)->childField->alias);

		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Join\SubJoin', $subJoinsToFriends->getByIndex(1));
		$this->assertSame($subJoinsToFriends->getByIndex(0)->childTable, $subJoinsToFriends->getByIndex(1)->parentTable);

		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Field', $subJoinsToFriends->getByIndex(1)->parentField);
		$this->assertEquals('friendId2', $subJoinsToFriends->getByIndex(1)->parentField->name);
		$this->assertSame($subJoinsToFriends->getByIndex(0)->childTable, $subJoinsToFriends->getByIndex(1)->parentField->table);
		$this->assertEquals('User_friendLink.friendId2', $subJoinsToFriends->getByIndex(1)->parentField->alias);

		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table', $subJoinsToFriends->getByIndex(1)->childTable);
		$this->assertEquals('User', $subJoinsToFriends->getByIndex(1)->childTable->name);
		$this->assertEquals('User_friends', $subJoinsToFriends->getByIndex(1)->childTable->alias);
	}

	public function assertBuiltUserTableJoinsToPostsTable(Definition\Table $table)
	{
		$linkToPosts = $table->links->getByName('posts');
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Join', $linkToPosts);
		$this->assertEquals('posts', $linkToPosts->name);
		$this->assertEquals(Definition\Table\Join::ONE_TO_MANY, $linkToPosts->type);
		$this->assertEquals(Definition\Table\Join::ACTION_INSERT, $linkToPosts->onInsert);
		$this->assertEquals(Definition\Table\Join::ACTION_IGNORE, $linkToPosts->onUpdate);
		$this->assertEquals(Definition\Table\Join::ACTION_IGNORE, $linkToPosts->onDelete);
		$this->assertSame($table, $linkToPosts->parentTable);
		$this->assertEquals('Post', $linkToPosts->childTableName);
		$this->assertNull($linkToPosts->intermediaryTables);

		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Join\ConstraintList', $linkToPosts->getConstraints());
		$this->assertEquals(1, $linkToPosts->getConstraints()->length());

		$joinToPosts = $linkToPosts->getConstraints()->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Join\Constraint', $joinToPosts);
		$this->assertSame($linkToPosts, $joinToPosts->link);
		$this->assertNull($joinToPosts->subJoins);

		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Field', $joinToPosts->parentField);
		$this->assertSame($table->fields->getByName('id'), $joinToPosts->parentField);

		$this->assertEquals('authorId', $joinToPosts->childField->name);
		$this->assertEquals('integer(11)', $joinToPosts->childField->type);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Field', $joinToPosts->childField);
		$this->assertEquals('authorId', $joinToPosts->childField->name);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table', $joinToPosts->childField->table);
		$this->assertEquals('Post', $joinToPosts->childField->table->name);
		$this->assertEquals($linkToPosts->getChildTable(), $joinToPosts->childField->table);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\ValidatorList', $joinToPosts->childField->validators);
		$this->assertEquals(0, $joinToPosts->childField->validators->length());
	}

	public function assertBuiltUserTableJoinsToAddressSubTable(Definition\Table $table)
	{
        $linkToAddress = $table->links->getByName('address');
        $this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Join', $linkToAddress);
        $this->assertEquals('address', $linkToAddress->name);
		$this->assertEquals(Definition\Table\Join::ONE_TO_ONE, $linkToAddress->type);
		$this->assertEquals(Definition\Table\Join::ACTION_INSERT, $linkToAddress->onInsert);
		$this->assertEquals(Definition\Table\Join::ACTION_UPDATE, $linkToAddress->onUpdate);
		$this->assertEquals(Definition\Table\Join::ACTION_DELETE, $linkToAddress->onDelete);
        $this->assertSame($table, $linkToAddress->parentTable);
        $this->assertEquals('UserAddress', $linkToAddress->childTableName);
        $this->assertNull($linkToAddress->intermediaryTables);

        $this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Join\ConstraintList', $linkToAddress->getConstraints());
        $this->assertEquals(1, $linkToAddress->getConstraints()->length());

        $joinToAddress = $linkToAddress->getConstraints()->getByIndex(0);
        $this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Join\Constraint', $joinToAddress);
        $this->assertSame($linkToAddress, $joinToAddress->link);
        $this->assertNull($joinToAddress->subJoins);

        $this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Field', $joinToAddress->parentField);
        $this->assertSame($table->fields->getByName('id'), $joinToAddress->parentField);

        $this->assertEquals('userId', $joinToAddress->childField->name);
        $this->assertEquals('integer(11)', $joinToAddress->childField->type);
        $this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Field', $joinToAddress->childField);
        $this->assertEquals('userId', $joinToAddress->childField->name);
        $this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table', $joinToAddress->childField->table);
        $this->assertEquals('UserAddress', $joinToAddress->childField->table->name);
        $this->assertEquals($linkToAddress->getChildTable(), $joinToAddress->childField->table);
        $this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\ValidatorList', $joinToAddress->childField->validators);
        $this->assertEquals(0, $joinToAddress->childField->validators->length());
	}

	public function assertBuiltTableMatchesPostManifest(Definition\Table $table)
	{
		$this->assertEquals('Post', $table->name);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table', $table);
		$this->assertEquals('Post', $table->name);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\FieldList', $table->fields);
		$this->assertEquals(3, $table->fields->length());
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\JoinList', $table->links);
		$this->assertEquals(2, $table->links->length());
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\ValidatorList', $table->validators);
		$this->assertEquals(0, $table->validators->length());

		$this->assertSame($table, $table->fields->getByIndex(0)->table);
		$this->assertSame($table, $table->fields->getByIndex(2)->table);
		$this->assertEquals('id', $table->fields->getByIndex(0)->name);
		$this->assertEquals('integer(11)', $table->fields->getByIndex(0)->type);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Field', $table->fields->getByIndex(0));
		$this->assertEquals('id', $table->fields->getByIndex(0)->name);
		$this->assertEquals('User_posts.id', $table->fields->getByIndex(0)->alias);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\ValidatorList', $table->fields->getByIndex(0)->validators);
		$this->assertEquals(0, $table->fields->getByIndex(0)->validators->length());

		$this->assertSame($table, $table->fields->getByIndex(1)->table);
		$this->assertSame($table, $table->fields->getByIndex(2)->table);
		$this->assertEquals('authorId', $table->fields->getByIndex(1)->name);
		$this->assertEquals('integer(11)', $table->fields->getByIndex(1)->type);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Field', $table->fields->getByIndex(1));
		$this->assertEquals('authorId', $table->fields->getByIndex(1)->name);
		$this->assertEquals('User_posts.authorId', $table->fields->getByIndex(1)->alias);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\ValidatorList', $table->fields->getByIndex(1)->validators);
		$this->assertEquals(0, $table->fields->getByIndex(1)->validators->length());

		$this->assertSame($table, $table->fields->getByIndex(2)->table);
		$this->assertSame($table, $table->fields->getByIndex(2)->table);
		$this->assertEquals('content', $table->fields->getByIndex(2)->name);
		$this->assertEquals('text', $table->fields->getByIndex(2)->type);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Field', $table->fields->getByIndex(2));
		$this->assertEquals('content', $table->fields->getByIndex(2)->name);
		$this->assertEquals('User_posts.content', $table->fields->getByIndex(2)->alias);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\ValidatorList', $table->fields->getByIndex(2)->validators);
		$this->assertEquals(0, $table->fields->getByIndex(2)->validators->length());

		$this->assertEquals('author', $table->links->getByIndex(0)->name);
		$this->assertSame($table, $table->links->getByIndex(0)->parentTable);
		$this->assertEquals('User', $table->links->getByIndex(0)->childTableName);
	}

	public function assertBuiltPostTableJoinsToAuthorTable(Definition\Table $table)
	{
		$linkToAuthor = $table->links->getByName('author');
		$this->assertEquals('author', $linkToAuthor->name);
		$this->assertEquals(Definition\Table\Join::MANY_TO_ONE, $linkToAuthor->type);
		$this->assertEquals(Definition\Table\Join::ACTION_ASSOCIATE, $linkToAuthor->onInsert);
		$this->assertEquals(Definition\Table\Join::ACTION_IGNORE, $linkToAuthor->onUpdate);
		$this->assertEquals(Definition\Table\Join::ACTION_IGNORE, $linkToAuthor->onDelete);
		$this->assertSame($table, $linkToAuthor->parentTable);
		$this->assertEquals('User', $linkToAuthor->childTableName);
		$this->assertNull($linkToAuthor->intermediaryTables);

		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Join\ConstraintList', $linkToAuthor->getConstraints());
		$this->assertEquals(1, $linkToAuthor->getConstraints()->length());

		$joinToAuthor = $linkToAuthor->getConstraints()->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Join\Constraint', $joinToAuthor);
		$this->assertSame($linkToAuthor, $joinToAuthor->link);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Field', $joinToAuthor->parentField);
		$this->assertEquals('authorId', $joinToAuthor->parentField->name);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Field', $joinToAuthor->childField);
		$this->assertEquals('id', $joinToAuthor->childField->name);

		$joinToAuthor = $linkToAuthor->getConstraints()->getByIndex(0);
		$this->assertSame($linkToAuthor, $joinToAuthor->link);
		$this->assertNull($joinToAuthor->subJoins);

		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Join\Constraint', $joinToAuthor);
		$this->assertSame($table->fields->getByName('authorId'), $joinToAuthor->parentField);

		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Field', $joinToAuthor->childField);
		$this->assertBuiltTableMatchesUserManifest($joinToAuthor->childField->table);
		$this->assertEquals('id', $joinToAuthor->childField->name);
		$this->assertEquals('integer(11)', $joinToAuthor->childField->type);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table', $joinToAuthor->childField->table);
		$this->assertEquals('User', $joinToAuthor->childField->table->name);
		$this->assertEquals('User_posts_author', $joinToAuthor->childField->table->alias);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Field', $joinToAuthor->childField);
		$this->assertSame($joinToAuthor->childField->table, $joinToAuthor->childField->table);
		$this->assertEquals('id', $joinToAuthor->childField->name);
		$this->assertEquals('User_posts_author.id', $joinToAuthor->childField->alias);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\ValidatorList', $joinToAuthor->childField->validators);
		$this->assertEquals(0, $joinToAuthor->childField->validators->length());
	}

	public function assertBuildPostsTableJoinsToCommentsTable(Definition\Table $table)
	{
		$linkToComments = $table->links->getByName('comments');
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Join', $linkToComments);
		$this->assertEquals('comments', $linkToComments->name);
		$this->assertEquals(Definition\Table\Join::ONE_TO_MANY, $linkToComments->type);
		$this->assertEquals(Definition\Table\Join::ACTION_INSERT, $linkToComments->onInsert);
		$this->assertEquals(Definition\Table\Join::ACTION_IGNORE, $linkToComments->onUpdate);
		$this->assertEquals(Definition\Table\Join::ACTION_IGNORE, $linkToComments->onDelete);
		$this->assertSame($table, $linkToComments->parentTable);
		$this->assertEquals('Comment', $linkToComments->childTableName);
		$this->assertNull($linkToComments->intermediaryTables);

		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Join\ConstraintList', $linkToComments->getConstraints());
		$this->assertEquals(1, $linkToComments->getConstraints()->length());

		$joinToComments = $linkToComments->getConstraints()->getByIndex(0);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Join\Constraint', $joinToComments);
		$this->assertSame($linkToComments, $joinToComments->link);
		$this->assertNull($joinToComments->subJoins);

		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Field', $joinToComments->parentField);
		$this->assertSame($table->fields->getByName('id'), $joinToComments->parentField);

		$this->assertEquals('postId', $joinToComments->childField->name);
		$this->assertEquals('integer(11)', $joinToComments->childField->type);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\Field', $joinToComments->childField);
		$this->assertEquals('postId', $joinToComments->childField->name);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table', $joinToComments->childField->table);
		$this->assertEquals('Comment', $joinToComments->childField->table->name);
		$this->assertEquals($linkToComments->getChildTable(), $joinToComments->childField->table);
		$this->assertInstanceOf('Celestial\Module\Data\Table\Definition\Table\ValidatorList', $joinToComments->childField->validators);
		$this->assertEquals(0, $joinToComments->childField->validators->length());
	}
}
