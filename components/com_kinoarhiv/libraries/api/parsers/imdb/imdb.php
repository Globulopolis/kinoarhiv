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
 * Parser class for Imdb.com
 *
 * @since  3.1
 */
class KAParserImdb extends KAApi
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
			'main'       => 'http://www.imdb.com/title/[id]/?ref_=nv_sr_1',
			'cast'       => 'http://www.imdb.com/title/[id]/fullcredits?ref_=tt_ql_1',
			'releases'   => 'http://www.imdb.com/title/[id]/releaseinfo?ref_=ttfc_ql_2',
			'posters'    => 'http://www.imdb.com/title/[id]/mediaviewer/[itemid]?ref_=tt_ov_i',
			'name'       => 'http://www.imdb.com/name/[id]/',
			'name_bio'   => 'http://www.imdb.com/name/[id]/bio?ref_=nm_ov_bio_sm',
			'awards'     => 'http://www.imdb.com/name/[id]/awards?ref_=nm_awd',
			'name_photo' => 'http://www.imdb.com/name/[id]/mediaviewer/[itemid]?ref_=nm_ov_ph'
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
		jimport('components.com_kinoarhiv.libraries.api.parsers.imdb.' . $entity, JPATH_ROOT);

		// Validate ID
		if (!preg_match('@^tt(\d+)@', $id))
		{
			return array('error' => 'Wrong ID format');
		}

		try
		{
			$this->headers['Referer'] = $this->headers['Referer'] . '/title/' . $id . '/?ref_=nv_sr_1';
			$html = $this->getDataById($id);
		}
		catch (Exception $e)
		{
			return array('error' => $e->getMessage());
		}

		$dom = new DOMDocument('1.0', 'utf-8');
		@$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);
		$result = new KAParserImdbMovie($this->params);
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

		if ($cache->get($cache_id, 'imdb') === false)
		{
			// ID of the image from mediabrowser
			$itemid = isset($options['itemid']) ? $options['itemid'] : '';

			$response = parent::getRemoteData(
				str_replace(array('[id]', '[itemid]'), array($id, $itemid), $this->urls[$page]),
				$this->headers
			);

			$output = $response;
			$cache->store($output, $cache_id, 'imdb');
		}
		else
		{
			$output = $cache->get($cache_id, 'imdb');
		}

		return $output;
	}
}
