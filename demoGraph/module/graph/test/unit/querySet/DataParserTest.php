<?php
namespace DemoGraph\Module\Graph\Test\QueryBuilder;

require_once dirname(dirname(__DIR__)) . '/UnitTest.php';

use DemoGraph\Module\Graph\QuerySet\DataParser;
use DemoGraph\Module\Graph\Definition;
use DemoGraph\Test\UnitTest;

class DataParserTest extends UnitTest
{
	public function testFormatResourceDataWithDataFromSingleResource()
	{
		$resourceDefinitionBuilder = $this->getTableDefinitionBuilder();

		$resource = $resourceDefinitionBuilder->buildFromName('User');
		while ($resource->links->length() > 0) {
			$resource->links->removeByIndex(0);
		}

		$rawData = array(
			'User' => array(
				array(
					'User.id' => 1,
					'User.forename' => 'David',
					'User.surname' => 'Bingham'
				),
				array(
					'User.id' => 3,
					'User.forename' => 'Flic',
					'User.surname' => 'Bingham'
				)
			)
		);
		$expectedParsedData = array(
			array(
				'id' => 1,
				'forename' => 'David',
				'surname' => 'Bingham'
			),
			array(
				'id' => 3,
				'forename' => 'Flic',
				'surname' => 'Bingham'
			)
		);

		$dataParser = new DataParser();
		$parsedData = $dataParser->formatResourceData($rawData, $resource);

		$this->assertEquals($expectedParsedData, $parsedData);
	}

	public function testFormatResourceDataWithDataFromOneToOneJoinedResources()
	{
		$resourceDefinitionBuilder = $this->getTableDefinitionBuilder();

		$resource = $resourceDefinitionBuilder->buildFromName('User');
		$resource->links->removeByPropertyValue('name', 'friends');
		$resource->links->removeByPropertyValue('name', 'posts');

		$rawData = array(
			'User' => array(
				array(
					'User.id' => 1,
					'User.forename' => 'David',
					'User.surname' => 'Bingham',
					'User_address.houseName' => 'Bingham House',
					'User_address.postcode' => 'BI34 7AM',
					'User_address.landlordId' => 3
				),
				array(
					'User.id' => 3,
					'User.forename' => 'Michael',
					'User.surname' => 'Hughes',
					'User_address.houseName' => 'Hughes House',
					'User_address.postcode' => 'HU56 3PM',
					'User_address.landlordId' => 4
				)
			)
		);
		$expectedParsedData = array(
			array(
				'id' => 1,
				'forename' => 'David',
				'surname' => 'Bingham',
				'address' => array(
					'houseName' => 'Bingham House',
					'postcode' => 'BI34 7AM',
					'landlordId' => 3
				)
			),
			array(
				'id' => 3,
				'forename' => 'Michael',
				'surname' => 'Hughes',
				'address' => array(
					'houseName' => 'Hughes House',
					'postcode' => 'HU56 3PM',
					'landlordId' => 4
				)
			)
		);

		$dataParser = new DataParser();
		$parsedData = $dataParser->formatResourceData($rawData, $resource);

		$this->assertEquals($expectedParsedData, $parsedData);
	}

	public function testFormatResourceDataWithDataFromOneToManyJoinedResources()
	{
		$resourceDefinitionBuilder = $this->getTableDefinitionBuilder();

		$resource = $resourceDefinitionBuilder->buildFromName('User');
		$resource->links->removeByPropertyValue('name', 'address');
		$resource->links->removeByPropertyValue('name', 'friends');

		$rawData = array(
			'User' => array(
				array(
					'User.id' => 1,
					'User.forename' => 'David',
					'User.surname' => 'Bingham'
				),
				array(
					'User.id' => 3,
					'User.forename' => 'Michael',
					'User.surname' => 'Hughes'
				)
			),
			'User_posts' => array(
				array(
					'User_posts.authorId' => 1,
					'User_posts.content' => 'First post by David'
				),
				array(
					'User_posts.authorId' => 3,
					'User_posts.content' => 'First post by Michael'
				),
				array(
					'User_posts.authorId' => 3,
					'User_posts.content' => 'Second post by Michael'
				),
				array(

					'User_posts.authorId' => 1,
					'User_posts.content' => 'Second post by David'
				)
			)
		);
		$expectedParsedData = array(
			array(
				'id' => 1,
				'forename' => 'David',
				'surname' => 'Bingham',
				'posts' => array(
					array(
						'authorId' => 1,
						'content' => 'First post by David'
					),
					array(
						'authorId' => 1,
						'content' => 'Second post by David'
					)
				)
			),
			array(
				'id' => 3,
				'forename' => 'Michael',
				'surname' => 'Hughes',
				'posts' => array(
					array(
						'authorId' => 3,
						'content' => 'First post by Michael'
					),
					array(
						'authorId' => 3,
						'content' => 'Second post by Michael'
					)
				)
			)
		);

		$dataParser = new DataParser();
		$parsedData = $dataParser->formatResourceData($rawData, $resource);

		$this->assertEquals($expectedParsedData, $parsedData);
	}

	public function testFormatResourceDataWithDataFromTwoManyToManyJoinedResources()
	{
		$resourceDefinitionBuilder = $this->getTableDefinitionBuilder();

		$resource = $resourceDefinitionBuilder->buildFromName('User');
		$resource->links->removeByPropertyValue('name', 'address');
		$resource->links->removeByPropertyValue('name', 'posts');

		$friendResource = $resource->links->getByName('friends')->getChildTable();
		$friendResource->links->removeByPropertyValue('name', 'address');
		$friendResource->links->removeByPropertyValue('name', 'posts');
		$friendResource->links->removeByPropertyValue('name', 'friends');

		$rawData = array(
			'User' => array(
				array(
					'User.id' => 1,
					'User.forename' => 'David',
					'User.surname' => 'Bingham'
				),
				array(
					'User.id' => 3,
					'User.forename' => 'Michael',
					'User.surname' => 'Hughes'
				)
			),
			'User_friends' => array(
				array(
					'User_friendLink.friendId1' => 1,
					'User_friends.id' => 2,
					'User_friends.forename' => 'Flic',
					'User_friends.surname' => 'Bingham'
				),
				array(
					'User_friendLink.friendId1' => 1,
					'User_friends.id' => 3,
					'User_friends.forename' => 'Michael',
					'User_friends.surname' => 'Hughes'
				),
				array(
					'User_friendLink.friendId1' => 3,
					'User_friends.id' => 1,
					'User_friends.forename' => 'David',
					'User_friends.surname' => 'Bingham'
				),
				array(
					'User_friendLink.friendId1' => 3,
					'User_friends.id' => 2,
					'User_friends.forename' => 'Flic',
					'User_friends.surname' => 'Bingham'
				),
				array(
					'User_friendLink.friendId1' => 3,
					'User_friends.id' => 4,
					'User_friends.forename' => 'Tamsin',
					'User_friends.surname' => 'Boatman'
				)
			)
		);
		$expectedParsedData = array(
			array(
				'id' => 1,
				'forename' => 'David',
				'surname' => 'Bingham',
				'friends' => array(
					array(
						'id' => 2,
						'forename' => 'Flic',
						'surname' => 'Bingham',
					),
					array(
						'id' => 3,
						'forename' => 'Michael',
						'surname' => 'Hughes',
					),
				)
			),
			array(
				'id' => 3,
				'forename' => 'Michael',
				'surname' => 'Hughes',
				'friends' => array(
					array(
						'id' => 1,
						'forename' => 'David',
						'surname' => 'Bingham',
					),
					array(
						'id' => 2,
						'forename' => 'Flic',
						'surname' => 'Bingham',
					),
					array(
						'id' => 4,
						'forename' => 'Tamsin',
						'surname' => 'Boatman',
					)
				)
			)
		);

		$dataParser = new DataParser();
		$parsedData = $dataParser->formatResourceData($rawData, $resource);

		$this->assertEquals($expectedParsedData, $parsedData);
	}

	public function testFormatResourceDataWithDataFromThreeManyToManyJoinedResources()
	{
		$resourceDefinitionBuilder = $this->getTableDefinitionBuilder();

		$resource = $resourceDefinitionBuilder->buildFromName('User');
		$resource->links->removeByPropertyValue('name', 'address');
		$resource->links->removeByPropertyValue('name', 'posts');

		$friendResource = $resource->links->getByName('friends')->getChildTable();
		$friendResource->links->removeByPropertyValue('name', 'address');
		$friendResource->links->removeByPropertyValue('name', 'posts');

		$friendOfFriendResource = $friendResource->links->getByName('friends')->getChildTable();
		$friendOfFriendResource->links->removeByPropertyValue('name', 'friends');
		$friendOfFriendResource->links->removeByPropertyValue('name', 'address');
		$friendOfFriendResource->links->removeByPropertyValue('name', 'posts');

		$rawData = array(
			'User' => array(
				array(
					'User.id' => 1,
					'User.forename' => 'David',
					'User.surname' => 'Bingham'
				),
				array(
					'User.id' => 2,
					'User.forename' => 'Flic',
					'User.surname' => 'Bingham'
				)
			),
			'User_friends' => array(
				array(
					'User_friendLink.friendId1' => 1,
					'User_friends.id' => 2,
					'User_friends.forename' => 'Flic',
					'User_friends.surname' => 'Bingham'
				),
				array(
					'User_friendLink.friendId1' => 1,
					'User_friends.id' => 3,
					'User_friends.forename' => 'Michael',
					'User_friends.surname' => 'Hughes'
				),
				array(
					'User_friendLink.friendId1' => 2,
					'User_friends.id' => 1,
					'User_friends.forename' => 'David',
					'User_friends.surname' => 'Bingham'
				),
				array(
					'User_friendLink.friendId1' => 2,
					'User_friends.id' => 3,
					'User_friends.forename' => 'Michael',
					'User_friends.surname' => 'Hughes'
				),
				array(
					'User_friendLink.friendId1' => 2,
					'User_friends.id' => 4,
					'User_friends.forename' => 'Tamsin',
					'User_friends.surname' => 'Boatman'
				)
			),
			'User_friends_friends' => array(
				array(
					'User_friends_friendLink.friendId1' => 1,
					'User_friends_friends.id' => 2,
					'User_friends_friends.forename' => 'Flic',
					'User_friends_friends.surname' => 'Bingham'
				),
				array(
					'User_friends_friendLink.friendId1' => 1,
					'User_friends_friends.id' => 3,
					'User_friends_friends.forename' => 'Michael',
					'User_friends_friends.surname' => 'Hughes'
				),
				array(
					'User_friends_friendLink.friendId1' => 2,
					'User_friends_friends.id' => 1,
					'User_friends_friends.forename' => 'David',
					'User_friends_friends.surname' => 'Bingham'
				),
				array(
					'User_friends_friendLink.friendId1' => 2,
					'User_friends_friends.id' => 3,
					'User_friends_friends.forename' => 'Michael',
					'User_friends_friends.surname' => 'Hughes'
				),
				array(
					'User_friends_friendLink.friendId1' => 2,
					'User_friends_friends.id' => 4,
					'User_friends_friends.forename' => 'Tamsin',
					'User_friends_friends.surname' => 'Boatman'
				)
			)
		);
		$expectedParsedData = array(
			array(
				'id' => 1,
				'forename' => 'David',
				'surname' => 'Bingham',
				'friends' => array(
					array(
						'id' => 2,
						'forename' => 'Flic',
						'surname' => 'Bingham',
						'friends' => array(
							array(
								'id' => 1,
								'forename' => 'David',
								'surname' => 'Bingham'
							),
							array(
								'id' => 3,
								'forename' => 'Michael',
								'surname' => 'Hughes'
							),
							array(
								'id' => 4,
								'forename' => 'Tamsin',
								'surname' => 'Boatman'
							)
						)
					),
					array(
						'id' => 3,
						'forename' => 'Michael',
						'surname' => 'Hughes',
						'friends' => array()
					)
				)
			),
			array(
				'id' => 2,
				'forename' => 'Flic',
				'surname' => 'Bingham',
				'friends' => array(
					array(
						'id' => 1,
						'forename' => 'David',
						'surname' => 'Bingham',
						'friends' => array(
							array(
								'id' => 2,
								'forename' => 'Flic',
								'surname' => 'Bingham'
							),
							array(
								'id' => 3,
								'forename' => 'Michael',
								'surname' => 'Hughes'
							)
						)
					),
					array(
						'id' => 3,
						'forename' => 'Michael',
						'surname' => 'Hughes',
						'friends' => array()
					),
					array(
						'id' => 4,
						'forename' => 'Tamsin',
						'surname' => 'Boatman',
						'friends' => array()
					)
				)
			)
		);

		$dataParser = new DataParser();
		$parsedData = $dataParser->formatResourceData($rawData, $resource);

		$this->assertEquals($expectedParsedData, $parsedData);
	}
}
