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
 * Awards View class
 *
 * @since  3.0
 */
class KinoarhivViewAwards extends JViewLegacy
{
	protected $item = null;

	protected $items = null;

	protected $pagination = null;

	protected $params;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	public function display($tpl = null)
	{
		$id = JFactory::getApplication()->input->get('id', null, 'int');

		if (!empty($id))
		{
			$this->award();
		}
		else
		{
			$this->awards();
		}
	}

	protected function awards()
	{
		$this->items = $this->get('Items');

		if (count($errors = $this->get('Errors')) || is_null($this->items))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$this->itemid = JFactory::getApplication()->input->get('Itemid', 0, 'int');
		$this->params = JComponentHelper::getParams('com_kinoarhiv');
		$this->pagination = $this->get('Pagination');

		$this->_prepareDocument();

		parent::display();
	}

	protected function award()
	{
		$this->item = $this->get('Item');

		if (count($errors = $this->get('Errors')) || is_null($this->item))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$this->itemid = JFactory::getApplication()->input->get('Itemid', 0, 'int');
		$this->params = JComponentHelper::getParams('com_kinoarhiv');

		// TODO Fix document title and pathway
		$this->_prepareDocument();

		parent::display('award');
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 *
	 * @since  3.0
	 */
	protected function _prepareDocument()
	{
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$menu = $menus->getActive();
		$pathway = $app->getPathway();

		$title = ($menu && $menu->title) ? $menu->title : JText::_('COM_KA_AWARDS_TITLE');

		// Create a new pathway object
		$path = (object) array(
			'name' => $title,
			'link' => 'index.php?option=com_kinoarhiv&view=awards&Itemid=' . $this->itemid
		);

		$pathway->setPathway(array($path));
		$this->document->setTitle($title);

		if ($menu && $menu->params->get('menu-meta_description') != '')
		{
			$this->document->setDescription($menu->params->get('menu-meta_description'));
		}
		else
		{
			$this->document->setDescription($this->params->get('meta_description'));
		}

		if ($menu && $menu->params->get('menu-meta_keywords') != '')
		{
			$this->document->setMetadata('keywords', $menu->params->get('menu-meta_keywords'));
		}
		else
		{
			$this->document->setMetadata('keywords', $this->params->get('meta_keywords'));
		}

		if ($menu && $menu->params->get('robots') != '')
		{
			$this->document->setMetadata('robots', $menu->params->get('robots'));
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
