<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

/**
 * Kinoarhiv search class.
 *
 * @since  3.0
 */
class KinoarhivControllerSearch extends JControllerLegacy
{
	/**
	 * Search
	 *
	 * @return  mixed
	 *
	 * @since  3.0
	 */
	public function results()
	{
		$app = JFactory::getApplication();
		$content = $this->input->post->get('content', '', 'word');

		if (KAComponentHelper::checkToken() === false)
		{
			KAComponentHelper::eventLog(JText::_('JINVALID_TOKEN'));
			$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=search', false));

			return false;
		}

		if (empty($content))
		{
			KAComponentHelper::eventLog('Wrong search query: content parameter');
			$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=search', false));

			return false;
		}

		$data = $this->input->post->get('form', array(), 'array');

		/** @var KinoarhivModelSearch $model */
		$model = $this->getModel('search');
		$form  = $model->getForm($data, false);

		if (!$form)
		{
			KAComponentHelper::eventLog($model->getError());
			$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=search', false));

			return false;
		}

		// Slashes cause errors, <> get stripped anyway later on. # causes problems.
		$badchars = array('#', '>', '<', '\\');

		// We need to check field name for person
		$titleField = ($content == 'names') ? 'name' : 'title';

		$data[$content][$titleField] = trim(str_replace($badchars, '', $data[$content][$titleField]));

		// If searchword enclosed in double quotes, strip quotes and do exact match
		if (substr($data[$content][$titleField], 0, 1) == '"' && substr($data[$content][$titleField], -1) == '"')
		{
			$data[$content][$titleField] = substr($data[$content][$titleField], 1, -1);
			$exactMatch = true;
		}
		else
		{
			$exactMatch = false;
		}

		$validData = $model->validate($form, $data, $content);

		if ($validData === false)
		{
			$errors = $model->getErrors();

			for ($i = 0, $n = count($errors); $i < $n && $i < 5; $i++)
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

			$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=search', false));

			return false;
		}

		$uri = JUri::getInstance();
		$uri->setQuery($data);

		if ($exactMatch)
		{
			$uri->setVar('exact_match', 1);
		}

		$uri = StringHelper::substr($uri->toString(array('query', 'fragment')), 1);

		$this->setRedirect(
			JRoute::_(
				'index.php?option=com_kinoarhiv&view=' . $content . '&content=' . $content
				. '&Itemid=' . $this->input->post->get('menu', '', 'int') . '&' . $uri,
				false
			)
		);

		return true;
	}
}
