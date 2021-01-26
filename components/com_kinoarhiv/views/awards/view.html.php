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

use Joomla\Registry\Registry;

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
	 * The menu object
	 *
	 * @var    JMenuItem
	 * @since  3.1
	 */
	protected $menu;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since  3.1
	 */
	public function display($tpl = null)
	{
		$app          = JFactory::getApplication();
		$params       = JComponentHelper::getParams('com_kinoarhiv');
		$id           = $app->input->get('id', null, 'int');
		$this->itemid = $app->input->get('Itemid', 0, 'int');
		$menu         = $app->getMenu()->getActive();
		$this->menu   = $menu;
		$menuParams   = new Registry;

		if ($menu)
		{
			$menuParams->loadString($menu->getParams());
		}

		$mergedParams = clone $menuParams;
		$mergedParams->merge($params);
		$this->params = $mergedParams;

		if (!empty($id))
		{
			$this->award();
		}
		else
		{
			$this->awards();
		}
	}

	/**
	 * Display list of items.
	 *
	 * @return  boolean|void
	 *
	 * @since  3.1
	 */
	protected function awards()
	{
		$items = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors')) || is_null($items))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		// Prepare the data
		foreach ($items as $item)
		{
			$item->text   = '';
			$item->event  = new stdClass;
			$item->params = new JObject;
			$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=awards&id=' . $item->id . '&Itemid=' . $this->itemid, false));

			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('content');
			$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.awards', &$item, &$params, 0));

			$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.awards', &$item, &$item->params, 0));
			$item->event->afterDisplayTitle = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.awards', &$item, &$item->params, 0));
			$item->event->beforeDisplayContent = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.awards', &$item, &$item->params, 0));
			$item->event->afterDisplayContent = trim(implode("\n", $results));
		}

		$this->items = $items;

		$this->prepareDocument();

		parent::display();
	}

	/**
	 * Display single item.
	 *
	 * @return  boolean|void
	 *
	 * @since  3.1
	 */
	protected function award()
	{
		$item = $this->get('Item');

		if (count($errors = $this->get('Errors')) || is_null($item))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$item->text   = '';
		$item->event  = new stdClass;
		$item->params = new JObject;
		$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=award&id=' . $item->id . '&Itemid=' . $this->itemid, false));

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.award', &$item, &$this->params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.award', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.award', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.award', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		// It's required for prepareDocument();
		$this->item = $item;

		$this->prepareDocument();

		echo JLayoutHelper::render('layouts.content.award',
			array('item' => $item, 'params' => $this->params, 'itemid' => $this->itemid),
			JPATH_COMPONENT
		);
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
		$pathway    = $app->getPathway();
		$menuParams = $this->menu->getParams();
		$id         = $app->input->get('id', null, 'int');

		if (!empty($id))
		{
			$titleAwards = ($this->menu && $this->menu->title) ? $this->menu->title : JText::_('COM_KA_AWARDS_TITLE');
			$title = $titleAwards . ' - ' . $this->item->title;

			// Create a new pathway object
			$path[] = (object) array(
				'name' => $titleAwards,
				'link' => 'index.php?option=com_kinoarhiv&view=awards&Itemid=' . $this->itemid
			);
			$path[] = (object) array(
				'name' => $this->item->title,
				'link' => 'index.php?option=com_kinoarhiv&view=awards&id=' . (int) $this->item->id . '&Itemid=' . $this->itemid
			);
		}
		else
		{
			$title = ($this->menu && $this->menu->title) ? $this->menu->title : JText::_('COM_KA_AWARDS_TITLE');

			// Create a new pathway object
			$path[] = (object) array(
				'name' => $title,
				'link' => 'index.php?option=com_kinoarhiv&view=awards&Itemid=' . $this->itemid
			);
		}

		if ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$pathway->setPathway($path);
		$this->document->setTitle($title);

		if ($this->menu && $menuParams->get('menu-meta_description') != '')
		{
			$this->document->setDescription($menuParams->get('menu-meta_description'));
		}
		else
		{
			$this->document->setDescription($this->params->get('meta_description'));
		}

		if ($this->menu && $menuParams->get('menu-meta_keywords') != '')
		{
			$this->document->setMetadata('keywords', $menuParams->get('menu-meta_keywords'));
		}
		else
		{
			$this->document->setMetadata('keywords', $this->params->get('meta_keywords'));
		}

		if ($this->menu && $menuParams->get('robots') != '')
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
