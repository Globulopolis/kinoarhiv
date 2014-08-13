<?php defined('_JEXEC') or die;

class KinoarhivModelGlobal extends JModelLegacy {
	public function getAjaxData($element='') {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$element = !empty($element) ? $element : $app->input->get('element', '', 'string');
		$all = $app->input->get('showAll', 0, 'int');
		$term = $app->input->get('term', '', 'string');
		$id = $app->input->get('id', 0, 'int');
		$lang = JFactory::getLanguage();
		$user = JFactory::getUser();
		$groups = implode(',', $user->getAuthorisedViewLevels());

		if ($element == 'countries') {
			// Do not remove `code` field from the query. It's necessary for flagging row in select
			if (empty($all)) {
				if (empty($id)) {
					if (empty($term)) return array();
					$db->setQuery("SELECT `id`, `name` AS `title`, `code` FROM ".$db->quoteName('#__ka_countries')." WHERE `name` LIKE '".$db->escape($term)."%'"." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `state` = 1");
					$result = $db->loadObjectList();
				} else {
					$db->setQuery("SELECT `id`, `name` AS `title`, `code` FROM ".$db->quoteName('#__ka_countries')." WHERE `id` = ".(int)$id." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `state` = 1");
					$result = $db->loadObject();
				}
			} else {
				$db->setQuery("SELECT `id`, `name` AS `title`, `code` FROM ".$db->quoteName('#__ka_countries')." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `state` = 1");
				$result = $db->loadObjectList();
			}
		} elseif ($element == 'genres') {
			if (empty($all)) {
				if (empty($id)) {
					if (empty($term)) return array();
					$db->setQuery("SELECT `id`, `name` AS `title` FROM ".$db->quoteName('#__ka_genres')." WHERE `name` LIKE '".$db->escape($term)."%'"." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `state` = 1 AND `access` IN (".$groups.")");
					$result = $db->loadObjectList();
				} else {
					$db->setQuery("SELECT `id`, `name` AS `title` FROM ".$db->quoteName('#__ka_genres')." WHERE `id` = ".(int)$id." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `state` = 1 AND `access` IN (".$groups.")");
					$result = $db->loadObject();
				}
			} else {
				$db->setQuery("SELECT `id`, `name` AS `title` FROM ".$db->quoteName('#__ka_genres')." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `state` = 1 AND `access` IN (".$groups.")");
				$result = $db->loadObjectList();
			}
		} elseif ($element == 'movies') {
			if (empty($all)) {
				if (empty($id)) {
					$db->setQuery("SELECT `id`, `title`, `year` FROM ".$db->quoteName('#__ka_movies')." WHERE `title` LIKE '".$db->escape($term)."%'"." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `state` = 1 AND `access` IN (".$groups.")");
					$result = $db->loadObjectList();
				} else {
					$db->setQuery("SELECT `id`, `title`, `year` FROM ".$db->quoteName('#__ka_movies')." WHERE `id` = ".(int)$id." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `state` = 1 AND `access` IN (".$groups.")");
					$result = $db->loadObject();
				}
			} else {
				$db->setQuery("SELECT `id`, `title`, `year` FROM ".$db->quoteName('#__ka_movies')." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `state` = 1 AND `access` IN (".$groups.")");
				$result = $db->loadObjectList();
			}
		} elseif ($element == 'awards') {
			$type = $app->input->get('type', -1, 'int');

			if ($type == 0) {
				$result = $this->getAjaxData('movies');
			} elseif ($type == 1) {
				$result = $this->getAjaxData('names');
			} else {
				if (empty($all)) {
					if (empty($id)) {
						$db->setQuery("SELECT `id`, `title` FROM ".$db->quoteName('#__ka_awards')." WHERE `title` LIKE '".$db->escape($term)."%'"." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `state` = 1");
						$result = $db->loadObjectList();
					} else {
						$db->setQuery("SELECT `id`, `title` FROM ".$db->quoteName('#__ka_awards')." WHERE `id` = ".(int)$id." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `state` = 1");
						$result = $db->loadObject();
					}
				} else {
					$db->setQuery("SELECT `id`, `title` FROM ".$db->quoteName('#__ka_awards')." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `state` = 1");
					$result = $db->loadObjectList();
				}
			}
		} elseif ($element == 'names') {
			if (empty($all)) {
				if (empty($id)) {
					$db->setQuery("SELECT `id`, `name`, `latin_name`, `date_of_birth` FROM ".$db->quoteName('#__ka_names')." WHERE (`name` LIKE '".$db->escape($term)."%' OR `latin_name` LIKE '".$db->escape($term)."%')"." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `state` = 1 AND `access` IN (".$groups.")");
					$result = $db->loadObjectList();
				} else {
					$db->setQuery("SELECT `id`, `name`, `latin_name`, `date_of_birth` FROM ".$db->quoteName('#__ka_names')." WHERE `id` = ".(int)$id." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `state` = 1 AND `access` IN (".$groups.")");
					$result = $db->loadObject();
				}
			} else {
				$db->setQuery("SELECT `id`, `name`, `latin_name`, `date_of_birth` FROM ".$db->quoteName('#__ka_names')." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `state` = 1 AND `access` IN (".$groups.")");
				$result = $db->loadObjectList();
			}
		} elseif ($element == 'tags') {
			if (empty($all)) {
				if (empty($id)) {
					$db->setQuery("SELECT `id`, `title` FROM ".$db->quoteName('#__tags')." WHERE `id` IN (".$db->escape($term).") AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `published` = 1 AND `access` IN (".$groups.")");
					$result = $db->loadObjectList();
				} else {
					$db->setQuery("SELECT `id`, `title` FROM ".$db->quoteName('#__tags')." WHERE `title` LIKE '".$db->escape($term)."%' AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `published` = 1 AND `access` IN (".$groups.")");
					$result = $db->loadObject();
				}
			} else {
				$db->setQuery("SELECT `id`, `title` FROM ".$db->quoteName('#__tags')." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `published` = 1 AND `access` IN (".$groups.")");
				$result = $db->loadObjectList();
			}
		} elseif ($element == 'career' || $element == 'careers') {
			if (empty($all)) {
				if (empty($id)) {
					$db->setQuery("SELECT `id`, `title` FROM ".$db->quoteName('#__ka_names_career')." WHERE `title` LIKE '".$db->escape($term)."%'"." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').")");
					$result = $db->loadObjectList();
				} else {
					$db->setQuery("SELECT `id`, `title` FROM ".$db->quoteName('#__ka_names_career')." WHERE `id` = ".(int)$id." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').")");
					$result = $db->loadObject();
				}
			} else {
				$db->setQuery("SELECT `id`, `title` FROM ".$db->quoteName('#__ka_names_career')." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').")");
				$result = $db->loadObjectList();
			}
		} elseif ($element == 'vendors') {
			if (empty($all)) {
				if (empty($id)) {
					$db->setQuery("SELECT `id`, `company_name`, `company_name_intl` FROM ".$db->quoteName('#__ka_vendors')." WHERE `company_name` LIKE '".$db->escape($term)."%' OR `company_name_intl` LIKE '".$db->escape($term)."%'"." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `state` = 1");
					$result = $db->loadObjectList();
				} else {
					$db->setQuery("SELECT `id`, `company_name`, `company_name_intl` FROM ".$db->quoteName('#__ka_vendors')." WHERE `id` = ".(int)$id." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `state` = 1");
					$result = $db->loadObject();
				}
			} else {
				$db->setQuery("SELECT `id`, `company_name`, `company_name_intl` FROM ".$db->quoteName('#__ka_vendors')." AND `language` IN (".$db->quote($lang->getTag()).",".$db->quote('*').") AND `state` = 1");
				$result = $db->loadObjectList();
			}
		} else {
			$result = array();
		}

		return $result;
	}
}
