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

use Joomla\String\StringHelper;

JLoader::register('KADatabaseHelper', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'database.php');

/**
 * Class KinoarhivModelName
 *
 * @since  3.0
 */
class KinoarhivModelName extends JModelForm
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
		$form = $this->loadForm('com_kinoarhiv.name', 'name', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		$id = JFactory::getApplication()->input->get('id', array(), 'array');
		$id = (isset($id[0]) && !empty($id[0])) ? $id[0] : 0;
		$user = JFactory::getUser();

		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_kinoarhiv.name.' . (int) $id))
			|| ($id == 0 && !$user->authorise('core.edit.state', 'com_kinoarhiv')))
		{
			$form->setFieldAttribute('state', 'disabled', 'true');
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
		$data = JFactory::getApplication()->getUserState('com_kinoarhiv.names.' . JFactory::getUser()->id . '.edit_data', array());

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
	 */
	public function getItem()
	{
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();
		$db = $this->getDBO();
		$tmpl = $app->input->get('template', '', 'string');
		$id = $app->input->get('id', array(), 'array');

		if ($tmpl == 'awards_edit')
		{
			$award_id = $app->input->get('award_id', 0, 'int');
			$query = $db->getQuery(true);

			$query->select(
				$db->quoteName('id', 'rel_aw_id') . ',' . $db->quoteName('item_id') . ',' . $db->quoteName('award_id')
					. ',' . $db->quoteName('desc', 'aw_desc') . ',' . $db->quoteName('year', 'aw_year')
			)
			->from($db->quoteName('#__ka_rel_awards'))
			->where($db->quoteName('id') . ' = ' . (int) $award_id);

			$db->setQuery($query);
			$result = $db->loadObject();
		}
		else
		{
			$result = array('name' => (object) array());

			if (count($id) == 0 || empty($id) || empty($id[0]))
			{
				return $result;
			}

			$query = $db->getQuery(true);

			$query->select(
				$db->quoteName(
					array('n.id', 'n.asset_id', 'n.name', 'n.latin_name', 'n.alias', 'n.fs_alias', 'n.date_of_birth',
						'n.date_of_death', 'n.birthplace', 'n.birthcountry', 'n.gender', 'n.height', 'n.desc', 'n.attribs',
						'n.ordering', 'n.state', 'n.access', 'n.metakey', 'n.metadesc', 'n.metadata', 'n.language'
					)
				)
			)
			->select($db->quoteName('n.fs_alias', 'fs_alias_orig'))
			->from($db->quoteName('#__ka_names', 'n'))
			->where($db->quoteName('n.id') . ' = ' . (int) $id[0]);

			// Join over the language
			$query->select($db->quoteName('l.title', 'language_title'))
				->join('LEFT', $db->quoteName('#__languages', 'l') . ' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('n.language'));

			// Join over the gallery item
			$query->select($db->quoteName('g.id', 'gid') . ',' . $db->quoteName('g.filename'))
				->join('LEFT', $db->quoteName('#__ka_names_gallery', 'g') . ' ON ' . $db->quoteName('g.name_id') . ' = ' . $db->quoteName('n.id')
					. ' AND ' . $db->quoteName('g.type') . ' = 3'
					. ' AND ' . $db->quoteName('g.photo_frontpage') . ' = 1');

			// Join over countries
			$query->select($db->quoteName('c.name', 'country') . ',' . $db->quoteName('c.code', 'country_code'))
				->join('LEFT', $db->quoteName('#__ka_countries', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('n.birthcountry'));

			$db->setQuery($query);
			$result['name'] = $db->loadObject();

			$result['name']->genres = $this->getGenres();
			$result['name']->genres_orig = implode(',', $result['name']->genres['ids']);
			$result['name']->careers = $this->getCareers();
			$result['name']->careers_orig = implode(',', $result['name']->careers['ids']);

			if (!empty($result['name']->attribs))
			{
				$result['attribs'] = json_decode($result['name']->attribs);
			}

			if (empty($result['name']->fs_alias))
			{
				$result['name']->fs_alias = strtolower($lang->transliterate(StringHelper::substr($result['name']->alias, 0, 1)));
			}
		}

		return $result;
	}

	public function publish($isUnpublish)
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');
		$state = $isUnpublish ? 0 : 1;
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_names'))
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

	protected function getGenres()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', array(), 'array');
		$result = array('data' => array(), 'ids' => array());
		$query = $db->getQuery(true);

		$query->select($db->quoteName('g.id') . ',' . $db->quoteName('g.name', 'title'))
			->from($db->quoteName('#__ka_genres', 'g'));

		$subquery = $db->getQuery(true);
		$subquery->select($db->quoteName('genre_id'))
			->from($db->quoteName('#__ka_rel_names_genres'))
			->where($db->quoteName('name_id') . ' = ' . (int) $id[0]);

		$query->where($db->quoteName('id') . ' IN (' . $subquery . ')');

		$db->setQuery($query);
		$result['data'] = $db->loadObjectList();

		foreach ($result['data'] as $value)
		{
			$result['ids'][] = $value->id;
		}

		return $result;
	}

	protected function getCareers()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', array(), 'array');
		$result = array('data' => array(), 'ids' => array());
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('c.id', 'c.title')))
			->from($db->quoteName('#__ka_names_career', 'c'));

		$subquery = $db->getQuery(true);
		$subquery->select($db->quoteName('career_id'))
			->from($db->quoteName('#__ka_rel_names_career'))
			->where($db->quoteName('name_id') . ' = ' . (int) $id[0]);

		$query->where($db->quoteName('id') . ' IN (' . $subquery . ')');

		$db->setQuery($query);
		$result['data'] = $db->loadObjectList();

		foreach ($result['data'] as $value)
		{
			$result['ids'][] = $value->id;
		}

		return $result;
	}

	public function getAwards()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$id = $app->input->get('id', null, 'int');
		$orderby = $app->input->get('sidx', '1', 'string');
		$order = $app->input->get('sord', 'asc', 'word');
		$limit = $app->input->get('rows', 50, 'int');
		$page = $app->input->get('page', 0, 'int');
		$search_field = $app->input->get('searchField', '', 'string');
		$search_operand = $app->input->get('searchOper', 'eq', 'cmd');
		$search_string = $app->input->get('searchString', '', 'string');
		$limitstart = $limit * $page - $limit;
		$limitstart = $limitstart <= 0 ? 0 : $limitstart;
		$result = (object) array('rows' => array());
		$where = "";

		if (!empty($search_string))
		{
			$where .= " AND " . KADatabaseHelper::transformOperands($db->quoteName($search_field), $search_operand, $db->escape($search_string));
		}

		$query = $db->getQuery(true);

		$query->select('COUNT(rel.id)')
			->from($db->quoteName('#__ka_rel_awards', 'rel'))
			->where($db->quoteName('rel.item_id') . ' = ' . (int) $id . ' AND ' . $db->quoteName('rel.type') . ' = 1' . $where)
			->join('LEFT', $db->quoteName('#__ka_awards', 'aw') . ' ON ' . $db->quoteName('aw.id') . ' = ' . $db->quoteName('rel.award_id'));

		$db->setQuery($query);
		$total = $db->loadResult();

		$total_pages = ($total > 0) ? ceil($total / $limit) : 0;
		$page = ($page > $total_pages) ? $total_pages : $page;

		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('rel.id', 'rel.item_id', 'rel.award_id', 'rel.desc', 'rel.year', 'rel.type', 'aw.title')))
			->from($db->quoteName('#__ka_rel_awards', 'rel'))
			->join('LEFT', $db->quoteName('#__ka_awards', 'aw') . ' ON ' . $db->quoteName('aw.id') . ' = ' . $db->quoteName('rel.award_id'))
			->where($db->quoteName('rel.item_id') . ' = ' . (int) $id . ' AND ' . $db->quoteName('rel.type') . ' = 1' . $where)
			->order($db->quoteName($orderby) . ' ' . strtoupper($order))
			->setLimit($limit, $limitstart);

		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$k = 0;

		foreach ($rows as $elem)
		{
			$result->rows[$k]['id'] = $elem->id . '_' . $elem->item_id . '_' . $elem->award_id;
			$result->rows[$k]['cell'] = array(
				'id'       => $elem->id,
				'award_id' => $elem->award_id,
				'title'    => $elem->title,
				'year'     => $elem->year,
				'desc'     => JHtml::_('string.truncate', $elem->desc, $params->get('limit_text'))
			);

			$k++;
		}

		$result->page = $page;
		$result->total = $total_pages;
		$result->records = $total;

		return $result;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.0
	 */
	public function save($data)
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$id = $app->input->post->get('id', null, 'int');
		$quick_save = $app->input->get('quick_save', 0, 'int');

		if ($quick_save == 0)
		{
			$metadata = array('robots' => $data['robots']);
			$attribs = json_encode($data['attribs']);
		}
		else
		{
			$metadata = array('robots' => array());
			$attribs = json_encode(array());
			$data['name']['state'] = 1;
			$data['name']['access'] = 1;
			$data['name']['metakey'] = '';
			$data['name']['metadesc'] = '';
		}

		$data = $data['name'];

		// Check if person with this name allready exists
		if (empty($id))
		{
			$query = $db->getQuery(true);

			$query->select('COUNT(id)')
				->from($db->quoteName('#__ka_names'))
				->where($db->quoteName('name') . " LIKE '" . $db->escape(trim($data['name'])) . "%' OR " . $db->quoteName('latin_name') . " LIKE '" . $db->escape(trim($data['latin_name'])) . "%'");

			$db->setQuery($query);
			$count = $db->loadResult();

			if ($count > 0)
			{
				$this->setError(JText::_('COM_KA_NAMES_EXISTS'));

				$app->setUserState('com_kinoarhiv.names.' . $user->id . '.data',
					array(
						'success' => false,
						'message' => JText::_('COM_KA_NAMES_EXISTS')
					)
				);

				return false;
			}
		}

		// Automatic handling of alias for empty fields
		if (in_array($app->input->get('task'), array('apply', 'save', 'save2new')) && (!isset($data['id']) || (int) $data['id'] == 0))
		{
			if ($data['alias'] == null)
			{
				$name = empty($data['latin_name']) ? $data['name'] : $data['latin_name'];

				if (JFactory::getConfig()->get('unicodeslugs') == 1)
				{
					$data['alias'] = JFilterOutput::stringURLUnicodeSlug($name);
				}
				else
				{
					$data['alias'] = JFilterOutput::stringURLSafe($name);
				}
			}

			if (empty($data['fs_alias']))
			{
				$data['fs_alias'] = rawurlencode(StringHelper::substr($data['alias'], 0, 1));
			}
		}

		$query = $db->getQuery(true);

		if (empty($id))
		{
			$query->insert($db->quoteName('#__ka_names'))
				->columns(
					$db->quoteName(
						array('id', 'asset_id', 'name', 'latin_name', 'alias', 'fs_alias', 'date_of_birth', 'date_of_death', 'birthplace',
							'birthcountry', 'gender', 'height', 'desc', 'attribs', 'ordering', 'state', 'access', 'metakey',
							'metadesc', 'metadata', 'language'
						)
					)
				)
				->values("'','0','" . $db->escape(trim($data['name'])) . "','" . $db->escape(trim($data['latin_name'])) . "','" . $data['alias'] . "','" . $data['fs_alias'] . "','" . $data['date_of_birth'] . "','" . $data['date_of_death'] . "','" . $db->escape(trim($data['birthplace'])) . "','" . (int) $data['birthcountry'] . "','" . (int) $data['gender'] . "','" . $db->escape($data['height']) . "','" . $db->escape($data['desc']) . "','" . $attribs . "','" . (int) $data['ordering'] . "','" . $data['state'] . "','" . (int) $data['access'] . "','" . $db->escape($data['metakey']) . "','" . $db->escape($data['metadesc']) . "','" . json_encode($metadata) . "','" . $db->escape($data['language']) . "'");
		}
		else
		{
			$query->update($db->quoteName('#__ka_names'))
				->set($db->quoteName('name') . " = '" . $db->escape(trim($data['name'])) . "'")
				->set($db->quoteName('latin_name') . " = '" . $db->escape(trim($data['latin_name'])) . "'")
				->set($db->quoteName('alias') . " = '" . $data['alias'] . "'")
				->set($db->quoteName('fs_alias') . " = '" . $data['fs_alias'] . "'")
				->set($db->quoteName('date_of_birth') . " = '" . $data['date_of_birth'] . "'")
				->set($db->quoteName('date_of_death') . " = '" . $data['date_of_death'] . "'")
				->set($db->quoteName('birthplace') . " = '" . $db->escape($data['birthplace']) . "'")
				->set($db->quoteName('birthcountry') . " = '" . (int) $data['birthcountry'] . "'")
				->set($db->quoteName('gender') . " = '" . (int) $data['gender'] . "'")
				->set($db->quoteName('height') . " = '" . $db->escape($data['height']) . "'")
				->set($db->quoteName('desc') . " = '" . $db->escape($data['desc']) . "'")
				->set($db->quoteName('attribs') . " = '" . $attribs . "'")
				->set($db->quoteName('ordering') . " = '" . (int) $data['ordering'] . "'")
				->set($db->quoteName('state') . " = '" . $data['state'] . "'")
				->set($db->quoteName('access') . " = '" . (int) $data['access'] . "'")
				->set($db->quoteName('metakey') . " = '" . $db->escape($data['metakey']) . "'")
				->set($db->quoteName('metadesc') . " = '" . $db->escape($data['metadesc']) . "'")
				->set($db->quoteName('metadata') . " = '" . json_encode($metadata) . "'")
				->set($db->quoteName('language') . " = '" . $db->escape($data['language']) . "'")
				->where($db->quoteName('id') . ' = ' . (int) $id);
		}

		try
		{
			$db->setQuery($query);
			$db->execute();

			if (empty($id))
			{
				$id = $db->insertid();

				// Create access rules
				$query = $db->getQuery(true);

				$query->select($db->quoteName('id'))
					->from($db->quoteName('#__assets'))
					->where($db->quoteName('name') . " = 'com_kinoarhiv' AND " . $db->quoteName('parent_id') . " = 1");

				$db->setQuery($query);
				$parent_id = $db->loadResult();

				$query = $db->getQuery(true);

				$query->select('MAX(lft)+2 AS lft, MAX(`rgt`)+2 AS rgt')
					->from($db->quoteName('#__assets'));

				$db->setQuery($query);
				$lft_rgt = $db->loadObject();

				$asset_title = !empty($data['latin_name']) ? $data['latin_name'] : $data['name'];
				$query = $db->getQuery(true);

				$query->insert($db->quoteName('#__assets'))
					->columns($db->quoteName(array('id', 'parent_id', 'lft', 'rgt', 'level', 'name', 'title', 'rules')))
					->values("'','" . $parent_id . "','" . $lft_rgt->lft . "','" . $lft_rgt->rgt . "','2','com_kinoarhiv.name." . $id . "','" . $db->escape($asset_title) . "','{}'");

				$db->setQuery($query);
				$db->execute();
				$asset_id = $db->insertid();

				$query = $db->getQuery(true);

				$query->update($db->quoteName('#__ka_names'))
					->set($db->quoteName('asset_id') . " = '" . (int) $asset_id . "'")
					->where($db->quoteName('id') . ' = ' . (int) $id);

				$db->setQuery($query);
				$db->execute();
			}
			else
			{
				// Alias was changed? Move all linked items into new filesystem location.
				if ($data['fs_alias'] != $data['fs_alias_orig'])
				{
					$this->moveMediaItems($id, $data['fs_alias_orig'], $data['fs_alias'], $params);
				}
			}

			$app->setUserState('com_kinoarhiv.names.' . $user->id . '.data',
				array(
					'success' => true,
					'message' => JText::_('COM_KA_ITEMS_SAVE_SUCCESS'),
					'data'    => array('id' => $id, 'name' => trim($data['name']), 'latin_name' => trim($data['latin_name']))
				)
			);
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		if ($quick_save == 0)
		{
			// Proccess genres IDs and store in relation table
			if (!empty($data['genres']) && ($data['genres_orig'] != $data['genres']))
			{
				$genres_arr = explode(',', $data['genres']);

				$query_result_g = true;
				$query = $db->getQuery(true);
				$db->setDebug(true);
				$db->lockTable('#__ka_rel_names_genres');
				$db->transactionStart();

				$query->delete($db->quoteName('#__ka_rel_names_genres'))
					->where($db->quoteName('name_id') . ' = ' . (int) $id);

				$db->setQuery($query);
				$db->execute();

				foreach ($genres_arr as $genre_id)
				{
					$query = $db->getQuery(true);

					$query->insert($db->quoteName('#__ka_rel_names_genres'))
						->columns($db->quoteName(array('genre_id', 'name_id')))
						->values("'" . (int) $genre_id . "','" . (int) $id . "'");

					$db->setQuery($query . ';');

					if ($db->execute() === false)
					{
						$query_result_g = false;
						break;
					}
				}

				if ($query_result_g === false)
				{
					$db->transactionRollback();
					$this->setError('Update genres was failed!');
				}
				else
				{
					$db->transactionCommit();
				}

				$db->unlockTables();
				$db->setDebug(false);
			}

			// Proccess careers IDs and store in relation table
			if (!empty($data['careers']) && ($data['careers_orig'] != $data['careers']))
			{
				$careers_arr = explode(',', $data['careers']);

				$query_result_c = true;
				$query = $db->getQuery(true);
				$db->setDebug(true);
				$db->lockTable('#__ka_rel_names_career');
				$db->transactionStart();

				$query->delete($db->quoteName('#__ka_rel_names_career'))
					->where($db->quoteName('name_id') . ' = ' . (int) $id);

				$db->setQuery($query);
				$db->execute();

				foreach ($careers_arr as $career_id)
				{
					$query = $db->getQuery(true);

					$query->insert($db->quoteName('#__ka_rel_names_career'))
						->columns($db->quoteName(array('career_id', 'name_id')))
						->values("'" . (int) $career_id . "','" . (int) $id . "'");

					$db->setQuery($query . ';');

					if ($db->execute() === false)
					{
						$query_result_c = false;
						break;
					}
				}

				if ($query_result_c === false)
				{
					$db->transactionRollback();
					$this->setError('Update careers was failed!');
				}
				else
				{
					$db->transactionCommit();
				}

				$db->unlockTables();
				$db->setDebug(false);
			}
		}

		return true;
	}

	/**
	 * Method to move all media items which is linked to the name into a new location, if name alias was changed.
	 *
	 * @param   int     $id         Name ID.
	 * @param   string  $old_alias  Old name alias.
	 * @param   string  $new_alias  New name alias.
	 * @param   object  $params     Component parameters.
	 *
	 * @return  boolean   True on success
	 */
	protected function moveMediaItems($id, $old_alias, $new_alias, $params)
	{
		if (empty($id) || empty($old_alias) || empty($new_alias))
		{
			$this->setError('Name ID or alias cannot be empty!');

			return false;
		}
		else
		{
			jimport('joomla.filesystem.folder');
			JLoader::register('KAFilesystemHelper', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'filesystem.php');

			// Move gallery items
			$path_poster = $params->get('media_actor_posters_root');
			$path_wallpp = $params->get('media_actor_wallpapers_root');
			$path_photo = $params->get('media_actor_photo_root');
			$old_folder_poster = $path_poster . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . 'posters';
			$old_folder_wallpp = $path_wallpp . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . 'wallpapers';
			$old_folder_photo = $path_photo . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . 'photo';
			$new_folder_poster = $path_poster . DIRECTORY_SEPARATOR . $new_alias . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . 'posters';
			$new_folder_wallpp = $path_wallpp . DIRECTORY_SEPARATOR . $new_alias . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . 'wallpapers';
			$new_folder_photo = $path_photo . DIRECTORY_SEPARATOR . $new_alias . DIRECTORY_SEPARATOR . $id . DIRECTORY_SEPARATOR . 'photo';

			if (!KAFilesystemHelper::move(
				array(JPath::clean($old_folder_poster), JPath::clean($old_folder_wallpp), JPath::clean($old_folder_photo)),
				array(JPath::clean($new_folder_poster), JPath::clean($new_folder_wallpp), JPath::clean($new_folder_photo))
				))
			{
				$this->setError('Error while moving the files from media folders into new location! See log for more information.');
			}

			// Remove parent folder for posters/wallpapers/screenshots. Delete only if folder(s) is empty.
			if (KAFilesystemHelper::getFolderSize($path_poster . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id) === 0)
			{
				if (file_exists($path_poster . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id))
				{
					JFolder::delete($path_poster . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id);
				}
			}

			if (KAFilesystemHelper::getFolderSize($path_wallpp . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id) === 0)
			{
				if (file_exists($path_wallpp . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id))
				{
					JFolder::delete($path_wallpp . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id);
				}
			}

			if (KAFilesystemHelper::getFolderSize($path_photo . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id) === 0)
			{
				if (file_exists($path_photo . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id))
				{
					JFolder::delete($path_photo . DIRECTORY_SEPARATOR . $old_alias . DIRECTORY_SEPARATOR . $id);
				}
			}
		}

		return true;
	}

	public function saveNameAccessRules()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$data = $app->input->post->get('form', array(), 'array');
		$id = $app->input->get('id', null, 'int');
		$rules = array();

		if (empty($id))
		{
			return array('success' => false, 'message' => 'Error');
		}

		foreach ($data['name']['rules'] as $rule => $groups)
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

		if (JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv') && JFactory::getUser()->authorise('core.edit.access', 'com_kinoarhiv'))
		{
			// Get parent id
			$query = $db->getQuery(true);

			$query->select($db->quoteName('id'))
				->from($db->quoteName('#__assets'))
				->where($db->quoteName('name') . " = 'com_kinoarhiv' AND " . $db->quoteName('parent_id') . " = 1");

			$db->setQuery($query);
			$parent_id = $db->loadResult();

			// Update rules
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__assets'))
				->set($db->quoteName('rules') . " = '" . $rules . "'")
				->where($db->quoteName('name') . " = 'com_kinoarhiv.name." . (int) $id . "' AND " . $db->quoteName('level') . " = 2 AND " . $db->quoteName('parent_id') . " = " . (int) $parent_id);

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
		else
		{
			return array('success' => false, 'message' => JText::_('COM_KA_NO_ACCESS_RULES_SAVE'));
		}
	}

	public function deleteRelAwards()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$data = $app->input->post->get('data', array(), 'array');
		$query = true;

		if (count($data) <= 0)
		{
			return array('success' => false, 'message' => JText::_('JERROR_NO_ITEMS_SELECTED'));
		}

		$db->setDebug(true);
		$db->lockTable('#__ka_rel_awards');
		$db->transactionStart();

		foreach ($data as $key => $value)
		{
			$ids = explode('_', substr($value['name'], 16));

			$db->setQuery("DELETE FROM " . $db->quoteName('#__ka_rel_awards') . " WHERE `id` = " . (int) $ids[0] . ";");
			$result = $db->execute();

			if ($result === false)
			{
				$query = false;
				break;
			}
		}

		if ($query === false)
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

	public function remove()
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');
		$params = JComponentHelper::getParams('com_kinoarhiv');

		// Remove award relations
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__ka_rel_awards'))
			->where($db->quoteName('item_id') . ' IN (' . implode(',', $ids) . ') AND ' . $db->quoteName('type') . ' = 1');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Remove genre relations
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__ka_rel_names_genres'))
			->where($db->quoteName('name_id') . ' IN (' . implode(',', $ids) . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Remove favorited persons
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__ka_user_marked_names'))
			->where($db->quoteName('name_id') . ' IN (' . implode(',', $ids) . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Remove career relations
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__ka_rel_names_career'))
			->where($db->quoteName('name_id') . ' IN (' . implode(',', $ids) . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Remove media items
		$query = $db->getQuery(true);
		$query->select('id, fs_alias')
			->from($db->quoteName('#__ka_names'))
			->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');

		$db->setQuery($query);
		$items = $db->loadObjectList();

		foreach ($items as $item)
		{
			// Delete root folders
			if (file_exists($params->get('media_actor_posters_root') . DIRECTORY_SEPARATOR . $item->fs_alias . DIRECTORY_SEPARATOR . $item->id))
			{
				JFolder::delete($params->get('media_actor_posters_root') . DIRECTORY_SEPARATOR . $item->fs_alias . DIRECTORY_SEPARATOR . $item->id);
			}

			if (file_exists($params->get('media_actor_photo_root') . DIRECTORY_SEPARATOR . $item->fs_alias . DIRECTORY_SEPARATOR . $item->id))
			{
				JFolder::delete($params->get('media_actor_photo_root') . DIRECTORY_SEPARATOR . $item->fs_alias . DIRECTORY_SEPARATOR . $item->id);
			}

			if (file_exists($params->get('media_actor_wallpapers_root') . DIRECTORY_SEPARATOR . $item->fs_alias . DIRECTORY_SEPARATOR . $item->id))
			{
				JFolder::delete($params->get('media_actor_wallpapers_root') . DIRECTORY_SEPARATOR . $item->fs_alias . DIRECTORY_SEPARATOR . $item->id);
			}
		}

		// Remove name(s) from DB
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__ka_names'))
			->where($db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Remove gallery items
		$query = $db->getQuery(true);
		$query->delete($db->quoteName('#__ka_names_gallery'))
			->where($db->quoteName('name_id') . ' IN (' . implode(',', $ids) . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Remove access rules
		$query_result = true;
		$db->setDebug(true);
		$db->lockTable('#__assets');
		$db->transactionStart();

		foreach ($ids as $id)
		{
			$query = $db->getQuery(true);
			$query->delete($db->quoteName('#__assets'))
				->where($db->quoteName('name') . " = 'com_kinoarhiv.name." . (int) $id . "' AND " . $db->quoteName('level') . " = 2");
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

		return $query_result;
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
