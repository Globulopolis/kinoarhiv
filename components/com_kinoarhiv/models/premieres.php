<?php defined('_JEXEC') or die;

class KinoarhivModelPremieres extends JModelList {
	protected $context = null;

	public function __construct($config = array()) {
		parent::__construct($config);

		if (empty($this->context)) {
			$input = JFactory::getApplication()->input;
			$page = $input->get('page', 'global');

			$this->context = strtolower($this->option.'.'.$this->getName().'.'.$page);
		}
	}

	protected function getListQuery() {
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$groups	= implode(',', $user->getAuthorisedViewLevels());
		$app = JFactory::getApplication();
		$params = $app->getParams('com_kinoarhiv');

		$query = $db->getQuery(true);

		$query->select("`m`.`id`, `m`.`parent_id`, `m`.`title`, `m`.`alias`, `m`.`introtext` AS `text`, `m`.`plot`, `m`.`rate_loc`, `m`.`rate_sum_loc`, `m`.`imdb_votesum`, `m`.`imdb_votes`, `m`.`imdb_id`, `m`.`kp_votesum`, `m`.`kp_votes`, `m`.`kp_id`, `m`.`rottentm_id`, `m`.`rate_custom`, `m`.`year`, DATE_FORMAT(`m`.`created`, '%Y-%m-%d') AS `created`, DATE_FORMAT(`m`.`modified`, '%Y-%m-%d') AS `modified`, `m`.`created_by`, `m`.`state`, `g`.`filename`, `g`.`dimension`");
		$query->from($db->quoteName('#__ka_movies').' AS `m`');
		$query->leftJoin($db->quoteName('#__ka_movies_gallery').' AS `g` ON `g`.`movie_id` = `m`.`id` AND `g`.`type` = 2 AND `g`.`poster_frontpage` = 1 AND `g`.`state` = 1');

		if (!$user->get('guest')) {
			$query->select('`u`.`favorite`');
			$query->leftJoin($db->quoteName('#__ka_user_marked_movies').' AS `u` ON `u`.`uid` = '.$user->get('id').' AND `u`.`movie_id` = `m`.`id`');
		}

		$where = '`m`.`state` = 1 AND `language` IN ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').') AND `parent_id` = 0 AND `m`.`access` IN ('.$groups.')';

		$query->where($where);

		$orderCol = $this->state->get('list.ordering', 'm.ordering');
		$orderDirn = $this->state->get('list.direction', 'desc');
		$query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
	}

	public function getSelectList() {
		$db = $this->getDBO();
		$result = array(
			'countries'=>array(), 'years'=>array(), 'months'=>array()
		);

		$db->setQuery("SELECT `name`, `code`"
			. "\n FROM ".$db->quoteName('#__ka_countries')
			. "\n WHERE `id` IN (SELECT `country_id` FROM ".$db->quoteName('#__ka_premieres')." WHERE `country_id` != 0)"
			. "\n GROUP BY `code`");
		$result['countries'] = $db->loadObjectList();

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
