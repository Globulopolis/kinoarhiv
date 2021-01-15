<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

/**
 * Content helper class
 *
 * @since  3.1
 */
class KAContentHelperBackend
{
	/**
	 * Method to save the access rules.
	 *
	 * @param   integer  $id         Item ID.
	 * @param   string   $assetName  The unique name for the asset.
	 * @param   string   $title      The descriptive title for the asset.
	 * @param   array    $rules      The form data.
	 *
	 * @return  mixed  lastInsertID on insert, true on update, false otherwise.
	 *
	 * @since   3.1
	 */
	public static function saveAccessRules($id, $assetName, $title = '', $rules = array())
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$rules = new JAccessRules($rules);

		if (empty($id))
		{
			// Get parent ID.
			$query = $db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__assets'))
				->where($db->quoteName('name') . " = 'com_kinoarhiv' AND " . $db->quoteName('parent_id') . " = 1");
			$db->setQuery($query);

			try
			{
				$parentID = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}

			// Get lft, rgt values
			$query = $db->getQuery(true)
				->select('MAX(lft)+2 AS lft')
				->from($db->quoteName('#__assets'));
			$db->setQuery($query);

			try
			{
				$lft = $db->loadResult();
				$rgt = (int) $lft + 1;
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}

			$query = $db->getQuery(true)
				->insert($db->quoteName('#__assets'))
				->columns($db->quoteName(array_keys($db->getTableColumns('#__assets'))))
				->values("'', '" . (int) $parentID . "', '" . $lft . "', '" . $rgt . "', '2', '" . $assetName . "', "
					. "'" . $db->escape($title) . "', '" . $rules . "'"
				);
		}
		else
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__assets'))
				->set($db->quoteName('rules') . " = '" . $rules . "'")
				->where($db->quoteName('name') . " = '" . $assetName . "'");
		}

		$db->setQuery($query);

		try
		{
			$db->execute();

			if (empty($id))
			{
				return $db->insertid();
			}
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Method to update tags mapping.
	 *
	 * @param   int     $itemID     Item ID
	 * @param   mixed   $ids        New tags IDs. Array of IDs or string with IDs separated by commas.
	 * @param   string  $typeAlias  Type alias. In form: component_name.view_name
	 *
	 * @return  boolean   True on success
	 *
	 * @since   3.1
	 */
	public static function updateTagMapping($itemID, $ids, $typeAlias)
	{
		$app = JFactory::getApplication();
		$db  = JFactory::getDbo();
		$ids = (!is_array($ids)) ? explode(',', $ids) : $ids;

		if (empty($typeAlias))
		{
			$app->enqueueMessage('An empty type alias to update tag mapping is forbidden.', 'error');

			return false;
		}

		if (!empty($ids))
		{
			// Remove existing tags from mapping table
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__contentitem_tag_map'))
				->where($db->quoteName('content_item_id') . ' = ' . (int) $itemID);
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}

			if ((is_array($ids) && empty($ids[0])) || empty($ids))
			{
				return true;
			}

			$query = $db->getQuery(true)
				->insert($db->quoteName('#__contentitem_tag_map'))
				->columns($db->quoteName(array('type_alias', 'core_content_id', 'content_item_id', 'tag_id', 'tag_date', 'type_id')));

			foreach ($ids as $tagID)
			{
				$query->values("'" . (string) $typeAlias . "', '0', '" . (int) $itemID . "', '" . (int) $tagID . "', " . $query->currentTimestamp() . ", '0'");
			}

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}
		}

		return true;
	}

	/**
	 * Update statistics on genres
	 *
	 * @param   string  $old    Original genres list(before edit).
	 * @param   string  $new    New genres list.
	 * @param   string  $table  Relation table name.
	 *
	 * @return  mixed   True on success, exception otherwise
	 *
	 * @since   3.1
	 */
	public static function updateGenresStat($old, $new, $table)
	{
		$db     = JFactory::getDbo();
		$oldArr = !is_array($old) ? explode(',', $old) : $old;
		$newArr = !is_array($new) ? explode(',', $new) : $new;
		$all    = array_filter(array_unique(array_merge($oldArr, $newArr)));

		$queryResult = true;
		$db->lockTable('#__ka_genres');
		$db->transactionStart();

		foreach ($all as $genreID)
		{
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_genres'));

			$subquery = $db->getQuery(true)
				->select('COUNT(genre_id)')
				->from($db->quoteName($table))
				->where($db->quoteName('genre_id') . ' = ' . (int) $genreID);

			$query->set($db->quoteName('stats') . ' = (' . $subquery . ')')
				->where($db->quoteName('id') . ' = ' . (int) $genreID);
			$db->setQuery($query . ';');

			if ($db->execute() === false)
			{
				$queryResult = false;
				break;
			}
		}

		if ($queryResult === false)
		{
			$db->transactionRollback();
			JFactory::getApplication()->enqueueMessage(
				JText::_('COM_KA_GENRES_TITLE') . ': ' . JText::_('COM_KA_GENRES_STATS_UPDATE_ERROR'),
				'error'
			);
		}
		else
		{
			$db->transactionCommit();
		}

		$db->unlockTables();

		return $queryResult;
	}
}
