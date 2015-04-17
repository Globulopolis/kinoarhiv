<?php defined('_JEXEC') or die;
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url			http://киноархив.com/
 */

class KinoarhivModelGenre extends JModelForm {
	public function getForm($data = array(), $loadData = true) {
		$form = $this->loadForm('com_kinoarhiv.genre', 'genre', array('control' => 'form', 'load_data' => $loadData));

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	protected function loadFormData() {
		$data = JFactory::getApplication()->getUserState('com_kinoarhiv.genres.'.JFactory::getUser()->id.'.edit_data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	public function getItem() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$_id = $app->input->get('id', array(), 'array');
		$id = !empty($_id) ? $_id[0] : $app->input->get('id', null, 'int');
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('id', 'name', 'alias', 'stats', 'state', 'access', 'language')))
			->from($db->quoteName('#__ka_genres'))
			->where($db->quoteName('id').' = '.(int)$id);

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	public function publish($isUnpublish) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');
		$state = $isUnpublish ? 0 : 1;
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_genres'))
			->set($db->quoteName('state').' = '.(int)$state)
			->where($db->quoteName('id').' IN ('.implode(',', $ids).')');

		$db->setQuery($query);

		try {
			$db->execute();

			return true;
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}
	}

	public function remove() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');
		$query = $db->getQuery(true);

		$query->delete($db->quoteName('#__ka_genres'))
			->where($db->quoteName('id').' IN ('.implode(',', $ids).')');

		$db->setQuery($query);

		try {
			$db->execute();

			return true;
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			return false;
		}
	}

	public function save($data) {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$id = $app->input->post->get('id', null, 'int');
		$name = trim($data['name']);

		if (empty($name)) {
			$this->setError(JText::_('COM_KA_REQUIRED'));

			$app->setUserState('com_kinoarhiv.genres.'.$user->id.'.data', array(
				'success' => false,
				'message' => JText::_('COM_KA_REQUIRED')
			));

			return false;
		}

		// Automatic handling of alias for empty fields
		if (in_array($app->input->get('task'), array('apply', 'save', 'save2new')) && (int) $app->input->get('id') == 0) {
			if ($data['alias'] == null) {
				if (JFactory::getConfig()->get('unicodeslugs') == 1) {
					$data['alias'] = JFilterOutput::stringURLUnicodeSlug($name);
				} else {
					$data['alias'] = JFilterOutput::stringURLSafe($name);
				}
			}
		}

		if (empty($id)) {
			// Check if genre with this name allready exists
			$query = $db->getQuery(true);

			$query->select('COUNT(id)')
				->from($db->quoteName('#__ka_genres'))
				->where($db->quoteName('name')." = '".$db->escape($name)."'");

			$db->setQuery($query);
			$count = $db->loadResult();

			if ($count > 0) {
				$this->setError(JText::_('COM_KA_COUNTRY_EXISTS'));

				$app->setUserState('com_kinoarhiv.genres.'.$user->id.'.data', array(
					'success' => false,
					'message' => JText::_('COM_KA_COUNTRY_EXISTS')
				));

				return false;
			}

			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_genres'))
				->columns($db->quoteName(array('id', 'name', 'alias', 'stats', 'state', 'access', 'language')))
				->values("'','".$db->escape($name)."','".$data['alias']."','".(int)$data['stats']."','".$data['state']."','".(int)$data['access']."','".$db->escape($data['language'])."'");
		} else {
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_genres'))
				->set($db->quoteName('name')." = '".$db->escape($name)."'")
				->set($db->quoteName('alias')." = '".$data['alias']."'")
				->set($db->quoteName('stats')." = '".(int)$data['stats']."'")
				->set($db->quoteName('state')." = '".$data['state']."'")
				->set($db->quoteName('access')." = '".(int)$data['access']."'")
				->set($db->quoteName('language')." = '".$db->escape($data['language'])."'")
				->where($db->quoteName('id').' = '.(int)$id);
		}

		try {
			$db->setQuery($query);
			$db->execute();

			if (empty($id)) {
				$id = $db->insertid();
			}

			$app->setUserState('com_kinoarhiv.genres.'.$user->id.'.data', array(
				'success' => true,
				'message' => JText::_('COM_KA_ITEMS_SAVE_SUCCESS'),
				'data'    => array('id' => $id, 'name' => $name)
			));

			return true;
		} catch(Exception $e) {
			$this->setError($e->getMessage());

			$app->setUserState('com_kinoarhiv.genres.'.$user->id.'.data', array(
				'success' => false,
				'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')
			));

			return false;
		}
	}

	public function updateStat() {
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$gid = $app->input->get('id', array(), 'array');
		$boxchecked = $app->input->get('boxchecked', 0, 'int');

		if (count($gid) > 1) {
			if (count($gid) != $boxchecked) {
				return array('success'=>false);
			}

			$result = true;
			$db->setDebug(true);
			$db->lockTable('#__ka_genres');
			$db->transactionStart();

			foreach ($gid as $genre_id) {
				$query = $db->getQuery(true)
					->update($db->quoteName('#__ka_genres'));

				$subquery = $db->getQuery(true)
					->select('COUNT(genre_id)')
					->from($db->quoteName('#__ka_rel_genres'))
					->where($db->quoteName('genre_id').' = '.(int)$genre_id);

				$query->set($db->quoteName('stats')." = (".$subquery.")")
					->where($db->quoteName('id').' = '.(int)$genre_id.';');

				$db->setQuery($query);
				$query = $db->execute();

				if ($query === false) {
					$result = false;
					break;
				}
			}

			if ($result === false) {
				$db->transactionRollback();
				$this->setError('Commit failed!');
			} else {
				$db->transactionCommit();
			}

			$db->unlockTables();
			$db->setDebug(false);
			$total = 0;
		} else {
			if (empty($gid[0])) {
				return array('success'=>false, 'message'=>JText::_('COM_KA_GENRES_STATS_UPDATE_ERROR'));
			}

			$query = $db->getQuery(true)
				->update($db->quoteName('#__ka_genres'));

			$subquery = $db->getQuery(true)
				->select('COUNT(genre_id)')
				->from($db->quoteName('#__ka_rel_genres'))
				->where($db->quoteName('genre_id').' = '.(int)$gid[0]);

			$query->set($db->quoteName('stats')." = (".$subquery.")")
				->where($db->quoteName('id').' = '.(int)$gid[0]);

			$db->setQuery($query);

			try {
				$db->execute();

				$query = $db->getQuery(true)
					->select($db->quoteName('stats'))
					->from($db->quoteName('#__ka_genres'))
					->where($db->quoteName('id').' = '.(int)$gid[0]);

				$db->setQuery($query);
				$total = $db->loadResult();
				$result = true;
			} catch(Exception $e) {
				$total = 0;
				$result = false;
			}
		}

		return array('success'=>$result, 'total'=>$total);
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
