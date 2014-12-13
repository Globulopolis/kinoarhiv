<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivModelProfile extends JModelList {
	protected function getListQuery() {
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$app = JFactory::getApplication();
		$page = $app->input->get('page', '', 'cmd');

		if ($page == 'favorite') {
			$tab = $app->input->get('tab', '', 'cmd');
			$query = $db->getQuery(true);

			if ($tab == '' || $tab == 'movies') {
				$query->select("`id`, `title`, `alias`, `year`")
					->from($db->quoteName('#__ka_movies'))
					->where('`state` = 1 AND `id` IN (SELECT `movie_id` FROM '.$db->quoteName('#__ka_user_marked_movies').' WHERE `uid` = '.$user->get('id').' AND `favorite` = 1) AND `access` IN ('.$groups.')')
					->order($db->escape('`created` DESC'));
			} elseif ($tab == 'names') {
				$query->select("`id`, `name`, `latin_name`, `alias`, `date_of_birth`")
					->from($db->quoteName('#__ka_names'))
					->where('`state` = 1 AND `id` IN (SELECT `name_id` FROM '.$db->quoteName('#__ka_user_marked_names').' WHERE `uid` = '.$user->get('id').' AND `favorite` = 1) AND `access` IN ('.$groups.')')
					->order($db->escape('`ordering` DESC'));
			}
		} elseif ($page == 'watched') {
			$query = $db->getQuery(true);

			$query->select("`id`, `title`, `alias`, `year`")
				->from($db->quoteName('#__ka_movies'))
				->where('`state` = 1 AND `id` IN (SELECT `movie_id` FROM '.$db->quoteName('#__ka_user_marked_movies').' WHERE `uid` = '.$user->get('id').' AND `watched` = 1) AND `access` IN ('.$groups.')')
				->order($db->escape('`created` DESC'));
		} elseif ($page == 'votes') {
			$query = $db->getQuery(true);

			$query->select("`m`.`id`, `m`.`title`, `m`.`alias`, `m`.`rate_loc`, `m`.`rate_sum_loc`, `m`.`year`, (SELECT COUNT(`uid`) FROM ".$db->quoteName('#__ka_user_votes')." WHERE `movie_id` = `m`.`id`) AS `total_voted`")
				->from($db->quoteName('#__ka_movies')." AS `m`");

			// Join over user votes
			$query->select(" `v`.`vote` AS `my_vote`, `v`.`_datetime`")
				->leftJoin($db->quoteName('#__ka_user_votes')." AS `v` ON `v`.`uid` = ".(int)$user->get('id')." AND `v`.`movie_id` = `m`.`id`");

			$query->where("`state` = 1 AND `id` IN (SELECT `movie_id` FROM ".$db->quoteName('#__ka_user_votes')." WHERE `uid` = ".$user->get('id').") AND `access` IN (".$groups.")");
			$query->order($db->escape('`_datetime` DESC'));
		} elseif ($page == 'reviews') {
			$query = $db->getQuery(true);

			$query->select("`r`.`id`, `r`.`movie_id`, `r`.`review`, `r`.`created`, `r`.`type`, `r`.`ip`, `r`.`state`, `m`.`title`, `m`.`year`");
			$query->from($db->quoteName('#__ka_reviews')." AS `r`");
			$query->leftJoin($db->quoteName('#__ka_movies')." AS `m` ON `m`.`id` = `r`.`movie_id`");
			$query->where('`r`.`uid` = '.(int)$user->get('id').' AND `m`.`state` = 1');
			$query->order($db->escape('`created` DESC'));
		} else {
			$query = null;
		}

		return $query;
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
