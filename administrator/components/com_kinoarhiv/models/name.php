<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

JLoader::register('DatabaseHelper', JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'database.php');

class KinoarhivModelName extends JModelForm {
	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_kinoarhiv.name', 'name', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		$input = JFactory::getApplication()->input;
		$ids = $input->get('id', array(), 'array');
		$id = (isset($id[0]) && !empty($id[0])) ? $id[0] : 0;
		$user = JFactory::getUser();

		if ($id != 0 && (!$user->authorise('core.edit.state', 'com_kinoarhiv.name.' . (int) $id)) || ($id == 0 && !$user->authorise('core.edit.state', 'com_kinoarhiv'))) {
			$form->setFieldAttribute('state', 'disabled', 'true');
		}

		return $form;
	}

	protected function loadFormData() {
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_kinoarhiv.edit.name.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	public function getItem($pk = null) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$tmpl = $app->input->get('template', '', 'string');
		$id = $app->input->get('id', array(), 'array');

		if ($tmpl == 'awards_edit') {
			$award_id = $app->input->get('award_id', 0, 'int');

			$db->setQuery("SELECT `id` AS `rel_aw_id`, `item_id`, `award_id`, `desc` AS `aw_desc`, `year` AS `aw_year`"
				. "\n FROM ".$db->quoteName('#__ka_rel_awards')
				. "\n WHERE `id` = ".(int)$award_id);
			$result = $db->loadObject();
		} else {
			$result = array('name'=>(object)array());
			if (count($id) == 0 || empty($id) || empty($id[0])) {
				return $result;
			}

			$db->setQuery("SELECT `n`.`id`, `n`.`asset_id`, `n`.`name`, `n`.`latin_name`, `n`.`alias`, `n`.`date_of_birth`, `n`.`date_of_death`, `n`.`birthplace`, `n`.`birthcountry`, `n`.`gender`, `n`.`height`, `n`.`desc`, `n`.`attribs`, `n`.`ordering`, `n`.`state`, `n`.`access`, `n`.`metakey`, `n`.`metadesc`, `n`.`metadata`, `n`.`language`, `l`.`title` AS `language_title`, `g`.`id` AS `gid`, `g`.`filename`, `c`.`name` AS `country`, `c`.`code` AS `country_code`"
				. "\n FROM ".$db->quoteName('#__ka_names')." AS `n`"
				. "\n LEFT JOIN ".$db->quoteName('#__languages')." AS `l` ON `l`.`lang_code` = `n`.`language`"
				. "\n LEFT JOIN ".$db->quoteName('#__ka_names_gallery')." AS `g` ON `g`.`name_id` = `n`.`id` AND `g`.`type` = 3 AND `g`.`photo_frontpage` = 1"
				. "\n LEFT JOIN ".$db->quoteName('#__ka_countries')." AS `c` ON `c`.`id` = `n`.`birthcountry`"
				. "\n WHERE `n`.`id` = ".(int)$id[0]);
			$result['name'] = $db->loadObject();

			$result['name']->genres = $this->getGenres();
			$result['name']->genres_orig = implode(',', $result['name']->genres['ids']);
			$result['name']->careers = $this->getCareers();
			$result['name']->careers_orig = implode(',', $result['name']->careers['ids']);;

			if (!empty($result['name']->attribs)) {
				$result['attribs'] = json_decode($result['name']->attribs);
			}
		}

		return $result;
	}

	protected function getGenres() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', array(), 'array');
		$result = array('data'=>array(), 'ids'=>array());

		$db->setQuery("SELECT `g`.`id`, `g`.`name` AS `title`"
			. "\n FROM ".$db->quoteName('#__ka_genres')." AS `g`"
			. "\n WHERE `id` IN (SELECT `genre_id` FROM ".$db->quoteName('#__ka_rel_names_genres')." WHERE `name_id` = ".(int)$id[0].")");
		$result['data'] = $db->loadObjectList();

		foreach ($result['data'] as $value) {
			$result['ids'][] = $value->id;
		}

		return $result;
	}

	protected function getCareers() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', array(), 'array');
		$result = array('data'=>array(), 'ids'=>array());

		$db->setQuery("SELECT `c`.`id`, `c`.`title`"
			. "\n FROM ".$db->quoteName('#__ka_names_career')." AS `c`"
			. "\n WHERE `id` IN (SELECT `career_id` FROM ".$db->quoteName('#__ka_rel_names_career')." WHERE `name_id` = ".(int)$id[0].")");
		$result['data'] = $db->loadObjectList();

		foreach ($result['data'] as $value) {
			$result['ids'][] = $value->id;
		}

		return $result;
	}

	public function apply($data) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$params = JComponentHelper::getParams('com_kinoarhiv');

		$id = $app->input->get('id', 0, 'int');
		$data = $data['name'];
		$metadata = array('robots' => $data['robots']);
		$form_data = $app->input->post->get('form', array(), 'array');
		$attribs = json_encode($form_data['attribs']);

		// Proccess genres IDs and store in relation table
		if (!empty($data['genres']) && ($data['genres_orig'] != $data['genres'])) {
			$genres_arr = explode(',', $data['genres']);

			$query = true;
			$db->lockTable('#__ka_rel_names_genres');
			$db->transactionStart();

			$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_rel_names_genres')." WHERE `name_id` = ".(int)$id);
			$db->execute();

			foreach ($genres_arr as $genre_id) {
				$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_rel_names_genres')." (`genre_id`, `name_id`) VALUES ('".(int)$genre_id."', '".(int)$id."');");
				$result = $db->execute();

				if ($result === false) {
					$query = false;
					break;
				}
			}

			if ($query === false) {
				$db->transactionRollback();
				$this->setError('Commit for "'.$db->getPrefix().'_ka_rel_names_genres" failed!');
				$db->unlockTables();
				return false;
			} else {
				$db->transactionCommit();
				$db->unlockTables();
			}
		}

		// Proccess careers IDs and store in relation table
		if (!empty($data['careers']) && ($data['careers_orig'] != $data['careers'])) {
			$careers_arr = explode(',', $data['careers']);

			$query = true;
			$db->lockTable('#__ka_rel_names_career');
			$db->transactionStart();

			$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_rel_names_career')." WHERE `name_id` = ".(int)$id);
			$db->execute();

			foreach ($careers_arr as $career_id) {
				$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_rel_names_career')." (`career_id`, `name_id`) VALUES ('".(int)$career_id."', '".(int)$id."');");
				$result = $db->execute();

				if ($result === false) {
					$query = false;
					break;
				}
			}

			if ($query === false) {
				$db->transactionRollback();
				$this->setError('Commit for "'.$db->getPrefix().'_ka_rel_names_career" failed!');
				$db->unlockTables();
				return false;
			} else {
				$db->transactionCommit();
				$db->unlockTables();
			}
		}

		if (empty($data['alias'])) {
			if (!empty($data['latin_name'])) {
				$alias = JFilterOutput::stringURLSafe($data['latin_name']);
			} else {
				$alias = JFilterOutput::stringURLSafe($data['name']);
			}
		} else {
			$alias = JFilterOutput::stringURLSafe($data['alias']);
		}

		if (empty($id)) {
			$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_names')
				. " (`id`, `asset_id`, `name`, `latin_name`, `alias`, `date_of_birth`, `date_of_death`, `birthplace`, `birthcountry`, `gender`, `height`, `desc`, `attribs`, `ordering`, `state`, `access`, `metakey`, `metadesc`, `metadata`, `language`)"
				. "\n VALUES ('', '0', '".$db->escape(trim($data['name']))."', '".$db->escape(trim($data['latin_name']))."', '".$alias."', '".$data['date_of_birth']."', '".$data['date_of_death']."', '".$db->escape($data['birthplace'])."', '".(int)$data['birthcountry']."', '".(int)$data['gender']."', '".$db->escape($data['height'])."', '".$db->escape($data['desc'])."', '".$attribs."', '".(int)$data['ordering']."', '".$data['state']."', '".(int)$data['access']."', '".$db->escape($data['metakey'])."', '".$db->escape($data['metadesc'])."', '".json_encode($metadata)."', '".$data['language']."')");
		} else {
			$db->setQuery("UPDATE ".$db->quoteName('#__ka_names')
				. "\n SET `name` = '".$db->escape(trim($data['name']))."', `latin_name` = '".$db->escape(trim($data['latin_name']))."', `alias` = '".$alias."', `date_of_birth` = '".$data['date_of_birth']."', `date_of_death` = '".$data['date_of_death']."', `birthplace` = '".$db->escape($data['birthplace'])."', `birthcountry` = '".(int)$data['birthcountry']."', `gender` = '".(int)$data['gender']."', `height` = '".$db->escape($data['height'])."', `desc` = '".$db->escape($data['desc'])."', `attribs` = '".$attribs."', `ordering` = '".(int)$data['ordering']."', `state` = '".$data['state']."', `access` = '".(int)$data['access']."', `metakey` = '".$db->escape($data['metakey'])."', `metadesc` = '".$db->escape($data['metadesc'])."', `metadata` = '".json_encode($metadata)."', `language` = '".$data['language']."'"
				. "\n WHERE `id` = ".(int)$id);
		}

		try {
			$db->execute();

			if (empty($id)) {
				$insertid = $db->insertid();
				$app->input->set('id', array($insertid)); // Need to proper redirect to edited item

				// Create access rules
				$db->setQuery("SELECT `id` FROM ".$db->quoteName('#__assets')." WHERE `name` = 'com_kinoarhiv' AND `parent_id` = 1");
				$parent_id = $db->loadResult();

				$db->setQuery("SELECT MAX(`lft`)+2 AS `lft`, MAX(`rgt`)+2 AS `rgt` FROM ".$db->quoteName('#__assets'));
				$lft_rgt = $db->loadObject();

				$asset_title = !empty($data['latin_name']) ? $data['latin_name'] : $data['name'];
				$db->setQuery("INSERT INTO ".$db->quoteName('#__assets')
					. "\n (`id`, `parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`)"
					. "\n VALUES ('', '".$parent_id."', '".$lft_rgt->lft."', '".$lft_rgt->rgt."', '2', 'com_kinoarhiv.name.".$insertid."', '".$db->escape($asset_title)."', '{}')");
				$db->execute();
				$asset_id = $db->insertid();

				$db->setQuery("UPDATE ".$db->quoteName('#__ka_names')
					. "\n SET `asset_id` = '".(int)$asset_id."'"
					. "\n WHERE `id` = ".(int)$insertid);
				$db->execute();
			} else {
				$app->input->set('id', array($id));

				// Alias was changed? Move all linked items into new filesystem location.
				if (JString::substr($alias, 0, 1) != JString::substr($data['alias_orig'], 0, 1)) {
					$this->moveMediaItems($id, $data['alias_orig'], $alias, $params);
				}
			}

			return true;
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	/**
	 * Method to move all media items which is linked to the name into a new location, if name alias was changed.
	 *
	 * @param   int      $id          Name ID.
	 * @param   string   $old_alias   Old name alias.
	 * @param   string   $new_alias   New name alias.
	 * @param   object   $params      Component parameters.
	 *
	 * @return  boolean   True on success
	 *
	*/
	protected function moveMediaItems($id, $old_alias, $new_alias, &$params) {
		if (empty($id) || empty($old_alias) || empty($new_alias)) {
			$this->setError('Name ID or alias cannot be empty!');

			return false;
		} else {
			jimport('joomla.filesystem.folder');
			JLoader::register('KAFilesystemHelper', JPATH_COMPONENT.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'filesystem.php');

			$error = false;
			$old_alias = JString::substr($old_alias, 0, 1);
			$new_alias = JString::substr($new_alias, 0, 1);

			// Move gallery items
			$path_poster = $params->get('media_actor_posters_root');
			$path_wallpp = $params->get('media_actor_wallpapers_root');
			$path_photo = $params->get('media_actor_photo_root');
			$old_folder_poster = $path_poster.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.'posters';
			$old_folder_wallpp = $path_wallpp.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.'wallpapers';
			$old_folder_photo = $path_photo.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.'photo';
			$new_folder_poster = $path_poster.DIRECTORY_SEPARATOR.$new_alias.DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.'posters';
			$new_folder_wallpp = $path_wallpp.DIRECTORY_SEPARATOR.$new_alias.DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.'wallpapers';
			$new_folder_photo = $path_photo.DIRECTORY_SEPARATOR.$new_alias.DIRECTORY_SEPARATOR.$id.DIRECTORY_SEPARATOR.'photo';

			if (!KAFilesystemHelper::move(
				array($old_folder_poster, $old_folder_wallpp, $old_folder_photo),
				array($new_folder_poster, $new_folder_wallpp, $new_folder_photo))
				) {
				$this->setError('Error while moving the files from media folders into new location! See log for more information.');
			}

			// Remove parent folder for posters/wallpapers/screenshots. Delete only if folder(s) is empty.
			if (KAFilesystemHelper::getFolderSize($path_poster.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id) === 0) {
				if (file_exists($path_poster.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id)) {
					JFolder::delete($path_poster.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id);
				}
			}
			if (KAFilesystemHelper::getFolderSize($path_wallpp.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id) === 0) {
				if (file_exists($path_wallpp.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id)) {
					JFolder::delete($path_wallpp.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id);
				}
			}
			if (KAFilesystemHelper::getFolderSize($path_photo.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id) === 0) {
				if (file_exists($path_photo.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id)) {
					JFolder::delete($path_photo.DIRECTORY_SEPARATOR.$old_alias.DIRECTORY_SEPARATOR.$id);
				}
			}
		}

		return true;
	}

	public function quickSave() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();

		// We need to set alias for quick save on movie page
		$name = 'n_name';
		$latin_name = 'n_latin_name';
		$date_of_birth = 'n_date_of_birth';
		$gender = 'n_gender';
		$ordering = 'n_ordering';
		$language = 'n_language';

		$data = $app->input->getArray(array(
			'form'=>array(
				$name=>'string', $latin_name=>'string', $date_of_birth=>'string', $gender=>'int', $ordering=>'int', $language=>'string'
			)
		));
		$name = $data['form'][$name];
		$latin_name = $data['form'][$latin_name];
		$alias = $name != '' ? $name : $latin_name;
		$date_of_birth = (empty($data['form'][$date_of_birth]) && $data['form'][$date_of_birth] == '0000-00-00') ? date('Y-m-d') : $data['form'][$date_of_birth];
		$gender = $data['form'][$gender];
		$ordering = empty($data['form'][$ordering]) ? 0 : $data['form'][$ordering];
		$metadata = json_encode(array('tags'=>array(), 'robots'=>''));
		$language = empty($data['form'][$language]) ? '*' : $data['form'][$language];
		$attribs = array();

		if (empty($name) && empty($latin_name)) {
			return array('success'=>false, 'message'=>JText::_('COM_KA_REQUIRED'));
		}

		$form = JForm::getInstance('com_kinoarhiv.name', __DIR__.'\forms\name.xml', array('control' => '', 'load_data' => false), false, "fields[@name = 'attribs']");

		foreach ($form->getXml()->xpath('//field') as $field) {
			$attribs[(string)$field->attributes()->name] = '';
		}

		if (empty($form)) {
			return false;
		}

		$db->setQuery("INSERT INTO ".$db->quoteName('#__ka_names')." (`id`, `asset_id`, `name`, `latin_name`, `alias`, `date_of_birth`, `date_of_death`, `birthplace`, `birthcountry`, `gender`, `height`, `desc`, `attribs`, `ordering`, `state`, `access`, `metakey`, `metadesc`, `metadata`, `language`)"
			. "\n VALUES ('', '0', '".$db->escape($name)."', '".$db->escape($latin_name)."', '".JFilterOutput::stringURLSafe($alias)."', '".$date_of_birth."', '', '', '', '".$gender."', '', '', '".json_encode($attribs)."', '".(int)$ordering."', '1', '1', '', '', '".$metadata."', '".$language."')");
		$query = $db->execute();

		if ($query !== true) {
			return array('success'=>false, 'message'=>JText::_('JERROR_AN_ERROR_HAS_OCCURRED'));
		} else {
			$insertid = $db->insertid();
			$rules = json_encode((object)array());

			$db->setQuery("SELECT MAX(`rgt`) + 1 FROM ".$db->quoteName('#__assets'));
			$lft = $db->loadResult();

			$db->setQuery("SELECT `id` FROM ".$db->quoteName('#__assets')." WHERE `name` = 'com_kinoarhiv' AND `parent_id` = 1 AND `level` = 1");
			$parent_id = $db->loadResult();

			$db->setQuery("INSERT INTO ".$db->quoteName('#__assets')." (`id`, `parent_id`, `lft`, `rgt`, `level`, `name`, `title`, `rules`)"
				. "\n VALUES ('', '".$parent_id."', '".$lft."', '".($lft+1)."', '1', 'com_kinoarhiv.name.".(int)$insertid."', '".$alias."', '".$rules."')");
			$assets_query = $db->execute();
			$assets_id = $db->insertid();

			$db->setQuery("UPDATE ".$db->quoteName('#__ka_names')." SET `asset_id` = '".$assets_id."' WHERE `id` = ".$insertid);
			$update_query = $db->execute();

			return array(
				'success'	=> true,
				'message'	=> JText::_('COM_KA_ITEMS_SAVE_SUCCESS'),
				'data'		=> array('id'=>$insertid, 'name'=>$name, 'latin_name'=>$latin_name)
			);
		}
	}

	public function saveNameAccessRules() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$data = $app->input->post->get('form', array(), 'array');
		$id = $app->input->get('id', null, 'int');
		$rules = array();

		if (empty($id)) {
			return array('success'=>false, 'message'=>'Error');
		}

		foreach ($data['name']['rules'] as $rule=>$groups) {
			foreach ($groups as $group=>$value) {
				if ($value != '') {
					$rules[$rule][$group] = (int)$value;
				} else {
					unset($data['rules'][$rule][$group]);
				}
			}
		}

		$rules = json_encode($rules);

		if (JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv') && JFactory::getUser()->authorise('core.edit.access', 'com_kinoarhiv')) {
			// Get parent id
			$db->setQuery("SELECT `id` FROM ".$db->quoteName('#__assets')." WHERE `name` = 'com_kinoarhiv' AND `parent_id` = 1");
			$parent_id = $db->loadResult();

			$db->setQuery("UPDATE ".$db->quoteName('#__assets')
				. "\n SET `rules` = '".$rules."'"
				. "\n WHERE `name` = 'com_kinoarhiv.name.".(int)$id."' AND `level` = 2 AND `parent_id` = ".(int)$parent_id);

			try {
				$db->execute();
				return array('success'=>true);
			} catch(Exception $e) {
				return array('success'=>false, 'message'=>$e->getMessage());
			}
		} else {
			return array('success'=>false, 'message'=>JText::_('COM_KA_NO_ACCESS_RULES_SAVE'));
		}
	}

	public function getAwards() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$id = $app->input->get('id', null, 'int');
		$orderby = $app->input->get('sidx', '1', 'string');
		$order = $app->input->get('sord', 'asc', 'word');
		$limit = $app->input->get('rows', 50, 'int');
		$page = $app->input->get('page', 0, 'int');
		$search_field = $app->input->get('searchField', '', 'string');
		$search_operand = $app->input->get('searchOper', 'eq', 'cmd');
		$search_string = $app->input->get('searchString', '', 'string');
		$limitstart = $limit * $page - $limit;
		$result = (object)array('rows'=>array());
		$where = "";

		if (!empty($search_string)) {
			$where .= " AND ".DatabaseHelper::transformOperands($db->quoteName($search_field), $search_operand, $db->escape($search_string));
		}

		$db->setQuery("SELECT COUNT(`rel`.`id`)"
			. "\n FROM ".$db->quoteName('#__ka_rel_awards')." AS `rel`"
			. "\n WHERE `rel`.`item_id` = ".(int)$id." AND `type` = 1".$where);
		$total = $db->loadResult();

		$total_pages = ($total > 0) ? ceil($total / $limit) : 0;
		$page = ($page > $total_pages) ? $total_pages : $page;

		$db->setQuery("SELECT `rel`.`id`, `rel`.`item_id`, `rel`.`award_id`, `rel`.`desc`, `rel`.`year`, `rel`.`type`, `aw`.`title`"
			. "\n FROM ".$db->quoteName('#__ka_rel_awards')." AS `rel`"
			. "\n LEFT JOIN ".$db->quoteName('#__ka_awards')." AS `aw` ON `aw`.`id` = `rel`.`award_id`"
			. "\n WHERE `rel`.`item_id` = ".(int)$id." AND `type` = 1".$where
			. "\n ORDER BY ".$db->quoteName($orderby).' '.strtoupper($order), $limitstart, $limit);
		$rows = $db->loadObjectList();

		$k = 0;
		foreach ($rows as $elem) {
			$result->rows[$k]['id'] = $elem->id.'_'.$elem->item_id.'_'.$elem->award_id;
			$result->rows[$k]['cell'] = array(
				'id'		=> $elem->id,
				'award_id'	=> $elem->award_id,
				'title'		=> $elem->title,
				'year'		=> $elem->year,
				'desc'		=> $elem->desc
			);

			$k++;
		}

		$result->page = $page;
		$result->total = $total_pages;
		$result->records = $total;

		return $result;
	}

	public function deleteRelAwards() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$data = $app->input->post->get('data', array(), 'array');
		$query = true;

		if (count($data) <= 0) {
			return array('success'=>false, 'message'=>JText::_('JERROR_NO_ITEMS_SELECTED'));
		}

		$db->setDebug(true);
		$db->lockTable('#__ka_rel_awards');
		$db->transactionStart();

		foreach ($data as $key=>$value) {
			$ids = explode('_', substr($value['name'], 16));

			$db->setQuery("DELETE FROM ".$db->quoteName('#__ka_rel_awards')." WHERE `id` = ".(int)$ids[0].";");
			$result = $db->execute();

			if ($result === false) {
				$query = false;
				break;
			}
		}

		if ($query === false) {
			$db->transactionRollback();
			$success = false;
			$message = JText::_('COM_KA_ITEMS_DELETED_ERROR');
		} else {
			$db->transactionCommit();
			$success = true;
			$message = JText::_('COM_KA_ITEMS_DELETED_SUCCESS');
		}

		$db->unlockTables();
		$db->setDebug(false);

		return array('success'=>$success, 'message'=>$message);
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
	public function validate($form, $data, $group = null) {
		// Filter and validate the form data.
		$data = $form->filter($data);
		$return = $form->validate($data, $group);

		// Check for an error.
		if ($return instanceof Exception) {
			$this->setError($return->getMessage());
			return false;
		}

		// Check the validation results.
		if ($return === false) {
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message) {
				$this->setError($message);
			}

			return false;
		}

		return $data;
	}
}
