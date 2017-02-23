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
		$this->params = $config;
		$this->headers = ArrayHelper::fromObject($this->params->get('imdb.headers'));

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
	 * Method to get movie data
	 *
	 * @param   string  $id    Movie ID from Imdb
	 * @param   string  $data  List of fields to return
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getMovieInfo($id, $data = '')
	{
		// Validate ID
		if (!preg_match('@^tt(\d+)@', $id))
		{
			return array('error' => 'Wrong ID format');
		}

		try
		{
			$this->headers['Referer'] = $this->headers['Referer'] . '/title/' . $id . '/?ref_=nv_sr_1';
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

		// Filter results by keys from $_GET['data']['imdb']
		if (!empty($data))
		{
			$filter = JFilterInput::getInstance();
			$cols = explode(',', $data);

			foreach ($cols as $col)
			{
				$col = str_ireplace('_', '', StringHelper::strtolower($filter->clean($col, 'word')));

				if ($col == 'castcrew')
				{
					$result['castcrew'] = $this->getCastCrew($this->getPageById($id, 'cast'));
				}
				elseif ($col == 'releases')
				{
					$result['releases'] = $this->getReleases($this->getPageById($id, 'releases'));
				}
				elseif ($col == 'posters')
				{
					$result['posters'] = $this->getMoviePosters($id, $xpath);
				}
				else
				{
					$method = 'get' . ucfirst($col);

					if (method_exists($this, $method))
					{
						$result[$col] = $this->$method($xpath);
					}
				}
			}
		}
		else
		{
			$result = array(
				'title'         => $this->getTitle($xpath),
				'year'          => $this->getYear($xpath),
				'genres'        => $this->getGenres($xpath),
				'rating'        => $this->getRating($xpath),
				'contentrating' => $this->getContentRating($xpath),
				'duration'      => $this->getDuration($xpath),
				'budget'        => $this->getBudget($xpath),
				'countries'     => $this->getCountries($xpath),
				'slogan'        => $this->getSlogan($xpath),
				'plot'          => $this->getPlot($xpath),
				'castcrew'      => $this->getCastCrew($this->getPageById($id, 'cast')),
				'releases'      => $this->getReleases($this->getPageById($id, 'releases')),
				'posters'       => $this->getMoviePosters($id, $xpath)
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
		$cache->setLifeTime($this->params->get('imdb.cache_lifetime'));
		$cache_id = $title . '-' . 'search_result';

		if ($cache->get($cache_id, 'parser_imdb') === false)
		{
			$this->headers['Referer'] = 'http://www.imdb.com/';
			$response = parent::getRemoteData(
				'http://www.imdb.com/find?q=' . parent::encodeUrl($title) . '&s=tt&exact=true&ref_=fn_tt_ex',
				$this->headers,
				30
			);

			if ($response->code == 200)
			{
				$output = $response->body;
				$cache->store($output, $cache_id, 'parser_imdb');
			}
			else
			{
				throw new Exception('HTTP error: ' . $response->code, $response->code);
			}
		}
		else
		{
			$output = $cache->get($cache_id, 'parser_imdb');
		}

		$dom = new DOMDocument('1.0', 'utf-8');
		@$dom->loadHTML($output);
		$xpath = new DOMXPath($dom);

		// Get first result and search ID
		if ($return_id)
		{
			$row = @$xpath->query('//table[@class="findList"]//tr[1]/td[1]/a');
			$imdb_url = $row->item(0)->getAttribute('href');
			preg_match('@title/(\w+)/?@is', $imdb_url, $matches);

			// ID should be a string because of leading zeros
			$id = ($matches && array_key_exists(1, $matches)) ? (string) $matches[1] : 0;

			return $id;
		}

		$table_rows = @$xpath->query('//table[@class="findList"]//tr');

		foreach ($table_rows as $nodes)
		{
			$imdb_url = $nodes->childNodes->item(0)->childNodes->item(1)->getAttribute('href');
			preg_match('@title/(\w+)/?@is', $imdb_url, $matches);

			// ID should be a string because of leading zeros
			$id = ($matches && array_key_exists(1, $matches)) ? (string) $matches[1] : 0;

			$result[] = array(
				'id'    => $id,
				'img'   => $nodes->childNodes->item(0)->childNodes->item(1)->firstChild->getAttribute('src'),
				'title' => trim($nodes->childNodes->item(2)->nodeValue),
				'link'  => JRoute::_('index.php?option=com_kinoarhiv&task=api.parser&action[imdb]=getMovie&id[imdb]=' . $id . '&format=json', false)
			);
		}

		return $result;
	}

	/**
	 * Method to get person data
	 *
	 * @param   string  $id    Person ID from Imdb
	 * @param   string  $data  List of fields to return
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function getPersonInfo($id, $data = '')
	{
		// Validate ID
		if (!preg_match('@^nm(\d+)@', $id))
		{
			return array('error' => 'Wrong ID format');
		}

		try
		{
			$this->headers['Referer'] = '';
			$html = $this->getPageById($id, 'name');
		}
		catch (Exception $e)
		{
			return array('error' => $e->getMessage());
		}

		$result = array();
		$dom = new DOMDocument('1.0', 'utf-8');
		@$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);

		// Filter results by keys from $_GET['data']['imdb']
		if (!empty($data))
		{
			$filter = JFilterInput::getInstance();
			$cols = explode(',', $data);

			foreach ($cols as $col)
			{
				$col = str_ireplace('_', '', StringHelper::strtolower($filter->clean($col, 'word')));

				if ($col == 'id')
				{
					$result['id'] = $id;
				}
				elseif ($col == 'biography')
				{
					$result['castcrew'] = $this->getBiography($id, $xpath);
				}
				elseif ($col == 'awards')
				{
					$result['releases'] = $this->getNameAwards($this->getPageById($id, 'awards'));
				}
				elseif ($col == 'posters')
				{
					$result['posters'] = $this->getNamePhoto($id, $xpath);
				}
				else
				{
					$method = 'get' . ucfirst($col);

					if (method_exists($this, $method))
					{
						$result[$col] = $this->$method($xpath);
					}
				}
			}
		}
		else
		{
			$result = array(
				'id'           => $id,
				'name'         => $this->getName($xpath),
				'career'       => $this->getCareer($xpath),
				'birthday'     => $this->getBirthday($xpath),
				'birthplace'   => $this->getBirthplace($xpath),
				'birthcountry' => $this->getBirthcountry($xpath),
				'filmography'  => $this->getFilmography($xpath),
				'biography'    => $this->getBiography($id, $xpath),
				'awards'       => $this->getNameAwards($this->getPageById($id, 'awards')),
				'posters'      => $this->getNamePhoto($id, $xpath)
			);
		}

		return $result;
	}

	/**
	 * Get movie page by ID and store in cache.
	 *
	 * @param   string  $id       Movie ID from Imdb
	 * @param   string  $page     Page URL
	 * @param   array   $options  Custom options
	 *
	 * @return  string
	 *
	 * @throws  Exception
	 * @since   3.1
	 */
	private function getPageById($id, $page = 'main', $options = array())
	{
		$cache = JCache::getInstance();
		$cache->setCaching(true);
		$cache->setLifeTime($this->params->get('imdb.cache_lifetime'));
		$cache_id = $id . '.' . $page;

		if ($cache->get($cache_id, 'parser_imdb') === false)
		{
			// ID of the image from mediabrowser
			$itemid = isset($options['itemid']) ? $options['itemid'] : '';

			$response = parent::getRemoteData(
				str_replace(array('[id]', '[itemid]'), array($id, $itemid), $this->urls[$page]),
				$this->headers,
				30
			);

			if ($response->code == 200)
			{
				$output = $response->body;
				$cache->store($output, $cache_id, 'parser_imdb');
			}
			else
			{
				throw new Exception('HTTP error: ' . $response->code, $response->code);
			}
		}
		else
		{
			$output = $cache->get($cache_id, 'parser_imdb');
		}

		return $output;
	}

	/**
	 * Get rating.
	 *
	 * @param   object  $xpath  DOMXPath object instance.
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	protected function getRating($xpath)
	{
		$rating = array('votesum' => 0, 'votes' => 0);
		$rating['votesum'] = @$xpath->query($this->params->get('imdb.patterns.ratings.rating'))->item(0)->nodeValue;
		$rating['votes'] = @$xpath->query($this->params->get('imdb.patterns.ratings.score'))->item(0)->nodeValue;

		if (!is_numeric($rating['votesum']) && !is_numeric($rating['votes']))
		{
			$rating['votesum'] = 0;
			$rating['votes'] = 0;
		}

		// Strip all unexpected digit separators
		$rating['votes'] = str_replace(array(',', '.', ' '), '', $rating['votes']);

		return $rating;
	}

	/**
	 * Get movie titles
	 *
	 * @param   object  $xpath  DOMXPath object instance.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	protected function getTitle($xpath)
	{
		$titles_orig = @$xpath->query($this->params->get('imdb.patterns.titles.original'));
		$titles_alt = @$xpath->query($this->params->get('imdb.patterns.titles.alternative'));

		/* Imdb have(can have) 2 rows with title. First title based on language detected by user IP country, and placed in <div>
		 * Second title is original movie title and can be placed in <h3> or <div>.
		 * Check if original title(in <div>) exists, if not when the movie have not yet a localized title.
		 *
		 * Trim() \xC2\xA0 will remove &nbsp;
		 */
		if ($titles_orig->length > 0)
		{
			$titles['original'] = trim($titles_orig->item(0)->nodeValue, " \t\n\r\0\x0B\xC2\xA0");
			$titles['alternative'] = trim($titles_alt->item(0)->nodeValue, " \t\n\r\0\x0B\xC2\xA0");
		}
		else
		{
			$titles['original'] = trim($titles_alt->item(0)->nodeValue, " \t\n\r\0\x0B\xC2\xA0");
			$titles['alternative'] = '';
		}

		return $titles;
	}

	/**
	 * Get movie year
	 *
	 * @param   object  $xpath  DOMXPath object instance.
	 *
	 * @return  integer
	 *
	 * @since   3.1
	 */
	protected function getYear($xpath)
	{
		$year = '';
		$node = @$xpath->query($this->params->get('imdb.patterns.year.0'))->item(0)->nodeValue;

		if (!empty($node))
		{
			$year = (int) $node;
		}
		else
		{
			$node = @$xpath->query($this->params->get('imdb.patterns.year.1'))->item(0)->nodeValue;

			if (!empty($node))
			{
				preg_match('@\((.+)\)@s', $node, $matches);
				$year = trim($matches[1]);
			}
		}

		return $year;
	}

	/**
	 * Get movie content rating(MPAA rating score and Metacritic).
	 *
	 * @param   object  $xpath  DOMXPath object instance.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	protected function getContentRating($xpath)
	{
		$rating_mpaa = @$xpath->query($this->params->get('imdb.patterns.mpaa'))->item(0)->nodeValue;
		$rating_mc = @$xpath->query($this->params->get('imdb.patterns.metacritic'))->item(0)->nodeValue;

		return array('mpaa' => trim($rating_mpaa), 'metacritic' => (int) $rating_mc);
	}

	/**
	 * Get movie genres.
	 *
	 * @param   object  $xpath  DOMXPath object instance.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	protected function getGenres($xpath)
	{
		$genres = array();

		// We need two query() because Imdb provide genres list in two places on the web-page.
		$patterns = $this->params->get('imdb.patterns.genres');
		$nodes_1 = @$xpath->query($patterns[0]);
		$nodes_2 = @$xpath->query($patterns[1]);

		foreach ($nodes_1 as $node)
		{
			$genres[] = trim($node->nodeValue);
		}

		foreach ($nodes_2 as $node)
		{
			$genres[] = trim($node->nodeValue);
		}

		return array_values(ArrayHelper::arrayUnique($genres));
	}

	/**
	 * Get movie duration.
	 *
	 * @param   object  $xpath  DOMXPath object instance.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	protected function getDuration($xpath)
	{
		$duration['original'] = trim(@$xpath->query($this->params->get('imdb.patterns.duration'))->item(0)->nodeValue);
		$duration['datetime'] = @$xpath->query($this->params->get('imdb.patterns.duration') . '/@datetime')->item(0)->nodeValue;
		$duration['time'] = preg_replace_callback(
			'#PT(\d+)([W|w|D|d|H|h|M|m|S|s])#',
			function($matches)
			{
				if (strtolower($matches[2]) == 'w')
				{
					$_time = $matches[1] * 604800;
				}
				elseif (strtolower($matches[2]) == 'd')
				{
					$_time = $matches[1] * 86400;
				}
				elseif (strtolower($matches[2]) == 'h')
				{
					$_time = $matches[1] * 3600;
				}
				elseif (strtolower($matches[2]) == 'm')
				{
					$_time = $matches[1] * 60;
				}
				else
				{
					$_time = $matches[1];
				}

				// TODO Crappy gmdate() isn't working with timestamp more than 86399 and will return 00:00:00. Need to fix.
				return gmdate('H:i:s', $_time);
			},
			$duration['datetime']
		);

		return $duration;
	}

	/**
	 * Get movie budget
	 *
	 * @param   object  $xpath  DOMXPath object instance.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	protected function getBudget($xpath)
	{
		$budget = array('orig' => '', 'alt' => '');
		$budget['orig'] = trim(@$xpath->query($this->params->get('imdb.patterns.budget'))->item(0)->nodeValue);
		$budget['alt'] = str_replace(',', ' ', $budget['orig']);

		return $budget;
	}

	/**
	 * Get movie countries
	 *
	 * @param   object  $xpath  DOMXPath object instance.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	protected function getCountries($xpath)
	{
		$countries = array();
		$nodes = @$xpath->query($this->params->get('imdb.patterns.countries'));

		foreach ($nodes as $node)
		{
			$countries[] = $node->nodeValue;
		}

		return $countries;
	}

	/**
	 * Get movie slogan(tagline)
	 *
	 * @param   object  $xpath  DOMXPath object instance.
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	protected function getSlogan($xpath)
	{
		return trim(@$xpath->query($this->params->get('imdb.patterns.slogan'))->item(0)->nodeValue);
	}

	/**
	 * Get movie plot
	 *
	 * @param   object  $xpath  DOMXPath object instance.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	protected function getPlot($xpath)
	{
		$plot = array('short' => '', 'full' => '');
		$patterns = $this->params->get('imdb.patterns.plot');
		$plot['short'] = trim(@$xpath->query($patterns[0])->item(0)->nodeValue);
		$plot['full'] = trim(@$xpath->query($patterns[1])->item(0)->nodeValue);

		return $plot;
	}

	/**
	 * Get movie cast and crew
	 *
	 * @param   string  $html  HTML page.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	protected function getCastCrew($html)
	{
		$cast = array(
			'cast' => array(),
			'crew' => array(
				'directors'  => array(),
				'scenarists' => array(),
				'producers'  => array(),
				'music'      => array()
			)
		);
		$dom = new DOMDocument('1.0', 'utf-8');
		@$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);
		$cast_nodes = @$xpath->query($this->params->get('imdb.patterns.cast.cast'));

		foreach ($cast_nodes as $key => $node)
		{
			$person = $node->childNodes->item(2)->nodeValue;
			$role = is_object($node->childNodes->item(6)) ? $node->childNodes->item(6)->nodeValue : '';
			$cast['cast'][] = array(
				'person' => preg_replace('/\s\s+/', '', trim($person)),
				'role'   => trim(preg_replace('/\s\s+/', ' ', $role))
			);
		}

		$directors_nodes = @$xpath->query($this->params->get('imdb.patterns.cast.directors'));

		foreach ($directors_nodes as $node)
		{
			$value = preg_replace('/\s\s+/', '', trim($node->nodeValue));

			if (!empty($value) && StringHelper::strlen($value) > 1)
			{
				$cast['crew']['directors'][] = $value;
			}
		}

		$scenarists_nodes = @$xpath->query($this->params->get('imdb.patterns.cast.scenarists'));

		foreach ($scenarists_nodes as $node)
		{
			$value = preg_replace('/\s\s+/', '', trim($node->nodeValue));

			if (!empty($value) && StringHelper::strlen($value) > 1)
			{
				$cast['crew']['scenarists'][] = $value;
			}
		}

		$producers_nodes = @$xpath->query($this->params->get('imdb.patterns.cast.producers'));

		foreach ($producers_nodes as $node)
		{
			$person = $node->childNodes->item(0)->nodeValue;
			$role = is_object($node->childNodes->item(4)) ? $node->childNodes->item(4)->nodeValue : '';
			$cast['crew']['producers'][] = array(
				'person' => preg_replace('/\s\s+/', '', trim($person)),
				'role'   => trim(preg_replace('/\s\s+/', ' ', $role))
			);
		}

		$music_nodes = @$xpath->query($this->params->get('imdb.patterns.cast.music'));

		foreach ($music_nodes as $node)
		{
			$value = preg_replace('/\s\s+/', '', trim($node->nodeValue));

			if (!empty($value) && StringHelper::strlen($value) > 1)
			{
				$cast['crew']['music'][] = $value;
			}
		}

		$crew_nodes = @$xpath->query($this->params->get('imdb.patterns.cast.crew'));

		foreach ($crew_nodes as $node)
		{
			$h4_node = $node->parentNode->parentNode->previousSibling->previousSibling->nodeValue;
			$h4_text = html_entity_decode(str_replace(array('by', 'By'), '', $h4_node), ENT_QUOTES, 'UTF-8');
			$h4_text = trim($h4_text, " \t\n\r\0\x0B\xC2\xA0");
			$h4_text_key = str_replace(' ', '_', StringHelper::strtolower($h4_text));
			$person = $node->childNodes->item(0)->nodeValue;
			$role = is_object($node->childNodes->item(4)) ? $node->childNodes->item(4)->nodeValue : '';
			$cast['crew']['other'][$h4_text_key][] = array(
				'person' => preg_replace('/\s\s+/', '', trim($person)),
				'role'   => trim(preg_replace('/\s\s+/', ' ', $role))
			);
		}

		return $cast;
	}

	/**
	 * Get movie release info
	 *
	 * @param   string  $html  HTML page.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	protected function getReleases($html)
	{
		$release = array();
		$dom = new DOMDocument('1.0', 'utf-8');
		@$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);
		$release_nodes = @$xpath->query($this->params->get('imdb.patterns.release'));

		foreach ($release_nodes as $node)
		{
			$release[] = array(
				'country' => $node->childNodes->item(0)->nodeValue,
				'date'    => $node->childNodes->item(2)->nodeValue,
				'info'    => trim($node->childNodes->item(4)->nodeValue)
			);
		}

		return $release;
	}

	/**
	 * Get movie posters
	 *
	 * @param   string  $id     Movie ID from Imdb
	 * @param   object  $xpath  DOMXPath object instance.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	protected function getMoviePosters($id, $xpath)
	{
		$nodes = @$xpath->query($this->params->get('imdb.patterns.posters.0'));
		$mv_url = $nodes->item(0)->childNodes->item(1)->getAttribute('href');
		$cover_url = $nodes->item(0)->childNodes->item(1)->childNodes->item(1)->getAttribute('src');
		$poster_url = '';
		$config = JFactory::getConfig();
		$tmp_img_path = JPath::clean($config->get('tmp_path') . '/parser/dl_images/');
		$tmp_img_path_www = JUri::base() . 'tmp/parser/dl_images/';
		$filename1 = $id . '_' . basename($cover_url);
		$filename2 = '';

		// Get the media viewer ID from $mv_url for poster.
		preg_match('@mediaviewer/(\w+)?@is', $mv_url, $ids);

		if (array_key_exists(1, $ids))
		{
			$html = $this->getPageById($id, 'posters', array('itemid' => $ids[1]));
			$dom = new DOMDocument('1.0', 'utf-8');
			@$dom->loadHTML($html);
			$mb_xpath = new DOMXPath($dom);
			$json_raw = @$mb_xpath->query($this->params->get('imdb.patterns.posters.1'))->item(0)->nodeValue;
			$object = json_decode($json_raw);

			// Check if json is valid for UTF8 symbols.
			if (json_last_error() == JSON_ERROR_UTF8)
			{
				$json_raw = utf8_decode($json_raw);
				$object = json_decode($json_raw);
			}

			foreach ($object->mediaViewerModel->allImages as $item)
			{
				if ($item->id == $ids[1])
				{
					$poster_url = $item->src;
					$filename2 = $id . '_' . basename($poster_url);
					break;
				}
			}

			// Create '$tmp_img_path' dir
			jimport('joomla.filesystem.folder');
			JFolder::create($tmp_img_path);

			if (!is_file($tmp_img_path . $filename1))
			{
				$response1 = parent::getRemoteData(
					$cover_url,
					array(
						'Referer' => 'http://www.imdb.com/title/' . $id . '/mediaviewer/' . $ids[1]
					)
				);

				if ($response1->code == 200)
				{
					file_put_contents($tmp_img_path . $filename1, $response1->body);
				}
			}

			if (!is_file($tmp_img_path . $filename2))
			{
				$response2 = parent::getRemoteData(
					$poster_url,
					array(
						'Referer' => 'http://www.imdb.com/title/' . $id . '/mediaviewer/' . $ids[1]
					)
				);

				if ($response2->code == 200)
				{
					file_put_contents($tmp_img_path . $filename2, $response2->body);
				}
			}
		}

		return array(
			'imdb'  => array(
				'cover'  => $cover_url,
				'poster' => $poster_url
			),
			'local' => array(
				'filesystem' => array(
					'cover'  => JPath::clean($tmp_img_path . $filename1),
					'poster' => JPath::clean($tmp_img_path . $filename2)
				),
				'webserver'  => array(
					'cover'  => $tmp_img_path_www . $filename1,
					'poster' => $tmp_img_path_www . $filename2
				)
			)
		);
	}

	/**
	 * Get person name
	 *
	 * @param   object  $xpath  DOMXPath object instance.
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	protected function getName($xpath)
	{
		$name = @$xpath->query($this->params->get('imdb.patterns.name.name'))->item(0)->nodeValue;

		// Convert entities to chars
		$name = html_entity_decode($name, ENT_QUOTES, 'UTF-8');

		// Trim() \xC2\xA0 will remove &nbsp;
		return trim($name, " \t\n\r\0\x0B\xC2\xA0");
	}

	/**
	 * Get person job/career
	 *
	 * @param   object  $xpath  DOMXPath object instance.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	protected function getCareer($xpath)
	{
		$nodes = @$xpath->query($this->params->get('imdb.patterns.name.career'));
		$career = array();

		if ($nodes->length > 0)
		{
			foreach ($nodes as $item)
			{
				$career[] = trim($item->nodeValue);
			}
		}

		return $career;
	}

	/**
	 * Get person birthday
	 *
	 * @param   object  $xpath  DOMXPath object instance.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	protected function getBirthday($xpath)
	{
		$nodes = @$xpath->query($this->params->get('imdb.patterns.name.birthday'));
		$birthday = array();

		if ($nodes->length > 0)
		{
			$birthday['date'] = preg_replace('/\s\s+/', ' ', trim($nodes->item(0)->nodeValue));
			$birthday['datetime'] = date('Y-m-d', strtotime($nodes->item(0)->getAttribute('datetime')));
		}

		return $birthday;
	}

	/**
	 * Get person birthplace
	 *
	 * @param   object  $xpath  DOMXPath object instance.
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	protected function getBirthplace($xpath)
	{
		$nodes = @$xpath->query($this->params->get('imdb.patterns.name.birthplace'));
		$birthplace = '';

		if ($nodes->length > 0)
		{
			$birthplace = preg_replace('/\s\s+/', ' ', trim($nodes->item(1)->nodeValue));
		}

		return $birthplace;
	}

	/**
	 * Get person country
	 *
	 * @param   object  $xpath  DOMXPath object instance.
	 *
	 * @return  string
	 *
	 * @since   3.1
	 */
	protected function getBirthcountry($xpath)
	{
		$nodes = @$xpath->query($this->params->get('imdb.patterns.name.birthplace'));
		$birthplace = '';

		if ($nodes->length > 0)
		{
			$birthplace = preg_replace('/\s\s+/', ' ', trim($nodes->item(1)->nodeValue));
			$comma = strrpos($birthplace, ',') + 2;
			$birthplace = StringHelper::substr($birthplace, $comma);
		}

		return $birthplace;
	}

	/**
	 * Get person filmography
	 *
	 * @param   object  $xpath  DOMXPath object instance.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	protected function getFilmography($xpath)
	{
		$filter = JFilterInput::getInstance();
		$nodes = @$xpath->query($this->params->get('imdb.patterns.name.filmography'));
		$films = array();
		$keys = array();

		if ($nodes->length > 0)
		{
			foreach ($nodes as $key => $node)
			{
				if ($node->getAttribute('class') == 'head')
				{
					$keys[$key] = $filter->clean($node->childNodes->item(5)->nodeValue, 'string');
				}

				if ($node->getAttribute('class') == 'filmo-category-section')
				{
					foreach ($node->childNodes as $item_node)
					{
						if ($item_node instanceof DOMElement)
						{
							$role = '';

							// Role must be followed after <br> tag.
							// Check if previous tag is DOMElement and is <br>
							if ($item_node->childNodes->item(5) instanceof DOMElement && $item_node->childNodes->item(5)->tagName == 'br')
							{
								$role_node = $item_node->childNodes->item(6);

								// Role node can return NULL value if not found in DOM
								if (!empty($role_node))
								{
									$role = str_replace("\n", ' ', trim($item_node->childNodes->item(6)->nodeValue));
									$role = $filter->clean($role, 'string');
								}
							}

							// Array key should be less by one because header div start from 0 but content div start from 1.
							$array_key           = $keys[$key - 1];
							$films[$array_key][] = array(
								'title' => trim($item_node->childNodes->item(3)->nodeValue),
								'year'  => trim($item_node->childNodes->item(1)->nodeValue, " \t\n\r\0\x0B\xC2\xA0"),
								'role'  => $role
							);
						}
					}
				}
			}
		}

		return $films;
	}

	/**
	 * Get person bio
	 *
	 * @param   string  $id     Movie ID from Imdb
	 * @param   object  $xpath  DOMXPath object instance.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	protected function getBiography($id, $xpath)
	{
		$filter = JFilterInput::getInstance();
		$nodes = @$xpath->query($this->params->get('imdb.patterns.name.bio.short'));
		$bio = array('short' => '', 'full' => '');

		if ($nodes->length > 0)
		{
			$bio_short = $filter->clean($nodes->item(0)->nodeValue, 'string');

			// Remove 'See full bio' text at the end of the text block
			preg_match('@(.*)See full bio@is', $bio_short, $matches);
			$bio['short'] = preg_replace('/\s\s+/', ' ', trim($matches[1]));
		}

		$this->headers['Referer'] = $this->headers['Referer'] . '/name/' . $id . '/';
		$html = $this->getPageById($id, 'name_bio');
		$dom = new DOMDocument('1.0', 'utf-8');
		@$dom->loadHTML($html);
		$_xpath = new DOMXPath($dom);
		$desc = '';
		$mini_bio = @$_xpath->query($this->params->get('imdb.patterns.name.bio.full.bio'));

		if ($mini_bio->length > 0)
		{
			$desc .= '<h4>' . $filter->clean($mini_bio->item(0)->parentNode->previousSibling->previousSibling->nodeValue, 'string') . '</h4>';
			$desc .= $filter->clean(trim($mini_bio->item(0)->nodeValue), 'string');
		}

		$trivia = $_xpath->query($this->params->get('imdb.patterns.name.bio.full.trivia'));

		if ($trivia->length > 0)
		{
			foreach ($trivia as $nodes)
			{
				if ($nodes->tagName == 'h4')
				{
					$desc .= '<h4>' . $filter->clean(trim($nodes->nodeValue), 'string') . '</h4>';
				}

				if ($nodes->tagName == 'div')
				{
					$desc .= $filter->clean(trim($nodes->nodeValue), 'string');
				}
			}

			$bio['full'] = $desc;
		}

		return $bio;
	}

	/**
	 * Get person awards
	 *
	 * @param   string  $html  HTML page.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	protected function getNameAwards($html)
	{
		$filter = JFilterInput::getInstance();
		$awards = array();
		$dom = new DOMDocument('1.0', 'utf-8');
		@$dom->loadHTML($html);
		$xpath = new DOMXPath($dom);
		$nodes = @$xpath->query($this->params->get('imdb.patterns.name.awards'));

		if ($nodes->length > 0)
		{
			// Loop through <table> nodes
			foreach ($nodes as $key => $node)
			{
				// Check if <h3> found before table
				if ($node->previousSibling->previousSibling->tagName == 'h3')
				{
					$awards[$key]['title'] = $filter->clean(trim($node->previousSibling->previousSibling->nodeValue), 'string');
				}

				// Check if tables have a rows
				if ($node->childNodes->length > 0)
				{
					// Loop through <tr> nodes
					foreach ($node->childNodes as $index => $tr_node)
					{
						$awards[$key]['awards'][$index]['year'] = trim($tr_node->childNodes->item(0)->nodeValue);
						$awards[$key]['awards'][$index]['nominated'] = trim($tr_node->childNodes->item(2)->childNodes->item(3)->nodeValue);
						$desc = trim($tr_node->childNodes->item(4)->childNodes->item(0)->nodeValue);
						$desc .= "\n" . trim($tr_node->childNodes->item(4)->childNodes->item(2)->nodeValue);
						$desc .= ' ' . trim($tr_node->childNodes->item(4)->childNodes->item(4)->nodeValue);
						$awards[$key]['awards'][$index]['desc'] = $desc;
					}
				}
			}
		}

		return $awards;
	}

	/**
	 * Get person photo
	 *
	 * @param   string  $id     Pesron ID from Imdb
	 * @param   object  $xpath  DOMXPath object instance.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	protected function getNamePhoto($id, $xpath)
	{
		$nodes = @$xpath->query($this->params->get('imdb.patterns.name.posters.0'));
		$mv_url = $nodes->item(0)->getAttribute('href');
		$cover_url = $nodes->item(0)->childNodes->item(1)->getAttribute('src');
		$poster_url = '';
		$config = JFactory::getConfig();
		$tmp_img_path = JPath::clean($config->get('tmp_path') . '/parser/dl_images/');
		$tmp_img_path_www = JUri::base() . 'tmp/parser/dl_images/';
		$filename1 = $id . '_' . basename($cover_url);
		$filename2 = '';

		// Get the media viewer ID from $mv_url for poster.
		preg_match('@mediaviewer/(\w+)?@is', $mv_url, $ids);

		if (array_key_exists(1, $ids))
		{
			$html = $this->getPageById($id, 'name_photo', array('itemid' => $ids[1]));
			$dom = new DOMDocument('1.0', 'utf-8');
			@$dom->loadHTML($html);
			$mb_xpath = new DOMXPath($dom);
			$json_raw = @$mb_xpath->query($this->params->get('imdb.patterns.posters.1'))->item(0)->nodeValue;
			$object = json_decode($json_raw);

			// Check if json is valid for UTF8 symbols.
			if (json_last_error() == JSON_ERROR_UTF8)
			{
				$json_raw = utf8_decode($json_raw);
				$object = json_decode($json_raw);
			}

			foreach ($object->mediaViewerModel->allImages as $item)
			{
				if ($item->id == $ids[1])
				{
					$poster_url = $item->src;
					$filename2 = $id . '_' . basename($poster_url);
					break;
				}
			}

			// Create '$tmp_img_path' dir
			jimport('joomla.filesystem.folder');
			JFolder::create($tmp_img_path);

			// If file not found in 'tmp' folder when request it from remote
			if (!is_file($tmp_img_path . $filename1))
			{
				$response1 = parent::getRemoteData(
					$cover_url,
					array(
						'Referer' => 'http://www.imdb.com/name/' . $id . '/mediaviewer/' . $ids[1]
					)
				);

				if ($response1->code == 200)
				{
					file_put_contents($tmp_img_path . $filename1, $response1->body);
				}
			}

			// If file not found in 'tmp' folder when request it from remote.
			if (!is_file($tmp_img_path . $filename2))
			{
				$response2 = parent::getRemoteData(
					$poster_url,
					array(
						'Referer' => 'http://www.imdb.com/name/' . $id . '/mediaviewer/' . $ids[1]
					)
				);

				if ($response2->code == 200)
				{
					file_put_contents($tmp_img_path . $filename2, $response2->body);
				}
			}
		}

		return array(
			'imdb'  => array(
				'cover'  => $cover_url,
				'poster' => $poster_url
			),
			'local' => array(
				'filesystem' => array(
					'cover'  => JPath::clean($tmp_img_path . $filename1),
					'poster' => JPath::clean($tmp_img_path . $filename2)
				),
				'webserver'  => array(
					'cover'  => $tmp_img_path_www . $filename1,
					'poster' => $tmp_img_path_www . $filename2
				)
			)
		);
	}
}
