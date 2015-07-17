<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

/**
 * Release class
 *
 * @since  3.0
 */
class KinoarhivModelRelease extends JModelItem
{
	protected $context = 'com_kinoarhiv.release';

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();
		$pk = $app->input->getInt('id');
		$this->setState('release.id', $pk);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @throws Exception
	 * @since   3.0
	 */
	public function getItem($pk = null)
	{
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('release.id');

		if ($this->_item === null)
		{
			$this->_item = array();
		}

		if (!isset($this->_item[$pk]))
		{
			$user = JFactory::getUser();
			$groups = $user->getAuthorisedViewLevels();
			$data = (object) array('rows' => array());

			try
			{
				$db = $this->getDbo();

				$query = $db->getQuery(true)
					->select(
						$db->quoteName(
							array('m.id', 'm.title', 'm.alias', 'm.year', 'm.plot', 'm.rate_loc', 'm.rate_sum_loc',
									'm.imdb_votesum', 'm.imdb_votes', 'm.imdb_id', 'm.kp_votesum', 'm.kp_votes', 'm.kp_id',
									'm.rate_fc', 'm.rottentm_id', 'm.metacritics', 'm.metacritics_id', 'm.rate_custom',
									'm.attribs', 'm.created', 'm.modified', 'g.filename', 'g.dimension'
							)
						)
					)
					->select($db->quoteName('m.introtext', 'text'))
					->from($db->quoteName('#__ka_movies', 'm'))
					->join('LEFT', $db->quoteName('#__ka_movies_gallery', 'g') . ' ON g.movie_id = m.id AND g.type = 2 AND g.poster_frontpage = 1 AND g.state = 1')
					->where($db->quoteName('m.id') . ' = ' . $pk . ' AND ' . $db->quoteName('m.state') . ' = 1 AND ' . $db->quoteName('m.access') . ' IN (' . implode(',', $groups) . ')');
				$db->setQuery($query);

				$data = $db->loadObject();

				if (isset($data->attribs))
				{
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

				if (empty($data->items))
				{
					throw new Exception(JText::_('COM_CONTENT_ERROR_ARTICLE_NOT_FOUND'), 404);
				}

				$this->_item[$pk] = $data;
			}
			catch (Exception $e)
			{
				if ($e->getCode() == 404)
				{
					// Need to go thru the error handler to allow Redirect to work.
					throw new Exception($e->getMessage(), 404);
				}
				else
				{
					$this->setError($e);
					$this->_item[$pk] = false;
				}
			}
		}

		return $this->_item[$pk];
	}
}
