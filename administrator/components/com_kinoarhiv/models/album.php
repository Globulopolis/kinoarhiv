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

use Joomla\Registry\Registry;

/**
 * Class KinoarhivModelAlbum
 *
 * @since  3.1
 */
class KinoarhivModelAlbum extends JModelForm
{
	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   3.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm('com_kinoarhiv.album', 'album', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array    The default data is an empty array.
	 *
	 * @since   3.0
	 */
	protected function loadFormData()
	{
		$data = JFactory::getApplication()->getUserState('com_kinoarhiv.music.albums.' . JFactory::getUser()->id . '.edit_data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since   3.0
	 */
	public function getItem()
	{
		$app = JFactory::getApplication();
		$db  = $this->getDbo();
		$id  = $app->input->get('id', array(), 'array');

		$query = $db->getQuery(true)
			->select(
				$db->quoteName(
					array('a.id', 'a.asset_id', 'a.title', 'a.alias', 'a.fs_alias', 'a.year', 'a.length', 'a.isrc',
						'a.desc', 'a.rate', 'a.rate_sum', 'a.covers_path', 'a.covers_path_www', 'a.cover_filename',
						'a.tracks_path', 'a.tracks_path_www', 'a.tracks_preview_path', 'a.buy_urls', 'a.attribs',
						'a.created', 'a.created_by', 'a.modified', 'a.modified_by', 'a.publish_up', 'a.publish_down',
						'a.ordering', 'a.metakey', 'a.metadesc', 'a.access', 'a.metadata', 'a.language', 'a.state'
					)
				)
			)
			->select($db->quoteName('a.fs_alias', 'fs_alias_orig'))
			->from($db->quoteName('#__ka_music_albums', 'a'))
			->where($db->quoteName('a.id') . ' = ' . (int) $id);

		// Join over the language
		$query->select($db->quoteName('l.title', 'language_title'))
			->leftJoin($db->quoteName('#__languages', 'l') . ' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language'));

		$db->setQuery($query);

		try
		{
			$result = $db->loadObject();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return array();
		}

		if (empty($id))
		{
			return $result;
		}

		$genres = $this->getGenres($id);

		if ($genres)
		{
			$genres = implode(',', $genres['id']);
			$result->genres = $genres;
			$result->genres_orig = $genres;
		}

		$registry = new Registry($result->attribs);
		$result->attribs = $registry->toArray();

		$registry = new Registry($result->metadata);
		$result->metadata = $registry->toArray();

		if ($id)
		{
			$tags = new JHelperTags;
			$tags->getTagIds($result->id, 'com_kinoarhiv.album');
			$result->tags = $tags;
		}

		return $result;
	}

	public function publish($isUnpublish)
	{
		$app   = JFactory::getApplication();
		$db    = $this->getDbo();
		$ids   = $app->input->get('id', array(), 'array');
		$state = $isUnpublish ? 0 : 1;
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_music_albums'))
			->set($db->quoteName('state') . ' = ' . (int) $state)
			->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);

		try
		{
			$db->execute();

			return true;
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}
	}

	/**
	 * Get list of genres for field.
	 *
	 * @param   integer  $id  Item ID.
	 *
	 * @return  mixed    Array with data, false otherwise.
	 *
	 * @since   3.0
	 */
	protected function getGenres($id)
	{
		$app = JFactory::getApplication();
		$db  = $this->getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('g.id') . ',' . $db->quoteName('g.name', 'title'))
			->from($db->quoteName('#__ka_music_rel_genres', 'rel'))
			->leftJoin($db->quoteName('#__ka_genres', 'g') . ' ON ' . $db->quoteName('g.id') . ' = ' . $db->quoteName('rel.genre_id'))
			->where($db->quoteName('rel.item_id') . ' = ' . (int) $id)
			->where($db->quoteName('rel.type') . ' = 0')
			->order($db->quoteName('rel.ordering') . ' ASC');

		$db->setQuery($query);

		try
		{
			$_genres = $db->loadAssocList();
			$genres = array();

			foreach ($_genres as $key => $id)
			{
				$genres['id'][$key] = $id['id'];
				$genres['title'][$key] = $id['title'];
			}
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return $genres;
	}

	// TODO Refactor
	protected function getTags()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$id = $app->input->get('id', array(), 'array');

		if (!empty($id[0]))
		{
			$query = $db->getQuery(true);

			$query->select($db->quoteName('metadata'))
				->from($db->quoteName('#__ka_music_albums'))
				->where($db->quoteName('id') . ' = ' . (int) $id[0]);

			$db->setQuery($query);
			$metadata = $db->loadResult();
			$meta_arr = json_decode($metadata);

			if (is_null($meta_arr) || count($meta_arr->tags) == 0)
			{
				return array('data' => array(), 'ids' => '');
			}

			$query = $db->getQuery(true);

			$query->select($db->quoteName(array('id', 'title')))
				->from($db->quoteName('#__tags'))
				->where($db->quoteName('id') . ' IN (' . implode(',', $meta_arr->tags) . ')')
				->order($db->quoteName('lft') . ' ASC');

			$db->setQuery($query);
			$result['data'] = $db->loadObjectList();

			foreach ($result['data'] as $value)
			{
				$result['ids'][] = $value->id;
			}
		}
		else
		{
			$result = array('data' => array(), 'ids' => '');
		}

		return $result;
	}

	// TODO Refactor
	public function getComposers()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$id = $app->input->get('id', null, 'int');
		$orderby = $app->input->get('sidx', '1', 'string');
		$order = $app->input->get('sord', 'asc', 'word');
		$page = $app->input->get('page', 0, 'int');
		$search_field = $app->input->get('searchField', '', 'string');
		$search_operand = $app->input->get('searchOper', 'eq', 'cmd');
		$search_string = $app->input->get('searchString', '', 'string');
		$result = (object) array();
		$result->rows = array();

		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'title')))
			->from($db->quoteName('#__ka_names_career'))
			->order($db->quoteName('ordering') . ' ASC');

		$db->setQuery($query);
		$_careers = $db->loadObjectList();
		$careers = array();

		foreach ($_careers as $career)
		{
			$careers[$career->id] = $career->title;
		}

		$query = $db->getQuery(true);

		$query->select($db->quoteName('n.id', 'name_id') . ',' . $db->quoteName('n.name') . ',' . $db->quoteName('n.latin_name'))
			->select($db->quoteName(array('t.type', 't.role', 't.ordering')))
			->from($db->quoteName('#__ka_names', 'n'))
			->join('LEFT', $db->quoteName('#__ka_music_rel_composers', 't') . ' ON ' . $db->quoteName('t.name_id') . ' = ' . $db->quoteName('n.id') . ' AND ' . $db->quoteName('t.album_id') . ' = ' . (int) $id);

		$where_subquery = $db->getQuery(true)
			->select($db->quoteName('name_id'))
			->from($db->quoteName('#__ka_music_rel_composers'))
			->where($db->quoteName('album_id') . ' = ' . (int) $id);

		$where = "";

		if (!empty($search_string))
		{
			if ($search_field == 'n.name')
			{
				$where .= " AND (" . KADatabaseHelper::transformOperands($db->quoteName($search_field), $search_operand, $db->escape($search_string)) . " OR " . KADatabaseHelper::transformOperands($db->quoteName('n.latin_name'), $search_operand, $db->escape($search_string)) . ")";
			}
			else
			{
				$where .= " AND " . KADatabaseHelper::transformOperands($db->quoteName($search_field), $search_operand, $db->escape($search_string));
			}
		}

		$query->where($db->quoteName('n.id') . ' IN (' . $where_subquery . ')' . $where);
		$query->group($db->quoteName('n.id'));

		// Preventing 'ordering asc/desc, ordering asc/desc' duplication
		if (strpos($orderby, 'ordering') !== false)
		{
			$query->order($db->quoteName('t.ordering') . ' ASC');
		}
		else
		{
			// We need this if grid grouping is used. At the first(0) index - grouping field
			$ord_request = explode(',', $orderby);

			if (count($ord_request) > 1)
			{
				$query->order($db->quoteName(trim($ord_request[1])) . ' ' . strtoupper($order) . ', ' . $db->quoteName('t.ordering') . ' ASC');
			}
			else
			{
				$query->order($db->quoteName(trim($orderby)) . ' ' . strtoupper($order) . ', ' . $db->quoteName('t.ordering') . ' ASC');
			}
		}

		$db->setQuery($query);
		$names = $db->loadObjectList();

		// Presorting based on the type of career person
		$i = 0;
		$_result = array();

		foreach ($names as $value)
		{
			$name = '';

			if (!empty($value->name))
			{
				$name .= $value->name;
			}

			if (!empty($value->name) && !empty($value->latin_name))
			{
				$name .= ' / ';
			}

			if (!empty($value->latin_name))
			{
				$name .= $value->latin_name;
			}

			foreach (explode(',', $value->type) as $k => $type)
			{
				$_result[$type][$i] = array(
					'name'     => $name,
					'name_id'  => $value->name_id,
					'role'     => $value->role,
					'ordering' => $value->ordering,
					'type'     => $careers[$type],
					'type_id'  => $type
				);

				$i++;
			}
		}

		// The final sorting of the array for the grid
		$k = 0;

		foreach ($_result as $row)
		{
			foreach ($row as $elem)
			{
				$result->rows[$k]['id'] = $elem['name_id'] . '_' . $id . '_' . $elem['type_id'];
				$result->rows[$k]['cell'] = array(
					'name'     => $elem['name'],
					'name_id'  => $elem['name_id'],
					'role'     => $elem['role'],
					'ordering' => $elem['ordering'],
					'type'     => $elem['type']
				);

				$k++;
			}
		}

		$result->page = $page;
		$result->total = 1;
		$result->records = count($result->rows);

		return $result;
	}

	// TODO Refactor
	public function deleteComposers()
	{
		$app = JFactory::getApplication();
		$db = $this->getDbo();
		$data = $app->input->post->get('data', array(), 'array');
		$query_result = true;

		if (count($data) <= 0)
		{
			return array('success' => false, 'message' => JText::_('JERROR_NO_ITEMS_SELECTED'));
		}

		$db->setDebug(true);
		$db->lockTable('#__ka_music_rel_composers');
		$db->transactionStart();

		foreach ($data as $key => $value)
		{
			$ids = explode('_', $value['name']);
			$query = $db->getQuery(true);

			$query->delete($db->quoteName('#__ka_music_rel_composers'))
				->where($db->quoteName('name_id') . ' = ' . (int) $ids[3] . ' AND ' . $db->quoteName('album_id') . ' = ' . (int) $ids[4] . ' AND FIND_IN_SET("' . (int) $ids[5] . '", ' . $db->quoteName('type') . ')');
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
			$success = false;
			$message = JText::_('COM_KA_ITEMS_DELETED_ERROR');
		}
		else
		{
			$db->transactionCommit();
			$success = true;
			$message = JText::_('COM_KA_ITEMS_DELETED_SUCCESS');
		}

		$db->unlockTables();
		$db->setDebug(false);

		return array('success' => $success, 'message' => $message);
	}

	public function saveAccessRules()
	{
		$app   = JFactory::getApplication();
		$db    = $this->getDbo();
		$data  = $app->input->post->get('form', array(), 'array');
		$id    = $app->input->get('id', null, 'int');
		$rules = array();

		if (empty($id))
		{
			return array('success' => false, 'message' => 'Error');
		}

		foreach ($data['album']['rules'] as $rule => $groups)
		{
			foreach ($groups as $group => $value)
			{
				if ($value != '')
				{
					$rules[$rule][$group] = (int) $value;
				}
				else
				{
					unset($data['rules'][$rule][$group]);
				}
			}
		}

		$rules = json_encode($rules);

		// Get parent id
		$query = $db->getQuery(true);

		$query->select($db->quoteName('id'))
			->from($db->quoteName('#__assets'))
			->where($db->quoteName('name') . " = 'com_kinoarhiv' AND " . $db->quoteName('parent_id') . " = 1");

		$db->setQuery($query);
		$parentID = $db->loadResult();

		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__assets'))
			->set($db->quoteName('rules') . " = '" . $rules . "'")
			->where($db->quoteName('#__assets') . ' = ' . $db->quote('com_kinoarhiv.album.' . (int) $id))
			->where($db->quoteName('level') . ' = 2 AND ' . $db->quoteName('parent_id') . " = " . (int) $parentID);

		$db->setQuery($query);

		try
		{
			$db->execute();

			return array('success' => true);
		}
		catch (Exception $e)
		{
			return array('success' => false, 'message' => $e->getMessage());
		}
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm   $form   The form to validate against.
	 * @param   array   $data   The data to validate.
	 * @param   string  $group  The name of the field group to validate.
	 *
	 * @return  mixed  Array of filtered data if valid, false otherwise.
	 *
	 * @see     JFormRule
	 * @see     JFilterInput
	 * @since   12.2
	 */
	public function validate($form, $data, $group = null)
	{
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
