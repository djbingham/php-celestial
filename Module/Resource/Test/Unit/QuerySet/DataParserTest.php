<?php
namespace Sloth\Module\Resource\Test\QueryBuilder;

require_once dirname(dirname(__DIR__)) . '/UnitTest.php';

use Sloth\Module\Resource\QuerySet\DataParser;
use Sloth\Module\Resource\Definition;
use DemoResource\Test\UnitTest;

class DataParserTest extends UnitTest
{
	public function testFormatResourceDataWithEmptyDataArray()
	{
		$resourceDefinitionBuilder = $this->getTableDefinitionBuilder();

		$resource = $resourceDefinitionBuilder->buildFromName('User');
		while ($resource->links->length() > 0) {
			$resource->links->removeByIndex(0);
		}

		$rawData = array();
		$dataParser = new DataParser();
		$parsedData = $dataParser->formatResourceData($rawData, $resource);
		$this->assertSame($rawData, $parsedData);
	}

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
						'content' => 'First post by David',
						'comments' => array()
					),
					array(
						'authorId' => 1,
						'content' => 'Second post by David',
						'comments' => array()
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
						'content' => 'First post by Michael',
						'comments' => array()
					),
					array(
						'authorId' => 3,
						'content' => 'Second post by Michael',
						'comments' => array()
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

	public function testFormatResourceDataFiltersOutRowsWithNoRecordsInLinkedTables()
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
				),
				array(
					'User.id' => 3,
					'User.forename' => 'Michael',
					'User.surname' => 'Hughes'
				),
				array(
					'User.id' => 4,
					'User.forename' => 'Tamsin',
					'User.surname' => 'Boatman'
				),
				array(
					'User.id' => 5,
					'User.forename' => 'Sam',
					'User.surname' => 'Hollings'
				)
			),
			'User_friends' => array(
				// David is friends with Flic, Michael and Tamsin
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
					'User_friendLink.friendId1' => 1,
					'User_friends.id' => 4,
					'User_friends.forename' => 'Tamsin',
					'User_friends.surname' => 'Boatman'
				),
				// Flic is friends with David, Michael and Tamsin
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
				),
				// Michael is friends with Tamsin and Sam
				array(
					'User_friendLink.friendId1' => 3,
					'User_friends.id' => 4,
					'User_friends.forename' => 'Tamsin',
					'User_friends.surname' => 'Boatman'
				),
				array(
					'User_friendLink.friendId1' => 3,
					'User_friends.id' => 5,
					'User_friends.forename' => 'Sam',
					'User_friends.surname' => 'Hollings'
				)
			),
			'User_friends_friends' => array(
				// David is friends with Flic, Michael and Tamsin
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
					'User_friends_friendLink.friendId1' => 1,
					'User_friends_friends.id' => 4,
					'User_friends_friends.forename' => 'Tamsin',
					'User_friends_friends.surname' => 'Boatman'
				),
				// Flic is friends with David, Michael and Tamsin
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
				),
				// Michael is friends with Flic and Tamsin
				array(
					'User_friends_friendLink.friendId1' => 3,
					'User_friends_friends.id' => 2,
					'User_friends_friends.forename' => 'Flic',
					'User_friends_friends.surname' => 'Bingham'
				),
				array(
					'User_friends_friendLink.friendId1' => 3,
					'User_friends_friends.id' => 5,
					'User_friends_friends.forename' => 'Sam',
					'User_friends_friends.surname' => 'Hollings'
				)
			)
		);
		$filters = array(
			'friends' => array(
				'forename' => 'Flic',
				'friends' => array(
					'forename' => 'Sam'
				)
			)
		);
		// David, Flic and Michael all have friends, but Michael's friends are all friendless.
		// Therefore, the only people with friends of friends are David and Flic.
		// Note that DataParser only looks at keys, not values of filters.
		// Its job is to filter out records that have data in some, but not all, filtered tables linked to a resource.
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
						'friends' => array(
							array(
								'id' => 2,
								'forename' => 'Flic',
								'surname' => 'Bingham'
							),
							array(
								'id' => 5,
								'forename' => 'Sam',
								'surname' => 'Hollings'
							)
						)
					),
					array(
						'id' => 4,
						'forename' => 'Tamsin',
						'surname' => 'Boatman',
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
						'friends' => array(
							array(
								'id' => 2,
								'forename' => 'Flic',
								'surname' => 'Bingham'
							),
							array(
								'id' => 5,
								'forename' => 'Sam',
								'surname' => 'Hollings'
							)
						)
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
		$parsedData = $dataParser->formatResourceData($rawData, $resource, $filters);

		$this->assertEquals($expectedParsedData, $parsedData);
	}
}