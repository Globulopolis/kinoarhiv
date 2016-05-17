<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

/**
 * Class KinoarhivModelGlobal to get items or list of items from DB
 *
 * @since  3.0
 */
class KinoarhivModelGlobal extends JModelLegacy
{
	/**
	 * Get some data from DB
	 *
	 * @param   string  $element  Type of data from DB
	 *
	 * @return  object
	 *
	 * @since   3.0
	 */
	public function getAjaxData($element = '')
	{
		$app = JFactory::getApplication();
		$element = !empty($element) ? $element : $app->input->get('element', '', 'string');

		switch ($element)
		{
			case 'countries':
				return $this->getCountries();
				break;

			case 'genres':
				return $this->getGenres();
				break;

			case 'movies':
				return $this->getMovies();
				break;

			case 'names':
				return $this->getNames();
				break;

			case 'awards':
				return $this->getAwards();
				break;

			case 'tags':
				return $this->getTags();
				break;

			case 'vendors':
				return $this->getVendors();
				break;

			case 'careers':
				return $this->getCareers();
				break;

			case 'mediatypes':
				return $this->getMediatypes();
				break;

			case 'trailer_files':
				return $this->getTrailerFiles();
				break;

			default:
				return (object) array('id' => 0, 'title' => JText::_('ERROR') . '!');
				break;
		}
	}

	/**
	 * Get country or list of countries.
	 *
	 * @return  object
	 *
	 * @since   3.0
	 */
	protected function getCountries()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$all = $app->input->get('showAll', 0, 'int');
		$term = $app->input->get('term', '', 'string');
		$multiple = $app->input->get('multiple', 0, 'int');
		$id = ($multiple == 1) ? $app->input->get('id', '', 'string') : $app->input->get('id', 0, 'int');
		$lang = $app->input->get('lang', '', 'string');

		// Do not remove `code` field from the query. It's necessary for flagging row in select
		$query = $db->getQuery(true)
			->select($db->quoteName('id') . ',' . $db->quoteName('name') . ' AS title' . ',' . $db->quoteName('code'))
			->from($db->quoteName('#__ka_countries'));

		if (!empty($lang))
		{
			$query->where($db->quoteName('language') . " = '" . $db->escape($lang) . "'");
		}

		if (empty($all))
		{
			if (empty($id))
			{
				if (empty($term))
				{
					return array();
				}

				$query->where($db->quoteName('name') . " LIKE '%" . $db->escape($term) . "%'")
					->order($db->quoteName('name'));
			}
			else
			{
				if ($multiple == 1)
				{
					$query->where($db->quoteName('id') . ' IN (' . $id . ')');
				}
				else
				{
					$query->where($db->quoteName('id') . ' = ' . (int) $id);
				}
			}
		}

		try
		{
			$db->setQuery($query);

			if (empty($all) && !empty($id) && $multiple != 1)
			{
				$result = $db->loadObject();
			}
			else
			{
				$result = $db->loadObjectList();
			}

			return $result;
		}
		catch (Exception $e)
		{
			return (object) array('id' => 0, 'title' => JText::_('ERROR') . '!');
		}
	}

	/**
	 * Get genre or list of genres.
	 *
	 * @return  object
	 *
	 * @since   3.0
	 */
	protected function getGenres()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$all = $app->input->get('showAll', 0, 'int');
		$term = $app->input->get('term', '', 'string');
		$multiple = $app->input->get('multiple', 0, 'int');
		$id = ($multiple == 1) ? $app->input->get('id', '', 'string') : $app->input->get('id', 0, 'int');
		$lang = $app->input->get('lang', '', 'string');
		$table = ($app->input->get('type', 'movie', 'word') == 'movie') ? '#__ka_genres' : '#__ka_music_genres';

		$query = $db->getQuery(true)
			->select($db->quoteName('id') . ',' . $db->quoteName('name') . ' AS title')
			->from($db->quoteName($table));

		if (!empty($lang))
		{
			$query->where($db->quoteName('language') . " = '" . $db->escape($lang) . "'");
		}

		if (empty($all))
		{
			if (empty($id))
			{
				if (empty($term))
				{
					return array();
				}

				$query->where($db->quoteName('name') . " LIKE '%" . $db->escape($term) . "%'")
					->order($db->quoteName('name'));
			}
			else
			{
				if ($multiple == 1)
				{
					$query->where($db->quoteName('id') . ' IN (' . $id . ')');
				}
				else
				{
					$query->where($db->quoteName('id') . ' = ' . (int) $id);
				}
			}
		}

		try
		{
			$db->setQuery($query);

			if (empty($all) && !empty($id) && $multiple != 1)
			{
				$result = $db->loadObject();
			}
			else
			{
				$result = $db->loadObjectList();
			}

			return $result;
		}
		catch (Exception $e)
		{
			return (object) array('id' => 0, 'title' => JText::_('ERROR') . '!');
		}
	}

	/**
	 * Get movie or list of movies.
	 *
	 * @return  object
	 *
	 * @since   3.0
	 */
	protected function getMovies()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$all = $app->input->get('showAll', 0, 'int');
		$term = $app->input->get('term', '', 'string');
		$multiple = $app->input->get('multiple', 0, 'int');
		$id = ($multiple == 1) ? $app->input->get('id', '', 'string') : $app->input->get('id', 0, 'int');
		$lang = $app->input->get('lang', '', 'string');
		$ignore = $app->input->get('ignore', array(), 'array');

		$query = $db->getQuery(true)
			->select($db->quoteName('id') . ',' . $db->quoteName('title') . ',' . $db->quoteName('year'))
			->from($db->quoteName('#__ka_movies'));

		if (!empty($ignore))
		{
			$query->where($db->quoteName('id') . ' NOT IN (' . implode(',', $ignore) . ')');
		}

		if (!empty($lang))
		{
			$query->where($db->quoteName('language') . " = '" . $db->escape($lang) . "'");
		}

		if (empty($all))
		{
			if (empty($id))
			{
				if (empty($term))
				{
					return array();
				}

				$query->where($db->quoteName('title') . " LIKE '%" . $db->escape($term) . "%'")
					->order($db->quoteName('title'));
			}
			else
			{
				if ($multiple == 1)
				{
					$query->where($db->quoteName('id') . ' IN (' . $id . ')');
				}
				else
				{
					$query->where($db->quoteName('id') . ' = ' . (int) $id);
				}
			}
		}

		try
		{
			$db->setQuery($query);

			if (empty($all) && !empty($id) && $multiple != 1)
			{
				$result = $db->loadObject();
			}
			else
			{
				$result = $db->loadObjectList();
			}

			return $result;
		}
		catch (Exception $e)
		{
			return (object) array('id' => 0, 'title' => JText::_('ERROR') . '!');
		}
	}

	/**
	 * Get person or list of persons.
	 *
	 * @return  object
	 *
	 * @since   3.0
	 */
	protected function getNames()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$all = $app->input->get('showAll', 0, 'int');
		$term = $app->input->get('term', '', 'string');
		$multiple = $app->input->get('multiple', 0, 'int');
		$id = ($multiple == 1) ? $app->input->get('id', '', 'string') : $app->input->get('id', 0, 'int');
		$lang = $app->input->get('lang', '', 'string');
		$ignore = $app->input->get('ignore', array(), 'array');

		$query = $db->getQuery(true)
			->select($db->quoteName('id') . ',' . $db->quoteName('name') . ',' . $db->quoteName('latin_name') . ',' . $db->quoteName('date_of_birth'))
			->from($db->quoteName('#__ka_names'));

		if (!empty($ignore))
		{
			$query->where($db->quoteName('id') . ' NOT IN (' . implode(',', $ignore) . ')');
		}

		if (!empty($lang))
		{
			$query->where($db->quoteName('language') . " = '" . $db->escape($lang) . "'");
		}

		if (empty($all))
		{
			if (empty($id))
			{
				if (empty($term))
				{
					return array();
				}

				$query->where('(' . $db->quoteName('name') . " LIKE '%" . $db->escape($term) . "%' OR "
					. $db->quoteName('latin_name') . " LIKE '%" . $db->escape($term) . "%')");
			}
			else
			{
				if ($multiple == 1)
				{
					$query->where($db->quoteName('id') . ' IN (' . $id . ')');
				}
				else
				{
					$query->where($db->quoteName('id') . ' = ' . (int) $id);
				}
			}
		}

		try
		{
			$db->setQuery($query);

			if (empty($all) && !empty($id) && $multiple != 1)
			{
				$result = $db->loadObject();
			}
			else
			{
				$result = $db->loadObjectList();
			}

			return $result;
		}
		catch (Exception $e)
		{
			return (object) array('id' => 0, 'title' => JText::_('ERROR') . '!');
		}
	}

	/**
	 * Get award or list of awards.
	 *
	 * @return  object
	 *
	 * @since   3.0
	 */
	protected function getAwards()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$all = $app->input->get('showAll', 0, 'int');
		$term = $app->input->get('term', '', 'string');
		$multiple = $app->input->get('multiple', 0, 'int');
		$id = ($multiple == 1) ? $app->input->get('id', '', 'string') : $app->input->get('id', 0, 'int');
		$lang = $app->input->get('lang', '', 'string');
		$type = $app->input->get('type', -1, 'int');

		if ($type == 0)
		{
			return $this->getAjaxData('movies');
		}
		elseif ($type == 1)
		{
			return $this->getAjaxData('names');
		}
		else
		{
			$query = $db->getQuery(true)
				->select($db->quoteName('id') . ',' . $db->quoteName('title'))
				->from($db->quoteName('#__ka_awards'));

			if (!empty($lang))
			{
				$query->where($db->quoteName('language') . " = '" . $db->escape($lang) . "'");
			}

			if (empty($all))
			{
				if (empty($id))
				{
					$query->where($db->quoteName('title') . " LIKE '%" . $db->escape($term) . "%'");
				}
				else
				{
					if ($multiple == 1)
					{
						$query->where($db->quoteName('id') . ' IN (' . $id . ')');
					}
					else
					{
						$query->where($db->quoteName('id') . ' = ' . (int) $id);
					}
				}
			}
		}

		try
		{
			$db->setQuery($query);

			if (empty($all) && !empty($id) && $multiple != 1)
			{
				$result = $db->loadObject();
			}
			else
			{
				$result = $db->loadObjectList();
			}

			return $result;
		}
		catch (Exception $e)
		{
			return (object) array('id' => 0, 'title' => JText::_('ERROR') . '!');
		}
	}

	/**
	 * Get tag or list of tags.
	 *
	 * @return  object
	 *
	 * @since   3.0
	 */
	protected function getTags()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$all = $app->input->get('showAll', 0, 'int');
		$term = $app->input->get('term', '', 'string');
		$multiple = $app->input->get('multiple', 0, 'int');
		$id = ($multiple == 1) ? $app->input->get('id', '', 'string') : $app->input->get('id', 0, 'int');
		$lang = $app->input->get('lang', '', 'string');

		$query = $db->getQuery(true)
			->select($db->quoteName('id') . ',' . $db->quoteName('title'))
			->from($db->quoteName('#__tags'))
			->where($db->quoteName('parent_id') . ' != 0');

		if (!empty($lang))
		{
			$query->where($db->quoteName('language') . " = '" . $db->escape($lang) . "'");
		}

		if (empty($all))
		{
			if (empty($id))
			{
				if (empty($term))
				{
					return array();
				}

				$query->where($db->quoteName('title') . " LIKE '%" . $db->escape($term) . "%'")
					->order($db->quoteName('title'));
			}
			else
			{
				if ($multiple == 1)
				{
					$query->where($db->quoteName('id') . ' IN (' . $id . ')');
				}
				else
				{
					$query->where($db->quoteName('id') . ' = ' . (int) $id);
				}
			}
		}

		try
		{
			$db->setQuery($query);

			if (empty($all) && !empty($id) && $multiple != 1)
			{
				$result = $db->loadObject();
			}
			else
			{
				$result = $db->loadObjectList();
			}

			return $result;
		}
		catch (Exception $e)
		{
			return (object) array('id' => 0, 'title' => JText::_('ERROR') . '!');
		}
	}

	/**
	 * Get vendor aka distibutor or list of vendors.
	 *
	 * @return  object
	 *
	 * @since   3.0
	 */
	protected function getVendors()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$all = $app->input->get('showAll', 0, 'int');
		$term = $app->input->get('term', '', 'string');
		$multiple = $app->input->get('multiple', 0, 'int');
		$id = ($multiple == 1) ? $app->input->get('id', '', 'string') : $app->input->get('id', 0, 'int');
		$lang = $app->input->get('lang', '', 'string');
		$ignore = $app->input->get('ignore', array(), 'array');

		$query = $db->getQuery(true)
			->select($db->quoteName('id') . ',' . $db->quoteName('company_name') . ',' . $db->quoteName('company_name_intl'))
			->from($db->quoteName('#__ka_vendors'));

		if (!empty($ignore))
		{
			$query->where($db->quoteName('id') . ' NOT IN (' . implode(',', $ignore) . ')');
		}

		if (!empty($lang))
		{
			$query->where($db->quoteName('language') . " = '" . $db->escape($lang) . "'");
		}

		if (empty($all))
		{
			if (empty($id))
			{
				if (empty($term))
				{
					return array();
				}

				$query->where('(' . $db->quoteName('company_name') . " LIKE '%" . $db->escape($term) . "%' OR "
					. $db->quoteName('company_name_intl') . " LIKE '%" . $db->escape($term) . "%')");
			}
			else
			{
				if ($multiple == 1)
				{
					$query->where($db->quoteName('id') . ' IN (' . $id . ')');
				}
				else
				{
					$query->where($db->quoteName('id') . ' = ' . (int) $id);
				}
			}
		}

		try
		{
			$db->setQuery($query);

			if (empty($all) && !empty($id) && $multiple != 1)
			{
				$result = $db->loadObject();
			}
			else
			{
				$result = $db->loadObjectList();
			}

			return $result;
		}
		catch (Exception $e)
		{
			return (object) array('id' => 0, 'title' => JText::_('ERROR') . '!');
		}
	}

	/**
	 * Get career or list of careers.
	 *
	 * @return  object
	 *
	 * @since   3.0
	 */
	protected function getCareers()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$all = $app->input->get('showAll', 0, 'int');
		$term = $app->input->get('term', '', 'string');
		$multiple = $app->input->get('multiple', 0, 'int');
		$id = ($multiple == 1) ? $app->input->get('id', '', 'string') : $app->input->get('id', 0, 'int');
		$lang = $app->input->get('lang', '', 'string');

		$query = $db->getQuery(true)
			->select($db->quoteName('id') . ',' . $db->quoteName('title'))
			->from($db->quoteName('#__ka_names_career'));

		if (!empty($lang))
		{
			$query->where($db->quoteName('language') . " = '" . $db->escape($lang) . "'");
		}

		if (empty($all))
		{
			if (empty($id))
			{
				if (empty($term))
				{
					return array();
				}

				$query->where($db->quoteName('title') . " LIKE '%" . $db->escape($term) . "%'")
						->order($db->quoteName('title'));
			}
			else
			{
				if ($multiple == 1)
				{
					$query->where($db->quoteName('id') . ' IN (' . $id . ')');
				}
				else
				{
					$query->where($db->quoteName('id') . ' = ' . (int) $id);
				}
			}
		}

		try
		{
			$db->setQuery($query);

			if (empty($all) && !empty($id) && $multiple != 1)
			{
				$result = $db->loadObject();
			}
			else
			{
				$result = $db->loadObjectList();
			}

			return $result;
		}
		catch (Exception $e)
		{
			return (object) array('id' => 0, 'title' => JText::_('ERROR') . '!');
		}
	}

	/**
	 * Get mediatype or list of mediatypes.
	 *
	 * @return  object
	 *
	 * @since   3.0
	 */
	protected function getMediatypes()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$all = $app->input->get('showAll', 0, 'int');
		$term = $app->input->get('term', '', 'string');
		$multiple = $app->input->get('multiple', 0, 'int');
		$id = ($multiple == 1) ? $app->input->get('id', '', 'string') : $app->input->get('id', 0, 'int');
		$lang = $app->input->get('lang', '', 'string');

		$query = $db->getQuery(true)
			->select($db->quoteName('id') . ',' . $db->quoteName('title'))
			->from($db->quoteName('#__ka_media_types'));

		if (!empty($lang))
		{
			$query->where($db->quoteName('language') . " = '" . $db->escape($lang) . "'");
		}

		if (empty($all))
		{
			if (empty($id))
			{
				if (empty($term))
				{
					return array();
				}

				$query->where($db->quoteName('title') . " LIKE '%" . $db->escape($term) . "%'")
					->order($db->quoteName('title'));
			}
			else
			{
				if ($multiple == 1)
				{
					$query->where($db->quoteName('id') . ' IN (' . $id . ')');
				}
				else
				{
					$query->where($db->quoteName('id') . ' = ' . (int) $id);
				}
			}
		}

		try
		{
			$db->setQuery($query);

			if (empty($all) && !empty($id) && $multiple != 1)
			{
				$result = $db->loadObject();
			}
			else
			{
				$result = $db->loadObjectList();
			}

			return $result;
		}
		catch (Exception $e)
		{
			return (object) array('id' => 0, 'title' => JText::_('ERROR') . '!');
		}
	}

	/**
	 * Get list of files associated with the trailer.
	 *
	 * @return  object
	 *
	 * @since   3.0
	 */
	protected function getTrailerFiles()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$id = $app->input->get('id', 0, 'int');
		$type = $app->input->get('type', '', 'string');

		if (empty($type))
		{
			return (object) array('id' => 0, 'title' => JText::_('ERROR') . '!');
		}
		else
		{
			if ($type == 'video')
			{
				$col = 'filename';
			}
			elseif ($type == 'subtitles')
			{
				$col = '_subtitles';
			}
			elseif ($type == 'chapters')
			{
				$col = '_chapters';
			}
			else
			{
				return (object) array('id' => 0, 'title' => JText::_('ERROR') . '!');
			}

			$query = $db->getQuery(true)
				->select($db->quoteName($col))
				->from($db->quoteName('#__ka_trailers'))
				->where($db->quoteName('id') . ' = ' . (int) $id);

			try
			{
				$db->setQuery($query);
				$result = $db->loadResult();

				if (!empty($result))
				{
					$result = json_decode($result);
				}
				else
				{
					$result = JText::_('COM_KA_NO_ITEMS');
				}

				return $result;
			}
			catch (Exception $e)
			{
				return (object) array('id' => 0, 'title' => JText::_('ERROR') . '!');
			}
		}
	}
}
