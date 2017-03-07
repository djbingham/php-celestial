# Celestial
Restful APIs for relational data.

**WORK IN PROGRESS: This project is not fully tested and is likely to change significantly before release.**


## Concept
Celestial is a PHP framework that requires minimal configuration to provide a restful API for any relational database. A single HTTP request is sufficient to insert, update, delete or search for data across any number of related tables.


## Additional Functionality
In addition to automating restful APIs, Celestial currently has a few additional pieces of functionality:

- Render data into HTML views
- Handle user authentication
- Support update and delete requests sent as POST calls from a browser (looks for /update or /delete on the URL and treats those as PUT or DELETE, respectively)

The features listed above are likely to be removed in future so that Celestial development can focus entirely on providing a restful HTTP interface for relational databases. The goal is to automate a specific aspect of back-end development, rather than become a monolithic framework for web applications.


## To Do
The following is not an exhausted list of planned work on this project, but reflects the current focus on achieving high levels of testing and documentation:

- Tidy up instantiation of module configurations and allow defaults to be set within each module
- Write detailed documentation for each of the following:
	- configuration
	- resource manifest
	- table manifest
	- custom PHP components
- Add thorough automated tests to modules that don't currently have them
- Add API integration tests
- Create the JavaScript library for client applications
- Implement a well-known OAuth solution instead of custom authentication logic
- Refactor `Data` modules into submodules of a single parent module


## Known Issues
- The formatting of configuration files and requests is a bit more cumbersome than the examples below, which can be seen in the demo project. Planned refinements should bring Celestial configuration and API requests into line with the step-by-step example below.

- Some modules (particularly those within `Module/Data`) are quite messy and tightly coupled. Work is planned to better isolate and clarify the scope of each module.


## Step-By-Step Examples
Example projects will be provided in the `Examples` folder of this project, beginning with a very simple "To Do List" API, then increasing in complexity to demonstrate all functionality provided by this framework.
