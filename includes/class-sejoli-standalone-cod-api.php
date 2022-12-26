<?php
namespace Sejoli_Standalone_Cod;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://sejoli.co.id
 * @since      1.0.0
 *
 * @package    Sejoli_Standalone_Cod
 * @subpackage Sejoli_Standalone_Cod/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Sejoli_Standalone_Cod
 * @subpackage Sejoli_Standalone_Cod/admin
 * @author     Sejoli Team <orangerdigiart@gmail.com>
 */
class API {

	/**
	 * Set endpoint for request.
	 *
	 * @since 1.0.0
     *
	 * @var string
	 */
	protected static $endpoint;

	/**
	 * Set headers for request.
	 *
	 * @since 1.0.0
     *
	 * @var array
	 */
	protected static $headers;

	/**
	 * Set method for request.
	 *
	 * @since 1.0.0
     *
	 * @var string
	 */
	protected static $method;

	/**
	 * Set timeout param for request.
	 *
	 * @since 1.0.0
     *
	 * @var integer
	 */
	protected static $timeout = 75;

	/**
	 * Set body data for request.
	 *
	 * @since 1.0.0
     *
	 * @var array
	 */
	protected static $body;

	/**
     * Container for wp_remote_request function
     *
     * @since   1.0.0
     *
     * @return 	(array|WP_Error) The response array or a WP_Error on failure
     */
	public static function do_request() {

		return wp_remote_request( static::$endpoint, [
			'headers' => static::$headers,
			'method'  => static::$method,
			'timeout' => static::$timeout,				    
			'body' 	  => static::$body
		]);

	}

	/**
     * Verify header status code
     *
     * @since   1.0.0
     *
     * @param 	$response 		$response to check
     * @param 	$code			status code to check, default 200 (success)
     *
     * @return 	(boolean)
     */
	public static function verify_response_code( $response, int $code = 200 ) {
		
		if( $response == NULL ) {
			return false;
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if( $response_code == $code ) {
			return true;
		}

		if( $response_code == 201 ) {
			return true;
		}

		return false;
		
	}

}
