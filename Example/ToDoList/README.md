# Example: To Do List
In this example, we'll walk through creating a simple To Do list using the Celestial framework. If you'd prefer to simply explore a working example, rather than follow the steps below, feel free to download this folder and run it on your machine by executing `./run.sh` from the root directory. The sample project includes a HTML web page for interacting with the To Do list, demonstrating how to render to HTML from a Celestial project.

The only requirement to run this example project is that you have Docker and Docker-Compose installed. The run script uses a temporary container to install dependencies, then brings up further containers to serve the application. As soon as the containers are started, you should see the log output from the `docker-compose up` command. Abort that command (`ctrl` + `c` on most terminals) to halt the containers.
 
Please note that this example is a work in progress and the steps below do not fulfill 100% of the sample project.

## Preparation
For each item in the list, we'll store a description and completion status:

We'll need a working database for our API project to interact with.

```
{
	"id": integer,
	"description": string,
	"completed": boolean
}
```

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

## API Development

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

### Testing
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

## Client Development
Now we'll build a web page to view and manage our To Do list.

### HTML View
Create a Handlebars template to render our To Do list and Create Item form.
```
# /View/Template/index.html
{{! Handlebars template: to do list }}
<html>
	<body>
		<h1>It works!</h1>

		<p>If you can see this message in your browser, then the project is up and running. Use the forms below to test out the Celestial APIs.</p>

		<h2>To Do List</h2>
			{{#each data.todo}}
				<div>
					<form action="/resource/update/item?redirect=" method="post">
						<input name="attributes[id]" value="{{id}}" type="hidden">
						<input name="attributes[description]" value="{{description}}" type="text">
						<input name="attributes[completed]" value="0" type="hidden">
						<input name="attributes[completed]" value="1" type="checkbox" {{#if completed}}checked{{/if}}>
						<button type="submit">Update</button>
						<a href="/resource/delete/item/{{id}}?redirect=">Delete</a>
					</form>
				</div>
			{{/each}}

		<h2>Create Item</h2>
		<form action="/resource/create/item?redirect=" method="post">
			<div>
				<label>Description: <input name="attributes[description]" type="text"></label>
			</div>
			<div>
				<label>Completed? <input name="attributes[completed]" type="checkbox" value="1"></label>
			</div>
			<div>
				<button type="submit">Create</button>
			</div>
		</form>
	</body>
</html>
```

### View Manifest
Create a route to tell Celestial how to load the view. In this case, we'll use Handlebars as the rendering engine and provide data via a `resourceList` data provider.
```
# /View/Manifest/.json
{
	"": {
		"engine": "handlebars",
		"path": "index.html",
		"options": {},
		"dataProviders": {
			"todo": {
				"engine": "resourceList",
				"options": {
					"resourceName": "Item"
				}
			}
		}
	}
}
```

We've named the file simply `.json` so that we won't have to add anything to our URL to reach views defined in this manifest. Similarly, the key for our first view entry is empty, meaning that this view will load for the base URL of our project: http://celestial.dev:8000
