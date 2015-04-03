<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivModelAwards extends JModelList {
	protected $context = null;

	public function __construct($config = array()) {
		parent::__construct($config);

		if (empty($this->context)) {
			$this->context = strtolower('com_kinoarhiv.awards');
		}
	}

	protected function populateState($ordering = null, $direction = null) {
		if ($this->context) {
			$app = JFactory::getApplication();
			$params = JComponentHelper::getParams('com_kinoarhiv');

			$value = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $params->get('list_limit'), 'uint');
			$limit = $value;
			$this->setState('list.limit', $value);

			$value = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
			$this->setState('list.start', $limitstart);
		} else {
			$this->setState('list.start', 0);
			$this->state->set('list.limit', 0);
		}
	}

	protected function getStoreId($id = '') {
		// Compile the store id.
		$id .= ':' . $this->getState('list.limit');

		return parent::getStoreId($id);
	}

	protected function getListQuery() {
		$db = $this->getDBO();
		$query = $db->getQuery(true);

		$query->select('`id`, `title`, `desc`')
			->from($db->quoteName('#__ka_awards'))
			->where('`state` = 1');

		return $query;
	}

	public function getItem() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', null, 'int');
		$query = $db->getQuery(true);

		$query->select('`id`, `title`, `desc`')
			->from($db->quoteName('#__ka_awards'))
			->where('`id` = '.(int)$id.' AND `state` = 1');
		$db->setQuery($query);

		try {
			$result = $db->loadObject();
		} catch (Exception $e) {
			$this->setError($e->getMessage());
			GlobalHelper::eventLog($e->getMessage());

			return false;
		}

		return $result;
	}

	public function getPagination() {
		JLoader::register('KAPagination', JPATH_COMPONENT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'pagination.php');

		$store = $this->getStoreId('getPagination');

		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		$limit = (int)$this->getState('list.limit') - (int)$this->getState('list.links');
		$page = new KAPagination($this->getTotal(), $this->getStart(), $limit);

		$this->cache[$store] = $page;

		return $this->cache[$store];
	}
}
