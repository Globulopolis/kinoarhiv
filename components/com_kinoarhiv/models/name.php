<?php defined('_JEXEC') or die;

class KinoarhivModelName extends JModelList {
	protected $context = null;

	public function __construct($config = array()) {
		parent::__construct($config);

		$input = JFactory::getApplication()->input;
		$page = $input->get('page', 'global');
		$this->context = strtolower($this->option.'.'.$this->getName().'.'.$page);
	}

	public function getData() {
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$params = $app->getParams('com_kinoarhiv');
		$id = $app->input->get('id', 0, 'int');

		$query = $db->getQuery(true);

		$query->select("`n`.`id`, `n`.`name`, `n`.`latin_name`, `n`.`alias`, DATE_FORMAT(`n`.`date_of_birth`, '%Y') AS `date_of_birth`, `n`.`date_of_birth` AS `date_of_birth_raw`, DATE_FORMAT(`n`.`date_of_death`, '%Y') AS `date_of_death`, `n`.`date_of_death` AS `date_of_death_raw`, `n`.`birthplace`, `n`.`gender`, `n`.`height`, `n`.`desc`, `n`.`metakey`, `n`.`metadesc`, `n`.`metadata`, `cn`.`name` AS `country`, `cn`.`code`, `g`.`filename`");
		$query->from($db->quoteName('#__ka_names').' AS `n`');
		$query->leftJoin($db->quoteName('#__ka_names_gallery').' AS `g` ON `g`.`name_id` = `n`.`id` AND `g`.`type` = 3 AND `g`.`photo_frontpage` = 1 AND `g`.`state` = 1');
		$query->leftJoin($db->quoteName('#__ka_countries').' AS `cn` ON `cn`.`id` = `n`.`birthcountry` AND `cn`.`state` = 1');

		if (!$user->get('guest')) {
			$query->select('`u`.`favorite`');
			$query->leftJoin($db->quoteName('#__ka_user_marked_names').' AS `u` ON `u`.`uid` = '.$user->get('id').' AND `u`.`name_id` = `n`.`id`');
		}

		$query->where('`n`.`id` = '.(int)$id.' AND `n`.`state` = 1 AND `access` IN ('.$groups.') AND `n`.`language` IN ('.$db->quote($lang->getTag()).','.$db->quote('*').')');

		$db->setQuery($query);
		$result = $db->loadObject();

		$result->zodiac = ($result->date_of_birth_raw != '0000-00-00') ? $this->getZodiacSign(substr($result->date_of_birth_raw, 5, 2), substr($result->date_of_birth_raw, 8, 2)) : '';

		// Select career
		$db->setQuery("SELECT `id`, `title`"
			. "\n FROM ".$db->quoteName('#__ka_names_career')
			. "\n WHERE `id` IN (SELECT `career_id` FROM ".$db->quoteName('#__ka_rel_names_career')." WHERE `name_id` = ".(int)$id.") AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').")"
			. "\n ORDER BY `title` ASC");
		$result->career = $db->loadObjectList();

		// Select genres
		$db->setQuery("SELECT `id`, `name`, `alias`"
			. "\n FROM ".$db->quoteName('#__ka_genres')
			. "\n WHERE `id` IN (SELECT `genre_id` FROM ".$db->quoteName('#__ka_rel_names_genres')." WHERE `name_id` = ".(int)$id.") AND `state` = 1 AND `access` IN (".$groups.") AND `language` IN (".$db->quote($lang->getTag()).','.$db->quote('*').")"
			. "\n ORDER BY `name` ASC");
		$result->genres = $db->loadObjectList();

		// Select movies
		$db->setQuery("SELECT `m`.`id`, `m`.`title`, `m`.`alias`, `m`.`year`, `r`.`role`"
			. "\n FROM ".$db->quoteName('#__ka_movies')." AS `m`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_rel_names')." AS `r` ON `r`.`name_id` = ".(int)$id
			. "\n WHERE `id` IN (SELECT `movie_id` FROM ".$db->quoteName('#__ka_rel_names')." WHERE `name_id` = ".(int)$id.") AND `m`.`state` = 1 AND `access` IN (".$groups.") AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').")"
			. "\n ORDER BY `year` ASC");
		$result->movies = $db->loadObjectList();

		return $result;
	}

	public function getZodiacSign($month, $day) {
		if ($day > 31 || $day < 0) return;
		if ($month > 12 || $month < 0) return;

		if ($month == 1) {
			$zodiac = ($day <= 20) ? 'capricorn' : 'aquarius';
		} elseif ($month == 2) {
			if ($day > 29) return;
			$zodiac = ($day <= 18) ? 'aquarius' : 'pisces';
		} elseif ($month == 3) {
			$zodiac = ($day <= 20) ? 'pisces' : 'aries';
		} elseif ($month == 4) {
			if ($day > 30) return;
			$zodiac = ($day <= 20) ? 'aries' : 'taurus';
		} elseif ($month == 5) {
			$zodiac = ($day <= 21) ? 'taurus' : 'gemini';
		} elseif ($month == 6) {
			if ($day > 30) return;
			$zodiac = ($day <= 22) ? 'gemini' : 'cancer';
		} elseif ($month == 7) {
			$zodiac = ($day <= 22) ? 'cancer' : 'leo';
		} elseif ($month == 8) {
			$zodiac = ($day <=21) ? 'leo' : 'virgo';
		} elseif ($month == 9) {
			if ($day > 30) return;
			$zodiac = ($day <= 23) ? 'virgo' : 'libra';
		} elseif ($month == 10) {
			$zodiac = ($day <=23) ? 'libra' : 'scorpio';
		} elseif ($month == 11) {
			if ($day > 30) return;
			$zodiac = ($day <= 21) ? 'scorpio' : 'sagittarius';
		} elseif ($month == 12) {
			$zodiac = ($day <= 22) ? 'sagittarius' : 'capricorn';
		}

		return $zodiac;
	}

	public function getNameData() {
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$lang = JFactory::getLanguage();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$id = $app->input->get('id', 0, 'int');

		$db->setQuery("SELECT `id`, `name`, `latin_name`, `alias`, `metakey`, `metadesc`, `metadata`"
			. "\n FROM ".$db->quoteName('#__ka_names')
			. "\n WHERE `id` = ".(int)$id." AND `state` = 1 AND `access` IN (".$groups.") AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').")");
		$result = $db->loadObject();

		return $result;
	}

	public function getAwards() {
		$db = $this->getDBO();
		$app = JFactory::getApplication();
		$id = $app->input->get('id', 0, 'int');

		$result = $this->getNameData();

		$db->setQuery("SELECT `a`.`desc`, `a`.`year`, `aw`.`title` AS `aw_title`, `aw`.`desc` AS `aw_desc`"
			. "\n FROM ".$db->quoteName('#__ka_rel_awards')." AS `a`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_awards')." AS `aw` ON `aw`.`id` = `a`.`award_id`"
			. "\n WHERE `type` = 1 AND `item_id` = ".(int)$id
			. "\n ORDER BY `year` ASC");
		$result->awards = $db->loadObjectList();

		return $result;
	}

	/**
	 * Build list of filters by dimensions for gallery
	 *
	 * @return  array
	 *
	*/
	public function getDimensionFilters() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$page = $app->input->get('page', null, 'cmd');
		$filter = $app->input->get('dim_filter', 0, 'string');

		if ($page == 'wallpapers') {
			$db->setQuery("SELECT `dimension` AS `value`, `dimension` AS `title`, SUBSTRING_INDEX(`dimension`, 'x', 1) AS `width`"
				. "\n FROM ".$db->quoteName('#__ka_names_gallery')
				. "\n WHERE `type` = 1"
				. "\n GROUP BY `width`"
				. "\n ORDER BY `width` DESC");
			$result = $db->loadAssocList();
		}

		return $result;
	}

	protected function getListQuery() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', 0, 'int');
		$page = $app->input->get('page', '', 'cmd');
		$filter = $app->input->get('dim_filter', '0', 'string');

		$query = $db->getQuery(true);

		if ($page == 'wallpapers') {
			$query->select('`id`, `filename`, `dimension`');
			$query->from($db->quoteName('#__ka_names_gallery'));

			if ($filter != '0') {
				$where = " AND `dimension` LIKE ".$db->quote($db->escape($filter, true)."%", false);
			} else {
				$where = "";
			}

			$query->where('`name_id` = '.(int)$id.' AND `state` = 1 AND `type` = 1'.$where);
		} elseif ($page == 'posters') {
			$query->select('`id`, `filename`, `dimension`');
			$query->from($db->quoteName('#__ka_names_gallery'));
			$query->where('`name_id` = '.(int)$id.' AND `state` = 1 AND `type` = 2');
		} elseif ($page == 'photos') {
			$query->select('`id`, `filename`, `dimension`');
			$query->from($db->quoteName('#__ka_names_gallery'));
			$query->where('`name_id` = '.(int)$id.' AND `state` = 1 AND `type` = 3');
		} else {
			$query = null;
		}

		return $query;
	}

	public function getPagination() {
		JLoader::register('KAPagination', JPATH_COMPONENT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'pagination.php');

		$app = JFactory::getApplication();
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

			$value = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $app->getCfg('list_limit'), 'uint');
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

	public function getUserStateFromRequest($key, $request, $default = null, $type = 'none', $resetPage = true) {
		$app = JFactory::getApplication();
		$old_state = $app->getUserState($key);
		$cur_state = (!is_null($old_state)) ? $old_state : $default;
		$new_state = $app->input->get($request, null, $type);

		if (($cur_state != $new_state) && ($resetPage)) {
			$app->input->set('limitstart', 0);
		}

		if ($new_state !== null) {
			$app->setUserState($key, $new_state);
		} else {
			$new_state = $cur_state;
		}

		return $new_state;
	}

	/*public function favorite() {
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$action = $app->input->get('action', '', 'cmd');
		$name_id = $app->input->get('id', 0, 'int');
		$itemid = $app->input->get('Itemid', 0, 'int');
		$success = false;
		$url = '';
		$text = '';

		$db->setQuery("SELECT `favorite` FROM `#__ka_user_marked_names` WHERE `uid` = ".(int)$user->get('id')." AND `name_id` = ".(int)$name_id);
		$query = $db->loadResult();

		if ($action == 'add') {
			if ($query == 1) {
				$message = JText::_('COM_KA_FAVORITE_ERROR');
			} else {
				if (is_null($query)) {
					$db->setQuery("INSERT INTO `#__ka_user_marked_names` (`uid`, `name_id`, `favorite`) VALUES ('".$user->get('id')."', '".(int)$name_id."', '1')");
				} elseif ($query == 0) {
					$db->setQuery("UPDATE `#__ka_user_marked_names` SET `favorite` = '1' WHERE `uid` = ".$user->get('id')." AND `name_id` = ".(int)$name_id);
				}

				$r = $db->execute();

				if ($r) {
					$success = true;
					$message = JText::_('COM_KA_FAVORITE_ADDED');
					$url = JRoute::_('index.php?option=com_kinoarhiv&view=name&task=favorite&action=delete&Itemid='.$itemid.'&id='.$name_id, false);
					$text = JText::_('COM_KA_REMOVEFROM_FAVORITE');
				} else {
					$message = JText::_('JERROR_ERROR');
				}
			}
		} elseif ($action == 'delete') {
			if ($query == 1) {
				$db->setQuery("DELETE FROM `#__ka_user_marked_names` WHERE `uid` = ".$user->get('id')." AND `name_id` = ".(int)$name_id);
				$r = $db->execute();

				if ($r) {
					$success = true;
					$message = JText::_('COM_KA_FAVORITE_REMOVED');
					$url = JRoute::_('index.php?option=com_kinoarhiv&view=name&task=favorite&action=add&Itemid='.$itemid.'&id='.$name_id, false);
					$text = JText::_('COM_KA_ADDTO_FAVORITE');
				} else {
					$message = JText::_('JERROR_ERROR');
				}
			} else {
				$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
			}
		} else {
			$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
		}

		return array('success'=>$success, 'message'=>$message, 'url'=>$url, 'text'=>$text);
	}*/
}
