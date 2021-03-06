<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Core helper
 *
 * PHP version 5
 * LICENSE: This source file is subject to the AGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/licenses/agpl.html
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    SwiftRiver - http://github.com/ushahidi/SwiftRiver
 * @category   Helpers
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/licenses/agpl.html GNU Affero General Public License (AGPL)
 */
class Ushahidi_Core {

	/**
	 * Checks whether the deployment referenced by the specified url
	 * is running a version of the platform is compatible with this plugin
	 *
	 * @param  string  $url URL of the Ushahidi deployment
	 * @return bool
	 */
	public static function is_compatible_deployment($deployment_url)
	{
		// Get the platform version
		$min_version = Kohana::$config->load('ushahidi.min_version');
		
		$max_version = Kohana::$config->load('ushahidi.max_version');
		
		// Endpoint to check whether the plugin has been installed on the
		// ushahidi deployment
		$version_endpoint = Kohana::$config->load('ushahidi.endpoints.ping');
		
		// Get the request url
		$request_url = self::get_request_url($deployment_url, $version_endpoint);
		
		// Send the request
		$api_response = self::api_request($request_url);
		
		if ( ! $api_response)
		{
			Kohana::$log->add(Log::ERROR, ":url is not a valid Ushahidi deployment or the SwiftRiver plugin "
			    . "is not installed on the target deployment", array(":url" => $deployment_url));
			return FALSE;
		}
        
		// Get the version of the deployment
		$deployment_version = $api_response['platform_version'];
		return ($deployment_version >= $min_version AND $deployment_version <= $max_version);
	}

	/**
	 * Given a deployment, gets the list of categories via the Ushahidi API
	 *
	 * @param  Model_Deployment $deployment
	 * @return bool
	 */
	public static function get_categories($deployment)
	{
		// Get the endpoint for fetching the categories
		$categories_endpoint = Kohana::$config->load('ushahidi.endpoints.categories');

		// Get the request url
		$request_url = self::get_request_url($deployment->deployment_url, $categories_endpoint);
		
		// Execute the request and fetch the response
		$api_response = self::api_request($request_url);
        
		if ( ! $api_response)
		{
			Kohana::$log->add(Log::ERROR, "An unknown error occurred.");
			return FALSE;
		}
		
		list($status, $categories) = array($api_response['error'], $api_response['payload']['categories']);	

		if ($status['code'] == '0')
		{
			Model_Deployment::add_categories($deployment->id, $categories);
		}
		else
		{
			// API returned an error
			Kohana::$log->add(Log::ERROR, "API returned an error - :message",
				array(":message" => $status['message']));
		}
	}
	
	/**
	 * Concatentates the deployment url and provided segment to produce
	 * a single request url
	 *
	 * @param  string  $deployment_url Base URL for the deployment
	 * @param  string  $endpoint Segment to be appended to the deployment url
	 * @return string
	 */
	public static function get_request_url($deployment_url, $endpoint)
	{
		$request_url = $deployment_url;
		
		if (substr($request_url, strlen($request_url)-1, 1) !== "/")
		{
			$request_url .= "/";
		}

		// Build out the request cURL
		return $request_url.$endpoint;
		
	}
	
	/**
	 * Executes an API request via cURL and returns the response
	 * as an array
	 *
	 * @param  string $request_url URL for the cURL request
	 * @return mixed  Array on success (request returns a 200 status code), FALSE otherwise
	 */
	public static function api_request($request_url)
	{
		// Cleanse query parameters from the supplied URL
		$split_url = explode("?", $request_url);
		$request_url = array_shift($split_url);
		
		// Send the request and fetch the response
		$request = Request::factory($request_url);
		if (count($split_url))
		{
			// Get the query params
			parse_str($split_url[0], $query_params);
			$request->query($query_params);            
		}

		// Initiate cURL request
		$api_response = Request_Client_Curl::factory()
			->options(CURLOPT_SSL_VERIFYPEER, FALSE)
			->execute($request);

		return $api_response->status() == 200
			? json_decode($api_response->body(), TRUE)
			: FALSE;
	}
}