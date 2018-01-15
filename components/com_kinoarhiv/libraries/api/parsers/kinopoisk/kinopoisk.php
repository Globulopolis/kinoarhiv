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

use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Parser class for Kinopoisk.ru
 *
 * @since  3.1
 */
class KAParserKinopoisk extends KAApi
{
	/**
	 * Parser parameters
	 *
	 * @var    object
	 * @since  3.1
	 */
	protected $params = null;

	/**
	 * Array of parsed URLs
	 *
	 * @var    array
	 * @since  3.1
	 */
	protected $urls = null;

	/**
	 * Array of headers to send to the server
	 *
	 * @var    array
	 * @since  3.1
	 */
	private $headers = null;

	/**
	 * Constructor activating the default information
	 *
	 * @param   mixed  $config  Parser config array or object
	 *
	 * @since   3.1
	 */
	public function __construct($config = array())
	{
		$this->params  = $config;
		$this->headers = ArrayHelper::fromObject($this->params->get('headers'));

		// Set up an array with pages
		$this->urls = array(
			'main'     => 'https://www.kinopoisk.ru/film/[id]/',
			'rating'   => 'https://rating.kinopoisk.ru/[id].xml',
			'cast'     => 'https://www.kinopoisk.ru/film/[id]/cast/',
			'releases' => 'https://www.kinopoisk.ru/film/[id]/dates/'
		);
	}

	/**
	 * Get info about entity.
	 *
	 * @param   string  $id      Item ID.
	 * @param   string  $entity  Entity type.
	 *
	 * @since   3.1
	 *
	 * @return  mixed
	 */
	public function getInfo($id, $entity)
	{
		jimport('components.com_kinoarhiv.libraries.api.parsers.kinopoisk.' . $entity, JPATH_ROOT);

		try
		{
			$html = $this->getDataById($id);
		}
		catch (Exception $e)
		{
			return array('error' => $e->getMessage());
		}

		$dom = new DOMDocument('1.0', 'utf-8');
		@$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);
		$result = new KAParserKinopoiskMovie($this->params);
		$result = $result->getRating($xpath);

		return $result;
	}

	/**
	 * Get movie web-page by ID and store in cache.
	 *
	 * @param   string  $id       Movie ID from Imdb
	 * @param   string  $page     Page URL
	 * @param   array   $options  Custom options
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	private function getDataById($id, $page = 'main', $options = array())
	{
		$caching = isset($options['cache']) ? $options['cache'] : true;
		$cache = JCache::getInstance();
		$cache->setCaching((bool) $caching);
		$cache->setLifeTime($this->params->get('cache_lifetime'));
		$cache_id = $id . '.' . $page;

		if ($cache->get($cache_id, 'kinopoisk') === false)
		{
			$response = parent::getRemoteData(
				str_replace('[id]', $id, $this->urls[$page]),
				$this->headers
			);

			$output = $response;
			$cache->store($output, $cache_id, 'kinopoisk');
		}
		else
		{
			$output = $cache->get($cache_id, 'kinopoisk');
		}

		return $output;
	}
}
