<?php defined('_JEXEC') or die;

/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */
class KinoarhivModelReleases extends JModelList
{
	protected $context = null;

	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'r.id',
				'title', 'm.title',
				'release_date', 'r.release_date',
				'name', 'c.name',
				'media_type', 'r.media_type',
				'vendor', 'v.company_name', 'v.company_name_intl',
				'language', 'r.language',
				'ordering', 'r.ordering');
		}

		parent::__construct($config);

		$this->context = strtolower($this->option . '.' . $this->getName() . '.premieres');
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout')) {
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
		if (!empty($forcedLanguage)) {
			$this->setState('filter.language', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
		}
	}

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

	protected function getListQuery()
	{
		$db = $this->getDBO();

		$query = $db->getQuery(true);

		$query->select(
			$this->getState(
				'list.select',
				$db->quoteName(array('r.id', 'r.movie_id', 'r.media_type', 'r.release_date', 'r.language', 'r.ordering'))
			)
		);
		$query->from($db->quoteName('#__ka_releases', 'r'))
			->select($db->quoteName(array('m.title', 'm.year')))
			->join('LEFT', $db->quoteName('#__ka_movies', 'm') . ' ON ' . $db->quoteName('m.id') . ' = ' . $db->quoteName('r.movie_id'))
			->select($db->quoteName(array('v.company_name', 'v.company_name_intl')))
			->join('LEFT', $db->quoteName('#__ka_vendors', 'v') . ' ON ' . $db->quoteName('v.id') . ' = ' . $db->quoteName('r.vendor_id'))
			->select($db->quoteName(array('c.name', 'c.code')))
			->join('LEFT', $db->quoteName('#__ka_countries', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('r.country_id'));

		// Join over the language
		$query->select($db->quoteName('l.title', 'language_title'))
			->join('LEFT', $db->quoteName('#__languages', 'l') . ' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('r.language'));

		// Filter by country
		$country = $this->getState('filter.country');
		if (is_numeric($country)) {
			$query->where('r.country_id = ' . (int)$country);
		}

		// Filter by vendor
		$vendor = $this->getState('filter.vendor');
		if (is_numeric($vendor)) {
			$query->where('r.vendor_id = ' . (int)$vendor);
		}

		// Filter by media type
		$mediatype = $this->getState('filter.media_type');
		if (is_numeric($mediatype)) {
			$query->where('r.media_type = ' . (int)$mediatype);
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language')) {
			$query->where('r.language = ' . $db->quote($language));
		}

		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('r.id = ' . (int)substr($search, 3));
			} elseif (stripos($search, 'title:') === 0) {
				$search = trim(substr($search, 6));
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('m.title LIKE ' . $search);
			} elseif (stripos($search, 'country:') === 0) {
				$search = trim(substr($search, 8));

				if (JString::strtolower($search) == JString::strtolower(JText::_('COM_KA_PREMIERE_WORLD')) || $search == 0) {
					$query->where('r.country_id = 0');
				} else {
					$search = $db->quote('%' . $db->escape($search, true) . '%');
					$query->where('c.name LIKE ' . $search);
				}
			} else {
				$search = trim(substr($search, 5));
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('r.release_date LIKE ' . $search);
			}
		}

		$orderCol = $this->state->get('list.ordering', 'r.release_date');
		$orderDirn = $this->state->get('list.direction', 'desc');
		if ($orderCol == 'r.ordering') {
			$orderCol = 'r.ordering ' . $orderDirn . ', r.release_date';
		}

		// SQL server change
		if ($orderCol == 'language') {
			$orderCol = 'l.title';
		}

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	/**
	 * Method to get a list of articles.
	 * Overridden to add a check for access levels.
	 * @return  mixed  An array of data items on success, false on failure.
	 * @since   1.6.1
	 */
	public function getItems()
	{
		$items = parent::getItems();

		if (JFactory::getApplication()->isSite()) {
			$user = JFactory::getUser();
			$groups = $user->getAuthorisedViewLevels();

			for ($x = 0, $count = count($items); $x < $count; $x++) {
				// Check the access level. Remove articles the user shouldn't see
				if (!in_array($items[$x]->access, $groups)) {
					unset($items[$x]);
				}
			}
		}

		return $items;
	}

	public function saveOrder()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$data = $app->input->post->get('ord', array(), 'array');
		$movie_id = $app->input->post->get('movie_id', 0, 'int');

		if (count($data) < 2) {
			return array('success' => false, 'message' => JText::_('COM_KA_SAVE_ORDER_AT_LEAST_TWO'));
		}

		$query_result = true;
		$db->setDebug(true);
		$db->lockTable('#__ka_releases');
		$db->transactionStart();

		foreach ($data as $key => $value) {
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_releases'))
				->set($db->quoteName('ordering') . " = '" . (int)$key . "'")
				->where(array($db->quoteName('ordering') . ' = ' . (int)$value, $db->quoteName('movie_id') . ' = ' . (int)$movie_id));

			$db->setQuery($query . ';');

			if ($db->execute() === false) {
				$query_result = false;
				break;
			}
		}

		if ($query_result === false) {
			$db->transactionRollback();
		} else {
			$db->transactionCommit();
		}

		$db->unlockTables();
		$db->setDebug(false);

		if ($query_result) {
			$success = true;
			$message = JText::_('COM_KA_SAVED');
		} else {
			$success = false;
			$message = JText::_('COM_KA_SAVE_ORDER_ERROR');
		}

		return array('success' => $success, 'message' => $message);
	}

	public function batch()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->post->get('id', array(), 'array');
		$batch_data = $app->input->post->get('batch', array(), 'array');

		if (empty($batch_data)) {
			return false;
		}

		$fields = array();

		if (!empty($batch_data['vendor_id'])) {
			$fields[] = $db->quoteName('vendor_id') . " = '" . (int)$batch_data['vendor_id'] . "'";
		}
		if (!empty($batch_data['country_id'])) {
			$fields[] = $db->quoteName('country_id') . " = '" . (int)$batch_data['country_id'] . "'";
		}
		if (!empty($batch_data['mediatype_id'])) {
			$fields[] = $db->quoteName('media_type') . " = '" . (int)$batch_data['mediatype_id'] . "'";
		}
		if (!empty($batch_data['language_id'])) {
			$fields[] = $db->quoteName('language') . " = '" . $db->escape((string)$batch_data['language_id']) . "'";
		}

		if (empty($fields)) {
			return false;
		}

		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_releases'))
			->set(implode(', ', $fields))
			->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try {
			$db->execute();
		} catch (Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}
}
