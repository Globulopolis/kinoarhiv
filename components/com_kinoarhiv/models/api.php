<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

/**
 * Global model class to provide an API
 *
 * @since  3.0
 */
class KinoarhivModelAPI extends JModelLegacy
{
	/**
	 * Proxy method and should be changed in future releases.
	 *
	 * @param   string  $element  Data type. Can be 'countries', 'genres', 'movies', 'awards', 'names', 'tags', 'careers', 'vendors'
	 *
	 * @return  object
	 *
	 * @since 3.0
	 */
	public function getData($element = '')
	{
		return $this->getAjaxData($element = '');
	}

	/**
	 * Method to get ajax data from some tables
	 *
	 * @param   string  $element  Data type. Can be 'countries', 'genres', 'movies', 'awards', 'names', 'tags', 'careers', 'vendors'
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 *
	 * @since 3.0
	 */
	public function getAjaxData($element = '')
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$element = !empty($element) ? $element : $app->input->get('element', '', 'string');
		$all = $app->input->get('showAll', 0, 'int');
		$term = $app->input->get('term', '', 'string');
		$id = $app->input->get('id', 0, 'int');
		$multiple = $app->input->get('multiple', 0, 'int');
		$lang = JFactory::getLanguage();
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());
		$language_in = "language IN (" . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ")";

		if ($element == 'countries')
		{
			// Do not remove `code` field from the query. It's necessary for flagging row in select
			if (empty($all))
			{
				if (empty($id))
				{
					if (empty($term))
					{
						return array();
					}

					$query = $db->getQuery(true)
						->select('id, name AS text, code')
						->from($db->quoteName('#__ka_countries'))
						->where('name LIKE "' . $db->escape($term) . '%" AND ' . $language_in . ' AND state = 1')
						->order('name ASC');

					$db->setQuery($query);
					$result = $db->loadObjectList();
				}
				else
				{
					if ($multiple == 1)
					{
						// TODO Convert ID's into string
						$ids = $app->input->get('id', '', 'string');
						$query = $db->getQuery(true)
							->select('id, name AS text, code')
							->from($db->quoteName('#__ka_countries'))
							->where('id IN (' . $ids . ') AND ' . $language_in . ' AND state = 1')
							->order('name ASC');

						$db->setQuery($query);
						$result = $db->loadObjectList();
					}
					else
					{
						$query = $db->getQuery(true)
							->select('id, name AS text, code')
							->from($db->quoteName('#__ka_countries'))
							->where('id = ' . (int) $id . ' AND ' . $language_in . ' AND state = 1');

						$db->setQuery($query);
						$result = $db->loadObject();
					}
				}
			}
			else
			{
				$query = $db->getQuery(true)
					->select('id, name AS text, code')
					->from($db->quoteName('#__ka_countries'))
					->where($language_in . ' AND state = 1')
					->order('name ASC');

				$db->setQuery($query);
				$result = $db->loadObjectList();
			}
		}
		elseif ($element == 'genres-movie')
		{
			if (empty($all))
			{
				if (empty($id))
				{
					if (empty($term))
					{
						return array();
					}

					$query = $db->getQuery(true)
						->select('id, name AS text')
						->from($db->quoteName('#__ka_genres'))
						->where("name LIKE '" . $db->escape($term) . "%'" . " AND " . $language_in . " AND state = 1 AND access IN (" . $groups . ")");

					$db->setQuery($query);
					$result = $db->loadObjectList();
				}
				else
				{
					if ($multiple == 1)
					{
						// TODO Convert ID's into string
						$ids = $app->input->get('id', '', 'string');
						$query = $db->getQuery(true)
							->select('id, name AS text')
							->from($db->quoteName('#__ka_genres'))
							->where('id IN (' . $ids . ') AND ' . $language_in . ' AND state = 1 AND access IN (' . $groups . ')')
							->order('name ASC');

						$db->setQuery($query);
						$result = $db->loadObjectList();
					}
					else
					{
						$query = $db->getQuery(true)
							->select('id, name AS text')
							->from($db->quoteName('#__ka_genres'))
							->where("id = " . (int) $id . " AND " . $language_in . " AND state = 1 AND access IN (" . $groups . ")");

						$db->setQuery($query);
						$result = $db->loadObject();
					}
				}
			}
			else
			{
				$query = $db->getQuery(true)
					->select('id, name AS text')
					->from($db->quoteName('#__ka_genres'))
					->where("language IN (" . $db->quote($lang->getTag()) . "," . $db->quote('*') . ") AND state = 1 AND access IN (" . $groups . ")");

				$db->setQuery($query);
				$result = $db->loadObjectList();
			}
		}
		elseif ($element == 'movies')
		{
			if (empty($all))
			{
				if (empty($id))
				{
					$query = $db->getQuery(true)
						->select('id, title, year')
						->from($db->quoteName('#__ka_movies'))
						->where("title LIKE '" . $db->escape($term) . "%'" . " AND " . $language_in . " AND state = 1 AND access IN (" . $groups . ")");

					$db->setQuery($query);
					$result = $db->loadObjectList();
				}
				else
				{
					$query = $db->getQuery(true)
						->select('id, title, year')
						->from($db->quoteName('#__ka_movies'))
						->where("id = " . (int) $id . " AND " . $language_in . " AND state = 1 AND access IN (" . $groups . ")");

					$db->setQuery($query);
					$result = $db->loadObject();
				}
			}
			else
			{
				$query = $db->getQuery(true)
					->select('id, title, year')
					->from($db->quoteName('#__ka_movies'))
					->where($language_in . " AND state = 1 AND access IN (" . $groups . ")");

				$db->setQuery($query);
				$result = $db->loadObjectList();
			}
		}
		elseif ($element == 'awards')
		{
			$type = $app->input->get('type', -1, 'int');

			if ($type == 0)
			{
				$result = $this->getAjaxData('movies');
			}
			elseif ($type == 1)
			{
				$result = $this->getAjaxData('names');
			}
			else
			{
				if (empty($all))
				{
					if (empty($id))
					{
						$query = $db->getQuery(true)
							->select('id, title')
							->from($db->quoteName('#__ka_awards'))
							->where("title LIKE '" . $db->escape($term) . "%'" . " AND " . $language_in . " AND state = 1");

						$db->setQuery($query);
						$result = $db->loadObjectList();
					}
					else
					{
						$query = $db->getQuery(true)
							->select('id, title')
							->from($db->quoteName('#__ka_awards'))
							->where("id = " . (int) $id . " AND " . $language_in . " AND state = 1");

						$db->setQuery($query);
						$result = $db->loadObject();
					}
				}
				else
				{
					$query = $db->getQuery(true)
						->select('id, title')
						->from($db->quoteName('#__ka_awards'))
						->where($language_in . " AND state = 1");

					$db->setQuery($query);
					$result = $db->loadObjectList();
				}
			}
		}
		elseif ($element == 'names')
		{
			if (empty($all))
			{
				if (empty($id))
				{
					$query = $db->getQuery(true)
						->select('id, name, latin_name, date_of_birth')
						->from($db->quoteName('#__ka_names'))
						->where("(name LIKE '" . $db->escape($term) . "%' OR latin_name LIKE '" . $db->escape($term) . "%')" . " AND " . $language_in . " AND state = 1 AND access IN (" . $groups . ")");

					$db->setQuery($query);
					$result = $db->loadObjectList();
				}
				else
				{
					if ($multiple == 1)
					{
						// TODO Convert ID's into string
						$ids = $app->input->get('id', '', 'string');
						$query = $db->getQuery(true)
							->select('id, name, latin_name, date_of_birth')
							->from($db->quoteName('#__ka_names'))
							->where('id IN (' . $ids . ') AND ' . $language_in . ' AND state = 1 AND access IN (' . $groups . ')');

						$db->setQuery($query);
						$result = $db->loadObjectList();
					}
					else
					{
						$query = $db->getQuery(true)
							->select('id, name, latin_name, date_of_birth')
							->from($db->quoteName('#__ka_names'))
							->where("id = " . (int) $id . " AND " . $language_in . " AND state = 1 AND access IN (" . $groups . ")");

						$db->setQuery($query);
						$result = $db->loadObject();
					}
				}
			}
			else
			{
				$query = $db->getQuery(true)
					->select('id, name, latin_name, date_of_birth')
					->from($db->quoteName('#__ka_names'))
					->where($language_in . " AND state = 1 AND access IN (" . $groups . ")");

				$db->setQuery($query);
				$result = $db->loadObjectList();
			}
		}
		elseif ($element == 'tags')
		{
			if (empty($all))
			{
				if (empty($id))
				{
					$query = $db->getQuery(true)
						->select('id, title AS text')
						->from($db->quoteName('#__tags'))
						->where("title LIKE '" . $db->escape($term) . "%' AND " . $language_in . " AND published = 1 AND access IN (" . $groups . ") AND parent_id != 0");

					$db->setQuery($query);
					$result = $db->loadObjectList();
				}
				else
				{
					if ($multiple == 1)
					{
						// TODO Convert ID's into string
						$ids = $app->input->get('id', '', 'string');
						$query = $db->getQuery(true)
							->select('id, title AS text')
							->from($db->quoteName('#__tags'))
							->where('id IN (' . $ids . ') AND ' . $language_in . ' AND published = 1 AND parent_id != 0')
							->order('title ASC');

						$db->setQuery($query);
						$result = $db->loadObjectList();
					}
					else
					{
						$query = $db->getQuery(true)
							->select('id, title AS text')
							->from($db->quoteName('#__tags'))
							->where('id = ' . (int) $id . ' AND ' . $language_in . ' AND published = 1 AND access IN (' . $groups . ') AND parent_id != 0');

						$db->setQuery($query);
						$result = $db->loadObject();
					}
				}
			}
			else
			{
				$query = $db->getQuery(true)
					->select('id, title')
					->from($db->quoteName('#__tags'))
					->where($language_in . " AND published = 1 AND access IN (" . $groups . ")");

				$db->setQuery($query);
				$result = $db->loadObjectList();
			}
		}
		elseif ($element == 'career' || $element == 'careers')
		{
			if (empty($all))
			{
				if (empty($id))
				{
					$query = $db->getQuery(true)
						->select('id, title')
						->from($db->quoteName('#__ka_names_career'))
						->where("title LIKE '" . $db->escape($term) . "%'" . " AND " . $language_in);

					$db->setQuery($query);
					$result = $db->loadObjectList();
				}
				else
				{
					$query = $db->getQuery(true)
						->select('id, title')
						->from($db->quoteName('#__ka_names_career'))
						->where("id = " . (int) $id . " AND " . $language_in);

					$db->setQuery($query);
					$result = $db->loadObject();
				}
			}
			else
			{
				$query = $db->getQuery(true)
					->select('id, title')
					->from($db->quoteName('#__ka_names_career'))
					->where($language_in);

				$db->setQuery($query);
				$result = $db->loadObjectList();
			}
		}
		elseif ($element == 'vendors')
		{
			if ($all == 0)
			{
				if (empty($id))
				{
					$query = $db->getQuery(true)
						->select('id, company_name, company_name_intl')
						->from($db->quoteName('#__ka_vendors'))
						->where("company_name LIKE '" . $db->escape($term) . "%' OR company_name_intl LIKE '" . $db->escape($term) . "%'" . " AND " . $language_in . " AND state = 1");

					$db->setQuery($query);
					$result = $db->loadObjectList();
				}
				else
				{
					if ($multiple == 1)
					{
						// TODO Convert ID's into string
						$ids = $app->input->get('id', '', 'string');
						$query = $db->getQuery(true)
							->select('id, company_name, company_name_intl')
							->from($db->quoteName('#__ka_vendors'))
							->where('id IN (' . $ids . ') AND ' . $language_in . ' AND state = 1');

						$db->setQuery($query);
						$result = $db->loadObjectList();
					}
					else
					{
						$query = $db->getQuery(true)
							->select('id, company_name, company_name_intl')
							->from($db->quoteName('#__ka_vendors'))
							->where("id = " . (int) $id . " AND " . $language_in . " AND state = 1");

						$db->setQuery($query);
						$result = $db->loadObject();
					}
				}
			}
			else
			{
				$query = $db->getQuery(true)
					->select('id, company_name, company_name_intl')
					->from($db->quoteName('#__ka_vendors'))
					->where($language_in . " AND state = 1");

				$db->setQuery($query);
				$result = $db->loadObjectList();
			}
		}
		else
		{
			$result = array();
		}

		return $result;
	}
}
