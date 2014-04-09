<?php defined('_JEXEC') or die;

class KinoarhivModelPremieres extends JModelList {
	protected $context = null;

	public function __construct($config = array()) {
		parent::__construct($config);

		if (empty($this->context)) {
			$this->context = $this->context = strtolower('com_kinoarhiv.premieres.global');
		}
	}

	protected function getListQuery() {
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$app = JFactory::getApplication();
		$params = $app->getParams('com_kinoarhiv');
		$country = $app->input->get('country', '', 'string');

		$query = $db->getQuery(true);

		$query->select("`m`.`id`, `m`.`parent_id`, `m`.`title`, `m`.`alias`, `m`.`introtext` AS `text`, `m`.`plot`, `m`.`rate_loc`, `m`.`rate_sum_loc`, `m`.`imdb_votesum`, `m`.`imdb_votes`, `m`.`imdb_id`, `m`.`kp_votesum`, `m`.`kp_votes`, `m`.`kp_id`, `m`.`rottentm_id`, `m`.`rate_custom`, `m`.`year`, DATE_FORMAT(`m`.`created`, '%Y-%m-%d') AS `created`, DATE_FORMAT(`m`.`modified`, '%Y-%m-%d') AS `modified`, `m`.`created_by`, `m`.`attribs`, `m`.`state`, `g`.`filename`, `g`.`dimension`")
			->from($db->quoteName('#__ka_movies').' AS `m`')
			->leftJoin($db->quoteName('#__ka_movies_gallery').' AS `g` ON `g`.`movie_id` = `m`.`id` AND `g`.`type` = 2 AND `g`.`poster_frontpage` = 1 AND `g`.`state` = 1');

		if (!$user->get('guest')) {
			$query->select(' `u`.`favorite`')
				->leftJoin($db->quoteName('#__ka_user_marked_movies').' AS `u` ON `u`.`uid` = '.$user->get('id').' AND `u`.`movie_id` = `m`.`id`');
		}

		$where = '`m`.`state` = 1 AND `language` IN ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').') AND `parent_id` = 0 AND `m`.`access` IN ('.$groups.')';

		if (!empty($country)) {
			$where .= ' AND `m`.`id` IN (SELECT `movie_id` FROM '.$db->quoteName('#__ka_premieres').' WHERE `country_id` = (SELECT `id` FROM '.$db->quoteName('#__ka_countries').' WHERE `code` = "'.$country.'" AND `language` IN ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')))';
		}

		$query->where($where);

		$orderCol = $this->state->get('list.ordering', 'm.ordering');
		$orderDirn = $this->state->get('list.direction', 'desc');
		$query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
	}

	public function getSelectList() {
		$db = $this->getDBO();
		$result = array(
			'countries' =>array(array('name'=>JText::_('JALL'), 'code'=>'')),
			'years'     =>array(array('year'=>date('Y'))),
			'months'    =>array(array('value'=>'', 'name'=>JText::_('JALL')))
		);

		$db->setQuery("SELECT `name`, `code`"
			. "\n FROM ".$db->quoteName('#__ka_countries')
			. "\n WHERE `id` IN (SELECT `country_id` FROM ".$db->quoteName('#__ka_premieres')." WHERE `country_id` != 0) AND `state` = 1"
			. "\n GROUP BY `code`");
		try {
			$countries = $db->loadAssocList();

			if (count($countries) > 0) {
				$result['countries'] = array_merge($result['countries'], $countries);
			}
		} catch (Exception $e) {
			GlobalHelper::eventLog($e->getMessage());
		}

		$db->setQuery("SELECT DATE_FORMAT(`premiere_date`, '%Y') AS `year`"
			. "\n FROM ".$db->quoteName('#__ka_premieres')
			. "\n GROUP BY `year`");
		try {
			$years = $db->loadAssocList();

			if (count($years) > 0) {
				$result['years'] = array_merge($result['years'], $years);
			}
		} catch (Exception $e) {
			GlobalHelper::eventLog($e->getMessage());
		}

		$db->setQuery("SELECT DATE_FORMAT(`premiere_date`, '%Y-%m') AS `value`, `premiere_date`"
			. "\n FROM ".$db->quoteName('#__ka_premieres')
			. "\n GROUP BY `value`");
		try {
			$months = $db->loadAssocList();

			if (count($months) > 0) {
				foreach ($months as $key=>$month) {
					$months[$key]['name'] = JHTML::_('date', strtotime($month['premiere_date']), 'F Y');
				}

				$result['months'] = array_merge($result['months'], $months);
			}
		} catch (Exception $e) {
			GlobalHelper::eventLog($e->getMessage());
		}

		return $result;
	}

	public function getPagination() {
		JLoader::register('KAPagination', JPATH_COMPONENT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'pagination.php');

		$store = $this->getStoreId('getPagination');

		if (isset($this->cache[$store])) {
			return $this->cache[$store];
		}

		$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');
		$page = new KAPagination($this->getTotal(), $this->getStart(), $limit);

		$this->cache[$store] = $page;

		return $this->cache[$store];
	}

	protected function populateState($ordering = null, $direction = null) {
		if ($this->context) {
			$app = JFactory::getApplication();

			$value = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $app->get('list_limit'), 'uint');
			$limit = $value;
			$this->setState('list.limit', $limit);

			$value = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
			$this->setState('list.start', $limitstart);

			$value = $app->getUserStateFromRequest($this->context . '.ordercol', 'filter_order', $ordering);
			if (!in_array($value, $this->filter_fields)) {
				$value = $ordering;
				$app->setUserState($this->context . '.ordercol', $value);
			}
			$this->setState('list.ordering', $value);

			$value = $app->getUserStateFromRequest($this->context . '.orderdirn', 'filter_order_Dir', $direction);
			if (!in_array(strtoupper($value), array('ASC', 'DESC', ''))) {
				$value = $direction;
				$app->setUserState($this->context . '.orderdirn', $value);
			}
			$this->setState('list.direction', $value);
		} else {
			$this->setState('list.start', 0);
			$this->state->set('list.limit', 0);
		}
	}
}
