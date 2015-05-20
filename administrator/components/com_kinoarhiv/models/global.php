<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivModelGlobal extends JModelLegacy {
	public function getAjaxData($element='') {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$element = !empty($element) ? $element : $app->input->get('element', '', 'string');
		$all = $app->input->get('showAll', 0, 'int');
		$term = $app->input->get('term', '', 'string');
		$id = $app->input->get('id', 0, 'int');
		$lang = $app->input->get('lang', '', 'string');
		$ignore = $app->input->get('ignore', array(), 'array');

		if ($element == 'countries') {
			// Do not remove `code` field from the query. It's necessary for flagging row in select
			if (empty($all)) {
				$where_lang = !empty($lang) ? " AND `language` = '".$db->escape($lang)."'" : "";

				if (empty($id)) {
					if (empty($term)) return array();
					$db->setQuery("SELECT `id`, `name` AS `title`, `code` FROM ".$db->quoteName('#__ka_countries')." WHERE `name` LIKE '%".$db->escape($term)."%'".$where_lang);
					$result = $db->loadObjectList();
				} else {
					$db->setQuery("SELECT `id`, `name` AS `title`, `code` FROM ".$db->quoteName('#__ka_countries')." WHERE `id` = ".(int)$id.$where_lang);
					$result = $db->loadObject();
				}
			} else {
				$where_lang = !empty($lang) ? " WHERE `language` = '".$db->escape($lang)."'" : "";

				$db->setQuery("SELECT `id`, `name` AS `title`, `code` FROM ".$db->quoteName('#__ka_countries').$where_lang);
				$result = $db->loadObjectList();
			}
		} elseif ($element == 'genres') {
			$table = ($app->input->get('type', 'movie', 'word') == 'movie') ? '#__ka_genres' : '#__ka_music_genres';

			if (empty($all)) {
				$where_lang = !empty($lang) ? " AND `language` = '".$db->escape($lang)."'" : "";

				if (empty($id)) {
					if (empty($term)) return array();
					$db->setQuery("SELECT `id`, `name` AS `title` FROM ".$db->quoteName($table)." WHERE `name` LIKE '%".$db->escape($term)."%'".$where_lang);
					$result = $db->loadObjectList();
				} else {
					$db->setQuery("SELECT `id`, `name` AS `title` FROM ".$db->quoteName($table)." WHERE `id` = ".(int)$id.$where_lang);
					$result = $db->loadObject();
				}
			} else {
				$where_lang = !empty($lang) ? " WHERE `language` = '".$db->escape($lang)."'" : "";

				$db->setQuery("SELECT `id`, `name` AS `title` FROM ".$db->quoteName($table).$where_lang);
				$result = $db->loadObjectList();
			}
		} elseif ($element == 'movies') {
			if (!empty($ignore)) {
				$ignored = " AND `id` NOT IN (".implode(',', $ignore).")";
			} else {
				$ignored = "";
			}

			if (empty($all)) {
				$where_lang = !empty($lang) ? " AND `language` = '".$db->escape($lang)."'" : "";

				if (empty($id)) {
					$db->setQuery("SELECT `id`, `title`, `year` FROM ".$db->quoteName('#__ka_movies')." WHERE `title` LIKE '%".$db->escape($term)."%'".$where_lang.$ignored);
					$result = $db->loadObjectList();
				} else {
					$db->setQuery("SELECT `id`, `title`, `year` FROM ".$db->quoteName('#__ka_movies')." WHERE `id` = ".(int)$id.$where_lang.$ignored);
					$result = $db->loadObject();
				}
			} else {
				$where_lang = !empty($lang) ? " WHERE `language` = '".$db->escape($lang)."'" : "";

				$db->setQuery("SELECT `id`, `title`, `year` FROM ".$db->quoteName('#__ka_movies').$where_lang.$ignored);
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
					$where_lang = !empty($lang) ? " AND `language` = '".$db->escape($lang)."'" : "";

					if (empty($id)) {
						$db->setQuery("SELECT `id`, `title` FROM ".$db->quoteName('#__ka_awards')." WHERE `title` LIKE '%".$db->escape($term)."%'".$where_lang);
						$result = $db->loadObjectList();
					} else {
						$db->setQuery("SELECT `id`, `title` FROM ".$db->quoteName('#__ka_awards')." WHERE `id` = ".(int)$id.$where_lang);
						$result = $db->loadObject();
					}
				} else {
					$where_lang = !empty($lang) ? " WHERE `language` = '".$db->escape($lang)."'" : "";

					$db->setQuery("SELECT `id`, `title` FROM ".$db->quoteName('#__ka_awards').$where_lang);
					$result = $db->loadObjectList();
				}
			}
		} elseif ($element == 'names') {
			if (!empty($ignore)) {
				$ignored = " AND `id` NOT IN (".implode(',', $ignore).")";
			} else {
				$ignored = "";
			}

			if (empty($all)) {
				$where_lang = !empty($lang) ? " AND `language` = '".$db->escape($lang)."'" : "";

				if (empty($id)) {
					$db->setQuery("SELECT `id`, `name`, `latin_name`, `date_of_birth` FROM ".$db->quoteName('#__ka_names')." WHERE (`name` LIKE '%".$db->escape($term)."%' OR `latin_name` LIKE '%".$db->escape($term)."%')".$where_lang.$ignored);
					$result = $db->loadObjectList();
				} else {
					$db->setQuery("SELECT `id`, `name`, `latin_name`, `date_of_birth` FROM ".$db->quoteName('#__ka_names')." WHERE `id` = ".(int)$id.$where_lang.$ignored);
					$result = $db->loadObject();
				}
			} else {
				$where_lang = !empty($lang) ? " WHERE `language` = '".$db->escape($lang)."'" : "";

				$db->setQuery("SELECT `id`, `name`, `latin_name`, `date_of_birth` FROM ".$db->quoteName('#__ka_names').$where_lang.$ignored);
				$result = $db->loadObjectList();
			}
		} elseif ($element == 'tags') {
			if (empty($all)) {
				$where_lang = !empty($lang) ? " AND `language` = '".$db->escape($lang)."'" : "";

				if (empty($id)) {
					$db->setQuery("SELECT `id`, `title` FROM ".$db->quoteName('#__tags')." WHERE `title` LIKE '%".$db->escape($term)."%' AND `title` != 'ROOT'".$where_lang);
					$result = $db->loadObjectList();
				} else {
					$db->setQuery("SELECT `id`, `title` FROM ".$db->quoteName('#__tags')." WHERE `id` = ".(int)$id.$where_lang);
					$result = $db->loadObject();
				}
			} else {
				$where_lang = !empty($lang) ? " WHERE `language` = '".$db->escape($lang)."'" : "";

				$db->setQuery("SELECT `id`, `title` FROM ".$db->quoteName('#__tags').$where_lang);
				$result = $db->loadObjectList();
			}
		} elseif ($element == 'career' || $element == 'careers') {
			if (empty($all)) {
				$where_lang = !empty($lang) ? " AND `language` = '".$db->escape($lang)."'" : "";

				if (empty($id)) {
					$db->setQuery("SELECT `id`, `title` FROM ".$db->quoteName('#__ka_names_career')." WHERE `title` LIKE '%".$db->escape($term)."%'".$where_lang);
					$result = $db->loadObjectList();
				} else {
					$db->setQuery("SELECT `id`, `title` FROM ".$db->quoteName('#__ka_names_career')." WHERE `id` = ".(int)$id.$where_lang);
					$result = $db->loadObject();
				}
			} else {
				$where_lang = !empty($lang) ? " WHERE `language` = '".$db->escape($lang)."'" : "";

				$db->setQuery("SELECT `id`, `title` FROM ".$db->quoteName('#__ka_names_career').$where_lang);
				$result = $db->loadObjectList();
			}
		} elseif ($element == 'trailer_files') {
			$type = $app->input->get('type', '', 'string');
			if (empty($type)) {
				$result = array();
			} else {
				if ($type == 'video') {
					$col = '`filename`';
				} elseif ($type == 'subtitles') {
					$col = '`_subtitles`';
				} elseif ($type == 'chapters') {
					$col = '`_chapters`';
				}

				$db->setQuery("SELECT ".$col." FROM ".$db->quoteName('#__ka_trailers')." WHERE `id` = ".(int)$id);
				$result = $db->loadResult();

				if (!empty($result)) {
					$result = json_decode($result);
				} else {
					$result = JText::_('COM_KA_NO_ITEMS');
				}
			}
		} elseif ($element == 'vendors') {
			if (empty($all)) {
				$where_lang = !empty($lang) ? " AND `language` = '".$db->escape($lang)."'" : "";

				if (empty($id)) {
					$db->setQuery("SELECT `id`, `company_name`, `company_name_intl` FROM ".$db->quoteName('#__ka_vendors')." WHERE `company_name` LIKE '%".$db->escape($term)."%' OR `company_name_intl` LIKE '".$db->escape($term)."%'".$where_lang);
					$result = $db->loadObjectList();
				} else {
					$db->setQuery("SELECT `id`, `company_name`, `company_name_intl` FROM ".$db->quoteName('#__ka_vendors')." WHERE `id` = ".(int)$id.$where_lang);
					$result = $db->loadObject();
				}
			} else {
				$where_lang = !empty($lang) ? " WHERE `language` = '".$db->escape($lang)."'" : "";

				$db->setQuery("SELECT `id`, `company_name`, `company_name_intl` FROM ".$db->quoteName('#__ka_vendors').$where_lang);
				$result = $db->loadObjectList();
			}
		} else {
			$result = array();
		}

		return $result;
	}
}
