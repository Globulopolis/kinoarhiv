<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

jimport('components.com_kinoarhiv.helpers.content', JPATH_ROOT);

/**
 * Class KinoarhivModelMediamanagerItem
 *
 * @since  3.0
 */
class KinoarhivModelMediamanagerItem extends JModelForm
{
	/**
	 * Method to save image information into DB. Accept gallery items for movie and poster for trailer.
	 *
	 * @param   string   $section      Section. (can be: movie, name, trailer, soundtrack)
	 * @param   integer  $item_id      Item ID.
	 * @param   string   $filename     System filename.
	 * @param   array    $image_sizes  Array with the sizes. array(width, height)
	 * @param   mixed    $item_type    Item type.
	 *                                 For movie: 2 - poster, 1 - wallpaper, 3 - screenshot.
	 *                                 For name: 2 - poster, 1 - wallpaper, 3 - photo.
	 *                                 For trailer: null
	 * @param   integer  $frontpage    Item published on frontpage.
	 *
	 * @return  boolean
	 *
	 * @since   3.0
	 */
	public function saveImageInDB($section, $item_id, $filename, $image_sizes = array(), $item_type = null, $frontpage = 0)
	{
		if (empty($section))
		{
			return array('success' => false, 'filename' => $filename, 'id' => 0);
		}

		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$result = array();
		$image_sizes = (count($image_sizes) == 0) ? array(0 => 0, 1 => 0) : $image_sizes;
		$dimension = floor($image_sizes[0]) . 'x' . floor($image_sizes[1]);

		if ($section == 'movie')
		{
			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_movies_gallery'), 'id')
				->columns($db->quoteName(array('id', 'filename', 'dimension', 'movie_id', 'type', 'frontpage', 'state')))
				->values("'', '" . $filename . "', '" . $dimension . "', '" . (int) $item_id . "', '" . (int) $item_type . "', '" . (int) $frontpage . "', '1'");
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}

			// Unpublish all items from frontpage and set last one to frontpage
			if ($frontpage == 1)
			{
				$query = $db->getQuery(true);

				$query->update($db->quoteName('#__ka_movies_gallery'))
					->set($db->quoteName('frontpage') . " = 0")
					->where($db->quoteName('movie_id') . ' = ' . (int) $item_id . ' AND ' . $db->quoteName('type') . ' = 2')
					->where($db->quoteName('id') . ' != ' . $result['id']);
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (RuntimeException $e)
				{
					$app->enqueueMessage($e->getMessage(), 'error');

					return false;
				}
			}
		}
		elseif ($section == 'name')
		{
			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_names_gallery'), 'id')
				->columns($db->quoteName(array('id', 'filename', 'dimension', 'name_id', 'type', 'frontpage', 'state')))
				->values("'', '" . $filename . "', '" . $dimension . "', '" . (int) $item_id . "', '" . (int) $item_type . "', '" . (int) $frontpage . "', '1'");
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}

			// Unpublish all items from frontpage and set last one to frontpage
			if ($frontpage == 1)
			{
				$query = $db->getQuery(true);

				$query->update($db->quoteName('#__ka_names_gallery'))
					->set($db->quoteName('frontpage') . " = 0")
					->where($db->quoteName('name_id') . ' = ' . (int) $item_id . ' AND ' . $db->quoteName('type') . ' = 3')
					->where($db->quoteName('id') . ' != ' . $result['id']);
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (RuntimeException $e)
				{
					$app->enqueueMessage($e->getMessage(), 'error');

					return false;
				}
			}
		}
		elseif ($section == 'trailer')
		{
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('screenshot') . " = '" . $db->escape($filename) . "'")
				->where($db->quoteName('id') . ' = ' . (int) $item_id);
			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}
		}

		return true;
	}

	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure.
	 *
	 * @since   3.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_kinoarhiv.mediamanager', 'mediamanager', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The default data is an array.
	 *
	 * @since   3.0
	 */
	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_kinoarhiv.trailers.' . JFactory::getUser()->id . '.edit_data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record for trailer.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since  3.0
	 */
	public function getItem()
	{
		$app = JFactory::getApplication();
		$type = $app->input->getWord('type', '');

		if ($type == 'video' || $type == 'subtitles' || $type == 'chapters')
		{
			return $this->getTrailerFiles($type, $app->input->get('item_id', 0, 'int'), $app->input->get('item', 0, 'int'));
		}

		$db = $this->getDbo();
		$item_id = $app->input->get('item_id', null, 'array');
		$query = $db->getQuery(true);

		$query->select(
			$db->quoteName(
				array('g.title', 'g.embed_code', 'g.screenshot', 'g.urls', 'g.resolution', 'g.dar', 'g.duration',
					'g.video', 'g.subtitles', 'g.chapters', 'g.frontpage', 'g.access', 'g.state', 'g.language', 'g.is_movie'
				)
			)
		)->select($db->quoteName('g.id', 'item_id') . ',' . $db->quoteName('g.movie_id', 'id'))
			->from($db->quoteName('#__ka_trailers', 'g'));

		$query->select($db->quoteName('m.title', 'movie_title'))
			->select($db->quoteName(array('m.alias', 'm.fs_alias')))
			->leftJoin($db->quoteName('#__ka_movies', 'm') . ' ON m.id = g.movie_id');

		$query->select($db->quoteName('l.title', 'language_title'))
			->leftJoin($db->quoteName('#__languages', 'l') . ' ON l.lang_code = g.language');

		$query->select($db->quoteName('ag.title', 'access_level'))
			->leftJoin($db->quoteName('#__viewlevels', 'ag') . ' ON ag.id = g.access');

		$query->where($db->quoteName('g.id') . ' = ' . (int) $item_id[0]);

		$db->setQuery($query);
		$result['trailer'] = $db->loadObject();

		return $result;
	}

	/**
	 * Method to get a single record for trailer file. I. e. used in fileinfo_edit template.
	 *
	 * @param   string   $type     Content type. Can be 'video', 'subtitles', 'chapters', 'image'.
	 * @param   integer  $item_id  Trailer ID
	 * @param   mixed    $item     File ID. If it's an empty value then return all files.
	 *
	 * @return  mixed  Array on success, false on failure.
	 *
	 * @since  3.0
	 */
	public function getTrailerFiles($type, $item_id, $item = '')
	{
		$db = $this->getDbo();
		$result = array();

		// Return an empty array if we request data for new file.
		$is_new = JFactory::getApplication()->input->getInt('new', 0);

		if ($is_new == 1)
		{
			return array('trailer_finfo_' . $type => array());
		}

		if ($type == 'video')
		{
			$query = $db->getQuery(true)
				->select($db->quoteName(array('screenshot', 'video')))
				->from($db->quoteName('#__ka_trailers'))
				->where($db->quoteName('id') . ' = ' . (int) $item_id);

			$db->setQuery($query);

			try
			{
				$columns = $db->loadAssoc();
			}
			catch (Exception $e)
			{
				return false;
			}

			if (!empty($columns))
			{
				$result = json_decode($columns['video'], true);

				// Return only one result by ID, all otherwise.
				if ($item !== '')
				{
					$result = $result[$item];
				}
				else
				{
					$result['screenshot'] = $columns['screenshot'];
				}
			}
		}
		elseif ($type == 'subtitles')
		{
			JLoader::register('KALanguage', JPATH_COMPONENT . '/libraries/language.php');

			$query = $db->getQuery(true)
				->select($db->quoteName('subtitles'))
				->from($db->quoteName('#__ka_trailers'))
				->where($db->quoteName('id') . ' = ' . (int) $item_id);

			$db->setQuery($query);

			try
			{
				$column = $db->loadColumn();
			}
			catch (Exception $e)
			{
				return false;
			}

			if (!empty($column))
			{
				if ($item !== '')
				{
					$column = json_decode($column[0], true);
					$result = $column[$item];
				}
				else
				{
					$result = json_decode($column[0], true);
				}
			}
		}
		elseif ($type == 'chapters')
		{
			$query = $db->getQuery(true)
				->select($db->quoteName('chapters'))
				->from($db->quoteName('#__ka_trailers'))
				->where($db->quoteName('id') . ' = ' . (int) $item_id);

			$db->setQuery($query);

			try
			{
				$column = $db->loadColumn();
			}
			catch (Exception $e)
			{
				return false;
			}

			if (!empty($column))
			{
				$column = json_decode($column[$item], true);
				$result = $column;
			}
		}
		elseif ($type == 'image')
		{
			$query = $db->getQuery(true)
				->select($db->quoteName('screenshot'))
				->from($db->quoteName('#__ka_trailers'))
				->where($db->quoteName('id') . ' = ' . (int) $item_id);

			$db->setQuery($query);

			try
			{
				$column = $db->loadColumn();
				$result['file'] = $column[0];
			}
			catch (Exception $e)
			{
				return false;
			}
		}

		return array('trailer_finfo_' . $type => $result);
	}

	/**
	 * Remove a file associated with trailer.
	 *
	 * @param   string   $type     Content type
	 * @param   integer  $item_id  Trailer ID
	 * @param   mixed    $item     File ID or array of IDs
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	public function removeTrailerFiles($type, $item_id, $item = null)
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();

		if ($type == 'video')
		{
			$query = $db->getQuery(true)
				->select($db->quoteName('video'))
				->from($db->quoteName('#__ka_trailers'))
				->where($db->quoteName('id') . ' = ' . (int) $item_id);

			$db->setQuery($query);
			$result = $db->loadResult();
			$result = json_decode($result, true);

			if (!is_array($item))
			{
				unset($result[$item]);
			}
			else
			{
				foreach ($result as $key => $value)
				{
					if (in_array($key, $item))
					{
						unset($result[$key]);
					}
				}
			}

			$result_obj = ArrayHelper::toObject($result);
			$json = json_encode($result_obj);

			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('video') . " = '" . $json . "'")
				->where($db->quoteName('id') . ' = ' . (int) $item_id);

			$db->setQuery($query);

			try
			{
				$db->execute();

				return true;
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}
		}
		elseif ($type == 'subtitles')
		{
			$query = $db->getQuery(true)
				->select($db->quoteName('subtitles'))
				->from($db->quoteName('#__ka_trailers'))
				->where($db->quoteName('id') . ' = ' . (int) $item_id);

			$db->setQuery($query);
			$result = $db->loadResult();
			$result = json_decode($result, true);

			if (!is_array($item))
			{
				unset($result[$item]);
			}
			else
			{
				foreach ($result as $key => $value)
				{
					if (in_array($key, $item))
					{
						unset($result[$key]);
					}
				}
			}

			$result_obj = ArrayHelper::toObject($result);
			$json = json_encode($result_obj);

			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('subtitles') . " = '" . $json . "'")
				->where($db->quoteName('id') . ' = ' . (int) $item_id);

			$db->setQuery($query);

			try
			{
				$db->execute();

				return true;
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}
		}
		elseif ($type == 'chapters')
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('chapters') . " = '{}'")
				->where($db->quoteName('id') . ' = ' . (int) $item_id);

			$db->setQuery($query);

			try
			{
				$db->execute();

				return true;
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}
		}
		elseif ($type == 'image')
		{
			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('screenshot') . " = ''")
				->where($db->quoteName('id') . ' = ' . (int) $item_id);

			$db->setQuery($query);

			try
			{
				$db->execute();

				return true;
			}
			catch (Exception $e)
			{
				$app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}
		}
		else
		{
			$app->enqueueMessage('Wrong type', 'error');

			return false;
		}
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  mixed  True on success, False on error, lastInsertID on trailer save.
	 *
	 * @since   3.0
	 */
	public function save($data)
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$type = $app->input->get('type', '', 'word');
		$section = $app->input->get('section', '', 'word');
		$id = $app->input->get('id', 0, 'int');

		if ($section == 'movie')
		{
			if ($type == 'trailers')
			{
				if (empty($data['item_id']))
				{
					// Check if trailer with this title allready exists
					$query = $db->getQuery(true);

					$query->select('COUNT(id)')
						->from($db->quoteName('#__ka_trailers'))
						->where($db->quoteName('title') . " = '" . $db->escape(trim($data['title'])) . "'");

					$db->setQuery($query);
					$count = $db->loadResult();

					if ($count > 0)
					{
						$app->enqueueMessage(JText::_('COM_KA_TRAILERS_EXISTS'), 'error');

						return false;
					}

					$query = $db->getQuery(true);

					$query->insert($db->quoteName('#__ka_trailers'))
						->columns(
							$db->quoteName(
								array('id', 'movie_id', 'title', 'embed_code', 'screenshot', 'urls', 'resolution',
									'dar', 'duration', 'video', 'subtitles', 'chapters', 'frontpage', 'access',
									'state', 'language', 'is_movie'
								)
							)
						)
						->values("'', '" . (int) $id . "', '" . $db->escape(trim($data['title'])) . "', "
							. "'" . $db->escape($data['embed_code']) . "', '', '" . $db->escape($data['urls']) . "', "
							. "'" . $db->escape($data['resolution']) . "', '" . $db->escape($data['dar']) . "', "
							. "'" . $db->escape($data['duration']) . "', '{}', '{}', '{}', '" . (int) $data['frontpage'] . "', "
							. "'" . (int) $data['access'] . "', '" . (int) $data['state'] . "', "
							. "'" . $data['language'] . "', '" . (int) $data['is_movie'] . "'");
				}
				else
				{
					$query = $db->getQuery(true);

					$query->update($db->quoteName('#__ka_trailers'))
						->set($db->quoteName('title') . " = '" . $db->escape($data['title']) . "'," . $db->quoteName('embed_code') . " = '" . $data['embed_code'] . "'")
						->set($db->quoteName('urls') . " = '" . $data['urls'] . "'," . $db->quoteName('resolution') . " = '" . $data['resolution'] . "'")
						->set($db->quoteName('dar') . " = '" . $data['dar'] . "'," . $db->quoteName('duration') . " = '" . $data['duration'] . "'")
						->set($db->quoteName('frontpage') . " = '" . (int) $data['frontpage'] . "'," . $db->quoteName('access') . " = '" . (int) $data['access'] . "'")
						->set($db->quoteName('state') . " = '" . (int) $data['state'] . "'," . $db->quoteName('language') . " = '" . $data['language'] . "'")
						->set($db->quoteName('is_movie') . " = '" . $data['is_movie'] . "'")
						->where($db->quoteName('id') . ' = ' . (int) $data['item_id']);
				}

				$db->setQuery($query);

				try
				{
					$db->execute();

					// We need to store LastInsertID in session for later use in controller.
					if (empty($data['item_id']))
					{
						$session_data = $app->getUserState('com_kinoarhiv.trailers.' . $user->id . '.edit_data');
						$session_data['trailer']['item_id'] = $db->insertid();
						$app->setUserState('com_kinoarhiv.trailers.' . $user->id . '.edit_data', $session_data);
					}

					return true;
				}
				catch (Exception $e)
				{
					$app->enqueueMessage($e->getMessage(), 'error');

					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Removes an item.
	 *
	 * @param   string   $section  Type of the item. Can be 'movie' or 'name'.
	 * @param   string   $type     Type of the section. Can be 'gallery', 'trailers', 'soundtracks'
	 * @param   integer  $tab      Tab number from gallery(or empty value for 'trailers', 'soundtracks').
	 * @param   integer  $id       The item ID (movie or name).
	 * @param   array    $ids      Array of ID to remove(file id).
	 *
	 * @return  boolean   True on success, false on error.
	 *
	 * @since   3.0
	 */
	public function remove($section, $type, $tab = 0, $id = 0, $ids = array())
	{
		$db = $this->getDbo();

		if ($section == 'movie')
		{
			if ($type == 'gallery')
			{
				$query = $db->getQuery(true)
					->delete($db->quoteName('#__ka_movies_gallery'))
					->where($db->quoteName('movie_id') . ' = ' . (int) $id)
					->where($db->quoteName('type') . ' = ' . (int) $tab)
					->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');
			}
			elseif ($type == 'trailers')
			{
				$query = $db->getQuery(true)
					->delete($db->quoteName('#__ka_trailers'))
					->where($db->quoteName('movie_id') . ' = ' . (int) $id)
					->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');
			}
			else
			{
				JFactory::getApplication()->enqueueMessage('Wrong type', 'error');

				return false;
			}
		}
		elseif ($section == 'name')
		{
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__ka_names_gallery'))
				->where($db->quoteName('name_id') . ' = ' . (int) $id)
				->where($db->quoteName('type') . ' = ' . (int) $tab)
				->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');
		}
		else
		{
			JFactory::getApplication()->enqueueMessage('Wrong section', 'error');

			return false;
		}

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Saves the manually set order of files on trailers edit page.
	 *
	 * @param   integer  $item_id  Trailer ID
	 * @param   array    $items    Array of items to sort.
	 * @param   string   $type     Content type.
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	public function saveOrderTrailerFiles($item_id, $items, $type)
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName($type))
			->from($db->quoteName('#__ka_trailers'))
			->where($db->quoteName('id') . ' = ' . $item_id);

		$db->setQuery($query);

		try
		{
			$result = $db->loadResult();

			if (empty($result))
			{
				return false;
			}
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		$result_arr = json_decode($result, true);
		$new_arr = (object) array();

		foreach ($items as $new_index => $old_index)
		{
			foreach ($result_arr as $value)
			{
				$new_arr->$new_index = $result_arr[$old_index];
			}
		}

		$query = $db->getQuery(true)
			->update($db->quoteName('#__ka_trailers'))
			->set($db->quoteName($type) . " = '" . json_encode($new_arr) . "'")
			->where($db->quoteName('id') . ' = ' . (int) $item_id);

		$db->setQuery($query);

		try
		{
			$db->execute();

			return true;
		}
		catch (Exception $e)
		{
			return false;
		}
	}

	/**
	 * Set and save default subtitle for trailer.
	 *
	 * @param   boolean  $isDefault  Action state
	 *
	 * @return  boolean
	 *
	 * @since   3.0
	 */
	public function subtitleSetDefault($isDefault)
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$id = $app->input->get('id', 0, 'int');

		// Should be null because items index can start from zero.
		$item = $app->input->get('item', null, 'int');

		if (empty($id) || is_null($item))
		{
			$app->enqueueMessage(JText::_('COM_KA_TRAILERS_HEADING_UPLOAD_FILES_NOTSAVED'), 'error');

			return false;
		}

		$query = $db->getQuery(true)
			->select($db->quoteName('subtitles'))
			->from($db->quoteName('#__ka_trailers'))
			->where($db->quoteName('id') . ' = ' . $id);

		$db->setQuery($query);
		$result = $db->loadResult();

		if (empty($result))
		{
			$app->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

			return false;
		}

		$result_arr = json_decode($result);

		foreach ($result_arr as $key => $value)
		{
			// Set all other items to false except selected
			if ($key != $item)
			{
				$result_arr->$key->default = false;
			}
			else
			{
				// Unset 'default' state from item which allready have 'default' state
				if ($isDefault === false)
				{
					$result_arr->$key->default = false;
				}
				else
				{
					$result_arr->$key->default = true;
				}
			}
		}

		$query = $db->getQuery(true)
			->update($db->quoteName('#__ka_trailers'))
			->set($db->quoteName('subtitles') . " = '" . json_encode($result_arr) . "'")
			->where($db->quoteName('id') . ' = ' . (int) $id);

		$db->setQuery($query);

		try
		{
			$db->execute();

			return true;
		}
		catch (Exception $e)
		{
			$app->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

			return false;
		}
	}

	/**
	 * Save info about chapter file into DB
	 *
	 * @param   string   $file        Filename
	 * @param   integer  $trailer_id  ID of the trailer
	 * @param   integer  $movie_id    ID of the movie
	 *
	 * @return  mixed    Last insert ID on INSERT or true on UPDATE
	 *
	 * @since  3.0
	 */
	public function saveChapters($file, $trailer_id, $movie_id)
	{
		/*$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('COUNT(id)')
			->from($db->quoteName('#__ka_trailers'))
			->where($db->quoteName('id') . ' = ' . (int) $trailer_id);

		$db->setQuery($query);
		$total = $db->loadResult();

		$chapters = array('file' => $file);

		if ($total == 0)
		{
			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_trailers'))
				->columns(
					$db->quoteName(
						array('id', 'movie_id', 'title', 'embed_code', 'screenshot', 'filename', 'duration',
							'_subtitles', '_chapters', 'frontpage', 'access', 'state', 'language', 'is_movie'
						)
					)
				)
				->values("'', '" . (int) $movie_id . "', '', '', '', '{}', '00:00:00', '{}', '" . $chapters . "', '0', '1', '0', 'language', '0'");

			$db->setQuery($query);
			$query = $db->execute();

			return $query ? (int) $db->insertid() : false;
		}
		else
		{
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('_chapters') . " = '" . json_encode($chapters) . "'")
				->where($db->quoteName('id') . ' = ' . (int) $trailer_id);

			$db->setQuery($query);
			$query = $db->execute();

			return $query ? true : false;
		}*/
	}

	/**
	 * Method to save edited data from edit form for video/subtitles/chapters.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  string
	 *
	 * @since  3.0
	 */
	public function saveFileinfoData($data)
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$type = $app->input->get('list', '', 'word');
		$id = $app->input->get('id', 0, 'int');
		$item_id = $app->input->get('item_id', 0, 'int');
		$item = $app->input->get('item', null, 'int');
		$is_new = $app->input->get('new', 0, 'int');

		if (empty($item_id) || ($is_new == 0 && is_null($item)))
		{
			$app->enqueueMessage('Wrong ID', 'error');

			return false;
		}

		// Select existing data
		$query = $db->getQuery(true)
			->select($db->quoteName($type))
			->from($db->quoteName('#__ka_trailers'))
			->where($db->quoteName('id') . ' = ' . (int) $item_id);

		$db->setQuery($query);

		try
		{
			$result = $db->loadResult();

			if (empty($result))
			{
				return false;
			}
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		$files = json_decode($result, true);

		if ($type == 'video')
		{
			$new_filename = JPath::clean($data['src']);
			$file = array(
				'src'        => $new_filename,
				'type'       => $data['type'],
				'resolution' => trim($data['resolution'])
			);

			if ($is_new == 0)
			{
				$old_filename = JPath::clean($files[$item]['src']);
				$files[$item] = $file;
			}
			else
			{
				$files[] = $file;
			}
		}
		elseif ($type == 'subtitles')
		{
			$new_filename = JPath::clean($data['file']);
			$file = array(
				'file'      => $new_filename,
				'lang'      => trim($data['lang']),
				'lang_code' => $data['lang_code'],
				'default'   => (bool) $data['default']
			);

			if ($is_new == 0)
			{
				$old_filename = JPath::clean($files[$item]['file']);
				$files[$item] = $file;
			}
			else
			{
				$files[] = $file;

				// Subtract 1 because foreach() count from 0.
				$item = count($files) - 1;
			}

			// Process 'default' flag for all subtitles
			foreach ($files as $key => $_item)
			{
				// Set default=false for all subtitles except current edited.
				if ($key !== $item)
				{
					$files[$key]['default'] = false;
				}
			}
		}
		elseif ($type == 'chapters')
		{
			if ($is_new == 0)
			{
				$old_filename = JPath::clean($files['file']);
			}

			$new_filename = JPath::clean($data['file']);
			$files['file'] = $new_filename;
		}

		$file_obj = json_encode((object) $files);

		if ($is_new == 0)
		{
			// Rename the file
			$path = KAContentHelper::getPath('movie', 'trailers', 0, $id);

			if ($old_filename !== $new_filename)
			{
				if (is_file($path . $old_filename))
				{
					if (@rename($path . $old_filename, $path . $new_filename) === false)
					{
						$app->enqueueMessage('Error while renaming file!', 'error');
					}
				}
				else
				{
					$app->enqueueMessage('Error while renaming file! File ' . $old_filename . ' not found', 'error');
				}
			}
		}

		$query = $db->getQuery(true)
			->update($db->quoteName('#__ka_trailers'))
			->set($db->quoteName($type) . " = '" . $db->escape($file_obj) . "'")
			->where($db->quoteName('id') . ' = ' . (int) $item_id);

		$db->setQuery($query);

		try
		{
			$db->execute();

			return true;
		}
		catch (Exception $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}
	}

	/**
	 * Save info about subtitles file into DB
	 *
	 * @param   string   $file         Filename
	 * @param   integer  $trailer_id   ID of the trailer
	 * @param   integer  $movie_id     ID of the movie
	 * @param   integer  $subtitle_id  ID of the movie
	 * @param   boolean  $edit         If set to true, we save info from subtitle information edit form.
	 *
	 * @return  mixed    Last insert ID on INSERT or true on UPDATE
	 *
	 * @since  3.0
	 */
	public function saveSubtitles($file, $trailer_id, $movie_id = 0, $subtitle_id = null, $edit = false)
	{
		/*jimport('joomla.filesystem.file');
		jimport('components.com_kinoarhiv.libraries.language', JPATH_ROOT . '/administrator');

		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$lang_list = KALanguage::listOfLanguages();

		$query = $db->getQuery(true)
			->select($db->quoteName('_subtitles'))
			->from($db->quoteName('#__ka_trailers'))
			->where($db->quoteName('id') . ' = ' . (int) $trailer_id);

		$db->setQuery($query);
		$result = $db->loadResult();

		if ($edit === true)
		{
			$subtl_obj = json_decode($result);
			$lang_data = json_decode($app->input->get('language', '', 'string'));
			$default = $app->input->get('default', 'false', 'string');
			$desc = $app->input->get('desc', '', 'string');
			$desc = $desc != '' ? ' ' . $desc : '';

			if (isset($subtl_obj->$subtitle_id))
			{
				if ($default == 'true')
				{
					// Set to false all 'default' flags
					foreach ($subtl_obj as $key => $subtl)
					{
						$subtl_obj->$key->default = false;
					}

					$subtl_obj->$subtitle_id->default = true;
				}

				$subtl_obj->$subtitle_id->lang_code = $lang_data->lang_code;
				$subtl_obj->$subtitle_id->lang = $lang_data->lang . $desc;

				$fs_alias = $this->getFilesystemAlias('movie', $movie_id, true);
				$rn_dest_dir = $this->getPath('movie', 'trailers', 0, $movie_id);
				$old_filename = $rn_dest_dir . $subtl_obj->$subtitle_id->file;
				$ext = JFile::getExt($old_filename);
				$rn_filename = $fs_alias . '-' . $trailer_id . '.subtitles.' . $lang_data->lang_code . '.' . $ext;
				$subtl_obj->$subtitle_id->file = $rn_filename;

				rename($old_filename, $rn_dest_dir . $rn_filename);
			}

			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('_subtitles') . " = '" . $db->escape(json_encode($subtl_obj)) . "'")
				->where($db->quoteName('id') . ' = ' . (int) $trailer_id);

			$db->setQuery($query);

			try
			{
				$db->execute();
				$result = true;
			}
			catch (Exception $e)
			{
				return false;
			}
		}
		else
		{
			$subtl_arr = json_decode($result, true);

			/* On 'else' condition we do nothing because no information about trailer exists in DB.
			 * In this situation files will be successfully uploaded, but not saved in DB.
			*/
			/*if (!empty($trailer_id))
			{
				if (preg_match('#subtitles\.(.*?)\.#si', $file, $matches))
				{
					// Get the language code from filename
					$lang_code = strtolower($matches[1]);
				}
				else
				{
					// Default set to English as it required for proper display
					$lang_code = 'en';
				}

				/* Uncomment block below if you want to check for duplicate languages.
				 Checking if lang allready exists and return false.*/
				/*$lang_exists = false;

				foreach ($subtl_arr as $k=>$v) {
					if ($v['lang_code'] == $lang_code) {
						$lang_exists = true;
						break;
					}
				}

				if ($lang_exists) {
					return false;
				}*/

				/*$subtl_arr[] = array(
					'default'   => false,
					'lang_code' => $lang_code,
					'lang'      => $lang_list[$lang_code],
					'file'      => $file
				);

				$subtl_obj = ArrayHelper::toObject($subtl_arr);
				$query = $db->getQuery(true);

				$query->update($db->quoteName('#__ka_trailers'))
					->set($db->quoteName('_subtitles') . " = '" . $db->escape(json_encode($subtl_obj)) . "'")
					->where($db->quoteName('id') . ' = ' . (int) $trailer_id);

				$db->setQuery($query);

				try
				{
					$db->execute();
					$result = true;
				}
				catch (Exception $e)
				{
					return false;
				}
			}
		}

		return $result;*/
	}

	/**
	 * Method to save information about uploaded videofile into JSON object and store it in database.
	 *
	 * @param   string   $filename    Filename to process and store.
	 * @param   integer  $trailer_id  Trailer ID.
	 * @param   integer  $movie_id    Movie ID.
	 *
	 * @return  mixed   Array of filtered data if valid, false otherwise.
	 *
	 * @since   3.0
	 */
	public function saveVideo($filename, $trailer_id, $movie_id)
	{
		/*$media = KAMedia::getInstance();
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('filename'))
			->from($db->quoteName('#__ka_trailers'))
			->where($db->quoteName('id') . ' = ' . (int) $trailer_id);

		$db->setQuery($query);
		$result = $db->loadResult();

		$result_arr = json_decode($result, true);

		// If not empty and items more than 0 when add to array and store
		if (!empty($result) && count($result_arr) > 0)
		{
			// Checking if file allready exists in DB
			$file_exists = false;

			foreach ($result_arr as $k => $v)
			{
				if ($v['src'] == $filename)
				{
					$file_exists = true;
					break;
				}
			}

			if ($file_exists)
			{
				return false;
			}
			// End

			$files_arr = $result_arr;
			$mime_type = $media->detectMime($this->getPath('movie', 'trailers', 0, $movie_id) . $filename);
			$video_info = json_decode($media->getVideoInfo($this->getPath('movie', 'trailers', 0, $movie_id) . $filename));
			$duration = $media->getVideoDuration($this->getPath('movie', 'trailers', 0, $movie_id) . $filename, true);

			if (is_array($duration))
			{
				$duration = '00:00:00:000';
			}

			if (is_object($video_info))
			{
				$stream_width  = !isset($video_info->streams[0]->width) ? 0 : $video_info->streams[0]->width;
				$stream_height = !isset($video_info->streams[0]->height) ? 0 : $video_info->streams[0]->height;
				$stream_dar    = !isset($video_info->streams[0]->display_aspect_ratio) ? '16x9' : $video_info->streams[0]->display_aspect_ratio;
			}
			else
			{
				$stream_width  = 0;
				$stream_height = 0;
				$stream_dar    = '16x9';
			}

			$files_arr[] = array(
				'src'        => $filename,
				'type'       => $mime_type,
				'resolution' => $stream_width . 'x' . $stream_height
			);

			$new_obj = ArrayHelper::toObject($files_arr);
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('filename') . " = '" . json_encode($new_obj) . "'")
				->set($db->quoteName('resolution') . " = '" . $stream_width . 'x' . $stream_height . "'")
				->set($db->quoteName('dar') . " = '" . $stream_dar . "'")
				->set($db->quoteName('duration') . " = '" . $duration . "'")
				->where($db->quoteName('id') . ' = ' . (int) $trailer_id);

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				return false;
			}
		}
		else
		{
			$mime_type = $media->detectMime($this->getPath('movie', 'trailers', 0, $movie_id) . $filename);
			$video_info = $media->getVideoInfo($this->getPath('movie', 'trailers', 0, $movie_id) . $filename);

			if ($video_info === false)
			{
				KAComponentHelper::eventLog(
					JText::sprintf(
						'COM_KA_MEDIAMANAGER_FFMPEG_NOTFOUND',
						JComponentHelper::getParams('com_kinoarhiv')->get('ffmpeg_path') . ', ' . JComponentHelper::getParams('com_kinoarhiv')->get('ffprobe_path')
					)
				);
			}

			$video_info = json_decode($video_info);
			$duration = $media->getVideoDuration($this->getPath('movie', 'trailers', 0, $movie_id) . $filename, true);

			if (is_array($duration))
			{
				$duration = '00:00:00:000';
			}

			if (is_object($video_info))
			{
				$stream_width  = !isset($video_info->streams[0]->width) ? 0 : $video_info->streams[0]->width;
				$stream_height = !isset($video_info->streams[0]->height) ? 0 : $video_info->streams[0]->height;
				$stream_dar    = !isset($video_info->streams[0]->display_aspect_ratio) ? '16x9' : $video_info->streams[0]->display_aspect_ratio;
			}
			else
			{
				$stream_width  = 0;
				$stream_height = 0;
				$stream_dar    = '16x9';
			}

			$files_arr = array(
				0 => array(
					'src'        => $filename,
					'type'       => $mime_type,
					'resolution' => $stream_width . 'x' . $stream_height
				)
			);

			$new_obj = ArrayHelper::toObject($files_arr);
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_trailers'))
				->set($db->quoteName('filename') . " = '" . json_encode($new_obj) . "'")
				->set($db->quoteName('resolution') . " = '" . $stream_width . 'x' . $stream_height . "'")
				->set($db->quoteName('dar') . " = '" . $stream_dar . "'")
				->set($db->quoteName('duration') . " = '" . $duration . "'")
				->where($db->quoteName('id') . ' = ' . (int) $trailer_id);

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				return false;
			}
		}

		return true;*/
	}

	/**
	 * Method to copy gallery items from one item to another.
	 *
	 * @param   string   $section   Section. (can be: movie, name, soundtrack)
	 * @param   string   $type      Item type.
	 * @param   integer  $from_id   ID to copy from.
	 * @param   string   $to_id     ID where to copy.
	 * @param   string   $from_tab  Content type.
	 *
	 * @return  boolean   True on success, false on error.
	 *
	 * @since   3.0
	 */
	public function copyfrom($section, $type, $from_id, $to_id, $from_tab)
	{
		$db = $this->getDbo();
		$app = JFactory::getApplication();
		$query_result = true;

		// Update DB
		if ($type == 'gallery')
		{
			if ($section == 'movie')
			{
				$table = '#__ka_movies_gallery';
				$col = 'movie_id';
			}
			elseif ($section == 'name')
			{
				$table = '#__ka_names_gallery';
				$col = 'name_id';
			}
			else
			{
				return false;
			}

			// Get all table columns from $table
			$cols_obj = $db->getTableColumns($table);
			$_keys = $db->quoteName(array_keys($cols_obj));
			$cols = implode(', ', $_keys);
			$cols_count = count($_keys);

			// Get all rows $table
			$query = $db->getQuery(true)
				->select($cols)
				->from($db->quoteName($table))
				->where($db->quoteName($col) . ' = ' . (int) $from_id . ' AND ' . $db->quoteName('type') . ' = ' . (int) $from_tab);

				// Remove from result set rows with the same filename(avoid duplicates).
				$subquery = $db->getQuery(true)
					->select($db->quoteName('filename'))
					->from($db->quoteName($table))
					->where($db->quoteName($col) . ' = ' . (int) $to_id . ' AND ' . $db->quoteName('type') . ' = ' . (int) $from_tab);

			$query->where($db->quoteName('filename') . 'NOT IN (' . $subquery . ')');

			$db->setQuery($query);
			$rows = $db->loadObjectList();

			$db->setDebug(true);
			$db->lockTable($table);
			$db->transactionStart();

			foreach ($rows as $values)
			{
				$value = "";
				$i = 0;

				foreach ($values as $key => $val)
				{
					if ($key == 'id')
					{
						$value .= "''";
					}
					else
					{
						if ($key == $col)
						{
							$value .= "'" . (int) $to_id . "'";
						}
						else
						{
							$value .= "'" . $db->escape($val) . "'";
						}
					}

					if ($i + 1 != $cols_count)
					{
						$value .= ', ';
					}

					$i++;
				}

				$query = $db->getQuery(true)
					->insert($db->quoteName($table))
					->columns($cols)
					->values($value);

				$db->setQuery($query . ';');

				if ($db->execute() === false)
				{
					$query_result = false;
					break;
				}
			}

			if ($query_result === false)
			{
				$db->transactionRollback();
			}
			else
			{
				$db->transactionCommit();
			}

			$db->unlockTables();
			$db->setDebug(false);
		}
		else
		{
			$app->enqueueMessage('Wrong item type', 'error');

			return false;
		}

		return true;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed   Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null)
	{
		// Include the plugins for the delete events.
		JPluginHelper::importPlugin($this->events_map['validate']);

		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('onUserBeforeDataValidation', array($form, &$data));

		// Filter and validate the form data.
		$data = $form->filter($data);
		$return = $form->validate($data, $group);

		// Check for an error.
		if ($return instanceof Exception)
		{
			$this->setError($return->getMessage());

			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message)
			{
				$this->setError($message);
			}

			return false;
		}

		return $data;
	}
}
