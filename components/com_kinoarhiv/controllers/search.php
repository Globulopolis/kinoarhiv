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
		if (JSession::checkToken() === false)
		{
			// Inform user about an error. This is disabled in reviews controller.
			$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=search', false), JText::_('JINVALID_TOKEN'), 'error');

			return false;
		}

		$app = JFactory::getApplication();
		$content = $this->input->post->get('content', '', 'word');

		if ($content == '')
		{
			$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=search', false), 'Wrong content', 'error');

			return false;
		}

		$data = $this->input->post->get('form', array(), 'array');
		$model = $this->getModel('search');
		$form = $model->getForm($data, false);

		if (!$form)
		{
			$this->setRedirect(JRoute::_('index.php?option=com_kinoarhiv&view=search', false), $model->getError(), 'error');

			return false;
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

		/*// Slashes cause errors, <> get stripped anyway later on. # causes problems.
		$badchars = array('#', '>', '<', '\\');
		$searchword = trim(str_replace($badchars, '', $this->input->post->get('searchword', null, 'string')));

		// If searchword enclosed in double quotes, strip quotes and do exact match
		if (substr($searchword, 0, 1) == '"' && substr($searchword, -1) == '"')
		{
			$post['searchword'] = substr($searchword, 1, -1);
			$this->input->set('searchphrase', 'exact');
		}
		else
		{
			$post['searchword'] = $searchword;
		}*/

		$post = $data;

		$uri = JUri::getInstance();
		$uri->setQuery($post);
		$uri->setVar('option', 'com_kinoarhiv');

		$this->setRedirect(JRoute::_('index.php' . $uri->toString(array('query', 'fragment')), false));
	}
}
