# Celestial
A web application framework.

**WORK IN PROGRESS: This project is not fully tested and is likely to change significantly before release.**


## Concept
- Provide a restful API for MySQL database interactions
- Require only JSON configuration to define API resources, which group fields from one or many tables into logical entities
- Automate efficient MySQL query building and execution for inserting, updating, deleting and searching complex data sets
- Provide a JavaScript library for client applications to parse the API resource configurations into models and handle API interactions


## To Do
- Create the JavaScript library for client applications
- Implement OAuth
- Write detailed documentation for the JSON configuration syntax
- Add thorough automated tests to the modules that don't have them
- Add API integration tests
- Create a more sensible demo project, possibly in a separate repository
- Separate filtering, validation and views into a separate project, leaving Celestial as just a Database API layer


## Known Issues
- The formatting of configuration files and requests is a bit more cumbersome than the examples below, which can be seen in the demo project. Planned refinements should bring Celestial configuration and API requests into line with the step-by-step example below.

- Some modules (particularly those within `Module/Data`) are quite messy and tightly coupled. Work is planned to better isolate and clarify the scope of each module.


## Step-By-Step Example
*Note that the following example does not work with the current version of the framework. All functionality described is supported, but the required formatting of JSON files is a little more verbose and a PHP configuration is required to supply database credentials and specify module dependencies.*

Consider a scenario in which we need to store users and their addresses.

### Declare database tables
**User table**
```
# ./table/user.json
{
	"table": "User",
	"fields": {
		"id": {
			"type": "number(11)",
			"field": "id",
			"autoIncrement": true,
			"isUnique": true,
			"validators": {
				"number.number": true,
				"number.integer": true,
				"number.maximumDigits": 11
			}
		},
		"username": {
			"type": "text(50)",
			"field": "username",
			"validators": {
				"text.text": true,
				"text.minimumLength": 2,
				"text.maximumLength": 50
			}
		},
		"password": {
			"type": "text(50)",
			"field": "password",
			"validators": {
				"text.text": true,
				"text.minimumLength": 2,
				"text.maximumLength": 50
			}
		}
	},
	"validators": [
		{
			"rule": "comparison.not-equal",
			"fields": {
				"0": "username",
				"1": "password"
			}
		}
	],
	"links": {
		"address": {
			"type": "oneToOne",
			"table": "UserAddress",`
			"onInsert": "insert",
			"onUpdate": "update",
			"onDelete": "delete",
			"joins": {
				"this.id": "address.userId"
			}
		}
	}
}
```

**Address table**
```
{
	"fields": {
		"userId": {
			"type": "number(11)",
			"field": "userId",
			"isUnique": true,
			"validators": {
				"text.minimumLength": 1,
				"text.maximumLength": 50
			}
		},
		"houseName": {
			"type": "text(50)",
			"field": "houseName"
		},
		"postcode": {
			"type": "text(50)",
			"field": "postcode"
		},
		"ownerId": {
			"type": "number(11)",
			"field": "ownerId"
		}
	},
	"links": {
		"owner": {
			"type": "oneToOne",
			"table": "User",
			"onInsert": "ignore",
			"onUpdate": "ignore",
			"onDelete": "ignore",
			"joins": {
				"this.owner": "owner.id"
			}
		}
	}
}
```

### Declare a resource
Resources are the logical business entities required by our application.

**User resource** - *user combined with their address*
```
# ./resource/user.json
{
	"name": "User",
	"table": "User",
	"primaryAttribute": "id",
	"attributes": {
		"id": true,
		"username": true,
		"password": true,
		"address": {
			"id": true,
			"houseName": true,
			"postcode": true
		},
	},
	"validators": [
		{
			"rule": "type.text",
			"attributes": ["username", "password", "address.postcode"]
		}
	]
}
```

### Test the API

**Create user** - *include both user and address data*
```
POST /resource/user {
	"username": "first_user",
	"password": "secret",
	"address": {
		"houseName": "37"
		"postCode": "PO24 1BH"
	}
}

# response
{
	"id": 1
	"username": "first_user",
	"password": "secret",
	"address": {
		"id": 1
		"houseName": "37"
		"postCode": "PO24 1BH"
	}
}
```

**Fetch user** - *verify user and their address returned*
```
GET /resource/user/1

# response
{
	"id": 1
	"username": "first_user",
	"password": "secret",
	"address": {
		"id": 1
		"houseName": "37"
		"postCode": "PO24 1BH"
	}
}
```

**Update username and address**
```
POST /resource/user/update/1 {
	"username": "first_user_updated",
	"password": "secret",
	"address": {
		"houseName": "Chezmoi"
		"postCode": "PO24 1BH"
	}
}

# response
{
	"id": 1
	"username": "first_user_updated",
	"password": "secret",
	"address": {
		"id": 1
		"houseName": "Chezmoi"
		"postCode": "PO24 1BH"
	}
}
```

**Query by username** - *verify search on user data works*
```
GET /resource/user/search?username=first_user_updated

# response
{
	"id": 1
	"username": "first_user_updated",
	"password": "secret",
	"address": {
		"id": 1
		"houseName": "Chezmoi"
		"postCode": "PO24 1BH"
	}
}
```

**Query by houseName** - *verify search on address data works*
```
GET /resource/user/search?address.houseName=Chezmoi

# response
{
	"id": 1
	"username": "first_user_updated",
	"password": "secret",
	"address": {
		"id": 1
		"houseName": "Chezmoi"
		"postCode": "PO24 1BH"
	}
}
```

### Create HTML template
In this example we use Mustache for HTML templates.

**User list**
```
# ./view/user/list.html
<h2>Users</h2>
<table>
	<tr>
		<th>ID</th>
		<th>Username</th>
		<th>House Name</th>
		<th>Post Code</th>
	</tr>
	{{#data.users}}
		<tr>
			<td>{{id}}</td>
			<td><a href="/user/{{username}}">{{username}}</a></td>
			<td>{{address.houseName}}</td>
			<td>{{address.postCode}}</td>
		</tr>
	{{/data.users}}
</table>
```

**Individual user**
```
# ./view/user/single.html
<h2>{{data.user.username}}</h2>
<p>
	Lives at:<br>
	{{data.user.address.houseName}}<br>
	{{data.user.address.postCode}}<br>
</p>
<a href="/users">Back to user list</a>
```

### Define routes
```
# ./route/index.json
{
	"users": {
		"engine": "mustache",
		"path": "user/list.html",
		"dataProviders": {
			"users": {
				"engine": "resourceList",
				"options": {
					"resourceName": "User"
				}
			}
		}
	},
	"user": {
		"engine": "mustache",
		"path": "user/single.html",
		"dataProviders": {
			"user": {
				"engine": "resource",
				"options": {
					"resourceName": "User",
					"filters": [
						{
							"subject": "username",
							"comparator": "=",
							"source": {
								"engine": "request.get",
								"options": {
									"item": "username"
								}
							}
						}
					]
				}
			}
		}
	}
}
```

### View generated pages

Load the following URLs in a web browser to see web pages generated from the above templates and data.

**View user list** - *list all users, rendered via the user list template above*
```
GET /users
```

**View individual user** - *user with username "first_user_updated", rendered via the user template above*
```
GET /users/first_user_updated
```
