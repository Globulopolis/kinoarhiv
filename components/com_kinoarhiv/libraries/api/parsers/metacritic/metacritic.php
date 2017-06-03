<?php
/**
 * @package     Kinoarhiv.Site.Api.Parser
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Parser class for Metacritic.com
 *
 * @since  3.1
 */
class KAParserMetacritic extends KAApi
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
			'main' => 'http://www.metacritic.com/movie/[id]/critic-reviews'
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

		$filter = JFilterInput::getInstance();
		$dom = new DOMDocument('1.0', 'utf-8');
		@$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);
		$metascore   = (int) @$xpath->query($this->params->get('patterns.ratings.score'))->item(0)->nodeValue;
		$metacritics = @$xpath->query($this->params->get('patterns.ratings.critics'));
		$critics = array();

		foreach ($metacritics as $i => $critic)
		{
			if ($critic->childNodes->item(1)->nodeName == 'div')
			{
				$critics[$i]['score'] = (int) $critic->childNodes->item(1)->nodeValue;
				$review_row = $critic->childNodes->item(3);
				$review_title = $review_row->childNodes->item(1)->childNodes;
				$critics[$i]['title'] = array(
					'source' => $filter->clean(trim($review_title->item(0)->nodeValue)),
					'author' => $filter->clean(trim($review_title->item(1)->nodeValue)),
					'date'   => preg_replace('/\s+/', ' ', $review_title->item(2)->nodeValue)
				);

				$review_content = $review_row->childNodes->item(3)->childNodes->item(1);
				$critics[$i]['summary'] = array(
					'summary' => preg_replace('/\s+/', ' ', $filter->clean(trim($review_content->nodeValue))),
					'link'    => $review_content->getAttribute('href')
				);
			}
		}

		$result = array(
			'rating' => array(
				'score'   => $metascore,
				'critics' => count($critics)
			),
			'critics' => array_values($critics)
		);

		return $result;
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
		if (empty($title))
		{
			throw new InvalidArgumentException('Wrong search value', 500);
		}

		$result = array();
		$cache = JCache::getInstance();
		$cache->setCaching(true);
		$cache->setLifeTime($this->params->get('metacritic.cache_lifetime'));
		$cache_id = $title . '-' . 'search_result';

		if ($cache->get($cache_id, 'parser_metacritic') === false)
		{
			$this->headers['Referer'] = 'http://www.metacritic.com/search/all/' . parent::encodeUrl($title) . '/results';

			$response = parent::getRemoteData(
				'http://www.metacritic.com/search/movie/' . parent::encodeUrl($title) . '/results',
				$this->headers,
				30
			);

			if ($response->code == 200)
			{
				$output = $response->body;
				$cache->store($output, $cache_id, 'parser_metacritic');
			}
			else
			{
				throw new Exception('HTTP error: ' . $response->code, $response->code);
			}
		}
		else
		{
			$output = $cache->get($cache_id, 'parser_metacritic');
		}

		$dom = new DOMDocument('1.0', 'utf-8');
		@$dom->loadHTML($output);
		$xpath = new DOMXPath($dom);

		// Get first result and search ID
		if ($return_id)
		{
			$row = @$xpath->query('//h3[@class="product_title basic_stat"]/a');

			if ($row->length > 0)
			{
				$mc_url = $row->item(0)->getAttribute('href');
				preg_match('@/movie/(.*)@is', $mc_url, $matches);

				// ID should be a string
				$id = ($matches && array_key_exists(1, $matches)) ? (string) $matches[1] : 0;

				return $id;
			}
			else
			{
				return false;
			}
		}

		$rows = @$xpath->query('//ul[@class="search_results module"]/li/div[@class="result_wrap"]/div');

		foreach ($rows as $nodes)
		{
			$mc_url = $nodes->childNodes->item(1)->childNodes->item(1)->childNodes->item(0)->getAttribute('href');
			preg_match('@/movie/(.*)@is', $mc_url, $matches);

			// ID should be a string
			$id = ($matches && array_key_exists(1, $matches)) ? (string) $matches[1] : 0;
			$year = ' (' . trim($nodes->childNodes->item(3)->childNodes->item(1)->childNodes->item(0)->childNodes->item(3)->nodeValue) . ')';

			$result[] = array(
				'id'    => $id,
				'title' => trim($nodes->childNodes->item(1)->childNodes->item(1)->childNodes->item(0)->nodeValue) . $year,
				'link'  => JRoute::_('index.php?option=com_kinoarhiv&task=api.parser&action[metacritic]=movie.info&id[metacritic]=' . $id . '&format=json', false)
			);
		}

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
		$cache_value = $cache->get($cache_id, 'metacritic');

		if ($cache_value === false || empty($cache_value))
		{
			$this->headers['Referer'] = $this->headers['Referer'] . $id;
			$response = parent::getRemoteData(
				str_replace('[id]', $id, $this->urls[$page]),
				$this->headers
			);

			$output = $response;
			$cache->store($output, $cache_id, 'metacritic');
		}
		else
		{
			$output = $cache->get($cache_id, 'metacritic');
		}

		return $output;
	}

	/**
	 * Get user reviews
	 *
	 * @param   string  $html  HTML page.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	protected function getUserReviews($html)
	{
		$filter = JFilterInput::getInstance();
		$reviews = array();
		$dom = new DOMDocument('1.0', 'utf-8');
		@$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);
		$critic_nodes = @$xpath->query($this->params->get('metacritic.patterns.reviews.users'));
		preg_match_all('@<div class="review_body">(.*?)<\/div>@is', $html, $matches);

		foreach ($critic_nodes as $key => $node)
		{
			$review_raw = trim($matches[1][$key]);

			// Search for Expand/Collapse and get only full text of review.
			if (preg_match('@<span class="blurb blurb_expanded">@is', $review_raw))
			{
				preg_match('@<span class="blurb blurb_expanded">(.*?)<\/span>@is', $review_raw, $review_expanded);
				$review_text = $filter->clean($review_expanded[1], 'string');
			}
			else
			{
				$review_text = $filter->clean($review_raw, 'string');
			}

			$reviews[] = array(
				'score'  => (int) $node->childNodes->item(1)->childNodes->item(3)->nodeValue,
				'author' => $node->childNodes->item(1)->childNodes->item(1)->childNodes->item(1)->childNodes->item(1)->nodeValue,
				'date'   => preg_replace('/\s\s+/', ' ', $node->childNodes->item(1)->childNodes->item(1)->childNodes->item(3)->nodeValue),
				'review' => $review_text
			);
		}

		return $reviews;
	}
}
