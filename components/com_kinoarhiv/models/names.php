<?php defined('_JEXEC') or die;

class KinoarhivModelNames extends JModelList {
	protected $context = null;
	protected $list_limit;

	public function __construct($config = array()) {
		parent::__construct($config);

		if (empty($this->context)) {
			$this->context = strtolower('com_kinoarhiv.names.global');
		}
	}

	protected function populateState($ordering = null, $direction = null) {
		$app = JFactory::getApplication();
		$params = $app->getParams('com_kinoarhiv');

		parent::populateState($params->get('sort_namelist_field'), strtoupper($params->get('sort_namelist_ord')));
	}

	protected function getListQuery() {
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$params = $app->getParams('com_kinoarhiv');

		$query = $db->getQuery(true);

		$query->select("`n`.`id`, `n`.`name`, `n`.`latin_name`, `n`.`alias`, DATE_FORMAT(`n`.`date_of_birth`, '%Y') AS `date_of_birth`, DATE_FORMAT(`n`.`date_of_death`, '%Y') AS `date_of_death`, `n`.`birthplace`, `n`.`gender`, `cn`.`name` AS `country`, `cn`.`code`, `gal`.`filename`, `gal`.`dimension`, GROUP_CONCAT(DISTINCT `g`.`name` SEPARATOR ', ') AS `genres`, GROUP_CONCAT(DISTINCT `cr`.`title` SEPARATOR ', ') AS `career`")
			->from($db->quoteName('#__ka_names').' AS `n`');

		$query->leftJoin($db->quoteName('#__ka_countries').' AS `cn` ON `cn`.`id` = `n`.`birthcountry` AND `cn`.`language` IN ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').') AND `cn`.`state` = 1');

		$query->leftJoin($db->quoteName('#__ka_names_gallery').' AS `gal` ON `gal`.`name_id` = `n`.`id` AND `gal`.`type` = 3 AND `gal`.`photo_frontpage` = 1 AND `gal`.`state` = 1');

		$query->leftJoin($db->quoteName('#__ka_genres').' AS `g` ON `g`.`id` IN (SELECT `genre_id` FROM '.$db->quoteName('#__ka_rel_names_genres').' WHERE `name_id` = `n`.`id`)');

		$query->leftJoin($db->quoteName('#__ka_names_career').' AS `cr` ON `cr`.`id` IN (SELECT `career_id` FROM '.$db->quoteName('#__ka_rel_names_career').' WHERE `name_id` = `n`.`id`)');

		if (!$user->get('guest')) {
			$query->select(' `u`.`favorite`');
			$query->leftJoin($db->quoteName('#__ka_user_marked_names').' AS `u` ON `u`.`uid` = '.$user->get('id').' AND `u`.`name_id` = `n`.`id`');
		}

		$where = '`n`.`state` = 1 AND `n`.`language` IN ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').') AND `n`.`access` IN ('.$groups.')';

		$query->where($where);
		$query->group($db->quoteName('n.id'));

		$orderCol = $this->state->get('list.ordering', '`ordering`');
		$orderDirn = $this->state->get('list.direction', 'DESC');
		$query->order($db->escape('`n`.'.$orderCol.' '.$orderDirn));

		return $query;
	}

	public function favorite() {
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$action = $app->input->get('action', '', 'cmd');
		$name_id = $app->input->get('id', 0, 'int');
		$name_ids = $app->input->get('ids', array(), 'array');

		if (!empty($name_ids)) {
			JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		}

		$itemid = $app->input->get('Itemid', 0, 'int');
		$success = false;
		$url = '';
		$text = '';

		if (empty($name_ids)) {
			$db->setQuery("SELECT `favorite` FROM `#__ka_user_marked_names` WHERE `uid` = ".(int)$user->get('id')." AND `name_id` = ".(int)$name_id);
			$query = $db->loadResult();
		}

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
					$url = JRoute::_('index.php?option=com_kinoarhiv&task=favorite&view=names&action=delete&Itemid='.$itemid.'&id='.$name_id, false);
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
					$url = JRoute::_('index.php?option=com_kinoarhiv&task=favorite&view=names&action=add&Itemid='.$itemid.'&id='.$name_id, false);
					$text = JText::_('COM_KA_ADDTO_FAVORITE');
				} else {
					$message = JText::_('JERROR_ERROR');
				}
			} else {
				if (!empty($name_ids)) {
					$query = true;
					$db->setDebug(true);
					$db->lockTable('#__ka_user_marked_names');
					$db->transactionStart();

					foreach ($name_ids as $id) {
						$db->setQuery("DELETE FROM `#__ka_user_marked_names` WHERE `uid` = ".$user->get('id')." AND `name_id` = ".(int)$id.";");
						$result = $db->execute();

						if ($result === false) {
							$query = false;
							break;
						}
					}

					if ($query === true) {
						$db->transactionCommit();

						$success = true;
						$message = JText::_('COM_KA_FAVORITE_REMOVED');
						$url = JRoute::_('index.php?option=com_kinoarhiv&task=favorite&action=add&Itemid='.$itemid.'&id='.$name_id, false);
						$text = JText::_('COM_KA_ADDTO_FAVORITE');
					} else {
						$db->transactionRollback();

						$message = JText::_('JERROR_ERROR');
					}

					$db->unlockTables();
					$db->setDebug(false);
				} else {
					$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
				}
			}
		} else {
			$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
		}

		return array('success'=>$success, 'message'=>$message, 'url'=>$url, 'text'=>$text);
	}
}
