<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivModelReleases extends JModelList {
	protected $context = null;

	public function __construct($config = array()) {
		parent::__construct($config);

		if (empty($this->context)) {
			$this->context = $this->context = strtolower('com_kinoarhiv.releases');
		}
	}

	protected function populateState($ordering = null, $direction = null) {
		if ($this->context) {
			$app = JFactory::getApplication();
			$params = JComponentHelper::getParams('com_kinoarhiv');

			$value = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $params->get('list_limit'), 'uint');
			$limit = $value;
			$this->setState('list.limit', $limit);

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
		$user = JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$country = $app->input->get('country', '', 'word'); // It's a string because country_id == 0 - all countries
		$year = $app->input->get('year', 0, 'int');
		$vendor = $app->input->get('vendor', 0, 'int');
		$month = $app->input->get('month', '', 'string');
		$mediatype = $app->input->get('mediatype', '', 'string');

		$query = $db->getQuery(true);

		$query->select("`m`.`id`, `m`.`parent_id`, `m`.`title`, `m`.`alias`, `m`.`introtext` AS `text`, `m`.`plot`, `m`.`rate_loc`, `m`.`rate_sum_loc`, `m`.`imdb_votesum`, `m`.`imdb_votes`, `m`.`imdb_id`, `m`.`kp_votesum`, `m`.`kp_votes`, `m`.`kp_id`, `m`.`rottentm_id`, `m`.`rate_custom`, `m`.`year`, DATE_FORMAT(`m`.`created`, '%Y-%m-%d') AS `created`, DATE_FORMAT(`m`.`modified`, '%Y-%m-%d') AS `modified`, `m`.`created_by`, `m`.`attribs`, `m`.`state`, `g`.`filename`, `g`.`dimension`")
			->from($db->quoteName('#__ka_movies').' AS `m`')
			->leftJoin($db->quoteName('#__ka_movies_gallery').' AS `g` ON `g`.`movie_id` = `m`.`id` AND `g`.`type` = 2 AND `g`.`poster_frontpage` = 1 AND `g`.`state` = 1');

			if ($country != '') {
				$query->select(' `r`.`release_date`, `r`.`vendor_id`')
				->leftJoin($db->quoteName('#__ka_releases').' AS `r` ON `r`.`movie_id` = `m`.`id` AND `r`.`country_id` = (SELECT `id` FROM '.$db->quoteName('#__ka_countries').' WHERE `code` = "'.$db->escape($country).'" AND `language` IN ('.$db->quote($lang->getTag()).','.$db->quote('*').'))');

				$query->select(' `v`.`company_name`, `v`.`company_name_intl`, `v`.`company_name_alias`')
				->leftJoin($db->quoteName('#__ka_vendors').' AS `v` ON `v`.`id` = `r`.`vendor_id` AND `v`.`state` = 1');
			} else {
				$query->select(' `r`.`release_date`, `r`.`vendor_id`')
				->leftJoin($db->quoteName('#__ka_releases').' AS `r` ON `r`.`movie_id` = `m`.`id` AND `r`.`country_id` != 0');

				$query->select(' `v`.`company_name`, `v`.`company_name_intl`, `v`.`company_name_alias`')
				->leftJoin($db->quoteName('#__ka_vendors').' AS `v` ON `v`.`id` = `r`.`vendor_id` AND `v`.`state` = 1 AND `v`.`language` IN ('.$db->quote($lang->getTag()).','.$db->quote('*').')');
			}

		if (!$user->get('guest')) {
			$query->select(' `u`.`favorite`')
				->leftJoin($db->quoteName('#__ka_user_marked_movies').' AS `u` ON `u`.`uid` = '.$user->get('id').' AND `u`.`movie_id` = `m`.`id`');
		}

		$where = '`m`.`state` = 1 AND `m`.`language` IN ('.$db->quote($lang->getTag()).','.$db->quote('*').') AND `parent_id` = 0 AND `m`.`access` IN ('.$groups.')';

		if ($params->get('use_alphabet') == 1) {
			$letter = $app->input->get('letter', '', 'string');

			if ($letter != '') {
				if ($letter == '0-1') {
					$where .= ' AND (`m`.`title` LIKE "0%" AND `m`.`title` LIKE "1%" AND `m`.`title` LIKE "2%" AND `m`.`title` LIKE "3%" AND `m`.`title` LIKE "4%" AND `m`.`title` LIKE "5%" AND `m`.`title` LIKE "6%" AND `m`.`title` LIKE "7%" AND `m`.`title` LIKE "8%" AND `m`.`title` LIKE "9%")';
				} else {
					if (preg_match('#\p{L}#u', $letter, $matches)) { // only any kind of letter from any language.
						$where .= ' AND `m`.`title` LIKE "'.$db->escape(JString::strtoupper($matches[0])).'%"';
					}
				}
			}
		}

		if ($country != '') {
			$where .= ' AND `m`.`id` IN (SELECT `movie_id` FROM '.$db->quoteName('#__ka_releases').' WHERE `country_id` = (SELECT `id` FROM '.$db->quoteName('#__ka_countries').' WHERE `code` = "'.$db->escape($country).'" AND `language` IN ('.$db->quote($lang->getTag()).','.$db->quote('*').')))';
		}

		if (!empty($year)) {
			$where .= ' AND `m`.`id` IN (SELECT `movie_id` FROM '.$db->quoteName('#__ka_releases').' WHERE `release_date` LIKE "%'.$year.'%")';
		}

		if ($month != '') {
			$where .= ' AND `m`.`id` IN (SELECT `movie_id` FROM '.$db->quoteName('#__ka_releases').' WHERE `release_date` LIKE "%'.$month.'%")';
		}

		if (!empty($vendor)) {
			$where .= ' AND `m`.`id` IN (SELECT `movie_id` FROM '.$db->quoteName('#__ka_releases').' WHERE `vendor_id` = "'.(int)$vendor.'")';
		}

		if ($mediatype != '') {
			$where .= ' AND `r`.`media_type` = '.(int)$mediatype;
		}

		$where .= " AND `r`.`release_date` != '".$db->nullDate()."'";

		$query->where($where);
		$query->group($db->quoteName('m.id'));

		$orderCol = $this->state->get('list.ordering', $db->quoteName('r.release_date'));
		$orderDirn = $this->state->get('list.direction', 'DESC');
		$query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
	}

	public function getSelectList() {
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();
		$country = $app->input->get('country', '', 'word'); // It's a string because country_id == 0 it'a world premiere
		$year = $app->input->get('year', 0, 'int');
		$month = $app->input->get('month', '', 'string');
		$vendor = $app->input->get('vendor', 0, 'int');
		$mediatype = $app->input->get('mediatype', '', 'string');
		$result = array(
			'countries' => array(
				array('name'=>JText::_('JALL'), 'code'=>'')
			),
			'years' => array(
				array('value'=>0, 'name'=>JText::_('JALL'))
			),
			'months' => array(
				array('value'=>'', 'name'=>JText::_('JALL'))
			),
			'vendors' => array(
				array('value'=>'', 'name'=>JText::_('JALL'))
			),
			'mediatype' => array(
				array('value'=>'', 'name'=>JText::_('JALL'))
			)
		);

		// Countries list
		$db->setQuery("SELECT `name`, `code`"
			. "\n FROM ".$db->quoteName('#__ka_countries')
			. "\n WHERE `id` IN (SELECT `country_id` FROM ".$db->quoteName('#__ka_releases')." WHERE `country_id` != 0) AND `state` = 1"
			. "\n GROUP BY `code`");
		try {
			$countries = $db->loadAssocList();

			if (count($countries) > 0) {
				$result['countries'] = array_merge($result['countries'], $countries);
			}
		} catch (Exception $e) {
			GlobalHelper::eventLog($e->getMessage());
		}

		// Years list
		if ($country !== '') {
			$year_where = " WHERE `country_id` = (SELECT `id` FROM ".$db->quoteName('#__ka_countries')." WHERE `code` = '".$db->escape($country)."' AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*')."))";
		} else {
			$year_where = "";
		}

		$db->setQuery("SELECT DATE_FORMAT(`release_date`, '%Y') AS `value`, DATE_FORMAT(`release_date`, '%Y') AS `name`"
			. "\n FROM ".$db->quoteName('#__ka_releases')
			. $year_where
			. "\n GROUP BY `value`");
		try {
			$years = $db->loadAssocList();

			if (count($years) > 0) {
				$result['years'] = array_merge($result['years'], $years);
			}
		} catch (Exception $e) {
			GlobalHelper::eventLog($e->getMessage());
		}

		// Months list
		if ($country != '') {
			$month_where = " WHERE `country_id` = (SELECT `id` FROM ".$db->quoteName('#__ka_countries')." WHERE `code` = '".$db->escape($country)."' AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*')."))";

			if (!empty($year)) {
				$month_where .= " AND `release_date` LIKE '%".$year."%'";
			}
		} else {
			if (!empty($year)) {
				$month_where = " WHERE `release_date` LIKE '%".$year."%'";
			} else {
				$month_where = "";
			}
		}

		$db->setQuery("SELECT DATE_FORMAT(`release_date`, '%Y-%m') AS `value`, `release_date`"
			. "\n FROM ".$db->quoteName('#__ka_releases')
			. $month_where
			. "\n GROUP BY `value`");
		try {
			$months = $db->loadAssocList();

			if (count($months) > 0) {
				foreach ($months as $key=>$month) {
					$months[$key]['name'] = JHTML::_('date', strtotime($month['release_date']), 'F Y');
				}

				$result['months'] = array_merge($result['months'], $months);
			}
		} catch (Exception $e) {
			GlobalHelper::eventLog($e->getMessage());
		}

		// Distributors list
		$db->setQuery("SELECT `id` AS `value`, `company_name` AS `name`, `company_name_intl`"
			. "\n FROM ".$db->quoteName('#__ka_vendors')
			. "\n WHERE `id` IN (SELECT `vendor_id` FROM ".$db->quoteName('#__ka_releases')." WHERE `vendor_id` != 0 AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').")) AND `state` = 1");
		try {
			$vendors = $db->loadAssocList();

			if (count($vendors) > 0) {
				$result['vendors'] = array_merge($result['vendors'], $vendors);
			}
		} catch (Exception $e) {
			GlobalHelper::eventLog($e->getMessage());
		}

		// Media types
		for ($i=0, $n=20; $i<$n; $i++) {
			$mediatypes[] = array('value'=>$i, 'name'=>JText::_('COM_KA_RELEASES_MEDIATYPE_'.$i));
		}
		$result['mediatype'] = array_merge($result['mediatype'], $mediatypes);

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
}
