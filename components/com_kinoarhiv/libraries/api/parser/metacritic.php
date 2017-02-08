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
		$this->headers = ArrayHelper::fromObject($this->params->get('metacritic.headers'));

		// Set up an array with pages
		$this->urls = array(
			'main'           => 'http://www.metacritic.com/movie/[id]',
			'critic_reviews' => 'http://www.metacritic.com/movie/[id]/critic-reviews',
			'user_reviews'   => 'http://www.metacritic.com/movie/[id]/user-reviews?sort-by=most-helpful&num_items=100'
		);
	}

	/**
	 * Method to get movie data
	 *
	 * @param   string  $id    Movie ID from Metacritic
	 * @param   string  $data  List of fields to return
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getMovieInfo($id, $data = '')
	{
		try
		{
			$html = $this->getPageById($id);
		}
		catch (Exception $e)
		{
			return array('error' => $e->getMessage());
		}

		$result = array();
		$dom = new DOMDocument('1.0', 'utf-8');
		@$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);
		$metascore = (int) @$xpath->query($this->params->get('metacritic.patterns.ratings.m_score'))->item(0)->nodeValue;
		$metacritics = (int) @$xpath->query($this->params->get('metacritic.patterns.ratings.m_critics'))->item(0)->nodeValue;
		$userscore = (string) @$xpath->query($this->params->get('metacritic.patterns.ratings.u_score'))->item(0)->nodeValue;
		$usercritics = (int) @$xpath->query($this->params->get('metacritic.patterns.ratings.u_critics'))->item(0)->nodeValue;

		// Filter results by keys from $_GET['data']['metacritic']
		if (!empty($data))
		{
			$filter = JFilterInput::getInstance();
			$cols = explode(',', $data);

			foreach ($cols as $col)
			{
				$col = str_ireplace('_', '', StringHelper::strtolower($filter->clean($col, 'word')));

				if ($col == 'criticreviews')
				{
					$result['criticreviews'] = $this->getCriticReviews($this->getPageById($id, 'critic_reviews'));
				}
				elseif ($col == 'userreviews')
				{
					$result['userreviews'] = $this->getUserReviews($this->getPageById($id, 'user_reviews'));
				}
				else
				{
					if (isset($$col))
					{
						$result[$col] = $$col;
					}
				}
			}
		}
		else
		{
			$result = array(
				'metascore'     => $metascore,
				'metacritics'   => $metacritics,
				'userscore'     => $userscore,
				'usercritics'   => $usercritics,
				'criticreviews' => $this->getCriticReviews($this->getPageById($id, 'critic_reviews')),
				'userreviews'   => $this->getUserReviews($this->getPageById($id, 'user_reviews'))
			);
		}

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
			$this->headers['Referer'] = 'http://www.metacritic.com/search/all/' . parent::_urlencode($title) . '/results';

			$response = parent::getRemoteData(
				'http://www.metacritic.com/search/movie/' . parent::_urlencode($title) . '/results',
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
	 * Get movie page by ID and store in cache.
	 *
	 * @param   string  $id    Movie ID from Metacritic
	 * @param   string  $page  Page URL
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 * @since   3.1
	 */
	private function getPageById($id, $page = 'main')
	{
		$cache = JCache::getInstance();
		$cache->setCaching(true);
		$cache->setLifeTime($this->params->get('metacritic.cache_lifetime'));
		$cache_id = $id . '.' . $page;

		if ($cache->get($cache_id, 'parser_metacritic') === false)
		{
			$response = parent::getRemoteData(
				str_replace('[id]', $id, $this->urls[$page]),
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

		return $output;
	}

	/**
	 * Get critic reviews
	 *
	 * @param   string  $html  HTML page.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	protected function getCriticReviews($html)
	{
		$filter = JFilterInput::getInstance();
		$reviews = array();
		$dom = new DOMDocument('1.0', 'utf-8');
		@$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);
		$critic_nodes = @$xpath->query($this->params->get('metacritic.patterns.reviews.critics'));

		foreach ($critic_nodes as $node)
		{
			if (is_object($node->childNodes->item(1)->childNodes->item(1)->childNodes->item(2)))
			{
				$date = preg_replace('/\s\s+/', ' ', $node->childNodes->item(1)->childNodes->item(1)->childNodes->item(2)->nodeValue);
			}
			else
			{
				$date = 'N/A';
			}

			if (!is_null($node->childNodes->item(1)->childNodes->item(1)->childNodes->item(1)->childNodes->item(1)->attributes))
			{
				$url = $node->childNodes->item(1)->childNodes->item(1)->childNodes->item(1)->childNodes->item(1)->getAttribute('href');
			}
			else
			{
				$url = '';
			}

			$reviews[] = array(
				'score'  => (int) $node->childNodes->item(1)->childNodes->item(3)->nodeValue,
				'source' => $node->childNodes->item(1)->childNodes->item(1)->childNodes->item(0)->nodeValue,
				'author' => $node->childNodes->item(1)->childNodes->item(1)->childNodes->item(1)->childNodes->item(1)->nodeValue,
				'date'   => $date,
				'review' => $filter->clean(trim($node->childNodes->item(3)->nodeValue), 'string'),
				'url'    => $url
			);
		}

		return $reviews;
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
