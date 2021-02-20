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
use Joomla\Registry\Registry;

/**
 * Movie View class
 *
 * @since  3.0
 */
class KinoarhivViewMovie extends JViewLegacy
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
	 * Method to get and show movie info data.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since  3.0
	 */
	protected function info()
	{
		$app        = JFactory::getApplication();
		$lang       = JFactory::getLanguage();
		$item       = $this->get('Data');
		$items      = $this->get('Items');
		$form       = $this->get('Form');
		$pagination = $this->get('Pagination');
		$pagination->hideEmptyLimitstart = true;

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		// Prepare the data
		$this->namesItemid    = KAContentHelper::getItemid('names');
		$this->releasesItemid = KAContentHelper::getItemid('releases');
		$this->profileItemid  = KAContentHelper::getItemid('profile');

		// Workaround for plugin interaction. Article must contain $text item.
		$item->text = '';

		$item->tagLayout = new JLayoutFile('components.com_kinoarhiv.layouts.content.tags', JPATH_ROOT);
		$item->tags      = new JHelperTags;
		$item->tags->getItemTags('com_kinoarhiv.movie', $item->id);

		$throttleEnable = $this->params->get('throttle_image_enable', 0);

		if ($throttleEnable == 0)
		{
			$checkingPosterPath = JPath::clean(
				$this->params->get('media_posters_root') . '/' . $item->fs_alias . '/' . $item->id . '/posters/' . $item->filename
			);

			if (!is_file($checkingPosterPath))
			{
				$item->poster = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $this->params->get('ka_theme') . '/no_movie_cover.png';
			}
			else
			{
				$posterFsAlias = rawurlencode($item->fs_alias);

				if (StringHelper::substr($this->params->get('media_posters_root_www'), 0, 1) == '/')
				{
					$item->poster = JUri::base() . StringHelper::substr($this->params->get('media_posters_root_www'), 1)
						. '/' . $posterFsAlias . '/' . $item->id . '/posters/thumb_' . $item->filename;
				}
				else
				{
					$item->poster = $this->params->get('media_posters_root_www') . '/' . $posterFsAlias . '/'
						. $item->id . '/posters/thumb_' . $item->filename;
				}
			}
		}
		else
		{
			$item->poster = JRoute::_(
				'index.php?option=com_kinoarhiv&task=media.view&element=movie&content=image&type=2&id=' . $item->id .
				'&fa=' . urlencode($item->fs_alias) . '&fn=' . $item->filename . '&format=raw&Itemid=' . $this->itemid . '&thumbnail=1'
			);
		}

		if (!empty($item->desc))
		{
			$item->desc = str_replace("\n", "<br />", $item->desc);
			$item->desc = str_replace(array('[code]', '[/code]'), array('<pre>', '</pre>'), $item->desc);
		}

		$item->_length = strftime('%H:%M', strtotime($item->length));
		list($hours, $minutes) = explode(':', $item->_length);
		$item->_hr_length = $hours * 60 + $minutes;

		if (!empty($item->rate_sum_loc) && !empty($item->rate_loc))
		{
			$plural = $lang->getPluralSuffixes($item->rate_loc);
			$item->rate_loc_value = round($item->rate_sum_loc / $item->rate_loc, (int) $this->params->get('vote_summ_precision'));
			$item->rate_loc_label = JText::sprintf(
				'COM_KA_RATE_LOCAL_' . $plural[0],
				$item->rate_loc_value,
				(int) $this->params->get('vote_summ_num')
			);
			$item->rate_loc_label_class = ' has-rating';
		}
		else
		{
			$item->rate_loc_value = 0;
			$item->rate_loc_label = JText::_('COM_KA_RATE_NO');
			$item->rate_loc_label_class = ' no-rating';
		}

		// Process slides
		if (($item->attribs->slider == '' && $this->params->get('slider') == 1) || $item->attribs->slider == 1)
		{
			if (!empty($item->slides))
			{
				foreach ($item->slides as $key => $slide)
				{
					$slideFsAlias = rawurlencode($item->fs_alias);
					$checkingSlidePath = JPath::clean(
						$this->params->get('media_scr_root') . '/' . $item->fs_alias . '/' . $item->id . '/screenshots/' . $slide->filename
					);

					if ($throttleEnable == 0)
					{
						if (!is_file($checkingSlidePath))
						{
							$noCover = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $this->params->get('ka_theme') . '/no_movie_cover.png';
							$item->slides[$key]->image = $noCover;
							$item->slides[$key]->th_image = $noCover;
							$dimension = KAContentHelper::getImageSize(
								JPATH_ROOT . '/media/com_kinoarhiv/images/themes/' . $this->params->get('ka_theme') . '/no_movie_cover.png',
								false
							);
							$item->slides[$key]->th_image_width = $dimension['width'];
							$item->slides[$key]->th_image_height = $dimension['height'];
						}
						else
						{
							if (StringHelper::substr($this->params->get('media_scr_root_www'), 0, 1) == '/')
							{
								$item->slides[$key]->image = JUri::base() . StringHelper::substr($this->params->get('media_scr_root_www'), 1)
									. '/' . $slideFsAlias . '/' . $item->id . '/screenshots/' . $slide->filename;
								$item->slides[$key]->th_image = JUri::base() . StringHelper::substr($this->params->get('media_scr_root_www'), 1)
									. '/' . $slideFsAlias . '/' . $item->id . '/screenshots/thumb_' . $slide->filename;
							}
							else
							{
								$item->slides[$key]->image = $this->params->get('media_scr_root_www') . '/'
									. $slideFsAlias . '/' . $item->id . '/screenshots/' . $slide->filename;
								$item->slides[$key]->th_image = $this->params->get('media_scr_root_www') . '/'
									. $slideFsAlias . '/' . $item->id . '/screenshots/thumb_' . $slide->filename;
							}

							$dimension = KAContentHelper::getImageSize(
								dirname($checkingSlidePath) . '/thumb_' . $slide->filename,
								true,
								(int) $this->params->get('size_x_scr'),
								$slide->dimension
							);
							$item->slides[$key]->th_image_width = $dimension['width'];
							$item->slides[$key]->th_image_height = $dimension['height'];
						}
					}
					else
					{
						$item->slides[$key]->image = JRoute::_(
							'index.php?option=com_kinoarhiv&task=media.view&element=movie&content=image&type=3&id=' . $item->id .
							'&fa=' . urlencode($item->fs_alias) . '&fn=' . $slide->filename . '&format=raw&Itemid=' . $this->itemid
						);
						$item->slides[$key]->th_image = JRoute::_(
							'index.php?option=com_kinoarhiv&task=media.view&element=movie&content=image&type=3&id=' . $item->id .
							'&fa=' . urlencode($item->fs_alias) . '&fn=' . $slide->filename . '&format=raw&Itemid=' . $this->itemid . '&thumbnail=1'
						);
						$dimension = KAContentHelper::getImageSize(
							dirname($checkingSlidePath) . '/thumb_' . $slide->filename,
							true,
							(int) $this->params->get('size_x_scr'),
							$slide->dimension
						);
						$item->slides[$key]->th_image_width = $dimension['width'];
						$item->slides[$key]->th_image_height = $dimension['height'];
					}
				}
			}
		}
		else
		{
			$item->slides = (object) array();
		}

		$item->event  = new stdClass;
		$item->params = new JObject;
		$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $item->id . '&Itemid=' . $this->itemid, false));

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$this->params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		// Reviews
		$this->items = $items;

		$this->item       = $item;
		$this->pagination = $pagination;
		$this->metadata   = json_decode($item->metadata);
		$this->form       = $form;
		$this->lang       = $lang;
		$this->view       = $app->input->getWord('view');

		$this->prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem(
			$this->item->title,
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);

		parent::display();
	}

	/**
	 * Method to get and show full cast and crew.
	 *
	 * @return  mixed
	 *
	 * @since  3.0
	 */
	protected function cast()
	{
		$item = $this->get('Cast');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$this->namesItemid = KAContentHelper::getItemid('names');
		$item->text        = '';
		$item->event       = new stdClass;
		$item->params      = new JObject;
		$item->params->set(
			'url',
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=cast&id=' . $item->id . '&Itemid=' . $this->itemid, false)
		);

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$this->params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->item = $item;
		$this->metadata = json_decode($item->metadata);

		$this->prepareDocument();
		$pathway = JFactory::getApplication()->getPathway();
		$pathway->addItem(
			$this->item->title,
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);
		$pathway->addItem(
			JText::_('COM_KA_MOVIE_CREATORS'),
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=cast&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);

		parent::display('cast');
	}

	/**
	 * Method to get and show wallpapers.
	 *
	 * @return  mixed
	 *
	 * @since  3.0
	 */
	protected function wallpapers()
	{
		$app        = JFactory::getApplication();
		$item       = $this->get('MovieData');
		$items      = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_movie_wallpp === '' && $this->params->get('tab_movie_wallpp') === '0') || $item->attribs->tab_movie_wallpp === '0')
		{
			$id = $app->input->get('id', null, 'int');
			$app->redirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $id . '&Itemid=' . $this->itemid, false));

			return false;
		}

		$item->text = '';
		$throttleEnable = $this->params->get('throttle_image_enable', 0);
		$fsAlias = rawurlencode($item->fs_alias);

		foreach ($items as $key => $_item)
		{
			$checkingPath = JPath::clean(
				$this->params->get('media_wallpapers_root') . '/' . $item->fs_alias . '/' . $item->id . '/wallpapers/' . $_item->filename
			);

			if ($throttleEnable == 0)
			{
				if (!is_file($checkingPath))
				{
					$items[$key]->image = 'javascript:void(0);';
					$items[$key]->th_image = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $this->params->get('ka_theme') . '/images/no_wp.png';
					$dimension = KAContentHelper::getImageSize(
						JPATH_ROOT . '/media/com_kinoarhiv/images/themes/' . $this->params->get('ka_theme') . '/no_wp.png',
						false
					);
					$items[$key]->th_image_width = $dimension['width'];
					$items[$key]->th_image_height = $dimension['height'];
				}
				else
				{
					if (StringHelper::substr($this->params->get('media_wallpapers_root_www'), 0, 1) == '/')
					{
						$items[$key]->image = JUri::base() . StringHelper::substr($this->params->get('media_wallpapers_root_www'), 1) . '/'
							. $fsAlias . '/' . $item->id . '/wallpapers/' . $_item->filename;
						$items[$key]->th_image = JUri::base() . StringHelper::substr($this->params->get('media_wallpapers_root_www'), 1) . '/'
							. $fsAlias . '/' . $item->id . '/wallpapers/thumb_' . $_item->filename;
					}
					else
					{
						$items[$key]->image = $this->params->get('media_wallpapers_root_www') . '/'
							. $fsAlias . '/' . $item->id . '/wallpapers/' . $item->_filename;
						$items[$key]->th_image = $this->params->get('media_wallpapers_root_www') . '/'
							. $fsAlias . '/' . $item->id . '/wallpapers/thumb_' . $item->_filename;
					}

					$dimension = KAContentHelper::getImageSize(
						dirname($checkingPath) . '/thumb_' . $_item->filename,
						true,
						(int) $this->params->get('size_x_wallpp'),
						$_item->dimension
					);
					$items[$key]->th_image_width = $dimension['width'];
					$items[$key]->th_image_height = $dimension['height'];
				}
			}
			else
			{
				$items[$key]->image = JRoute::_(
					'index.php?option=com_kinoarhiv&task=media.view&element=movie&content=image&type=1&id=' . $item->id .
					'&fa=' . urlencode($item->fs_alias) . '&fn=' . $_item->filename . '&format=raw&Itemid=' . $this->itemid
				);
				$items[$key]->th_image = JRoute::_(
					'index.php?option=com_kinoarhiv&task=media.view&element=movie&content=image&type=1&id=' . $item->id .
					'&fa=' . urlencode($item->fs_alias) . '&fn=' . $_item->filename . '&format=raw&Itemid=' . $this->itemid . '&thumbnail=1'
				);
				$dimension = KAContentHelper::getImageSize(
					dirname($checkingPath) . '/thumb_' . $_item->filename,
					true,
					(int) $this->params->get('size_x_wallpp'),
					$_item->dimension
				);
				$items[$key]->th_image_width = $dimension['width'];
				$items[$key]->th_image_height = $dimension['height'];
			}
		}

		$item->event = new stdClass;
		$item->params = new JObject;
		$item->params->set(
			'url',
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=wallpapers&id=' . $item->id . '&Itemid=' . $this->itemid, false)
		);

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$this->params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->item       = $item;
		$this->items      = $items;
		$this->filters    = $this->getDimensionList();
		$this->pagination = $pagination;
		$this->metadata   = json_decode($item->metadata);

		$this->prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem(
			$this->item->title,
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);
		$pathway->addItem(
			JText::_('COM_KA_MOVIE_TAB_WALLPAPERS'),
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=wallpapers&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);

		parent::display('wallpp');
	}

	/**
	 * Method to get and show posters.
	 *
	 * @return  mixed
	 *
	 * @since  3.0
	 */
	protected function posters()
	{
		$app        = JFactory::getApplication();
		$item       = $this->get('MovieData');
		$items      = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_movie_posters === '' && $this->params->get('tab_movie_posters') === '0') || $item->attribs->tab_movie_posters === '0')
		{
			$id = $app->input->get('id', null, 'int');
			$app->redirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $id . '&Itemid=' . $this->itemid, false));

			return false;
		}

		$item->text = '';
		$throttleEnable = $this->params->get('throttle_image_enable', 0);
		$fsAlias = rawurlencode($item->fs_alias);

		foreach ($items as $key => $_item)
		{
			$checkingPath = JPath::clean(
				$this->params->get('media_posters_root') . '/' . $item->fs_alias . '/' . $item->id . '/posters/' . $_item->filename
			);

			if ($throttleEnable == 0)
			{
				if (!is_file($checkingPath))
				{
					$items[$key]->image = 'javascript:void(0);';
					$items[$key]->th_image = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $this->params->get('ka_theme') . '/no_poster.png';
					$dimension = KAContentHelper::getImageSize(
						JPATH_ROOT . '/media/com_kinoarhiv/images/themes/' . $this->params->get('ka_theme') . '/no_poster.png',
						false
					);
					$items[$key]->th_image_width = $dimension['width'];
					$items[$key]->th_image_height = $dimension['height'];
				}
				else
				{
					if (StringHelper::substr($this->params->get('media_posters_root_www'), 0, 1) == '/')
					{
						$items[$key]->image = JUri::base() . StringHelper::substr($this->params->get('media_posters_root_www'), 1) . '/'
							. $fsAlias . '/' . $item->id . '/posters/' . $_item->filename;
						$items[$key]->th_image = JUri::base() . StringHelper::substr($this->params->get('media_posters_root_www'), 1) . '/'
							. $fsAlias . '/' . $item->id . '/posters/thumb_' . $_item->filename;
					}
					else
					{
						$items[$key]->image = $this->params->get('media_posters_root_www') . '/'
							. $fsAlias . '/' . $item->id . '/posters/' . $_item->_filename;
						$items[$key]->th_image = $this->params->get('media_posters_root_www') . '/'
							. $fsAlias . '/'	. $item->id . '/posters/thumb_' . $_item->_filename;
					}

					$dimension = KAContentHelper::getImageSize(
						dirname($checkingPath) . '/thumb_' . $_item->filename,
						true,
						(int) $this->params->get('size_x_posters'),
						$_item->dimension
					);
					$items[$key]->th_image_width = $dimension['width'];
					$items[$key]->th_image_height = $dimension['height'];
				}
			}
			else
			{
				$items[$key]->image = JRoute::_(
					'index.php?option=com_kinoarhiv&task=media.view&element=movie&content=image&type=2&id=' . $item->id .
					'&fa=' . urlencode($item->fs_alias) . '&fn=' . $_item->filename . '&format=raw&Itemid=' . $this->itemid
				);
				$items[$key]->th_image = JRoute::_(
					'index.php?option=com_kinoarhiv&task=media.view&element=movie&content=image&type=2&id=' . $item->id .
					'&fa=' . urlencode($item->fs_alias) . '&fn=' . $_item->filename . '&format=raw&Itemid=' . $this->itemid . '&thumbnail=1'
				);
				$dimension = KAContentHelper::getImageSize(
					dirname($checkingPath) . '/thumb_' . $_item->filename,
					true,
					(int) $this->params->get('size_x_posters'),
					$_item->dimension
				);
				$items[$key]->th_image_width = $dimension['width'];
				$items[$key]->th_image_height = $dimension['height'];
			}
		}

		$item->event = new stdClass;
		$item->params = new JObject;
		$item->params->set(
			'url',
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=posters&id=' . $item->id . '&Itemid=' . $this->itemid, false)
		);

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$this->params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->item       = $item;
		$this->items      = $items;
		$this->pagination = $pagination;
		$this->metadata   = json_decode($item->metadata);

		$this->prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem(
			$this->item->title,
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);
		$pathway->addItem(
			JText::_('COM_KA_MOVIE_TAB_POSTERS'),
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=posters&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);

		parent::display('posters');
	}

	/**
	 * Method to get and show screenshots.
	 *
	 * @return  mixed
	 *
	 * @since  3.0
	 */
	protected function screenshots()
	{
		$app        = JFactory::getApplication();
		$item       = $this->get('MovieData');
		$items      = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_movie_scr === '' && $this->params->get('tab_movie_scr') === '0') || $item->attribs->tab_movie_scr === '0')
		{
			$id = $app->input->get('id', null, 'int');
			$app->redirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $id . '&Itemid=' . $this->itemid, false));

			return false;
		}

		$item->text = '';
		$throttleEnable = $this->params->get('throttle_image_enable', 0);
		$fsAlias = rawurlencode($item->fs_alias);

		foreach ($items as $key => $_item)
		{
			$checkingPath = JPath::clean(
				$this->params->get('media_scr_root') . '/' . $item->fs_alias . '/' . $item->id . '/screenshots/' . $_item->filename
			);

			if ($throttleEnable == 0)
			{
				if (!is_file($checkingPath))
				{
					$items[$key]->image = 'javascript:void(0);';
					$items[$key]->th_image = JUri::base() . 'media/com_kinoarhiv/images/themes/'
						. $this->params->get('ka_theme') . '/no_movie_cover.png';
					$dimension = KAContentHelper::getImageSize(
						JPATH_ROOT . '/media/com_kinoarhiv/images/themes/' . $this->params->get('ka_theme') . '/no_movie_cover.png',
						false
					);
					$items[$key]->th_image_width = $dimension['width'];
					$items[$key]->th_image_height = $dimension['height'];
				}
				else
				{
					if (StringHelper::substr($this->params->get('media_scr_root_www'), 0, 1) == '/')
					{
						$items[$key]->image = JUri::base() . StringHelper::substr($this->params->get('media_scr_root_www'), 1) . '/'
							. $fsAlias . '/' . $item->id . '/screenshots/' . $_item->filename;
						$items[$key]->th_image = JUri::base() . StringHelper::substr($this->params->get('media_scr_root_www'), 1) . '/'
							. $fsAlias . '/' . $item->id . '/screenshots/thumb_' . $_item->filename;
					}
					else
					{
						$items[$key]->image = $this->params->get('media_scr_root_www') . '/'
							. $fsAlias . '/' . $item->id . '/screenshots/' . $_item->filename;
						$items[$key]->th_image = $this->params->get('media_scr_root_www') . '/'
							. $fsAlias . '/' . $item->id . '/screenshots/thumb_' . $_item->filename;
					}

					$dimension = KAContentHelper::getImageSize(
						dirname($checkingPath) . '/thumb_' . $_item->filename,
						true,
						(int) $this->params->get('size_x_scr'),
						$_item->dimension
					);
					$items[$key]->th_image_width = $dimension['width'];
					$items[$key]->th_image_height = $dimension['height'];
				}
			}
			else
			{
				$items[$key]->image = JRoute::_(
					'index.php?option=com_kinoarhiv&task=media.view&element=movie&content=image&type=3&id=' . $item->id .
					'&fa=' . urlencode($item->fs_alias) . '&fn=' . $_item->filename . '&format=raw&Itemid=' . $this->itemid
				);
				$items[$key]->th_image = JRoute::_(
					'index.php?option=com_kinoarhiv&task=media.view&element=movie&content=image&type=3&id=' . $item->id .
					'&fa=' . urlencode($item->fs_alias) . '&fn=' . $_item->filename . '&format=raw&Itemid=' . $this->itemid . '&thumbnail=1'
				);
				$dimension = KAContentHelper::getImageSize(
					dirname($checkingPath) . '/thumb_' . $_item->filename,
					true,
					(int) $this->params->get('size_x_scr'),
					$_item->dimension
				);
				$items[$key]->th_image_width = $dimension['width'];
				$items[$key]->th_image_height = $dimension['height'];
			}
		}

		$item->event = new stdClass;
		$item->params = new JObject;
		$item->params->set(
			'url',
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=screenshots&id=' . $item->id . '&Itemid=' . $this->itemid, false)
		);

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$this->params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->item       = $item;
		$this->items      = $items;
		$this->pagination = $pagination;
		$this->metadata   = json_decode($item->metadata);

		$this->prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem(
			$this->item->title,
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);
		$pathway->addItem(
			JText::_('COM_KA_MOVIE_TAB_SCREENSHOTS'),
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=screenshots&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);

		parent::display('screenshots');
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
		$app  = JFactory::getApplication();
		$item = $this->get('Awards');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_movie_awards === '' && $this->params->get('tab_movie_awards') === '0') || $item->attribs->tab_movie_awards === '0')
		{
			$id = $app->input->get('id', null, 'int');
			$app->redirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $id . '&Itemid=' . $this->itemid, false));

			return false;
		}

		// Prepare the data
		$item->text = '';
		$item->event = new stdClass;
		$item->params = new JObject;
		$item->params->set(
			'url',
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=awards&id=' . $item->id . '&Itemid=' . $this->itemid, false)
		);

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$this->params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->item = $item;
		$this->metadata = json_decode($item->metadata);

		$this->prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem(
			$this->item->title,
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);
		$pathway->addItem(
			JText::_('COM_KA_MOVIE_TAB_AWARDS'),
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=awards&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);

		parent::display('awards');
	}

	/**
	 * Method to get and show trailers.
	 *
	 * @return  mixed
	 *
	 * @since  3.0
	 */
	protected function trailers()
	{
		$app  = JFactory::getApplication();
		$item = $this->get('Trailers');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_movie_tr === '' && $this->params->get('tab_movie_tr') === '0') || $item->attribs->tab_movie_tr === '0')
		{
			$id = $app->input->get('id', null, 'int');
			$app->redirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $id . '&Itemid=' . $this->itemid, false));

			return false;
		}

		$item->text   = '';
		$item->event  = new stdClass;
		$item->params = new JObject;
		$item->params->set(
			'url',
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=trailers&id=' . $item->id . '&Itemid=' . $this->itemid, false)
		);

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$this->params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->item = $item;
		$this->metadata = json_decode($item->metadata);

		$this->prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem(
			$this->item->title,
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);
		$pathway->addItem(
			JText::_('COM_KA_MOVIE_TAB_TRAILERS'),
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=trailers&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);

		parent::display('trailers');
	}

	/**
	 * Method to get and show soundtracks and albums.
	 *
	 * @return  mixed
	 *
	 * @since  3.1
	 */
	protected function soundtracks()
	{
		$app   = JFactory::getApplication();
		$lang  = JFactory::getLanguage();
		$item  = $this->get('MovieData');
		$items = $this->get('SoundtrackAlbums');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_movie_snd === '' && $this->params->get('tab_movie_snd') === '0') || $item->attribs->tab_movie_snd === '0')
		{
			$id = $app->input->get('id', null, 'int');
			$app->redirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $id . '&Itemid=' . $this->itemid, false));

			return false;
		}

		// Get proper itemid for &view=?&Itemid=? links.
		$namesItemid = KAContentHelper::getItemid('names');
		$this->albumsItemid = KAContentHelper::getItemid('albums');

		$introtextLinks = $this->params->get('introtext_links', 1);
		$throttleEnable = $this->params->get('throttle_image_enable', 0);
		$item->text     = '';

		foreach ($items as $key => $album)
		{
			$album->attribs = json_decode($album->attribs);

			// Replace genres BB-code
			$album->text = preg_replace_callback('#\[genres\s+ln=(.+?)\](.*?)\[/genres\]#i', function ($matches)
			{
				return JText::_($matches[1]) . $matches[2];
			},
				$album->text
			);

			// Replace person BB-code
			$album->text = preg_replace_callback('#\[names\s+ln=(.+?)\](.*?)\[/names\]#i', function ($matches) use ($namesItemid, $introtextLinks)
			{
				$html = JText::_($matches[1]) . ': ';

				if ($introtextLinks)
				{
					$name = preg_replace(
						'#\[name=(.+?)\](.+?)\[/name\]#',
						'<a href="' . JRoute::_('index.php?option=com_kinoarhiv&view=name&id=$1&Itemid=' . $namesItemid, false) . '" title="$2">$2</a>',
						$matches[2]
					);
				}
				else
				{
					$name = preg_replace('#\[name=(.+?)\](.+?)\[/name\]#', '$2', $matches[2]);
				}

				return $html . $name . '<br/>';
			},
				$album->text
			);

			$checkingPath = KAContentHelper::getAlbumCheckingPath($album->covers_path, $this->params->get('media_music_images_root'), $album);

			if ($throttleEnable == 0)
			{
				$album->fs_alias = rawurlencode($album->fs_alias);

				if (!is_file($checkingPath))
				{
					$album->cover = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $this->params->get('ka_theme') . '/no_album_cover.png';
					$dimension = KAContentHelper::getImageSize(
						JPATH_ROOT . '/media/com_kinoarhiv/images/themes/' . $this->params->get('ka_theme') . '/no_album_cover.png',
						true,
						(int) $this->params->get('music_covers_size')
					);
					$album->coverWidth  = $dimension['width'];
					$album->coverHeight = $dimension['height'];
				}
				else
				{
					$filename = (!is_file(JPath::clean($checkingPath . '/thumb_' . $album->filename)))
						? $album->filename : 'thumb_' . $album->filename;

					if (!empty($album->covers_path))
					{
						if (StringHelper::substr($album->covers_path_www, 0, 1) == '/')
						{
							$album->cover = JUri::base() . StringHelper::substr($album->covers_path_www, 1) . '/' . $filename;
						}
						else
						{
							$album->cover = $album->covers_path_www . '/' . $filename;
						}
					}
					else
					{
						if (StringHelper::substr($this->params->get('media_music_images_root_www'), 0, 1) == '/')
						{
							$album->cover = JUri::base() . StringHelper::substr($this->params->get('media_music_images_root_www'), 1) . '/'
								. $album->fs_alias . '/' . $album->id . '/' . $filename;
						}
						else
						{
							$album->cover = $this->params->get('media_music_images_root_www') . '/' . $album->fs_alias
								. '/' . $album->id . '/' . $filename;
						}
					}

					$dimension = KAContentHelper::getImageSize(
						$checkingPath,
						true,
						(int) $this->params->get('music_covers_size'),
						$album->dimension
					);
					$album->coverWidth  = $dimension['width'];
					$album->coverHeight = $dimension['height'];
				}
			}
			else
			{
				// Check for thumbnail image. If not found when load full image.
				$thumbnail = (!is_file(JPath::clean($checkingPath . '/thumb_' . $album->filename))) ? 0 : 1;

				$album->cover = JRoute::_(
					'index.php?option=com_kinoarhiv&task=media.view&element=album&content=image&type=1&id=' . $album->id .
					'&fa=' . urlencode($album->fs_alias) . '&fn=' . $album->filename . '&format=raw&Itemid=' . $this->itemid .
					'&thumbnail=' . $thumbnail
				);
				$dimension = KAContentHelper::getImageSize(
					$checkingPath,
					true,
					(int) $this->params->get('music_covers_size'),
					$album->dimension
				);
				$album->coverWidth  = $dimension['width'];
				$album->coverHeight = $dimension['height'];
			}

			if (!empty($album->rate) && !empty($album->rate_sum))
			{
				$plural = $lang->getPluralSuffixes($album->rate);
				$album->rate_value = round($album->rate_sum / $album->rate, (int) $this->params->get('vote_summ_precision'));
				$album->rate_label = JText::sprintf(
					'COM_KA_RATE_LOCAL_' . $plural[0],
					$album->rate_value,
					(int) $this->params->get('vote_summ_num')
				);
				$album->rate_label_class = ' has-rating';
			}
			else
			{
				$album->rate_value = 0;
				$album->rate_label = JText::_('COM_KA_RATE_NO');
				$album->rate_label_class = ' no-rating';
			}
		}

		$item->event = new stdClass;
		$item->params = new JObject;
		$item->params->set(
			'url',
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=soundtracks&id=' . $item->id . '&Itemid=' . $this->itemid, false)
		);

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$this->params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->item     = $item;
		$this->items    = $items;
		$this->metadata = json_decode($item->metadata);

		$this->prepareDocument();
		$pathway = $app->getPathway();
		$pathway->addItem(
			$this->item->title,
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);
		$pathway->addItem(
			JText::_('COM_KA_MOVIE_TAB_SOUNDTRACKS'),
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=soundtracks&id=' . $this->item->id . '&Itemid=' . $this->itemid)
		);

		parent::display('soundtracks');
	}

	/**
	 * Build list of image resolutions.
	 *
	 * @return  mixed
	 *
	 * @since  3.0
	 */
	protected function getDimensionList()
	{
		$app        = JFactory::getApplication();
		$active     = $app->input->get('dim_filter', '0', 'string');
		$dimensions = $this->get('DimensionFilters');
		array_push($dimensions, array('width' => '0', 'title' => JText::_('COM_KA_FILTERS_DIMENSION_NOSORT')));

		// Build select
		$list = '<label for="dim_filter">' . JText::_('COM_KA_FILTERS_DIMENSION') . '</label>
		<select name="dim_filter" id="dim_filter" class="inputbox" onchange="this.form.submit()" autocomplete="off">';

		foreach ($dimensions as $dimension)
		{
			$selected = ($dimension['width'] == $active) ? 'selected="selected"' : '';
			$list .= '<option value="' . $dimension['width'] . '" ' . $selected . '>' . $dimension['title'] . '</option>';
		}

		$list .= '</select>';

		return array('dimensions.list' => $list);
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
		$title   = ($this->menu && $this->menu->title && $this->menu->link == 'index.php?option=com_kinoarhiv&view=movies')
			? $this->menu->title
			: JText::_('COM_KA_MOVIES');

		// Create a new pathway object
		$path = (object) array(
			'name' => $title,
			'link' => 'index.php?option=com_kinoarhiv&view=movies&Itemid=' . $this->itemid
		);

		$pathway->setPathway(array($path));
		$titleAdd = empty($this->page) ? '' : ' - ' . JText::_('COM_KA_MOVIE_TAB_' . StringHelper::ucwords($this->page));
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
