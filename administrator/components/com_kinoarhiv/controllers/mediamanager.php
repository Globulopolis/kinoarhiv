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

/**
 * Mediamanager controller class.
 *
 * @since  3.0
 */
class KinoarhivControllerMediamanager extends JControllerLegacy
{
	/**
	 * Method to add a new record.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function add()
	{
		$this->edit(true);
	}

	/**
	 * Method to edit an existing record or add a new record.
	 *
	 * @param   boolean  $isNew  Variable to check if it's new item or not.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function edit($isNew = false)
	{
		$view = $this->getView('mediamanager', 'html');
		$model = $this->getModel('mediamanageritem');
		$view->setModel($model, true);

		if ($isNew === true)
		{
			$tpl = 'add';
		}
		elseif ($isNew === false)
		{
			$tpl = 'edit';
		}

		$view->display($tpl);
	}

	/**
	 * Proxy for $this->setFrontpage(true) method
	 *
	 * @return void
	 *
	 * @since  3.0
	 */
	public function unsetFrontpage()
	{
		$this->setFrontpage(true);
	}

	/**
	 * Method to publish or unpublish posters(photo) on movie(person) info page(not on posters page)
	 *
	 * @param   boolean  $state  Action state
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	public function setFrontpage($state = false)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.edit.state', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$app = JFactory::getApplication();
		$tab = $app->input->get('tab', 0, 'int') != 0 ? '&tab=' . $app->input->get('tab', 0, 'int') : '';
		$redirect = 'index.php?option=com_kinoarhiv&view=mediamanager&section=' . $app->input->get('section', '', 'word')
			. '&type=' . $app->input->get('type', '', 'word') . $tab . '&id=' . $app->input->get('id', 0, 'int');
		$model = $this->getModel('mediamanager');
		$result = $model->setFrontpage($state);

		if ($result === false)
		{
			$this->setRedirect($redirect, JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');

			return;
		}

		$message = $state ? JText::_('COM_KA_ITEMS_EDIT_UNSET_ONFRONTPAGE') : JText::_('COM_KA_ITEMS_EDIT_SET_ONFRONTPAGE');
		$this->setRedirect($redirect, $message);
	}

	/**
	 * Method to unpublish a list of items
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function unpublish()
	{
		$this->publish(true);
	}


	/**
	 * Method to publish a list of items
	 *
	 * @param   boolean  $isUnpublish  Action state
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function publish($isUnpublish = false)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.edit.state', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$app = JFactory::getApplication();
		$tab = $app->input->get('tab', 0, 'int') != 0 ? '&tab=' . $app->input->get('tab', 0, 'int') : '';
		$redirect = 'index.php?option=com_kinoarhiv&view=mediamanager&section=' . $app->input->get('section', '', 'word')
			. '&type=' . $app->input->get('type', '', 'word') . $tab . '&id=' . $app->input->get('id', 0, 'int');
		$model = $this->getModel('mediamanager');
		$result = $model->publish($isUnpublish);

		if ($result === false)
		{
			$this->setRedirect($redirect, JText::_('COM_KA_ITEMS_EDIT_ERROR'), 'error');

			return;
		}

		$message = $isUnpublish ? JText::_('COM_KA_ITEMS_EDIT_UNPUBLISHED') : JText::_('COM_KA_ITEMS_EDIT_PUBLISHED');
		$this->setRedirect($redirect, $message);
	}

	/**
	 * Unset default subtitle for trailer.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function subtitleUnsetDefault()
	{
		$this->subtitleSetDefault(false);
	}

	/**
	 * Set default subtitle for trailer.
	 *
	 * @param   boolean  $isDefault  Action state
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function subtitleSetDefault($isDefault = true)
	{
		JSession::checkToken('get') or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('mediamanageritem');
		$id = $this->input->get('id', 0, 'int');
		$item_id = $this->input->get('item_id', null, 'array');
		$result = $model->subtitleSetDefault($isDefault);

		if (!$result)
		{
			// Errors enqueue in the model
			$this->setRedirect('index.php?option=com_kinoarhiv&task=mediamanager.edit&section=movie&type=trailers&id=' . $id . '&item_id[]=' . $item_id[0]);

			return;
		}

		$this->setRedirect('index.php?option=com_kinoarhiv&task=mediamanager.edit&section=movie&type=trailers&id=' . $id . '&item_id[]=' . $item_id[0]);
	}

	/**
	 * Proxy to KinoarhivControllerMediamanager::save()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function apply()
	{
		$this->save();
	}

	/**
	 * Proxy to KinoarhivControllerMediamanager::save()
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function save2new()
	{
		$this->save();
	}

	/**
	 * Method to save a record(trailer).
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function save()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.edit', 'com_kinoarhiv') && !$user->authorise('core.edit.delete', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('mediamanageritem');
		$section = $app->input->get('section', '', 'word');
		$type = $app->input->get('type', '', 'word');
		$id = $app->input->get('id', 0, 'int');
		$data = $this->input->post->get('form', array(), 'array');
		$form_group = 'trailer';
		$form = $model->getForm($data, false);
		$item_id = $data[$form_group]['item_id'];

		if (!$form)
		{
			$app->enqueueMessage(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'), 'error');

			return;
		}

		$validData = $model->validate($form, $data, $form_group);

		if ($validData === false)
		{
			KAComponentHelperBackend::renderErrors($model->getErrors());
			$this->setRedirect('index.php?option=com_kinoarhiv&task=mediamanager.edit&section='
				. $section . '&type=' . $type . '&id=' . $id . '&item_id[]=' . $item_id
			);

			return;
		}

		// Store data for use in KinoarhivModelMediamanagerItem::loadFormData()
		$app->setUserState('com_kinoarhiv.trailers.' . $user->id . '.edit_data', $validData);
		$result = $model->save($validData[$form_group]);

		if (!$result)
		{
			// Errors enqueue in the model
			$this->setRedirect('index.php?option=com_kinoarhiv&task=mediamanager.edit&section='
				. $section . '&type=' . $type . '&id=' . $id . '&item_id[]=' . $item_id
			);

			return;
		}

		$session_data = $app->getUserState('com_kinoarhiv.trailers.' . $user->id . '.edit_data');

		// Set the success message.
		$message = JText::_('COM_KA_ITEMS_SAVE_SUCCESS');

		// Delete session data taken from model
		$app->setUserState('com_kinoarhiv.trailers.' . $user->id . '.edit_data', null);

		switch ($this->getTask())
		{
			case 'save2new':
				$this->setRedirect('index.php?option=com_kinoarhiv&task=mediamanager.add&section=' . $section
					. '&type=' . $type . '&id=' . $id, $message
				);
				break;

			case 'apply':
				$this->setRedirect('index.php?option=com_kinoarhiv&task=mediamanager.edit&section='
					. $section . '&type=' . $type . '&id=' . $id . '&item_id[]=' . $session_data[$form_group]['item_id'], $message
				);
				break;

			case 'save':
			default:
				$this->setRedirect('index.php?option=com_kinoarhiv&view=mediamanager&section=' . $section
					. '&type=' . $type . '&id=' . $id, $message);
				break;
		}
	}

	/**
	 * Method to cancel an trailer edit.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function cancel()
	{
		$app = JFactory::getApplication();

		// Clean the session data.
		$app->setUserState('com_kinoarhiv.trailers.' . JFactory::getUser()->id . '.edit_data', null);

		$this->setRedirect(
			'index.php?option=com_kinoarhiv&view=mediamanager&section=' . $app->input->get('section', '', 'word')
			. '&type=' . $app->input->get('type', '', 'word') . '&id=' . $app->input->get('id', 0, 'int')
		);
	}

	/**
	 * Method to remove an item(s).
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	// TODO Refactor - В методе модели удалить всю(все) выбранные строки. В методе контроллера удалить все файлы - получить родительскую папку и перебрать файло.
	public function remove()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user = JFactory::getUser();

		// Check if the user is authorized to do this.
		if (!$user->authorise('core.edit', 'com_kinoarhiv') && !$user->authorise('core.edit.delete', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$app = JFactory::getApplication();
		$model = $this->getModel('mediamanageritem');
		$section = $app->input->get('section', '', 'word');
		$type = $app->input->get('type', '', 'word');
		/*$model->remove();
		$errors = $model->getErrors();

		if (count($errors) > 0)
		{
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					if ($app->input->get('format', 'html', 'word') == 'raw')
					{
						echo $errors[$i]->getMessage() . "\n";
					}
					else
					{
						$app->enqueueMessage($errors[$i]->getMessage(), 'error');
					}
				}
				else
				{
					if ($app->input->get('format', 'html', 'word') == 'raw')
					{
						echo $errors[$i] . "\n";
					}
					else
					{
						$app->enqueueMessage($errors[$i], 'error');
					}
				}
			}
		}

		if ($app->input->get('reload', 1, 'int') == 1)
		{
			$this->setRedirect(JUri::getInstance()->toString());
		}*/
	}

	/**
	 * Method to run batch operations.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function batch()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user = JFactory::getUser();

		if (!$user->authorise('core.edit', 'com_kinoarhiv')
			&& !$user->authorise('core.edit.state', 'com_kinoarhiv'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		$app = JFactory::getApplication();
		$ids = $app->input->post->get('item_id', array(), 'array');
		$redirect = 'index.php?option=com_kinoarhiv&view=mediamanager&section=' . $app->input->get('section', '', 'word')
			. '&type=' . $app->input->get('type', '', 'word') . '&id=' . $app->input->get('id', 0, 'int');

		if (count($ids) != 0)
		{
			$model = $this->getModel('mediamanager');
			$result = $model->batch();

			if ($result === false)
			{
				KAComponentHelperBackend::renderErrors($model->getErrors());
				$this->setRedirect($redirect);

				return;
			}
		}

		$this->setRedirect($redirect);
	}
}
