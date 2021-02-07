<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

/**
 * Release class
 *
 * @since  3.0
 */
class KinoarhivModelRelease extends JModelItem
{
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
		$app      = JFactory::getApplication();
		$menu     = $app->getMenu()->getActive();
		$pk       = $app->input->getInt('id');
		$itemType = $menu->getParams()->get('item_type');

		$this->setState('release.id', $pk);
		$this->setState('item_type', $itemType);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @throws  Exception
	 * @since   3.1
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
			$db       = $this->getDbo();
			$user     = JFactory::getUser();
			$groups   = $user->getAuthorisedViewLevels();
			$itemType = (int) $this->getState('item_type');

			try
			{
				$query = $db->getQuery(true);

				if ($itemType === 0)
				{
					$query->select(
						$db->quoteName(
							array(
								'm.id', 'm.title', 'm.alias', 'm.fs_alias', 'm.year', 'm.plot', 'm.rate_loc',
								'm.rate_sum_loc', 'm.imdb_votesum', 'm.imdb_votes', 'm.imdb_id', 'm.kp_votesum',
								'm.kp_votes', 'm.kp_id', 'm.rate_fc', 'm.rottentm_id', 'm.metacritics',
								'm.metacritics_id', 'm.rate_custom', 'm.attribs', 'm.created', 'm.modified',
								'g.filename', 'g.dimension'
							)
						)
					);

					$subQuery = $db->getQuery(true)
						->select('COUNT(' . $db->quoteName('movie_id') . ')')
						->from($db->quoteName('#__ka_user_votes_movies'))
						->where($db->quoteName('movie_id') . ' = ' . $db->quoteName('m.id'));

					$query->select('(' . $subQuery . ') AS ' . $db->quoteName('total_votes'))
						->select($db->quoteName('m.introtext', 'text'))
						->from($db->quoteName('#__ka_movies', 'm'))
						->leftJoin($db->quoteName('#__ka_movies_gallery', 'g') . ' ON g.movie_id = m.id AND g.type = 2 AND g.frontpage = 1 AND g.state = 1');
				}
				elseif ($itemType === 1)
				{
					$query->select(
						$db->quoteName(
							array(
								'm.id', 'm.title', 'm.alias', 'm.fs_alias', 'm.year', 'm.length', 'm.isrc', 'm.rate',
								'm.rate_sum', 'm.covers_path', 'm.covers_path_www', 'm.attribs', 'm.created', 'm.modified'
							)
						)
					);

					$subQuery = $db->getQuery(true)
						->select('COUNT(' . $db->quoteName('album_id') . ')')
						->from($db->quoteName('#__ka_user_votes_albums'))
						->where($db->quoteName('album_id') . ' = ' . $db->quoteName('m.id'));

					$query->select('(' . $subQuery . ') AS ' . $db->quoteName('total_votes'))
						->select($db->quoteName('m.introtext', 'text'))
						->from($db->quoteName('#__ka_music_albums', 'm'));

					// Join over gallery item
					$query->select($db->quoteName(array('g.filename', 'g.dimension')))
						->leftJoin($db->quoteName('#__ka_music_albums_gallery', 'g') . ' ON g.item_id = m.id AND g.type = 1 AND g.frontpage = 1 AND g.state = 1');
				}

				if (!$user->get('guest'))
				{
					if ($itemType === 0)
					{
						$query->select($db->quoteName(array('u.favorite', 'u.watched')))
							->leftJoin($db->quoteName('#__ka_user_marked_movies', 'u') . ' ON u.uid = ' . $user->get('id') . ' AND u.movie_id = m.id');

						$query->select($db->quoteName('v.vote', 'my_vote'))
							->select($db->quoteName('v._datetime'))
							->leftJoin($db->quoteName('#__ka_user_votes_movies', 'v') . ' ON v.movie_id = m.id AND v.uid = ' . $user->get('id'));
					}
					elseif ($itemType === 1)
					{
						$query->select($db->quoteName('u.favorite'))
							->leftJoin($db->quoteName('#__ka_user_marked_albums', 'u') . ' ON u.uid = ' . $user->get('id') . ' AND u.album_id = m.id');

						$query->select($db->quoteName('v.vote', 'my_vote'))
							->select($db->quoteName('v._datetime'))
							->leftJoin($db->quoteName('#__ka_user_votes_albums', 'v') . ' ON v.album_id = m.id AND v.uid = ' . $user->get('id'));
					}
				}

				$query->select($db->quoteName('user.name', 'username'))
					->leftJoin($db->quoteName('#__users', 'user') . ' ON user.id = m.created_by');

				$query->where($db->quoteName('m.id') . ' = ' . $pk . ' AND ' . $db->quoteName('m.state') . ' = 1')
					->where($db->quoteName('m.access') . ' IN (' . implode(',', $groups) . ')');

				$db->setQuery($query);

				$data = $db->loadObject();

				if (isset($data->attribs))
				{
					$data->attribs = json_decode($data->attribs);
				}

				$query = $db->getQuery(true)
					->select(
						$this->getState(
							'item.select',
							'r.id, r.release_date, r.desc, cn.name, cn.code, media.title AS media_type'
						)
					)
					->from($db->quoteName('#__ka_releases', 'r'))
					->leftJoin($db->quoteName('#__ka_countries', 'cn') . ' ON ' . $db->quoteName('cn.id') . ' = ' . $db->quoteName('r.country_id'))
					->leftJoin($db->quoteName('#__ka_media_types', 'media') . ' ON ' . $db->quoteName('media.id') . ' = ' . $db->quoteName('r.media_type'))
					->where($db->quoteName('r.item_id') . ' = ' . $pk)
					->where($db->quoteName('r.item_type') . ' = ' . $itemType)
					->where($db->quoteName('r.language') . ' IN (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')')
					->order($db->quoteName('r.release_date') . ' DESC');

				$db->setQuery($query);
				$rows = $db->loadObjectList();

				if (empty($rows))
				{
					$this->setError(($itemType === 0) ? JText::_('COM_KA_MOVIE_NOT_FOUND') : JText::_('COM_KA_MUSIC_ALBUM_NOT_FOUND'));

					return false;
				}

				$data->items = $rows;
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
					$this->setError($e->getMessage());
					$this->_item[$pk] = false;
				}
			}
		}

		return $this->_item[$pk];
	}
}
