# Example: To Do List API
In this example, we'll walk through creating an API to support a simple To Do list. Please note that this example is a work in progress and not yet tested.

For each item in the list, we'll store a description and completion status:

```
{
	"id": integer,
	"description": string,
	"completed": boolean
}
```

## Preparation
We'll need a working database for our API project to interact with.

### Database
Before we can have a working API, we will of course require a database. Use the following SQL to create a database with a single table to contain items in our To Do list:

```mysql
# /Database/ToDoList.sql

CREATE DATABASE `ToDoList`;

CREATE TABLE `item` (
	`id` INT(11) AUTO_INCREMENT PRIMARY KEY,
	`description` VARCHAR(200) NOT NULL,
	`completed` BOOLEAN
)
```

### Framework Configuration
First, we create a configuration file to instruct the framework which modules we'll be using and where our project is on the local file system. Configuration will be explained in a separate document, not yet written. For now, just use the files provided within this example project, in `/Config` as a starting point. The same applies to app initialisation, which is handled by `/index.php` and `/AppInitialisation.php`.

## Project

### Resource Manifest
Celestial creates restful APIs based on resources, which are defined by a set of JSON manifest files. A resource manifest tells Celestial which table (or set of tables) should be written to and read from by requests to that resource. It also specifies the names of resource attributes and which table field each refers to. In this example, we're working with a single table so we'll only need one resource manifest, which we'll call `item`, to represent items in the To Do list:

```
# /Schema/Resource/Item.json

{
	"name": "Item",
	"table": "Item",
	"primaryAttribute": "id",
	"attributes": {
		"id": true,
		"description": true,
		"completed": true
	}
}
```

### Database Table Manifest
In order for Celestial to interpret API requests and build database queries, we'll need to tell it a bit more about the database table itself - field names, types, auto increment, etc. We do this by creating another JSON manifest file for each database table. In this example, we only need one manifest for the `item` table:

```
# /Schema/Table/Item.json

{
	"fields": {
		"id": {
			"type": "number(11)",
			"field": "id",
			"autoIncrement": true,
			"isUnique": true
		},
		"description": {
			"type": "text(200)",
			"field": "description"
		},
		"completed": {
			"type": "boolean",
			"field": "completed"
		}
	}
}
```

### API Endpoints
We're done! The following API endpoints will now be provided by Celestial, with no further development work.

#### Create an item
```
# request
POST /resource/item {
	"description": string,
	"completed": boolean
}

# response
{
	"id": integer,
	"description": string,
	"completed": boolean
}
```

#### Get an item
```
# request
GET /resource/item/2

# response
{
	"id": integer,
	"description": string,
	"completed": boolean
}
```

#### Get a list of items
```
# request
GET /resource/item

# response
[
	{
		"id": integer,
		"description": string,
		"completed": boolean
	},
	{
		"id": integer,
		"description": string,
		"completed": boolean
	},
	{
		"id": integer,
		"description": string,
		"completed": boolean
	}
]
```

#### Update an item
```
# request
PUT /resource/item/2 {
	"description": string,
	"completed": boolean
}

# or
POST /resource/update/item/2 {
	"description": string,
	"completed": boolean
}

# response
{
	"id": integer,
	"description": string,
	"completed": boolean
}
```

#### Delete an item
```
# request
DELETE /resource/item/3

# or
POST /resource/delete/item/3

# response
{
	"id": integer,
	"description": string,
	"completed": boolean
}
```
