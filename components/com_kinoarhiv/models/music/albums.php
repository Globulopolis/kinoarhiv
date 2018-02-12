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

use Joomla\Utilities\ArrayHelper;
use Joomla\String\StringHelper;

/**
 * Music albums class
 *
 * @since  3.0
 */
class KinoarhivModelAlbums extends JModelList
{
	/**
	 * Context string for the model type.  This is used to handle uniqueness
	 * when dealing with the getStoreId() method and caching data structures.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $context = null;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelLegacy
	 * @since   3.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			// Setup a list of columns for ORDER BY from 'sort_movielist_field' params from component settings
			$config['filter_fields'] = array('id', 'a.id', 'title', 'year', 'created', 'ordering', 'a.ordering');
		}

		parent::__construct($config);

		if (empty($this->context))
		{
			$this->context = strtolower('com_kinoarhiv.albums');
		}
	}

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
		if ($this->context)
		{
			$app = JFactory::getApplication();
			$params = JComponentHelper::getParams('com_kinoarhiv');

			$value = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $params->get('list_limit'), 'uint');
			$limit = $value;
			$this->setState('list.limit', $value);

			$value = $app->getUserStateFromRequest($this->context . '.limitstart', 'limitstart', 0);
			$limitstart = ($limit != 0 ? (floor($value / $limit) * $limit) : 0);
			$this->setState('list.start', $limitstart);

			$value = $app->getUserStateFromRequest($this->context . '.ordercol', 'filter_order', $params->get('sort_albumlist_field'));

			if (!in_array($value, $this->filter_fields))
			{
				$value = $ordering;
				$app->setUserState($this->context . '.ordercol', $value);
			}

			$this->setState('list.ordering', $value);

			$value = $app->getUserStateFromRequest($this->context . '.orderdirn', 'filter_order_Dir', strtoupper($params->get('sort_albumlist_field')));

			if (!in_array(strtoupper($value), array('ASC', 'DESC', '')))
			{
				$value = $direction;
				$app->setUserState($this->context . '.orderdirn', $value);
			}

			$this->setState('list.direction', $value);
		}
		else
		{
			$this->setState('list.start', 0);
			$this->state->set('list.limit', 0);
		}
	}

	/**
	 * Method to get a store id based on the model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  An identifier string to generate the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   3.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('list.limit');
		$id .= ':' . $this->getState('list.ordering');
		$id .= ':' . $this->getState('list.direction');

		return parent::getStoreId($id);
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   3.0
	 */
	protected function getListQuery()
	{
		return null;
	}

	/**
	 * Get the values from search inputs
	 *
	 * @return   object
	 *
	 * @since  3.0
	 */
	public function getFiltersData()
	{
		jimport('models.search', JPATH_COMPONENT);

		$searchModel = new KinoarhivModelSearch;

		return $searchModel->getActiveFilters();
	}

	/**
	 * Method to add an album into favorites
	 *
	 * @param   integer  $id  Album ID.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	public function favoriteAdd($id)
	{
		$db = $this->getDbo();
		$app = JFactory::getApplication();
		$userID = JFactory::getUser()->get('id');

		// Check if any record with person ID exists in database.
		$query = $db->getQuery(true)
			->select($db->quoteName(array('uid', 'favorite')))
			->from($db->quoteName('#__ka_user_marked_albums'))
			->where($db->quoteName('uid') . ' = ' . (int) $userID)
			->where($db->quoteName('album_id') . ' = ' . (int) $id);

		$db->setQuery($query);

		try
		{
			$result = $db->loadAssoc();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		if (!$result)
		{
			$query = $db->getQuery(true)
				->insert($db->quoteName('#__ka_user_marked_albums'))
				->columns($db->quoteName(array('uid', 'album_id', 'favorite', 'favorite_added')))
				->values("'" . (int) $userID . "', '" . (int) $id . "', '1', NOW()");

			$db->setQuery($query);
		}
		else
		{
			if ($result['favorite'] == 1)
			{
				$app->enqueueMessage(JText::_('COM_KA_FAVORITE_ERROR'), 'notice');

				return false;
			}

			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_user_marked_albums'))
				->set($db->quoteName('favorite') . " = '1', " . $db->quoteName('favorite_added') . " = NOW()")
				->where($db->quoteName('uid') . ' = ' . (int) $userID)
				->where($db->quoteName('album_id') . ' = ' . (int) $id);

			$db->setQuery($query);
		}

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
			KAComponentHelper::eventLog($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Removes album(s) from favorites.
	 *
	 * @param   mixed  $id  Album ID or array of IDs.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	public function favoriteRemove($id)
	{
		$db = $this->getDbo();
		$app = JFactory::getApplication();
		$userID = JFactory::getUser()->get('id');

		if (!is_array($id))
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_user_marked_albums'))
				->set($db->quoteName('favorite') . " = '0'")
				->where($db->quoteName('uid') . ' = ' . (int) $userID)
				->where($db->quoteName('album_id') . ' = ' . (int) $id);

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
				KAComponentHelper::eventLog($e->getMessage());

				return false;
			}
		}
		else
		{
			$queryResult = true;
			$db->lockTable('#__ka_user_marked_albums');
			$db->transactionStart();

			foreach ($id as $_id)
			{
				$query = $db->getQuery(true);

				$query->update($db->quoteName('#__ka_user_marked_albums'))
					->set($db->quoteName('favorite') . " = '0'")
					->where($db->quoteName('uid') . ' = ' . (int) $userID)
					->where($db->quoteName('album_id') . ' = ' . (int) $_id);

				$db->setQuery($query . ';');

				if ($db->execute() === false)
				{
					$queryResult = false;
					break;
				}
			}

			if ($queryResult === true)
			{
				$db->transactionCommit();
				$db->unlockTables();
			}
			else
			{
				$db->transactionRollback();
				$db->unlockTables();
				$app->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

				return false;
			}
		}

		return true;
	}

	/**
	 * Add a music album into favorites.
	 *
	 * @return array
	 *
	 * @throws Exception
	 *
	 * @since  3.1
	 */
	public function favorite()
	{
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		$action = $app->input->get('action', '', 'cmd');
		$albumID = $app->input->get('id', 0, 'int');
		$albumsIDs = $app->input->get('ids', array(), 'array');
		$result = '';
		$itemid = $app->input->get('Itemid', 0, 'int');
		$success = false;
		$url = '';
		$text = '';

		if (empty($albumsIDs))
		{
			$query = $db->getQuery(true)
				->select('favorite')
				->from($db->quoteName('#__ka_user_marked_albums'))
				->where('uid = ' . (int) $user->get('id') . ' AND album_id = ' . (int) $albumID);

			$db->setQuery($query);
			$result = $db->loadResult();
		}

		if ($action == 'add')
		{
			if ($result == 1)
			{
				$message = JText::_('COM_KA_FAVORITE_ERROR');
			}
			else
			{
				if (is_null($result))
				{
					$query = $db->getQuery(true)
						->insert($db->quoteName('#__ka_user_marked_albums'))
						->columns($db->quoteName(array('uid', 'album_id', 'favorite', 'favorite_added')))
						->values("'" . $user->get('id') . "', '" . (int) $albumID . "', '1', NOW()");

					$db->setQuery($query);
				}
				elseif ($result == 0)
				{
					$query = $db->getQuery(true)
						->update($db->quoteName('#__ka_user_marked_albums'))
						->set("favorite = '1', favorite_added = NOW()")
						->where('uid = ' . $user->get('id') . ' AND album_id = ' . (int) $albumID);

					$db->setQuery($query);
				}

				if ($db->execute())
				{
					$success = true;
					$message = JText::_('COM_KA_FAVORITE_ADDED');
					$url = JRoute::_('index.php?option=com_kinoarhiv&task=albums.favorite&action=delete&Itemid=' . $itemid . '&id=' . $albumID . '&format=json', false);
					$text = JText::_('COM_KA_REMOVEFROM_FAVORITE');
				}
				else
				{
					$message = JText::_('JERROR_ERROR');
				}
			}
		}
		elseif ($action == 'delete')
		{
			if ($result == 1)
			{
				$query = $db->getQuery(true)
					->update($db->quoteName('#__ka_user_marked_albums'))
					->set("favorite = '0'")
					->where('uid = ' . $user->get('id') . ' AND album_id = ' . (int) $albumID);

				$db->setQuery($query);

				if ($db->execute())
				{
					$success = true;
					$message = JText::_('COM_KA_FAVORITE_REMOVED');
					$url = JRoute::_('index.php?option=com_kinoarhiv&task=albums.favorite&action=add&Itemid=' . $itemid . '&id=' . $albumID . '&format=json', false);
					$text = JText::_('COM_KA_ADDTO_FAVORITE');
				}
				else
				{
					$message = JText::_('JERROR_ERROR');
				}
			}
			else
			{
				if (!empty($albumsIDs))
				{
					$queryResult = true;
					$db->lockTable('#__ka_user_marked_albums');
					$db->transactionStart();

					foreach ($albumsIDs as $id)
					{
						$query = $db->getQuery(true);

						$query->update($db->quoteName('#__ka_user_marked_albums'))
							->set("favorite = '0'")
							->where('uid = ' . $user->get('id') . ' AND album_id = ' . (int) $id);

						$db->setQuery($query . ';');

						if ($db->execute() === false)
						{
							$queryResult = false;
							break;
						}
					}

					if ($queryResult === true)
					{
						$db->transactionCommit();

						$success = true;
						$message = JText::_('COM_KA_FAVORITE_REMOVED');
						$url = JRoute::_('index.php?option=com_kinoarhiv&task=albums.favorite&action=add&Itemid=' . $itemid . '&id=' . $albumID . '&format=json', false);
						$text = JText::_('COM_KA_ADDTO_FAVORITE');
					}
					else
					{
						$db->transactionRollback();

						$message = JText::_('JERROR_ERROR');
					}

					$db->unlockTables();
				}
				else
				{
					$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
				}
			}
		}
		else
		{
			$message = JText::_('JERROR_AN_ERROR_HAS_OCCURRED');
		}

		return array('success' => $success, 'message' => $message, 'url' => $url, 'text' => $text);
	}

	/**
	 * Process user vote.
	 *
	 * @param   integer  $id     Album ID.
	 * @param   integer  $value  Item rating.
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function vote($id, $value)
	{
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$error_message = array('success' => false, 'message' => JText::_('COM_KA_REQUEST_ERROR'));

		$queryAttribs = $db->getQuery(true)
			->select('attribs')
			->from($db->quoteName('#__ka_music_albums'))
			->where('id = ' . (int) $id);

		$db->setQuery($queryAttribs);
		$attribs = json_decode($db->loadResult());

		if (($attribs->allow_votes == '' && $params->get('allow_votes') == 1) || $attribs->allow_votes == 1)
		{
			// Update rating and insert or update user vote in #__ka_user_votes_albums
			// Check if value in range from 1 to 'vote_summ_num'
			if ($value >= 1 || $value <= $params->get('vote_summ_num'))
			{
				// At first we check if user allready voted and when just update the rating and vote
				$query = $db->getQuery(true)
					->select('v.vote, r.rate, r.rate_sum')
					->from($db->quoteName('#__ka_user_votes_albums', 'v'))
					->join('LEFT', $db->quoteName('#__ka_music_albums', 'r') . ' ON r.id = v.album_id')
					->where('album_id = ' . (int) $id . ' AND uid = ' . $user->get('id'));

				$db->setQuery($query);
				$voteResult = $db->loadObject();

				if (!empty($voteResult->vote))
				{
					// User allready voted
					$rateSum = ($voteResult->rate_sum - $voteResult->vote) + $value;

					try
					{
						$query = $db->getQuery(true)
							->update($db->quoteName('#__ka_music_albums'))
							->set("rate_sum = '" . (int) $rateSum . "'")
							->where('id = ' . (int) $id);

						$db->setQuery($query);
						$m_query = $db->execute();

						$query = $db->getQuery(true)
							->update($db->quoteName('#__ka_user_votes_albums'))
							->set("vote = '" . (int) $value . "', _datetime = NOW()")
							->where('album_id = ' . (int) $id . ' AND uid = ' . $user->get('id'));

						$db->setQuery($query);
						$v_query = $db->execute();

						if ($m_query && $v_query)
						{
							$result = array('success' => true, 'message' => JText::_('COM_KA_RATE_RATED'));
						}
						else
						{
							$result = $error_message;
						}
					}
					catch (Exception $e)
					{
						$result = $error_message;
						KAComponentHelper::eventLog($e->getMessage());
					}
				}
				else
				{
					$query = $db->getQuery(true)
						->select('rate, rate_sum')
						->from($db->quoteName('#__ka_music_albums'))
						->where('id = ' . (int) $id);

					$db->setQuery($query);
					$voteResult = $db->loadObject();

					$rate = (int) $voteResult->rate + 1;
					$rateSum = (int) $voteResult->rate_sum + (int) $value;

					try
					{
						$query = $db->getQuery(true)
							->update($db->quoteName('#__ka_music_albums'))
							->set("rate = '" . (int) $rate . "', rate_sum = '" . (int) $rateSum . "'")
							->where('id = ' . (int) $id);

						$db->setQuery($query);
						$m_query = $db->execute();

						$query = $db->getQuery(true)
							->insert($db->quoteName('#__ka_user_votes_albums'))
							->columns($db->quoteName(array('uid', 'album_id', 'vote', '_datetime')))
							->values("'" . $user->get('id') . "', '" . $id . "', '" . (int) $value . "', NOW()");

						$db->setQuery($query);
						$v_query = $db->execute();

						if ($m_query && $v_query)
						{
							$result = array('success' => true, 'message' => JText::_('COM_KA_RATE_RATED'));
						}
						else
						{
							$result = $error_message;
						}
					}
					catch (Exception $e)
					{
						$result = $error_message;
						KAComponentHelper::eventLog($e->getMessage());
					}
				}
			}
			else
			{
				$result = $error_message;
			}
		}
		else
		{
			$result = $error_message;
		}

		return $result;
	}

	/**
	 * Removes user votes.
	 *
	 * @param   array  $ids  Array of IDs
	 *
	 * @return  array
	 *
	 * @since   3.1
	 */
	public function votesRemove($ids)
	{
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$allowedIDs = array();

		// Get attributes to check if user can change vote.
		$queryAttribs = $db->getQuery(true)
			->select(array('id', 'attribs'))
			->from($db->quoteName('#__ka_music_albums'))
			->where('id IN (' . implode(',', $ids) . ')');

		$db->setQuery($queryAttribs);
		$attribsObjects = $db->loadObjectList();

		foreach ($attribsObjects as $attribs)
		{
			$movieAttribs = json_decode($attribs->attribs);

			if (($movieAttribs->allow_votes == '' && $params->get('allow_votes') == 1) || $movieAttribs->allow_votes == 1)
			{
				$allowedIDs[] = $attribs->id;
			}
		}

		if (empty($allowedIDs))
		{
			return array('success' => false, 'message' => JText::_('COM_KA_REQUEST_ERROR'));
		}

		$queryVote = $db->getQuery(true)
			->select('a.id, a.rate, a.rate_sum, v.vote')
			->from($db->quoteName('#__ka_user_votes_albums', 'v'))
			->join('LEFT', $db->quoteName('#__ka_music_albums', 'a') . ' ON a.id = v.album_id')
			->where('album_id IN (' . implode(',', $allowedIDs) . ') AND uid = ' . $user->get('id'));

		$db->setQuery($queryVote);
		$votes = $db->loadObjectList();

		// Check if user has votes at least for one album.
		if (empty($votes))
		{
			return array('success' => false, 'message' => JText::_('COM_KA_REQUEST_ERROR'));
		}

		$queryResult = true;
		$db->lockTable('#__ka_music_albums')
			->lockTable('#__ka_user_votes_albums');
		$db->transactionStart();

		foreach ($votes as $voteObject)
		{
			if (!empty($voteObject->vote))
			{
				$rate = $voteObject->rate - 1;
				$rateSum = $voteObject->rate_sum - $voteObject->vote;

				$query = $db->getQuery(true)
					->update($db->quoteName('#__ka_music_albums'))
					->set("rate = '" . (int) $rate . "', rate_sum = '" . (int) $rateSum . "'")
					->where('id = ' . (int) $voteObject->id);
				$db->setQuery($query . ';');

				if ($db->execute() === false)
				{
					$queryResult = false;
					break;
				}
			}
		}

		if (!$queryResult)
		{
			$db->transactionRollback();
		}
		else
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__ka_user_votes_albums'))
				->where('album_id IN (' . implode(',', $ids) . ') AND uid = ' . $user->get('id'));
			$db->setQuery($query);

			if ($db->execute())
			{
				$db->transactionCommit();
			}
			else
			{
				$db->transactionRollback();
				$queryResult = false;
			}
		}

		$db->unlockTables();

		if (!$queryResult)
		{
			return array('success' => false, 'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED'));
		}

		return array('success' => true, 'message' => (count($ids) > 1) ? JText::_('COM_KA_RATES_REMOVED') : JText::_('COM_KA_RATE_REMOVED'));
	}

	/**
	 * Method to get a KAPagination object for the data set.
	 *
	 * @return  KAPagination  A KAPagination object for the data set.
	 *
	 * @since   3.0
	 */
	public function getPagination()
	{
		JLoader::register('KAPagination', JPath::clean(JPATH_COMPONENT . '/libraries/pagination.php'));

		$store = $this->getStoreId('getPagination');

		if (isset($this->cache[$store]))
		{
			return $this->cache[$store];
		}

		$limit = (int) $this->getState('list.limit') - (int) $this->getState('list.links');
		$page = new KAPagination($this->getTotal(), $this->getStart(), $limit);

		$this->cache[$store] = $page;

		return $this->cache[$store];
	}
}
