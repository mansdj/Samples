/**
 * Handler for automated API unit testing.
 *
 * @author David Mans
 * @type {{getToken, setToken, getTests, getConfigs, saveTestSuite}}
 */
var Handler = (function ()
{

	//Private properties
	var _request;
	var _response;
	var _token;

	//Private Methods
	/**
	 * Submit the AJAX request
	 *
	 * @param obj
	 * @private
     */
	var _callAction = function ( obj )
	{

		var data = _encodeObject( obj );

		_request = $.ajax( {
			url: "local_api.php", async: false, method: "POST", data: data, dataType: "json"
		} );

		_request.done( function ( msg )
		{
			_response = _request.responseJSON;
		} );

		_request.fail( function ( jqXHR, textStatus )
		{
			_response = textStatus;
		} );

	};

	/**
	 * URL encode the request object
	 *
	 * @param obj
	 * @returns {string}
     * @private
     */
	var _encodeObject = function ( obj )
	{

		var result = [];

		for ( var p in obj )
		{
			if ( obj.hasOwnProperty( p ) )
			{
				result.push( typeof obj[ p ] == "object" ? _encodeObject( obj[ p ] ) : encodeURIComponent( p ) + "=" + encodeURIComponent( obj[ p ] ) );
			}
		}

		return result.join( "&" );

	};

	//Public Methods
	/**
	 * Getter for API token
	 *
	 * @returns string
     */
	var getToken = function ()
	{
		return _token;
	};

	/**
	 * Setter for API token
	 * @param token
     */
	var setToken = function ( token )
	{
		_token = token;
	};

	/**
	 * Request available test cases
	 *
	 * @param mode
	 * @returns object|string
     */
	var getTests = function ( mode )
	{

		var params = [];

		var action = "getTestCases";

		var macParams = [];

		macParams.push( action );
		macParams.push( mode );
		macParams.push( params );

		var mac = Utility.createMac( macParams, _token );

		params.push( { type: mode } );

		var obj = { action: action, params: params, mac: mac };

		_callAction( obj );

		return _response;
	};

	/**
	 * Retrieve API configuration data
	 *
	 * @returns object|string
     */
	var getConfigs = function ()
	{

		var params = [];

		var action = "getConfigs";

		var macParams = [];

		macParams.push( action );
		macParams.push( params );

		var mac = Utility.createMac( macParams, _token );

		var obj = { action: "getConfigs", params: params, mac: mac };

		_callAction( obj );

		return _response;
	};

	/**
	 * Build test suite for API unit test cases
	 *
	 * @param domain
	 * @param protocol
	 * @param sequence
	 * @param addParams
	 * @param filename
     * @returns object|string
     */
	var saveTestSuite = function ( domain, protocol, sequence, addParams, filename )
	{

		var params = [];

		var action = "makeTestSuite";

		var macParams = [];

		macParams.push( action );
		macParams.push( domain );
		macParams.push( protocol );

		for ( var h = 0; h < sequence.length; h ++ )
		{
			macParams.push( sequence[ h ] );
		}

		if ( addParams.length > 0 && addParams != "" )
		{
			for ( var i = 0; i < addParams.length; i ++ )
			{
				macParams.push( addParams[ i ] );
			}
		}
		else
		{
			addParams = [];
			macParams.push( addParams );
		}

		macParams.push( filename );

		console.log( "Params:", macParams );

		params.push( { domain: domain } );
		params.push( { protocol: protocol } );
		params.push( { sequence: sequence.join( "," ) } );
		params.push( { additional: addParams.join( "," ) } );
		params.push( { filename: filename } );

		var mac = Utility.createMac( macParams, _token );

		var obj = { action: "makeTestSuite", params: params, mac: mac };

		_callAction( obj );

		return _response;
	};

	return {
		getToken: getToken,
		setToken: setToken,
		getTests: getTests,
		getConfigs: getConfigs,
		saveTestSuite: saveTestSuite
	};
}());