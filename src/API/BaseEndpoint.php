<?php

namespace TelegramPluginBoilerplate\API;

abstract class BaseEndpoint
{
	/**
	 * **(REQUIRED)** The first URL segment after core prefix. Should be unique to your package/plugin.
	 *
	 * @var	string
	 */
	protected string $namespace = 'v1';

	/**
	 * **(REQUIRED)** The base URL for route you are adding.
	 *
	 * @var	string
	 */
	protected string $route = '';

	/**
	 * The method used for the endpoint.
	 *
	 * @var	string
	 */
	protected string $method = 'GET';

	public function __construct()
	{
		if (empty($this->namespace))
			throw new \LogicException(get_class($this) . ' must initialize $namespace property!');
		if (empty($this->route))
			throw new \LogicException(get_class($this) . ' must initialize $route property!');
		if (empty($this->method))
			throw new \LogicException(get_class($this) . ' must initialize $method property!');

		add_action('rest_api_init', [$this, 'initialize']);
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
	public function initialize(): void
	{
		register_rest_route(
			$this->namespace,
			$this->route,
			[
				'methods'             => $this->method,
				'callback'            => [$this, 'handle'],
				'permission_callback' => [$this, 'checkPermission'],
			]
		);
	}

	/**
	 * Handles the main logic and functions of this endpoint.
	 *
	 * @param	\WP_REST_Request	$request
	 *
	 * @return	void
	 */
	abstract function handle(\WP_REST_Request $request);

	/**
	 * Checks authorization headers for token.
	 *
	 * @return	bool|\WP_Error
	 *
	 * @todo			Fetch token from settings
	 */
	public function checkPermission(): bool|\WP_Error
	{
		return true; // TODO: Remove this line

		$token      = '-'; // TODO: Change this
		$headerAuth = $this->getAuthorizationHeader();

		if (empty($token))
		{
			return new \WP_Error(
				'token_not_found',
				esc_html__('No token is defined to receive requests', 'telegram-plugin-boilerplate'),
				['status' => 500],
			);
		}

		if (empty($headerAuth))
		{
			return new \WP_Error(
				'authorization_missing',
				esc_html__('Not authorized', 'telegram-plugin-boilerplate'),
				['status' => 401],
			);
		}

		if (stripos($headerAuth, 'Bearer') !== false)
		{
			$headerAuth = explode(' ', $headerAuth)[1];
		} else {
			return new \WP_Error(
				'token_missing',
				esc_html__('Not authorized', 'telegram-plugin-boilerplate'),
				['status' => 401],
			);
		}

		if ($token != $headerAuth)
		{
			return new \WP_Error(
				'invalid_token',
				esc_html__('Not authorized', 'telegram-plugin-boilerplate'),
				['status' => 401],
			);
		}

		return true;
	}

	/**
	 * Returns header authorization.
	 *
	 * @return	string|null
	 *
	 * @source	https://StackOverflow.com/a/40582472/
	 */
	private function getAuthorizationHeader(): string|null
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
	 * @param	string|int|float	...$sprintfParams
	 *
	 * @return	\WP_REST_Response
	 */
	protected function getRestResponse(int $status, string $message = '', string|int|float ...$sprintfParams): \WP_REST_Response
	{
		$return = new \WP_REST_Response();

		$return->set_status($status);

		if (!empty($message))
		{
			if (!empty($sprintfParams))
				$message = sprintf($message, ...$sprintfParams);

			$return->set_data(['message' => trim($message)]);
		}

		return $return;
	}
}
