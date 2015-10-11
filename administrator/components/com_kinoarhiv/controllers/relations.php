<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

/**
 * Class KinoarhivControllerRelations
 *
 * @since  3.0
 */
class KinoarhivControllerRelations extends JControllerLegacy
{
	/**
	 * Typical view method for MVC based architecture
	 *
	 * This function is provide as a default implementation, in most cases
	 * you will need to override it in your own controllers.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JControllerLegacy  A JControllerLegacy object to support chaining.
	 *
	 * @since   3.0
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$app = JFactory::getApplication();
		$action = $app->input->get('action', '', 'cmd');
		$task = $app->input->get('task', '', 'cmd');
		$model = $this->getModel('relations');

		switch ($action)
		{
			case 'getList':
				$result = $model->getDataList($task);

				echo json_encode($result);
				break;
			case 'saveOrder':
				$result = $model->saveOrder($task);

				echo json_encode($result);
				break;
			default:
				return;
				break;
		}
	}

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
		$view = $this->getView('relations', 'html');
		$model = $this->getModel('relations');
		$view->setModel($model, true);

		if ($isNew === true)
		{
			$tpl = 'relations_add';
		}
		elseif ($isNew === false)
		{
			$tpl = 'relations_edit';
		}

		$view->display($tpl);
	}

	/**
	 * Method to remove an item(s).
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	public function delete()
	{
		$model = $this->getModel('relations');
		$result = $model->relations_remove();

		echo json_encode($result);
	}

	/**
	 * Proxy to KinoarhivControllerRelations::save()
	 *
	 * @return  mixed
	 *
	 * @since   3.0
	 */
	public function save2new()
	{
		$this->save();
	}

	/**
	 * Proxy to KinoarhivControllerRelations::save()
	 *
	 * @return  mixed
	 *
	 * @since   3.0
	 */
	public function apply()
	{
		$this->save();
	}

	/**
	 * Method to save a record.
	 *
	 * @return  mixed
	 *
	 * @since   3.0
	 */
	public function save()
	{
		$document = JFactory::getDocument();

		if ($document->getType() == 'html')
		{
			JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		}
		else
		{
			JSession::checkToken() or jexit(
				json_encode(
					array(
						'success' => false,
						'message' => JText::_('JINVALID_TOKEN')
					)
				)
			);
		}

		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv'))
		{
			if ($document->getType() == 'html')
			{
				JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

				return;
			}
			else
			{
				echo json_encode(
					array(
						'success' => false,
						'message' => JText::_('JERROR_ALERTNOAUTHOR')
					)
				);

				return;
			}
		}

		$model = $this->getModel('relations');
		$result = $model->apply();

		$document->setMimeEncoding('application/json');

		echo json_encode($result);
	}

	/**
	 * Method to save the submitted ordering values for records.
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	public function saveOrder()
	{
		$document = JFactory::getDocument();

		if ($document->getType() == 'html')
		{
			JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		}
		else
		{
			JSession::checkToken() or jexit(
				json_encode(
					array(
						'success' => false,
						'message' => JText::_('JINVALID_TOKEN')
					)
				)
			);
		}

		if (!JFactory::getUser()->authorise('core.admin', 'com_kinoarhiv'))
		{
			if ($document->getType() == 'html')
			{
				JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

				return;
			}
			else
			{
				echo json_encode(
					array(
						'success' => false,
						'message' => JText::_('JERROR_ALERTNOAUTHOR')
					)
				);

				return;
			}
		}

		$model = $this->getModel('relations');
		$result = $model->saveOrder();

		$document->setMimeEncoding('application/json');

		echo json_encode($result);
	}
}
