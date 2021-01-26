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
 * Genres View class
 *
 * @since  3.0
 */
class KinoarhivViewGenres extends JViewLegacy
{
	protected $items = null;

	protected $params;

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
		$app         = JFactory::getApplication();
		$this->menu  = $app->getMenu()->getActive();
		$this->items = $this->get('Items');

		if (count($errors = $this->get('Errors')) || is_null($this->items))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$this->moviesItemid = KAContentHelper::getItemid('movies');
		$this->albumsItemid = KAContentHelper::getItemid('albums');
		$this->params       = JComponentHelper::getParams('com_kinoarhiv');

		// Merge the menu item params with the component params so that the menu params take priority
		$temp         = clone $this->params;
		$temp->merge($this->menu->getParams());
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
		$app        = JFactory::getApplication();
		$menus      = $app->getMenu();
		$menu       = $menus->getActive();
		$menuParams = $menu->getParams();
		$title      = ($menu && $menu->title) ? $menu->title : JText::_('COM_KA_GENRES');

		if ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($menu && $menuParams->get('menu-meta_description') != '')
		{
			$this->document->setDescription($menuParams->get('menu-meta_description'));
		}
		else
		{
			$this->document->setDescription($this->params->get('meta_description'));
		}

		if ($menu && $menuParams->get('menu-meta_keywords') != '')
		{
			$this->document->setMetadata('keywords', $menuParams->get('menu-meta_keywords'));
		}
		else
		{
			$this->document->setMetadata('keywords', $this->params->get('meta_keywords'));
		}

		if ($menu && $menuParams->get('robots') != '')
		{
			$this->document->setMetadata('robots', $menuParams->get('robots'));
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
