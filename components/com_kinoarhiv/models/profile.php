<?php defined('_JEXEC') or die;

class KinoarhivModelProfile extends JModelList {
	protected function getListQuery() {
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$app = JFactory::getApplication();
		$tab = $app->input->get('tab', '', 'cmd');

		if ($tab == 'favorite') {
			$page = $app->input->get('page', '', 'cmd');
			$query = $db->getQuery(true);

			if ($page == '' || $page == 'movies') {
				$query->select("`id`, `title`, `alias`, `year`");
				$query->from($db->quoteName('#__ka_movies'));
				$query->where('`state` = 1 AND `id` IN (SELECT `movie_id` FROM '.$db->quoteName('#__ka_user_marked_movies').' WHERE `uid` = '.$user->get('id').' AND `favorite` = 1) AND `access` IN ('.$groups.')');
				$query->order($db->escape('`created` DESC'));
			} elseif ($page == 'names') {
				$query->select("`id`, `name`, `latin_name`, `alias`, `date_of_birth`");
				$query->from($db->quoteName('#__ka_names'));
				$query->where('`state` = 1 AND `id` IN (SELECT `name_id` FROM '.$db->quoteName('#__ka_user_marked_names').' WHERE `uid` = '.$user->get('id').' AND `favorite` = 1) AND `access` IN ('.$groups.')');
				$query->order($db->escape('`ordering` DESC'));
			}
		} elseif ($tab == 'watched') {
			$query = $db->getQuery(true);

			$query->select("`id`, `title`, `alias`, `year`");
			$query->from($db->quoteName('#__ka_movies'));
			$query->where('`state` = 1 AND `id` IN (SELECT `movie_id` FROM '.$db->quoteName('#__ka_user_marked_movies').' WHERE `uid` = '.$user->get('id').' AND `watched` = 1) AND `access` IN ('.$groups.')');
			$query->order($db->escape('`created` DESC'));
		} elseif ($tab == 'votes') {
			$query = $db->getQuery(true);

			$query->select("`m`.`id`, `m`.`title`, `m`.`alias`, `m`.`rate_loc`, `m`.`rate_sum_loc`, `m`.`year`, (SELECT COUNT(`uid`) FROM ".$db->quoteName('#__ka_user_votes')." WHERE `movie_id` = `m`.`id`) AS `total_voted`");
			$query->from($db->quoteName('#__ka_movies')." AS `m`");

			// Join over user votes
			$query->select(" `v`.`vote` AS `my_vote`, `v`.`_datetime`");
			$query->leftJoin($db->quoteName('#__ka_user_votes')." AS `v` ON `v`.`uid` = ".(int)$user->get('id')." AND `v`.`movie_id` = `m`.`id`");

			$query->where("`state` = 1 AND `id` IN (SELECT `movie_id` FROM ".$db->quoteName('#__ka_user_votes')." WHERE `uid` = ".$user->get('id').") AND `access` IN (".$groups.")");
			$query->order($db->escape('`_datetime` DESC'));
		} elseif ($tab == 'reviews') {
			$query = $db->getQuery(true);

			$query->select("`r`.`id`, `r`.`movie_id`, `r`.`review`, `r`.`r_datetime`, `r`.`type`, `r`.`ip`, `m`.`title`, `m`.`year`");
			$query->from($db->quoteName('#__ka_reviews')." AS `r`");
			$query->leftJoin($db->quoteName('#__ka_movies')." AS `m` ON `m`.`id` = `r`.`movie_id`");
			$query->where('`r`.`state` = 1 AND `r`.`uid` = '.(int)$user->get('id'));
			$query->order($db->escape('`r_datetime` DESC'));
		} else {
			$query = null;
		}

		return $query;
	}
}
