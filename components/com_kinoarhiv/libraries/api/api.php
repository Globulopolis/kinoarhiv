<?php
/**
 * @package     Kinoarhiv.Site.Api
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * API class
 *
 * @since  3.1
 */
class KAApi
{
	/**
	 * A container for KAApi instance.
	 *
	 * @var    KAApi
	 * @since  3.1
	 */
	protected static $instance;

	/**
	 * A container for KAParser instances.
	 *
	 * @var    KAApi
	 * @since  3.1
	 */
	protected static $parsers = array();

	/**
	 * Returns the global API object, only creating it if it doesn't already exist.
	 *
	 * @return  KAApi
	 *
	 * @since   3.1
	 */
	public static function getInstance()
	{
		if (is_object(self::$instance))
		{
			return self::$instance;
		}

		return self::$instance = new KAApi;
	}

	/**
	 * Returns the global Parser object, only creating it if it doesn't already exist.
	 *
	 * @param   string  $name     Parser name or names separated by commas.
	 * @param   mixed   $options  Parser config array or object.
	 *
	 * @return  object
	 *
	 * @since   3.1
	 *
	 * @throws  Exception
	 */
	public function getParser($name = '', $options = array())
	{
		$path = JPath::clean(__DIR__ . '/parser/' . $name . '.php');
		$class = 'KAParser' . ucfirst($name);

		if (!class_exists($class))
		{
			if (file_exists($path))
			{
				require_once $path;
			}
			else
			{
				throw new Exception('Unknown parser type or something went wrong!', 500);
			}
		}

		if (empty($options))
		{
			$options = $this->getParserConfig();
		}

		if (!isset(static::$parsers[$name]) || !is_object(static::$parsers[$name]))
		{
			static::$parsers[$name] = new $class($options);
		}

		return static::$parsers[$name];
	}

	/**
	 * Returns the parser configuration object, only creating it if it doesn't already exist.
	 *
	 * @param   string  $filename  Config filename.
	 * @param   string  $path      Path to the config.
	 *
	 * @return  object
	 *
	 * @since   3.1
	 *
	 * @throws  RuntimeException
	 */
	private function getParserConfig($filename = 'config.json', $path = null)
	{
		if (empty($path))
		{
			$path = __DIR__ . '/parser/';
		}

		$path = JPath::clean($path . $filename);

		if (!is_file($path))
		{
			throw new RuntimeException('Cannot find or load parser config file', 500);
		}

		$config = new Registry;
		$config->loadFile($path);

		return $config;
	}

	/**
	 * Get data from remote server
	 *
	 * @param   string   $url      URL
	 * @param   null     $headers  Headers to send
	 * @param   integer  $timeout  Request timeout in seconds
	 *
	 * @return  object
	 *
	 * @since  3.1
	 */
	public static function getRemoteData($url, $headers = null, $timeout = 30)
	{
		jimport('libraries.vendor.Snoopy.Snoopy', JPATH_COMPONENT);

		$http = new Snoopy;
		$response = (object) array();

		if (is_object($headers))
		{
			$headers = ArrayHelper::fromObject($headers);
		}

		foreach ($headers as $key => $value)
		{
			if ($key == 'User-Agent')
			{
				$http->agent = $value;
			}
			elseif ($key == 'Referer')
			{
				$http->referer = $value;
			}
			elseif ($key == 'Accept')
			{
				$http->accept = $value;
			}
			elseif ($key == 'Cookie')
			{
				$cookies = explode(';', $value);

				foreach ($cookies as $cookie)
				{
					$_cookie = explode('=', $cookie);
					$http->cookies[trim($_cookie[0])] = trim($_cookie[1]);
				}
			}
			else
			{
				$http->rawheaders[$key] = $value;
			}
		}

		// Set timeout
		$http->read_timeout = $timeout;

		$http->maxredirs = 0;
		$http->offsiteok = false;

		// Get a page
		if ($http->fetch($url))
		{
			$response->body = $http->results;
		}
		else
		{
			$response->error = $http->error;
		}

		$response->code = (int) $http->status;
		$response->headers = $http->headers;

		return $response;
	}

	/**
	 * Encode string for searching
	 *
	 * @param   string  $uri  Text
	 *
	 * @return  string
	 *
	 * @since  3.1
	 */
	public static function encodeUrl($uri)
	{
		$entities = array(
			'%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D'
		);
		$replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");

		return str_replace($entities, $replacements, urlencode($uri));
	}
}
