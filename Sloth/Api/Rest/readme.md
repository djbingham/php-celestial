# Dependencies

The controllers in this API require a resource module and a render module.

Either the modules must be registered with the names below, or the controllers must be extended, with the protected "get<...>Module" methods set to return the appropriate modules.

Required modules:

1. "restResource" extending Resource module (override default by changing "getResourceModule" method)

2. "restRender" extending Render module (override default by changing "getRenderModule" method)
