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
		$this->page   = $app->input->get('page', '', 'cmd');
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
		$lang             = JFactory::getLanguage();
		$item             = $this->get('Data');
		$items            = $this->get('Items');
		$this->form       = $this->get('Form');
		$this->pagination = $this->get('Pagination');
		$this->itemid     = $app->input->get('Itemid');
		$this->page       = $app->input->get('page', '', 'cmd');
		$this->pagination->hideEmptyLimitstart = true;

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$params       = JComponentHelper::getParams('com_kinoarhiv');
		$this->config = JFactory::getConfig();
		$checkingPath = JPath::clean($item->covers_path . '/' . $item->cover_filename);

		// Prepare the data
		$this->releasesItemid = KAContentHelper::getItemid('releases', array('params' => array('item_type' => 1, 'page_type' => 'list')));
		$this->profileItemid  = KAContentHelper::getItemid('profile');

		// Workaround for plugin interaction. Article must contain $text item.
		$item->text = '';

		$item->tagLayout = new JLayoutFile('components.com_kinoarhiv.layouts.content.tags', JPATH_ROOT);
		$item->tags      = new JHelperTags;
		$item->tags->getItemTags('com_kinoarhiv.album', $item->id);

		if (!is_file($checkingPath))
		{
			$item->cover = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_album_cover.png';
			$dimension   = KAContentHelper::getImageSize(
				JPATH_ROOT . '/media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_album_cover.png',
				false
			);
			$item->coverWidth  = $dimension['width'];
			$item->coverHeight = $dimension['height'];
		}
		else
		{
			$item->cover = $item->covers_path_www . '/' . $item->cover_filename;
			$dimension   = KAContentHelper::getImageSize(
				$checkingPath,
				true,
				(int) $params->get('music_covers_size')
			);
			$item->coverWidth  = $dimension['width'];
			$item->coverHeight = $dimension['height'];
		}

		$item->playlist = array();

		if (!empty($item->tracks))
		{
			foreach ($item->tracks as $key => $track)
			{
				$item->tracks[$key]->src = $item->tracks_path_www . '/' . $track->filename;
				unset($item->tracks[$key]->filename);

				$item->playlist[$key] = array(
					'id'  => $item->tracks[$key]->id,
					'src' => $item->tracks[$key]->src
				);
			}
		}

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
		$item->text   = '';
		$item->event  = new stdClass;
		$item->params = new JObject;
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
		$menuParams = $menu->getParams();

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
