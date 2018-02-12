<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

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
	 * @return  mixed
	 *
	 * @since   3.1
	 */
	public function getParser($name, $options = array())
	{
		$path = __DIR__ . '/parsers/' . $name;
		$classPath = JPath::clean($path . '/' . $name . '.php');
		$class = 'KAParser' . ucfirst($name);

		if (!class_exists($class))
		{
			if (file_exists($classPath))
			{
				require_once $classPath;
			}
			else
			{
				return false;
			}
		}

		if (empty($options))
		{
			$options = $this->getParserConfig($path . '/config.json');
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
	 * @param   string  $path  Path to the config.
	 *
	 * @return  object
	 *
	 * @since   3.1
	 *
	 * @throws  RuntimeException
	 */
	private function getParserConfig($path)
	{
		$path = JPath::clean($path);

		if (!is_file($path))
		{
			throw new RuntimeException('Cannot load parser config file.', 500);
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
	 * @return  string|boolean
	 *
	 * @since   3.1
	 */
	public static function getRemoteData($url, $headers = null, $timeout = 30)
	{
		try
		{
			$response = JHttpFactory::getHttp()->get($url, $headers, $timeout);
		}
		catch (RuntimeException $e)
		{
			JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_DOWNLOAD_SERVER_CONNECT', $e->getMessage()), JLog::WARNING, 'jerror');

			return false;
		}

		if (302 == $response->code && isset($response->headers['Location']))
		{
			return $response->body;
		}
		elseif (200 != $response->code)
		{
			JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_DOWNLOAD_SERVER_CONNECT', $response->code), JLog::WARNING, 'jerror');

			return false;
		}

		return $response->body;
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
