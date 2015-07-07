<?php defined('_JEXEC') or die;

/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */
class KinoarhivControllerUpdate extends JControllerLegacy
{
	public function display($cachable = false, $urlparams = array())
	{
		$app = JFactory::getApplication();
		$version = $this->input->get('version', 0, 'uint');

		if (!empty($version)) {
			$model = $this->getModel('update');
			$result = $model->update($version);

			if ($result === false) {
				$errors = $model->getErrors();

				for ($i = 0, $n = count($errors); $i < $n; $i++) {
					if ($errors[$i] instanceof Exception) {
						$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
					} else {
						$app->enqueueMessage($errors[$i], 'warning');
					}
				}

				$this->setRedirect('index.php?option=com_kinoarhiv');

				return false;
			}

			$app->enqueueMessage(JText::_('COM_KA_UPDATE_DB_SUCCESS'));
			$this->setRedirect('index.php?option=com_kinoarhiv');
		} else {
			$this->setRedirect('index.php?option=com_kinoarhiv', JText::_('ERROR'), 'error');
		}

		return $this;
	}
}
