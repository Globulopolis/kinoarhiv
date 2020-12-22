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
 * Search View class
 *
 * @since  3.0
 */
class KinoarhivViewSearch extends JViewLegacy
{
	protected $form;

	protected $params;

	protected $menu;

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
		$app          = JFactory::getApplication();
		$this->form   = $this->get('Form');
		$this->menu   = $app->getMenu()->getActive();
		$this->params = JComponentHelper::getParams('com_kinoarhiv');
		$this->itemid = $app->input->get('Itemid', 0, 'int');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$this->params     = JComponentHelper::getParams('com_kinoarhiv');
		$this->homeItemid = array(
			'movies' => KAContentHelper::getItemid('movies'),
			'names'  => KAContentHelper::getItemid('names'),
			'albums' => KAContentHelper::getItemid('albums')
		);

		// Merge the menu item params with the component params so that the menu params take priority
		$temp         = clone $this->params;
		$temp->merge($this->menu->params);
		$this->params = $temp;

		$this->prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	protected function prepareDocument()
	{
		$app     = JFactory::getApplication();
		$pathway = $app->getPathway();
		$title   = ($this->menu && $this->menu->title) ? $this->menu->title : JText::_('COM_KA_SEARCH_ADV');

		// Create a new pathway object
		$path = (object) array(
			'name' => $title,
			'link' => 'index.php?option=com_kinoarhiv&view=search&Itemid=' . $this->itemid
		);

		if ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$pathway->setPathway(array($path));
		$this->document->setTitle($title);

		if ($this->menu && $this->menu->params->get('menu-meta_description') != '')
		{
			$this->document->setDescription($this->menu->params->get('menu-meta_description'));
		}
		else
		{
			$this->document->setDescription($this->params->get('meta_description'));
		}

		if ($this->menu && $this->menu->params->get('menu-meta_keywords') != '')
		{
			$this->document->setMetadata('keywords', $this->menu->params->get('menu-meta_keywords'));
		}
		else
		{
			$this->document->setMetadata('keywords', $this->params->get('meta_keywords'));
		}

		if ($this->menu && $this->menu->params->get('robots') != '')
		{
			$this->document->setMetadata('robots', $this->menu->params->get('robots'));
		}
		else
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		if ($this->params->get('generator') == 'none')
		{
			$this->document->setGenerator('');
		}
		elseif ($this->params->get('generator') == 'site')
		{
			$this->document->setGenerator($this->document->getGenerator());
		}
		else
		{
			$this->document->setGenerator($this->params->get('generator'));
		}
	}
}
