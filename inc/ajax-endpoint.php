<?php

namespace SolrPlugin;

/**
 * Provides an AJAX endpoint, which can be cached via Varnish.
 * Create an AJAX endpoint via `new Ajax_Endpoint( $action, $callback );`, with
 * $callback being a (anonymous if you like) function.
 *
 * Example:
 *
 * new Ajax_Endpoint( 'my-endpoint', function( $param ) {
 *    // $param is optional
 *    // Process Request
 * });
 *
 * The callback function retrieves an optional parameter.
 * Request via `/__ajax/{Plugin::DOMAIN}/{my-endpoint}/{optional parameter}`
 *
 * @see http://coderrr.com/create-an-api-endpoint-in-wordpress/
 * @author Kim-Christian Meyer <kim.meyer@palasthotel.de>
 */
class Ajax_Endpoint {
	
	const AJAX_PREFIX = "__ajax";
	const AJAX_VALUE = "1";
	const VAR_DOMAIN = "ajax-domain";
	const VAR_ACTION_PREFIX = "ajax-action-";
	const VAR_PARAM_PREFIX = "ajax-param-";
	
	/**
	 * GET keys action & param for request
	 */
	public static function PARAM() {
		return self::VAR_PARAM_PREFIX . Plugin::DOMAIN;
	}
	
	public static function ACTION() {
		return self::VAR_ACTION_PREFIX . Plugin::DOMAIN;
	}
	
	// AJAX request key
	private $request_key;
	
	// This callback function is called, when AJAX request ist submitted.
	private $callback_function;
	
	/**
	 * Hook WordPress
	 * @return void
	 */
	public function __construct( $request_key, callable $callback_function ) {
		$this->request_key       = $request_key;
		$this->callback_function = $callback_function;
		
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
		add_action( 'parse_request', array( $this, 'sniff_requests' ), 0 );
		add_action( 'init', array( $this, 'add_endpoint' ), 0 );
	}
	
	/**
	 * Add public query vars
	 *
	 * @param array $vars List of current public query vars
	 *
	 * @return array $vars
	 */
	public function add_query_vars( $vars ) {
		$vars[] = self::ACTION();
		$vars[] = self::PARAM();
		$vars[] = self::AJAX_PREFIX;
		$vars[] = self::VAR_DOMAIN;
		
		return $vars;
	}
	
	/**
	 * Add API Endpoint
	 * This is where the magic happens - brush up on your regex skillz
	 * @return void
	 */
	public function add_endpoint() {
		add_rewrite_rule(
			'^' . self::AJAX_PREFIX . '/' . Plugin::DOMAIN . '/([^/]+)(?:/([^/]+))?/?',
			'index.php?' . self::AJAX_PREFIX . '=' . self::AJAX_VALUE . '&'.self::VAR_DOMAIN.'='.Plugin::DOMAIN.'&' . self::ACTION() . '=$matches[1]&' . self::PARAM() . '=$matches[2]', 'top'
		);
	}
	
	/**
	 * Sniff Requests
	 * This is where we hijack all API requests
	 * If $_GET['ajax-action'] is set, we kill WP and proceed to our own ajax handler
	 */
	public function sniff_requests() {
		global $wp;
		if ( isset( $wp->query_vars[ self::AJAX_PREFIX ] ) && $wp->query_vars[ self::AJAX_PREFIX ] == self::AJAX_VALUE
		     && isset( $wp->query_vars[ self::VAR_DOMAIN ]) && $wp->query_vars[self::VAR_DOMAIN] == Plugin::DOMAIN ) {
			$this->handle_request();
			exit;
		}
	}
	
	/**
	 * Handle Requests
	 * Decide what to do.
	 * @return void
	 */
	protected function handle_request() {
		global $wp;
		
		// $param Equals empty string if not set.
		$action = (empty($wp->query_vars[ self::ACTION() ]))? "": $wp->query_vars[ self::ACTION() ];
		$param = (empty($wp->query_vars[ self::PARAM() ]))? "": $wp->query_vars[ self::PARAM() ];
		
		if ( ! empty( $action ) && $action === $this->request_key && is_callable( $this->callback_function ) ) {
			call_user_func( $this->callback_function, $param );
		}
		
		// TODO: set 404
		exit;
	}
	
	/**
	 * get url for ajax request
	 *
	 * @param string $param
	 *
	 * @return string
	 */
	public function getURL( $param = "" ) {
		return '/' . self::AJAX_PREFIX . '/' . Plugin::DOMAIN . '/' . $this->request_key . '/' . $param;
	}
}