<?php

namespace TelegramPluginBoilerplate\API;

abstract class BaseEndpoint
{
	/**
	 * **(REQUIRED)** The first URL segment after core prefix. Should be unique to your package/plugin.
	 *
	 * @var	string
	 */
	protected $namespace = 'v1';

	/**
	 * **(REQUIRED)** The base URL for route you are adding.
	 *
	 * @var	string
	 */
	protected $route = null;

	/**
	 * The method used for the endpoint.
	 *
	 * @var	string
	 */
	protected $method = 'GET';

	public function __construct()
	{
		if (empty($this->namespace))
			throw new \LogicException(get_class($this) . ' must initialize $namespace property!');
		if (empty($this->route))
			throw new \LogicException(get_class($this) . ' must initialize $route property!');
		if (empty($this->method))
			throw new \LogicException(get_class($this) . ' must initialize $method property!');

		add_action('rest_api_init', [$this, 'rest_api_init']);
	}

	/**
	 * Adds a custom endpoint.
	 *
	 * Result: https://SiteURL.com/wp-json/v1/{$this->route}
	 *
	 * @return	void
	 *
	 * @hooked	action: `rest_api_init` - 10
	 */
	public function rest_api_init()
	{
		register_rest_route(
			$this->namespace,
			$this->route,
			[
				'methods'             => $this->method,
				'callback'            => [$this, 'handle'],
				'permission_callback' => [$this, 'permission_callback'],
			]
		);
	}

	/**
	 * Handles the main logic and functions of this endpoint.
	 *
	 * @param	array	$request
	 *
	 * @return	void
	 */
	abstract function handle($request);

	/**
	 * Checks authorization headers for token.
	 *
	 * @return	void
	 */
	public function permission_callback()
	{
		return true;

		$token       = '-';
		$header_auth = $this->get_authorization_header();

		if (empty($token))
		{
			return new \WP_Error(
				esc_html__('Token Not Found', FDTBWPB_TEXT_DOMAIN),
				esc_html__('No token is defined to receive requests', FDTBWPB_TEXT_DOMAIN),
				['status' => 500],
			);
		}

		if (empty($header_auth))
		{
			return new \WP_Error(
				esc_html__('Authorization Missing', FDTBWPB_TEXT_DOMAIN),
				esc_html__('Not authorized', FDTBWPB_TEXT_DOMAIN),
				['status' => 401],
			);
		}

		if (stripos($header_auth, 'Bearer') !== false)
		{
			$header_auth = explode(' ', $header_auth)[1];
		} else {
			return new \WP_Error(
				esc_html__('Token Missing', FDTBWPB_TEXT_DOMAIN),
				esc_html__('Not authorized', FDTBWPB_TEXT_DOMAIN),
				['status' => 401],
			);
		}

		if ($token != $header_auth)
		{
			return new \WP_Error(
				esc_html__('Invalid Token', FDTBWPB_TEXT_DOMAIN),
				esc_html__('Not authorized', FDTBWPB_TEXT_DOMAIN),
				['status' => 401],
			);
		}

		return true;
	}

	/**
	 * Gets header authorization.
	 *
	 * @return	string|null
	 *
	 * @source	https://StackOverflow.com/a/40582472/1930153
	 */
	private function get_authorization_header()
	{
		$headers = null;

		if (isset($_SERVER['Authorization']))
		{
			$headers = trim($_SERVER["Authorization"]);
		} else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
			$headers = trim($_SERVER["HTTP_AUTHORIZATION"]); // Nginx or fast CGI
		} elseif (function_exists('apache_request_headers')) {
			$requestHeaders = apache_request_headers();
			// Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
			$requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
			if (isset($requestHeaders['Authorization']))
				$headers = trim($requestHeaders['Authorization']);
		}

		return $headers;
	}

	/**
	 * Returns the proper WP REST response.
	 *
	 * @param	int 				$status
	 * @param	string 				$message
	 * @param	string|int|float	...$sprintf_params
	 *
	 * @return	\WP_REST_Response
	 */
	protected function get_rest_reponse($status, $message = null, ...$sprintf_params)
	{
		$return = new \WP_REST_Response();

		$return->set_status($status);

		if (!empty($message))
		{
			if (!empty($sprintf_params))
				$message = sprintf($message, ...$sprintf_params);

			$return->set_data(['message' => trim($message)]);
		}

		return $return;
	}
}
