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
		$model = $this->getModel('search');
		$form = $model->getForm($data, false);

		if (!$form)
		{
			KAComponentHelper::eventLog($model->getError());
			$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=search', false));

			return false;
		}

		// Slashes cause errors, <> get stripped anyway later on. # causes problems.
		$badchars = array('#', '>', '<', '\\');

		// We need to check field name for person
		$title_field = ($content == 'names') ? 'name' : 'title';

		$data[$content][$title_field] = trim(str_replace($badchars, '', $data[$content][$title_field]));

		// If searchword enclosed in double quotes, strip quotes and do exact match
		if (substr($data[$content][$title_field], 0, 1) == '"' && substr($data[$content][$title_field], -1) == '"')
		{
			$data[$content][$title_field] = substr($data[$content][$title_field], 1, -1);
			$exact_match = true;
		}
		else
		{
			$exact_match = false;
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
		$uri->setVar('option', 'com_kinoarhiv');
		$uri->setVar('view', $content);
		$uri->setVar('Itemid', $this->input->post->get('m_itemid', '', 'int'));
		$uri->setVar('content', $content);

		if ($exact_match)
		{
			$uri->setVar('exact_match', 1);
		}

		$this->setRedirect(JRoute::_('index.php' . $uri->toString(array('query', 'fragment')), false));

		return true;
	}
}
