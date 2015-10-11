<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

class KinoarhivModelCareer extends JModelForm
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
		$form = $this->loadForm('com_kinoarhiv.career', 'career', array('control' => 'form', 'load_data' => $loadData));

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
		$data = JFactory::getApplication()->getUserState('com_kinoarhiv.careers.' . JFactory::getUser()->id . '.edit_data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	public function getItem()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$_id = $app->input->get('id', array(), 'array');
		$id = !empty($_id) ? $_id[0] : $app->input->get('id', null, 'int');
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('id', 'title', 'is_mainpage', 'is_amplua', 'ordering', 'language')))
			->from($db->quoteName('#__ka_names_career'))
			->where($db->quoteName('id') . ' = ' . (int) $id);

		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	public function onmainpage($offmainpage)
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');
		$state = $offmainpage ? 0 : 1;
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__ka_names_career'))
			->set($db->quoteName('is_mainpage') . ' = ' . (int) $state)
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

	public function remove()
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$ids = $app->input->get('id', array(), 'array');
		$query = $db->getQuery(true);

		$query->delete($db->quoteName('#__ka_names_career'))
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

	public function save($data)
	{
		$app = JFactory::getApplication();
		$db = $this->getDBO();
		$user = JFactory::getUser();
		$id = $app->input->post->get('id', null, 'int');
		$title = trim($data['title']);

		if (empty($title))
		{
			$this->setError(JText::_('COM_KA_REQUIRED'));

			$app->setUserState('com_kinoarhiv.careers.' . $user->id . '.data',
				array(
					'success' => false,
					'message' => JText::_('COM_KA_REQUIRED')
				)
			);

			return false;
		}

		if (empty($id))
		{
			// Check if career with this title allready exists
			$query = $db->getQuery(true);

			$query->select('COUNT(id)')
				->from($db->quoteName('#__ka_names_career'))
				->where($db->quoteName('title') . " = '" . $db->escape($title) . "'");

			$db->setQuery($query);
			$count = $db->loadResult();

			if ($count > 0)
			{
				$this->setError(JText::_('COM_KA_CAREER_EXISTS'));

				$app->setUserState('com_kinoarhiv.careers.' . $user->id . '.data',
					array(
						'success' => false,
						'message' => JText::_('COM_KA_CAREER_EXISTS')
					)
				);

				return false;
			}

			$query = $db->getQuery(true);

			$query->insert($db->quoteName('#__ka_names_career'))
				->columns($db->quoteName(array('id', 'title', 'is_mainpage', 'is_amplua', 'ordering', 'language')))
				->values("'','" . $db->escape($title) . "','" . (int) $data['is_mainpage'] . "','" . (int) $data['is_amplua'] . "','" . (int) $data['ordering'] . "','" . $db->escape($data['language']) . "'");
		}
		else
		{
			$query = $db->getQuery(true);

			$query->update($db->quoteName('#__ka_names_career'))
				->set($db->quoteName('title') . " = '" . $db->escape($title) . "'")
				->set($db->quoteName('is_mainpage') . " = '" . (int) $data['is_mainpage'] . "'")
				->set($db->quoteName('is_amplua') . " = '" . (int) $data['is_amplua'] . "'")
				->set($db->quoteName('ordering') . " = '" . (int) $data['ordering'] . "'")
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
			}

			$app->setUserState('com_kinoarhiv.careers.' . $user->id . '.data',
				array(
					'success' => true,
					'message' => JText::_('COM_KA_ITEMS_SAVE_SUCCESS'),
					'data'    => array('id' => $id, 'title' => $title)
				)
			);

			return true;
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			$app->setUserState('com_kinoarhiv.careers.' . $user->id . '.data',
				array(
					'success' => false,
					'message' => JText::_('JERROR_AN_ERROR_HAS_OCCURRED')
				)
			);

			return false;
		}
	}
}
