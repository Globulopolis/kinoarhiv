<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

/**
 * Class KinoarhivModelReleases
 *
 * @since  3.0
 */
class KinoarhivModelReleases extends JModelList
{
	protected $context = 'com_kinoarhiv.releases';

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelLegacy
	 * @since   3.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'r.id',
				'title', 'm.title',
				'release_date', 'r.release_date',
				'name', 'c.name', 'country',
				'media_type', 'r.media_type',
				'vendor', 'v.company_name',
				'language', 'r.language',
				'ordering', 'r.ordering');
		}

		parent::__construct($config);

		$this->context = strtolower($this->option . '.' . $this->getName() . '.premieres');
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$country = $this->getUserStateFromRequest($this->context . '.filter.country', 'filter_country', '');
		$this->setState('filter.country', $country);

		$vendor = $this->getUserStateFromRequest($this->context . '.filter.vendor', 'filter_vendor', '');
		$this->setState('filter.vendor', $vendor);

		$mediaType = $this->getUserStateFromRequest($this->context . '.filter.media_type', 'filter_media_type', '');
		$this->setState('filter.media_type', $mediaType);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// List state information.
		parent::populateState('r.ordering', 'desc');

		$forcedLanguage = $app->input->get('forcedLanguage');

		if (!empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
		}
	}

	/**
	 * Method to get a store id based on the model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  An identifier string to generate the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   3.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.country');
		$id .= ':' . $this->getState('filter.vendor');
		$id .= ':' . $this->getState('filter.mediatype');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   3.0
	 */
	protected function getListQuery()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				'r.id, r.movie_id, r.release_date, r.language, r.ordering, m.title, m.year, v.company_name, ' .
				'c.name, c.code, media.title AS media_type'
			)
		);
		$query->from($db->quoteName('#__ka_releases', 'r'))
			->join('LEFT', $db->quoteName('#__ka_movies', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('r.movie_id'))
			->join('LEFT', $db->quoteName('#__ka_vendors', 'v') . ' ON ' . $db->quoteName('v.id') . ' = ' . $db->quoteName('r.vendor_id'))
			->join('LEFT', $db->quoteName('#__ka_countries', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('r.country_id'))
			->join('LEFT', $db->quoteName('#__ka_media_types', 'media') . ' ON ' . $db->quoteName('media.id') . ' = ' . $db->quoteName('r.media_type'));

		// Join over the language
		$query->select($db->quoteName('l.title', 'language_title'))
			->join('LEFT', $db->quoteName('#__languages', 'l') . ' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('r.language'));

		// Filter by country
		$country = $this->getState('filter.country');

		if (is_numeric($country))
		{
			$query->where('r.country_id = ' . (int) $country);
		}

		// Filter by vendor
		$vendor = $this->getState('filter.vendor');

		if (is_numeric($vendor))
		{
			$query->where('r.vendor_id = ' . (int) $vendor);
		}

		// Filter by media type
		$mediatype = $this->getState('filter.media_type');

		if (is_numeric($mediatype))
		{
			$query->where('r.media_type = ' . (int) $mediatype);
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$query->where('r.language = ' . $db->quote($language));
		}

		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('r.id = ' . (int) substr($search, 3));
			}
			elseif (stripos($search, 'title:') === 0)
			{
				$search = trim(substr($search, 6));
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('m.title LIKE ' . $search);
			}
			elseif (stripos($search, 'country:') === 0)
			{
				$search = trim(substr($search, 8));

				if (StringHelper::strtolower($search) == StringHelper::strtolower(JText::_('COM_KA_RELEASES_WORLD')) || $search == 0)
				{
					$query->where('r.country_id = 0');
				}
				else
				{
					$search = $db->quote('%' . $db->escape($search, true) . '%');
					$query->where('c.name LIKE ' . $search);
				}
			}
			else
			{
				$search = trim(substr($search, 5));
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('r.release_date LIKE ' . $search);
			}
		}

		$orderCol = $this->state->get('list.ordering', 'r.release_date');
		$orderDirn = $this->state->get('list.direction', 'desc');

		if ($orderCol == 'r.ordering')
		{
			$orderCol = 'r.ordering ' . $orderDirn . ', r.release_date';
		}

		// SQL server change
		if ($orderCol == 'language')
		{
			$orderCol = 'l.title';
		}

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	/**
	 * Method to get a list of articles.
	 * Overridden to add a check for access levels.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   1.6.1
	 */
	public function getItems()
	{
		$items = parent::getItems();

		if (JFactory::getApplication()->isClient('site'))
		{
			$user = JFactory::getUser();
			$groups = $user->getAuthorisedViewLevels();

			for ($x = 0, $count = count($items); $x < $count; $x++)
			{
				// Check the access level. Remove articles the user shouldn't see
				if (!in_array($items[$x]->access, $groups))
				{
					unset($items[$x]);
				}
			}
		}

		return $items;
	}

	/**
	 * Method save careers ordering in lists.
	 *
	 * @param   array    $data      Indexed array of IDs
	 * @param   integer  $movie_id  Movie ID
	 *
	 * @return  boolean
	 *
	 * @since   3.0
	 */
	public function saveOrder($data, $movie_id)
	{
		$db = $this->getDbo();
		$query_result = true;
		$db->setDebug(true);
		$db->lockTable('#__ka_releases');
		$db->transactionStart();

		foreach ($data as $key => $value)
		{
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_releases'))
				->set($db->quoteName('ordering') . " = '" . (int) $key . "'")
				->where($db->quoteName('ordering') . ' = ' . (int) $value)
				->where($db->quoteName('movie_id') . ' = ' . (int) $movie_id);
			$db->setQuery($query . ';');

			if ($db->execute() === false)
			{
				$query_result = false;
				break;
			}
		}

		if ($query_result === false)
		{
			$db->transactionRollback();
		}
		else
		{
			$db->transactionCommit();
		}

		$db->unlockTables();
		$db->setDebug(false);

		return (bool) $query_result;
	}

	/**
	 * Method to perform batch operations on an item or a set of items.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 *
	 * @since   3.0
	 */
	public function batch()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$ids = $app->input->post->get('id', array(), 'array');
		$batch_data = $app->input->post->get('batch', array(), 'array');

		if (empty($batch_data))
		{
			return false;
		}

		$fields = array();

		if (!empty($batch_data['vendor_id']))
		{
			$fields[] = $db->quoteName('vendor_id') . " = '" . (int) $batch_data['vendor_id'] . "'";
		}

		if (!empty($batch_data['country_id']))
		{
			$fields[] = $db->quoteName('country_id') . " = '" . (int) $batch_data['country_id'] . "'";
		}

		if (!empty($batch_data['mediatype_id']))
		{
			$fields[] = $db->quoteName('media_type') . " = '" . (int) $batch_data['mediatype_id'] . "'";
		}

		if (!empty($batch_data['language_id']))
		{
			$fields[] = $db->quoteName('language') . " = '" . $db->escape((string) $batch_data['language_id']) . "'";
		}

		if (empty($fields))
		{
			return false;
		}

		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_releases'))
			->set(implode(', ', $fields))
			->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}
}
