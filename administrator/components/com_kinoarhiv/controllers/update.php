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
 * Update controller class
 *
 * @since  3.0
 */
class KinoarhivControllerUpdate extends JControllerLegacy
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
		$version = $this->input->get('version', 0, 'uint');

		if (!empty($version))
		{
			$model = $this->getModel('update');
			$result = $model->update($version);

			if ($result === false)
			{
				$errors = $model->getErrors();

				for ($i = 0, $n = count($errors); $i < $n; $i++)
				{
					if ($errors[$i] instanceof Exception)
					{
						$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
					}
					else
					{
						$app->enqueueMessage($errors[$i], 'warning');
					}
				}

				$this->setRedirect('index.php?option=com_kinoarhiv');

				return false;
			}

			$app->enqueueMessage(JText::_('COM_KA_UPDATE_DB_SUCCESS'));
			$this->setRedirect('index.php?option=com_kinoarhiv');
		}
		else
		{
			$this->setRedirect('index.php?option=com_kinoarhiv', JText::_('ERROR'), 'error');
		}

		return $this;
	}
}
