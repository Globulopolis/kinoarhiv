<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Parser class for Rottentomatoes.com
 *
 * @since  3.1
 */
class KAParserRottentomatoes extends KAApi
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
		$this->params = $config;
		$this->headers = ArrayHelper::fromObject($this->params->get('headers'));

		// Set up an array with pages
		$this->urls = array(
			'main' => 'https://www.rottentomatoes.com/m/[id]/'
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

		$json_raw = @$xpath->query($this->params->get('patterns.json_data'))->item(0)->nodeValue;
		$object = json_decode($json_raw);

		// Check if json is valid for UTF8 symbols.
		if (json_last_error() == JSON_ERROR_UTF8)
		{
			$json_raw = utf8_decode($json_raw);
			$object = json_decode($json_raw);
		}

		$t_score = trim(@$xpath->query($this->params->get('patterns.ratings.t_score'))->item(0)->nodeValue);
		$t_av_rating = trim(@$xpath->query($this->params->get('patterns.ratings.t_av_rating'))->item(0)->nodeValue);
		$t_reviews_counted = trim(@$xpath->query($this->params->get('patterns.ratings.t_reviews_counted'))->item(0)->nodeValue);
		$t_fresh = trim(@$xpath->query($this->params->get('patterns.ratings.t_fresh'))->item(0)->nodeValue);
		$t_rotten = trim(@$xpath->query($this->params->get('patterns.ratings.t_rotten'))->item(0)->nodeValue);
		$t_consensus = explode('Critics Consensus:', @$xpath->query($this->params->get('patterns.ratings.t_consensus'))->item(0)->nodeValue);
		$a_score = trim(@$xpath->query($this->params->get('patterns.ratings.a_score'))->item(0)->nodeValue);
		$a_av_rating = trim(@$xpath->query($this->params->get('patterns.ratings.a_av_rating'))->item(0)->nodeValue);
		$a_user_ratings = trim(@$xpath->query($this->params->get('patterns.ratings.a_user_ratings'))->item(0)->nodeValue);

		$consensus = array_key_exists(1, $t_consensus) ? trim($t_consensus[1]) : '';

		$result = array(
			'rating'   => array(
				'score'           => (int) $t_score,
				'av_rating'       => $t_av_rating,
				'reviews_counted' => (int) $t_reviews_counted,
				'fresh'           => (int) $t_fresh,
				'rotten'          => (int) $t_rotten,
				'consensus'       => $consensus
			),
			'audience' => array(
				'score'        => $a_score,
				'av_rating'    => $a_av_rating,
				'user_ratings' => $a_user_ratings,
			),
			'reviews'  => $object->review
		);

		return $result;
	}

	/**
	 * Method to get movie data
	 *
	 * @param   string  $id    Movie ID from Rottentomatoes
	 * @param   string  $data  List of fields to return
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getMovieInfo($id, $data = '')
	{
		/*try
		{
			$html = $this->getPageById($id);
		}
		catch (Exception $e)
		{
			return array('error' => $e->getMessage());
		}

		$cols = array();
		$dom = new DOMDocument('1.0', 'utf-8');
		@$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);
		$json_raw = @$xpath->query($this->params->get('rottentomatoes.patterns.json_data'))->item(0)->nodeValue;
		$object = json_decode($json_raw);

		// Check if json is valid for UTF8 symbols.
		if (json_last_error() == JSON_ERROR_UTF8)
		{
			$json_raw = utf8_decode($json_raw);
			$object = json_decode($json_raw);
		}

		// Filter results by keys from $_GET['data']
		if (!empty($data))
		{
			$filter = JFilterInput::getInstance();
			$cols = explode(',', $data);

			foreach ($cols as $col)
			{
				$cols[] = StringHelper::strtolower($filter->clean($col, 'word'));
			}
		}

		$t_score = trim(@$xpath->query($this->params->get('rottentomatoes.patterns.ratings.t_score'))->item(0)->nodeValue);
		$t_av_rating = trim(@$xpath->query($this->params->get('rottentomatoes.patterns.ratings.t_av_rating'))->item(0)->nodeValue);
		$t_reviews_counted = trim(@$xpath->query($this->params->get('rottentomatoes.patterns.ratings.t_reviews_counted'))->item(0)->nodeValue);
		$t_fresh = trim(@$xpath->query($this->params->get('rottentomatoes.patterns.ratings.t_fresh'))->item(0)->nodeValue);
		$t_rotten = trim(@$xpath->query($this->params->get('rottentomatoes.patterns.ratings.t_rotten'))->item(0)->nodeValue);
		$t_consensus = explode('Critics Consensus:', @$xpath->query($this->params->get('rottentomatoes.patterns.ratings.t_consensus'))->item(0)->nodeValue);
		$a_score = trim(@$xpath->query($this->params->get('rottentomatoes.patterns.ratings.a_score'))->item(0)->nodeValue);
		$a_av_rating = trim(@$xpath->query($this->params->get('rottentomatoes.patterns.ratings.a_av_rating'))->item(0)->nodeValue);
		$a_user_ratings = trim(@$xpath->query($this->params->get('rottentomatoes.patterns.ratings.a_user_ratings'))->item(0)->nodeValue);

		$result = array(
			'tomatometer' => array(
				'score'           => $t_score,
				'av_rating'       => $t_av_rating,
				'reviews_counted' => $t_reviews_counted,
				'fresh'           => $t_fresh,
				'rotten'          => $t_rotten,
				'consensus'       => trim($t_consensus[1])
			),
			'audience'    => array(
				'score'        => $a_score,
				'av_rating'    => $a_av_rating,
				'user_ratings' => $a_user_ratings,
			),
			'reviews'     => $object->review
		);

		return !empty($data) ? array_intersect_key($result, array_flip($cols)) : $result;*/
	}

	/**
	 * Search movie by title.
	 *
	 * @param   string   $title      Movie title
	 * @param   boolean  $return_id  Just return an ID of the first result
	 *
	 * @return  mixed
	 *
	 * @throws  Exception
	 * @since   3.1
	 */
	public function getMovieSearch($title, $return_id = false)
	{
		/*if (empty($title))
		{
			throw new InvalidArgumentException('Wrong search value', 500);
		}

		$input = JFactory::getApplication()->input;
		$showall = $input->get('showall', array(), 'array');
		$result = array();
		$cache = JCache::getInstance();
		$cache->setCaching(true);
		$cache->setLifeTime($this->params->get('rottentomatoes.cache_lifetime'));
		$cache_id = $title . '-' . 'search_result';

		if ($cache->get($cache_id, 'parser_rottentomatoes') === false)
		{
			if (isset($showall['rottentomatoes']) && $showall['rottentomatoes'] == 1)
			{
				$this->headers['Referer'] = 'https://www.rottentomatoes.com/search/?search=' . parent::encodeUrl($title);
				$url = 'https://www.rottentomatoes.com/api/private/v1.0/search?q=' . parent::encodeUrl($title) . '&t=movie&page=1';
			}
			else
			{
				$url = 'https://www.rottentomatoes.com/search/?search=' . parent::encodeUrl($title);
			}

			$response = parent::getRemoteData(
				$url,
				$this->headers,
				30
			);

			if ($response->code == 200)
			{
				$output = $response->body;
				$cache->store($output, $cache_id, 'parser_rottentomatoes');
			}
			else
			{
				throw new Exception('HTTP error: ' . $response->code, $response->code);
			}
		}
		else
		{
			$output = $cache->get($cache_id, 'parser_rottentomatoes');
		}

		if (isset($showall['rottentomatoes']) && $showall['rottentomatoes'] == 1)
		{
			$object = json_decode($output);

			// Check if json is valid for UTF8 symbols. Sometimes rottentomatoes return wrong encoded data.
			if (json_last_error() == JSON_ERROR_UTF8)
			{
				$output = utf8_decode($output);
				$object = json_decode($output);
			}

			if (is_null($object))
			{
				// Remove old cache
				$cache->clean('parser_rottentomatoes');

				throw new Exception('Error: no valid json returned from remote server or something wrong with cached data. Try to reload page.', 500);
			}

			// Get first result and search ID
			if ($return_id)
			{
				$url_parts = explode('/', $object->movies[0]->url);
				$id = (array_key_exists(2, $url_parts)) ? (string) $url_parts[2] : 0;

				return $id;
			}

			foreach ($object->movies as $item)
			{
				$url_parts = explode('/', $item->url);
				$id = (array_key_exists(2, $url_parts)) ? (string) $url_parts[2] : 0;
				$year = isset($item->year) ? ' (' . $item->year . ')' : '';

				$result[] = array(
					'id'    => $id,
					'img'   => $item->posterImage,
					'title' => $item->name . $year,
					'link'  => JRoute::_('index.php?option=com_kinoarhiv&task=api.parser&action[rottentomatoes]=movie.info&id[rottentomatoes]='
						. $id . '&format=json', false
					)
				);
			}
		}
		else
		{
			$dom = new DOMDocument('1.0', 'utf-8');
			@$dom->loadHTML($output);
			$xpath = new DOMXPath($dom);

			// Get first result and search ID
			if ($return_id)
			{
				$row = @$xpath->query("//h2[contains(., 'Movies')]/following-sibling::ul[@class='results_ul']//li//div[@class='details']/div/a");
				$rt_url = $row->item(0)->getAttribute('href');
				$url_parts = explode('/', $rt_url);
				$id = (array_key_exists(2, $url_parts)) ? (string) $url_parts[2] : 0;

				return $id;
			}

			$rows = @$xpath->query("//h2[contains(., 'Movies')]/following-sibling::ul[@class='results_ul']//li//div[@class='details']/div");

			foreach ($rows as $index => $nodes)
			{
				$rt_url = $nodes->childNodes->item(1)->getAttribute('href');
				$url_parts = explode('/', $rt_url);
				$id = (array_key_exists(2, $url_parts)) ? (string) $url_parts[2] : 0;

				$result[] = array(
					'id'    => $id,
					'img'   => $nodes->parentNode->parentNode->childNodes->item(3)->childNodes->item(1)->childNodes->item(1)->getAttribute('src'),
					'title' => preg_replace('/\s\s+/', '', trim($nodes->nodeValue)),
					'link'  => JRoute::_('index.php?option=com_kinoarhiv&task=api.parser&action[rottentomatoes]=movie.info&id[rottentomatoes]='
						. $id . '&format=json', false
					)
				);
			}

			if (@$xpath->query("//h2[contains(., 'Movies')]/following-sibling::ul/following-sibling::div[@class='clickForMore']")->length > 0)
			{
				$result[] = array(
					'more' => JRoute::_('index.php?option=com_kinoarhiv&task=api.parser&action[rottentomatoes]=movie.search&title[rottentomatoes]='
						. $title . '&showall[rottentomatoes]=1&format=json', false
					)
				);
			}
		}

		return $result;*/
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

		if ($cache->get($cache_id, 'rottentomatoes') === false)
		{
			$response = parent::getRemoteData(
				str_replace('[id]', $id, $this->urls[$page]),
				$this->headers
			);

			$output = $response;
			$cache->store($output, $cache_id, 'rottentomatoes');
		}
		else
		{
			$output = $cache->get($cache_id, 'rottentomatoes');
		}

		return $output;
	}
}
