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

jimport('components.com_kinoarhiv.helpers.component', JPATH_ROOT);

/**
 * Component helper class
 *
 * @since  3.0
 */
class KAComponentHelperBackend
{
	/**
	 * Include some necessary JS into the HEAD of the document. Don't include if document format is not a html.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function setHeadTags()
	{
		$document = JFactory::getDocument();
		$params   = JComponentHelper::getParams('com_kinoarhiv');

		// Return nothing because JHtml::script doesn't work for JDocumentRaw
		if ($document->getType() != 'html')
		{
			return;
		}

		JHtml::_('stylesheet', 'media/com_kinoarhiv/css/component/plugins_backend.min.css');
		JHtml::_('stylesheet', 'media/com_kinoarhiv/css/component/styles_backend.min.css');
		JHtml::_('stylesheet', 'media/com_kinoarhiv/css/aurora.min.css');

		JHtml::_('jquery.framework');
		JHtml::_('script', 'media/com_kinoarhiv/js/aurora.min.js');
		JHtml::_('script', 'media/com_kinoarhiv/js/core.min.js');
		JHtml::_('script', 'media/com_kinoarhiv/js/backend.min.js');

		// Add some variables into the global scope
		$jsVars = array(
			'api_root' => JUri::base(), // We need to request API controller from backend not from frontend.
			'img_root' => JUri::root(),
			'uri_root' => JUri::root(),
			'uri_base' => JUri::base(),
			'params' => array(
				'ka_theme' => $params->get('ka_theme')
			),
			'language' => array(
				'tag'                          => JFactory::getLanguage()->getTag(),
				'JGLOBAL_SELECT_AN_OPTION'     => JText::_('JGLOBAL_SELECT_AN_OPTION', true), // Default placeholder, if not set for Select2,
				'COM_KA_CLOSE'                 => JText::_('COM_KA_CLOSE', true),
				'JERROR_AN_ERROR_HAS_OCCURRED' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED', true),
				'COM_KA_FILE_UPLOAD_ERROR'     => JText::_('COM_KA_FILE_UPLOAD_ERROR', true),
				'COM_KA_FILES_UPLOAD_SUCCESS'  => JText::_('COM_KA_FILES_UPLOAD_SUCCESS', true),
				'COM_KA_REQUIRED'              => JText::_('COM_KA_REQUIRED', true),
				'JCLEAR'                       => JText::_('JCLEAR', true),
				'COM_KA_READ_MORE'             => JText::_('COM_KA_READ_MORE', true),
				'COM_KA_READ_LESS'             => JText::_('COM_KA_READ_LESS', true),
				'COM_KA_DELETE_SELECTED'       => JText::_('COM_KA_DELETE_SELECTED', true),
				'COM_KA_NEWWINDOW_BLOCKED_A'   => JText::_('COM_KA_NEWWINDOW_BLOCKED_A'),
				'COM_KA_NEWWINDOW_BLOCKED_B'   => JText::_('COM_KA_NEWWINDOW_BLOCKED_B'),
			)
		);
		$document->addScriptDeclaration('var KA_vars = ' . json_encode($jsVars) . ';');
	}

	/**
	 * Load mediamanager assets
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public static function loadMediamanagerAssets()
	{
		JHtml::_('jquery.framework');
		JHtml::_('stylesheet', 'media/com_kinoarhiv/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css');
		JHtml::_('script', 'media/com_kinoarhiv/plupload/plupload.full.min.js');
		KAComponentHelper::getScriptLanguage('', 'media/com_kinoarhiv/plupload/i18n');
		JHtml::_('script', 'media/com_kinoarhiv/plupload/jquery.plupload.queue/jquery.plupload.queue.min.js');
	}

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
