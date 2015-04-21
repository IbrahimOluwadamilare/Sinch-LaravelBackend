### Tuts+ Tutorial: Creating a Dating Application with Sinch: RESTful API

#### Instructor: Carlos Cessa

In this tutorial, we're going to create a dating application for iOS similar to Tinder. For voice and messaging, we will leverage the Sinch platform, making use of its powerful SDK. In the first part, we will focus on the development of a RESTful API to store and retrieve user information. In the second part, the iOS client will hook into this API to find nearby users based on its current location.

Source Files for the Tuts+ Tutorial: [Creating a Dating Application with Sinch: RESTful API](http://code.tutsplus.com/tutorials/creating-a-dating-application-with-sinch-restful-api--cms-23709)

**Read this tutorial on [Tuts+](https://code.tutsplus.com)**

## API Documentation

### Sessions Resource

The url for the REST API _sessions_ resource, it's located in the following URL:

- http://YOUR_API_URL/sessions

It has support for the following methods:

- POST		- Create an user session
- DELETE/{token}	- Destroy an existing user session

___

#### POST sessions

Start an user session in the system.

- __Endpoint URL__ - http://YOUR_API_URL/sessions
- __Request Method__ - POST
- __Response Format__ - JSON
- __Requires Authentication__ - No
- __Response Codes__ - 200 | 403

##### Parameters

	{
		"email"		: "jdoe@gmail.com",
		"fbId"		: "USER_FB_ID",
		"gender"	: "male",
		"location"	: { "lat" : 37.427208696456866, "lon" : -122.17097282409668 }
		"mobile"	: "141649811",
		"name"		: "John Doe"
	}

Altough the only required parameters are: _fbId_ and either _email_ or _mobile_. It's suggested to send all the information, to start a new session the system uses only the _email_ and _fbId_ or the _mobile_ and _fbId_. However if the user attempting to start a session does not exists, the system will attempt to create a new user record, in order for this to work all the information previously mentioned must be sent.

##### Responses

###### Successful request

	{
		"user_id"	: "551335b76803fa1e068b4585",
		"user_name"	: "John Doe",
		"token"		: "55136a586803fa1f068b4581"
	}

###### Unsuccessful request

In the case of an unsuccessful request the API will send a 403 error code.

	{
		"error"		: "ERROR_INVALID_PARAMETERS",
		"status"	: 403
	}

___

#### DELETE sessions/{token}

Removes an existing user session from the system.

- __Endpoint URL__ - http://YOUR_API_URL/sessions/{token}
- __Request Method__ - DELETE
- __Response Format__ - JSON
- __Requires Authentication__ - Yes
- __Response Codes__ - 200 | 403

##### Parameters

	{
		
	}

##### Responses

###### Successful request

	{}

###### Unsuccessful request

	{
		"error"		: "ERROR_REMOVING_SESSION",
		"status"	: 403
	}

___

### Users Resource

The url for the REST API _users_ resource, it's located in the following URL:

- http://YOUR_API_URL/users

It has support for the following methods:

- GET - Retrieve a list of users
- GET/{id} - Retrieve a single user record
- POST - Create an user in the database
- PUT/{id} - Update an existing user information
- DELETE/{id} - Remove an user record

___

#### DELETE users/{id}

Removes an existing user record.

- __Endpoint URL__ - http://YOUR_API_URL/users/{id}
- __Request Method__ - DELETE
- __Response Format__ - JSON
- __Requires Authentication__ - Yes
- __Response Codes__ - 200 | 403

##### Parameters

	{
		"token"		: "55136a586803fa1f068b4581"
	}

##### Responses

###### Successful request

	{
		"_id"		: "551335b76803fa1e068b4585",
		"email"		: "jdoe@gmail.com",
		"fbId"		: "USER_FB_ID",
		"gender"	: "male",
		"location"	: {
			type		: "Point",
			coordinates	: [ -122.17097282409668, 37.427208696456866 ]
		},
		"mobile"	: "141649811",
		"name"		: "John Doe"
	}

###### Unsuccessful request

In the case of an unsuccessful request the API will send a 403 error code.

	{
		"error"		: "PERMISSION_DENIED",
		"status"	: 403
	}

___

#### GET users

Retrieve a list of existing users in the system.

- __Endpoint URL__ - http://YOUR_API_URL/users
- __Request Method__ - GET
- __Response Format__ - JSON
- __Requires Authentication__ - No
- __Response Codes__ - 200 | 403

##### Parameters

	{
		"distance"	: 150000,
		"token"		: "55136a586803fa1f068b4581"
	}

The parameters in the request are optional, for a unregistered user the method will return a list of all the available users in the system.

If a _token_ and distance parameter are given, the system will return the users that are located near the user with the active session at a maximum distance as specified in the request _distance_ parameter, given in __meters__. The obtained result in a query for a logged in user will exclude the user issuing it.

##### Responses

###### Successful request

	{
		[
			{
				"_id"		: "551335b76803fa1e068b4585",
				"email"		: "jdoe@gmail.com",
				"fbId"		: "USER_FB_ID",
				"gender"	: "male",
				"location"	: {
					type		: "Point",
					coordinates	: [ -122.17097282409668, 37.427208696456866 ]
				},
				"mobile"	: "141649811",
				"name"		: "John Doe"
			}
		]
	}

###### Unsuccessful request

In the case of an unsuccessful request the API will send a 403 error code.

	{
		"error"		: "PERMISSION_DENIED",
		"status"	: 403
	}

___

#### GET users/{id}

Retrieve an existing user record from the system.

- __Endpoint URL__ - http://YOUR_API_URL/users/{id}
- __Request Method__ - GET
- __Response Format__ - JSON
- __Requires Authentication__ - Yes
- __Response Codes__ - 200 | 403

##### Parameters

	{
		"token"		: "55136a586803fa1f068b4581"
	}

The id of the user to retrieve does not need to match the id of the user with the active session, any user can retrieve the details for all the users.

##### Responses

###### Successful request

	{
		"_id"		: "551335b76803fa1e068b4585",
		"email"		: "jdoe@gmail.com",
		"fbId"		: "USER_FB_ID",
		"gender"	: "male",
		"location"	: {
			type		: "Point",
			coordinates	: [ -122.17097282409668, 37.427208696456866 ]
		},
		"mobile"	: "141649811",
		"name"		: "John Doe"
	}

###### Unsuccessful request

In the case of an unsuccessful request the API will send a 403 error code.

	{
		"error"		: "PERMISSION_DENIED",
		"status"	: 403
	}

___

#### POST users

Create an user record in the system.

- __Endpoint URL__ - http://YOUR_API_URL/users
- __Request Method__ - POST
- __Response Format__ - JSON
- __Requires Authentication__ - No
- __Response Codes__ - 200 | 403

##### Parameters

	{
		"email"		: "jdoe@gmail.com",
		"fbId"		: "USER_FB_ID",
		"gender"	: "male",
		"location"	: { "lat" : 37.427208696456866, "lon" : -122.17097282409668 }
		"mobile"	: "141649811",
		"name"		: "John Doe"
	}

The only optional parameters are the _email_ and _mobile_, however one of those must be present and is used to start an user session in the system.

##### Responses

###### Successful request

	{
		"_id"		: "551335b76803fa1e068b4585",
		"email"		: "jdoe@gmail.com",
		"fbId"		: "USER_FB_ID",
		"gender"	: "male",
		"location"	: {
			type		: "Point",
			coordinates	: [ -122.17097282409668, 37.427208696456866 ]
		},
		"mobile"	: "141649811",
		"name"		: "John Doe"
	}

###### Unsuccessful request

In the case of an unsuccessful request the API will send a 403 error code.

	{
		"error"		: "ERROR_INVALID_PARAMETERS",
		"status"	: 403
	}
	
___

#### PUT users/{id}

Update an existing user record.

- __Endpoint URL__ - http://YOUR_API_URL/users/{id}
- __Request Method__ - PUT
- __Response Format__ - JSON
- __Requires Authentication__ - Yes
- __Response Codes__ - 200 | 403

##### Parameters

	{
		"gender"	: "female",
		"name"		: "Jane Doe",
		"token"		: "55136a586803fa1f068b4581"
	}

##### Responses

###### Successful request

	{
		"gender"	: "female",
		"name"		: "Jane Doe"
	}

###### Unsuccessful request

In the case of an unsuccessful request the API will send a 403 error code.

	{
		"error"		: "PERMISSION_DENIED",
		"status"	: 403
	}
