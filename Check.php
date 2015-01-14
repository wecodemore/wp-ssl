<?php

namespace WCM\SSL;

/**
 * Class Check
 * @package WCM\SSL
 */
class Check
{
	/** @var string */
	private $path;

	/** @var int */
	private $port = 443;

	/** @var null|int */
	private $blog_id;

	/**
	 * @param string $path
	 * @param int    $port
	 * @param int    $blog_id
	 */
	public function __construct( $path = '', $port = 443, $blog_id = null )
	{
		$this->path = $path;
		$this->setPort( $port );
		$this->setBlogID( $blog_id );
	}

	/**
	 * @param int $port
	 * @return $this
	 */
	public function setPort( $port )
	{
		$this->port = absint( $port );
		return $this;
	}

	/**
	 * @param int $blog_id
	 * @return $this
	 */
	public function setBlogID( $blog_id )
	{
		$this->blog_id = absint( $blog_id );
		return $this;
	}

	/**
	 * Returns a path relative from the WP root dir
	 * Adds https for secure connections
	 * @return string $url
	 */
	public function getUrl()
	{
		$scheme = $this->isSSL() ? 'https' : 'http';

		// Check if the url is accessible, or if is_ssl() told us 'secure' by mistake
		if ( in_array( 'curl', get_loaded_extensions() ) )
		{
			// This part replaces url_is_accessable_via_ssl
			$url = set_url_scheme( get_site_url( null, $this->path, $scheme ) );
			$response = wp_remote_get( $url, 'https' );

			if ( ! is_wp_error( $response ) )
			{
				$status = wp_remote_retrieve_response_code( $response );
				in_array( $status, array( 200, 401, ) ) and $scheme = 'http';
			}
		}

		return get_site_url( $this->blog_id, $this->path, $scheme );
	}


	/**
	 * Extended version of the core is_ssl() function
	 *
	 * HTTPS
	 * When using ISAPI with IIS, the value will be off,
	 * if the request was not made through the HTTPS protocol
	 *
	 * HTTP_X_FORWARDED_PROTO
	 * required if you are operating behind a load balancer that terminates the SSL connection
	 * and then forwards to the machines behind it on port 80
	 * (common set-up on the Amazon cloud)
	 * @return boolean true/false Whether on a secure connection or not
	 */
	public function isSSL()
	{
		$port = $this->getPort();

		if (
			! empty( $_SERVER['HTTPS'] )
			and $_SERVER['HTTPS']
			)
		{
			// Maybe won't work if Apache is configured
			// name based (more than one website per IP)
			// Accepts 'on', '1', etc. as well
			if ( filter_var(
				strtolower( $_SERVER['HTTPS'] ),
				FILTER_VALIDATE_BOOLEAN,
				FILTER_NULL_ON_FAILURE
			) )
				return true;
		}
		elseif (
			isset( $_SERVER['SERVER_PORT'] )
			and (string) $port === $_SERVER['SERVER_PORT']
			)
		{
			return true;
		}
		elseif (
			isset( $_SERVER['HTTP_X_FORWARDED_PORT'] )
			and (string) $port === $_SERVER['HTTP_X_FORWARDED_PORT']
			)
		{
			return true;
		}
		elseif (
			isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] )
			and 'https' === strtolower( $_SERVER['HTTP_X_FORWARDED_PROTO'] )
			)
		{
			return true;
		}

		return false;
	}


	/**
	 * Gets the correct port
	 * Adapted from @link http://stackoverflow.com/a/5004886/376483
	 *
	 * Testing with fsocketopen() is avoided.
	 * Assuming the user knows his custom port, if set.
	 *
	 * @return integer $port
	 */
	public function getPort()
	{
		// Check if the port is set. If not, try to get it from the environment
		if (
			! isset( $_SERVER["SERVER_PORT"] )
			or ! $_SERVER["SERVER_PORT"]
			)
		{
			if ( ! isset( $_ENV["SERVER_PORT"] ) )
			{
				getenv("SERVER_PORT");
				$_SERVER["SERVER_PORT"] = $_ENV["SERVER_PORT"];
			}
		}

		// Fall back to custom port
		if (
			! isset( $_SERVER["SERVER_PORT"] )
			or ! $_SERVER["SERVER_PORT"]
		)
			$_SERVER["SERVER_PORT"] = $this->port;

		return $_SERVER["SERVER_PORT"];
	}
}