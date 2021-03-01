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
use Joomla\String\StringHelper;

/**
 * Movie View class
 *
 * @since  3.0
 */
class KinoarhivViewAlbum extends JViewLegacy
{
	/**
	 * The form object for the reviews item
	 *
	 * @var    JForm
	 * @since  1.6
	 */
	protected $form;

	/**
	 * The item object details
	 *
	 * @var    object
	 * @since  1.6
	 */
	protected $item = null;

	/**
	 * The items details
	 *
	 * @var    object
	 * @since  1.6
	 */
	protected $items = null;

	protected $filters = null;

	protected $pagination = null;

	/**
	 * @var    string
	 * @since  1.6
	 */
	protected $page;

	/**
	 * Component config object
	 *
	 * @var    JObject
	 * @since  3.0
	 */
	protected $params;

	protected $user;

	protected $itemid;

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
	 * @since  3.0
	 */
	public function display($tpl = null)
	{
		$app          = JFactory::getApplication();
		$this->user   = JFactory::getUser();
		$this->page   = $app->input->get('page', '');
		$this->itemid = $app->input->get('Itemid');

		// Used in layouts
		$this->moviesItemid = $this->itemid;

		$params     = JComponentHelper::getParams('com_kinoarhiv');
		$menu       = $app->getMenu()->getActive();
		$this->menu = $menu;
		$menuParams = new Registry;

		if ($menu)
		{
			$menuParams->loadString($menu->getParams());
		}

		$mergedParams = clone $menuParams;
		$mergedParams->merge($params);
		$this->params = $mergedParams;

		if (method_exists($this, $this->page))
		{
			$this->{$this->page}();
		}
		else
		{
			$this->info();
		}
	}

	/**
	 * Method to get and show album info data.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since  3.0
	 */
	public function info()
	{
		$this->user       = JFactory::getUser();
		$app              = JFactory::getApplication();
		$params           = JComponentHelper::getParams('com_kinoarhiv');
		$lang             = JFactory::getLanguage();
		$item             = $this->get('Data');
		$items            = $this->get('Items');
		$this->form       = $this->get('Form');
		$this->pagination = $this->get('Pagination');
		$this->itemid     = $app->input->get('Itemid');
		$this->page       = $app->input->get('page', '');
		$this->pagination->hideEmptyLimitstart = true;

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		// Prepare the data
		$this->releasesItemid = KAContentHelper::getItemid('releases', array('params' => array('item_type' => 1, 'page_type' => 'list')));
		$this->profileItemid  = KAContentHelper::getItemid('profile');
		$this->namesItemid    = KAContentHelper::getItemid('names');

		// Workaround for plugin interaction. Article must contain $text item.
		$item->text = '';

		$item->tagLayout = new JLayoutFile('components.com_kinoarhiv.layouts.content.tags', JPATH_ROOT);
		$item->tags      = new JHelperTags;
		$item->tags->getItemTags('com_kinoarhiv.album', $item->id);

		$item->cover = KAContentHelper::getAlbumCover($item, $params);

		if (!empty($item->desc))
		{
			$item->desc = str_replace("\n", "<br />", $item->desc);
		}

		list($hours, $minutes) = explode(':', $item->length);
		$item->minutes = $hours * 60 + $minutes;

		if (!empty($item->rate_sum) && !empty($item->rate))
		{
			$plural = $lang->getPluralSuffixes($item->rate);
			$item->rate_value = round($item->rate_sum / $item->rate, (int) $params->get('vote_summ_precision'));
			$item->rate_label = JText::sprintf('COM_KA_RATE_LOCAL_' . $plural[0], $item->rate_value, (int) $params->get('vote_summ_num'));
			$item->rate_label_class = ' has-rating';
		}
		else
		{
			$item->rate_value = 0;
			$item->rate_label = JText::_('COM_KA_RATE_NO');
			$item->rate_label_class = ' no-rating';
		}

		$item->event  = new stdClass;
		$item->params = new JObject;
		$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $item->id . '&Itemid=' . $this->itemid, false));

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.albums', &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.album', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.album', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.album', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->params = $params;
		$this->item   = $item;

		// Reviews
		$this->items = $items;

		$this->metadata = json_decode($item->metadata);
		$this->lang     = $lang;
		$this->view     = $app->input->getWord('view');

		$this->prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem(
			$this->item->title,
			JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);

		parent::display();
	}

	/**
	 * Method to get and show awards.
	 *
	 * @return  mixed
	 *
	 * @since  3.0
	 */
	protected function awards()
	{
		$app    = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$item   = $this->get('Awards');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_album_awards === '' && $params->get('tab_album_awards') === '0') || $item->attribs->tab_album_awards === '0')
		{
			$id = $app->input->get('id', null, 'int');
			$app->redirect(JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $id . '&Itemid=' . $this->itemid, false));

			return false;
		}

		// Prepare the data
		$this->awardsItemid = KAContentHelper::getItemid('awards');
		$item->text         = '';
		$item->event        = new stdClass;
		$item->params       = new JObject;
		$item->params->set(
			'url',
			JRoute::_('index.php?option=com_kinoarhiv&view=album&page=awards&id=' . $item->id . '&Itemid=' . $this->itemid, false)
		);

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.albums', &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.album', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.album', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.album', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->params   = $params;
		$this->item     = $item;
		$this->metadata = json_decode($item->metadata);

		$this->prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem(
			$this->item->title,
			JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);
		$pathway->addItem(
			JText::_('COM_KA_MOVIE_TAB_AWARDS'),
			JRoute::_('index.php?option=com_kinoarhiv&view=album&page=awards&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);

		parent::display('awards');
	}

	/**
	 * Method to get and show album covers.
	 *
	 * @return  mixed
	 *
	 * @since  3.0
	 */
	protected function covers()
	{
		$app    = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$item   = $this->get('AlbumData');
		$items  = $this->get('Covers');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_album_covers === '' && $params->get('tab_album_covers') === '0') || $item->attribs->tab_album_covers === '0')
		{
			$id = $app->input->get('id', null, 'int');
			$app->redirect(JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $id . '&Itemid=' . $this->itemid, false));

			return false;
		}

		$files = array();

		foreach ($items as $row)
		{
			$files[$row->type][$row->id] = KAContentHelper::getAlbumCover(
				(object) array(
					'id'              => $row->id,
					'filename'        => $row->filename,
					'covers_path'     => $item->covers_path,
					'covers_path_www' => $item->covers_path_www,
					'fs_alias'        => $item->fs_alias,
					'dimension'       => $row->dimension
				),
				$params
			);
		}

		// Prepare the data
		$item->text   = '';
		$item->event  = new stdClass;
		$item->params = new JObject;
		$item->params->set(
			'url',
			JRoute::_('index.php?option=com_kinoarhiv&view=album&page=covers&id=' . $item->id . '&Itemid=' . $this->itemid, false)
		);

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.albums', &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.album', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.album', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.album', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->params   = $params;
		$this->item     = $item;
		$this->files    = $files;
		$this->metadata = json_decode($item->metadata);

		$this->prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem(
			$this->item->title,
			JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);
		$pathway->addItem(
			JText::_('COM_KA_ALBUM_TAB_COVERS'),
			JRoute::_('index.php?option=com_kinoarhiv&view=album&page=covers&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);

		parent::display('covers');
	}

	/**
	 * Method to get and show album crew.
	 *
	 * @return  mixed
	 *
	 * @since  3.1
	 */
	protected function crew()
	{
		$app    = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$item   = $this->get('Crew');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		// Prepare the data
		$this->namesItemid = KAContentHelper::getItemid('names');
		$item->text        = '';
		$item->event       = new stdClass;
		$item->params      = new JObject;
		$item->params->set(
			'url',
			JRoute::_('index.php?option=com_kinoarhiv&view=album&page=crew&id=' . $item->id . '&Itemid=' . $this->itemid, false)
		);

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.albums', &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.album', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.album', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.album', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->params   = $params;
		$this->item     = $item;
		$this->metadata = json_decode($item->metadata);

		$this->prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem(
			$this->item->title,
			JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);
		$pathway->addItem(
			JText::_('COM_KA_ALBUM_TAB_CREW'),
			JRoute::_('index.php?option=com_kinoarhiv&view=album&page=crew&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);

		parent::display('crew');
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
		$pathway    = $app->getPathway();

		$title = ($menu && $menu->title && $menu->link == 'index.php?option=com_kinoarhiv&view=albums')
				  ? $menu->title
				  : JText::_('COM_KA_ALBUMS');

		// Create a new pathway object
		$path = (object) array(
			'name' => $title,
			'link' => 'index.php?option=com_kinoarhiv&view=albums&Itemid=' . $this->itemid
		);

		$pathway->setPathway(array($path));
		$titleAdd = empty($this->page) ? '' : ' - ' . JText::_('COM_KA_ALBUM_TAB_' . StringHelper::ucwords($this->page));
		$title    = KAContentHelper::formatItemTitle($this->item->title, '', $this->item->year) . $titleAdd;

		if ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->item->metadesc)
		{
			$this->document->setDescription($this->item->metadesc);
		}
		elseif ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->item->metakey)
		{
			$this->document->setMetadata('keywords', $this->item->metakey);
		}
		elseif ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
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
