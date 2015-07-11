<?php defined('_JEXEC') or die;

/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */
class KinoarhivModelRelease extends JModelItem
{
	protected $_context = 'com_kinoarhiv.release';

	protected function populateState()
	{
		$app = JFactory::getApplication();
		$pk = $app->input->getInt('id');
		$this->setState('release.id', $pk);
	}

	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int)$this->getState('release.id');

		if ($this->_item === null) {
			$this->_item = array();
		}

		if (!isset($this->_item[$pk])) {
			$user = JFactory::getUser();
			$groups = $user->getAuthorisedViewLevels();
			$data = (object)array('rows' => array());

			try {
				$db = $this->getDbo();

				$query = $db->getQuery(true)
					->select($db->quoteName(array('m.id', 'm.title', 'm.alias', 'm.year', 'm.plot', 'm.rate_loc', 'm.rate_sum_loc', 'm.imdb_votesum', 'm.imdb_votes', 'm.imdb_id', 'm.kp_votesum', 'm.kp_votes', 'm.kp_id', 'm.rate_fc', 'm.rottentm_id', 'm.metacritics', 'm.metacritics_id', 'm.rate_custom', 'm.attribs', 'm.created', 'm.modified', 'g.filename', 'g.dimension')))->select($db->quoteName('m.introtext') . ' AS `text`')
					->from($db->quoteName('#__ka_movies', 'm'))
					->leftJoin($db->quoteName('#__ka_movies_gallery', 'g') . ' ON `g`.`movie_id` = `m`.`id` AND `g`.`type` = 2 AND `g`.`poster_frontpage` = 1 AND `g`.`state` = 1')
					->where($db->quoteName('m.id') . ' = ' . $pk . ' AND ' . $db->quoteName('m.state') . ' = 1 AND ' . $db->quoteName('m.access') . ' IN (' . implode(',', $groups) . ')');
				$db->setQuery($query);

				$data = $db->loadObject();

				if (isset($data->attribs)) {
					$data->attribs = json_decode($data->attribs);
				}

				$query = $db->getQuery(true)
					->select(
						$this->getState('item.select', $db->quoteName(array('r.id', 'r.media_type', 'r.release_date', 'r.desc', 'cn.name', 'cn.code')))
					);
				$query->from($db->quoteName('#__ka_releases', 'r'))
					->join('LEFT', $db->quoteName('#__ka_countries', 'cn') . ' ON ' . $db->quoteName('cn.id') . ' = ' . $db->quoteName('r.country_id'))
					->where($db->quoteName('r.language') . ' IN (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')')
					->order($db->quoteName('r.release_date') . ' DESC');
				$db->setQuery($query);

				$data->items = $db->loadObjectList();

				if (empty($data->items)) {
					return JError::raiseError(404, JText::_('COM_CONTENT_ERROR_ARTICLE_NOT_FOUND')); // TODO Remove deprecated JError call
				}

				$this->_item[$pk] = $data;
			} catch (Exception $e) {
				if ($e->getCode() == 404) {
					// Need to go thru the error handler to allow Redirect to work.
					JError::raiseError(404, $e->getMessage()); // TODO Remove deprecated JError call
				} else {
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
		}

		return $this->_item[$pk];
	}
}
