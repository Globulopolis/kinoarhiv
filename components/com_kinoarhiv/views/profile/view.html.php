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

/**
 * Profile View class
 *
 * @since  3.0
 */
class KinoarhivViewProfile extends JViewLegacy
{
	/**
	 * The user data from com_users
	 *
	 * @var    JObject
	 * @since  3.1
	 */
	protected $data;

	/**
	 * The form
	 *
	 * @var    JForm
	 * @since  3.1
	 */
	protected $form;

	protected $itemid = null;

	/**
	 * The items details
	 *
	 * @var    JObject
	 * @since  3.1
	 */
	protected $items = null;

	/**
	 * Page
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $page = '';

	/**
	 * Sub page
	 *
	 * @var    string
	 * @since  3.1
	 */
	protected $tab = 'movies';

	protected $pagination = null;

	/**
	 * The component parameters
	 *
	 * @var    JRegistry
	 * @since  1.6
	 */
	protected $params;

	/**
	 * An instance of JDatabaseDriver.
	 *
	 * @var    JDatabaseDriver
	 * @since  3.1
	 */
	protected $db;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed
	 *
	 * @since  3.0
	 */
	public function display($tpl = null)
	{
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		if ($user->get('guest'))
		{
			$err = JText::_('JGLOBAL_AUTH_ACCESS_DENIED') . '. ' . JText::_('JERROR_ALERTNOAUTHOR');
			$app->enqueueMessage($err, 'error');
			KAComponentHelper::eventLog($err);

			return false;
		}

		$this->page = $app->input->get('page', '', 'cmd');
		$this->tab = $app->input->get('tab', 'movies', 'cmd');
		$this->itemid = $app->input->get('Itemid', 0, 'int');

		switch ($this->page)
		{
			case 'reviews':
				$this->reviews();
				break;
			case 'favorite':
				$this->favorite();
				break;
			case 'watched':
				$this->watched();
				break;
			case 'votes':
				$this->votes();
				break;
			default:
				jimport('components.com_users.models.profile', JPATH_ROOT);
				JForm::addFormPath(JPATH_ROOT . '/components/com_users/models/forms/');

				$profileModel = new UsersModelProfile;
				$this->data = $profileModel->getData();
				$this->form = $profileModel->getForm(new JObject(array('id' => $user->id)));
				$this->params = JComponentHelper::getParams('com_users');
				$this->db = JFactory::getDbo();

				// Check for errors.
				if (count($errors = $this->get('Errors')) || count($errors = $profileModel->getErrors()))
				{
					KAComponentHelper::eventLog(implode('<br />', $errors), 'ui');

					return false;
				}

				// View also takes responsibility for checking if the user logged in with remember me.
				$cookieLogin = $user->get('cookieLogin');

				if (!empty($cookieLogin))
				{
					// If so, the user must login to edit the password and other data.
					// What should happen here? Should we force a logout which destroys the cookies?
					$app = JFactory::getApplication();
					$app->enqueueMessage(JText::_('JGLOBAL_REMEMBER_MUST_LOGIN'), 'message');
					$app->redirect(JRoute::_('index.php?option=com_users&view=login', false));

					return false;
				}

				// Check if a user was found.
				if (!$this->data->id)
				{
					KAComponentHelper::eventLog(JText::_('JERROR_USERS_PROFILE_NOT_FOUND'), 'ui');

					return false;
				}

				JPluginHelper::importPlugin('content');
				$this->data->text = '';
				JEventDispatcher::getInstance()->trigger('onContentPrepare', array ('com_users.user', &$this->data, &$this->data->params, 0));
				unset($this->data->text);

				$lang = \JFactory::getLanguage();
				$lang->load('com_users');

				// Escape strings for HTML output
				$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

				parent::display();
		}
	}

	protected function favorite()
	{
		$app = JFactory::getApplication();
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->params = JComponentHelper::getParams('com_kinoarhiv');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$pathway = $app->getPathway();

		if ($this->tab == 'movies')
		{
			$this->document->setTitle($this->document->getTitle() . ' - ' . JText::_('COM_KA_FAVORITE') . ' - ' . JText::_('COM_KA_MOVIES'));

			$pathway->addItem(
				JText::_('COM_KA_FAVORITE'),
				JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&Itemid=' . $this->itemid)
			);
			$pathway->addItem(
				JText::_('COM_KA_MOVIES'),
				JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&tab=movies&Itemid=' . $this->itemid)
			);
		}
		elseif ($this->tab == 'names')
		{
			$this->document->setTitle($this->document->getTitle() . ' - ' . JText::_('COM_KA_FAVORITE') . ' - ' . JText::_('COM_KA_PERSONS'));

			$pathway->addItem(
				JText::_('COM_KA_FAVORITE'),
				JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&Itemid=' . $this->itemid)
			);
			$pathway->addItem(
				JText::_('COM_KA_PERSONS'),
				JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&tab=names&Itemid=' . $this->itemid)
			);
		}
		elseif ($this->tab == 'albums')
		{
			$this->document->setTitle($this->document->getTitle() . ' - ' . JText::_('COM_KA_FAVORITE') . ' - ' . JText::_('COM_KA_MUSIC_ALBUMS'));

			$pathway->addItem(
				JText::_('COM_KA_FAVORITE'),
				JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&Itemid=' . $this->itemid)
			);
			$pathway->addItem(
				JText::_('COM_KA_MUSIC_ALBUMS'),
				JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=favorite&tab=albums&Itemid=' . $this->itemid)
			);
		}

		parent::display('favorite_' . $this->tab);
	}

	protected function watched()
	{
		$app = JFactory::getApplication();
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->params = JComponentHelper::getParams('com_kinoarhiv');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$this->document->setTitle($this->document->getTitle() . ' - ' . JText::_('COM_KA_WATCHED'));
		$pathway = $app->getPathway();
		$pathway->addItem(JText::_('COM_KA_WATCHED'), JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=watched&Itemid=' . $this->itemid));

		parent::display('watched');
	}

	protected function votes()
	{
		$app = JFactory::getApplication();
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->params = JComponentHelper::getParams('com_kinoarhiv');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$this->lang = JFactory::getLanguage();
		$pathway = $app->getPathway();

		if ($this->tab == 'movies')
		{
			$this->document->setTitle($this->document->getTitle() . ' - ' . JText::_('COM_KA_PROFILE_VOTES') . ' - ' . JText::_('COM_KA_MOVIES'));

			$pathway->addItem(
				JText::_('COM_KA_PROFILE_VOTES'),
				JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=votes&Itemid=' . $this->itemid)
			);
			$pathway->addItem(
				JText::_('COM_KA_MOVIES'),
				JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=votes&tab=movies&Itemid=' . $this->itemid)
			);
		}
		elseif ($this->tab == 'albums')
		{
			$this->document->setTitle($this->document->getTitle() . ' - ' . JText::_('COM_KA_PROFILE_VOTES') . ' - ' . JText::_('COM_KA_MUSIC_ALBUMS'));

			$pathway->addItem(
				JText::_('COM_KA_PROFILE_VOTES'),
				JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=votes&Itemid=' . $this->itemid)
			);
			$pathway->addItem(
				JText::_('COM_KA_MUSIC_ALBUMS'),
				JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=votes&tab=albums&Itemid=' . $this->itemid)
			);
		}

		parent::display('votes_' . $this->tab);
	}

	protected function reviews()
	{
		$app = JFactory::getApplication();
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->params = JComponentHelper::getParams('com_kinoarhiv');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$this->document->setTitle($this->document->getTitle() . ' - ' . JText::_('COM_KA_REVIEWS'));
		$pathway = $app->getPathway();
		$pathway->addItem(JText::_('COM_KA_REVIEWS'), JRoute::_('index.php?option=com_kinoarhiv&view=profile&page=reviews&Itemid=' . $this->itemid));

		parent::display('reviews');
	}
}
