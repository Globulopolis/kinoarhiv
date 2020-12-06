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

	/**
	 * The Joomla config object
	 *
	 * @var    JObject
	 * @since  3.0
	 */
	protected $config;

	protected $user;

	protected $itemid;

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
		$this->page   = $app->input->get('page', '', 'cmd');
		$this->itemid = $app->input->get('Itemid');

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
		$user       = JFactory::getUser();
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

		$params = JComponentHelper::getParams('com_kinoarhiv');
		$config = JFactory::getConfig();
		$throttleEnable = $params->get('throttle_image_enable', 0);
		$checkingPath = JPath::clean(
			$params->get('media_posters_root') . '/' . $item->fs_alias . '/' . $item->id . '/posters/' . $item->filename
		);

		// Prepare the data
		// Workaround for plugin interaction. Article must contain $text item.
		$item->text = '';

		if ($throttleEnable == 0)
		{
			if (!is_file($checkingPath))
			{
				$item->poster = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_movie_cover.png';
			}
			else
			{
				$fsAlias = rawurlencode($item->fs_alias);

				if (StringHelper::substr($params->get('media_posters_root_www'), 0, 1) == '/')
				{
					$item->poster = JUri::base() . StringHelper::substr($params->get('media_posters_root_www'), 1) . '/' . $fsAlias
						. '/' . $item->id . '/posters/thumb_' . $item->filename;
				}
				else
				{
					$item->poster = $params->get('media_posters_root_www') . '/' . $fsAlias . '/' . $item->id . '/posters/thumb_' . $item->filename;
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
			$item->rate_loc_value = round($item->rate_sum_loc / $item->rate_loc, (int) $params->get('vote_summ_precision'));
			$item->rate_loc_label = JText::sprintf('COM_KA_RATE_LOCAL_' . $plural[0], $item->rate_loc_value, (int) $params->get('vote_summ_num'));
			$item->rate_loc_label_class = ' has-rating';
		}
		else
		{
			$item->rate_loc_value = 0;
			$item->rate_loc_label = JText::_('COM_KA_RATE_NO');
			$item->rate_loc_label_class = ' no-rating';
		}

		// Process slides
		if (($item->attribs->slider == '' && $params->get('slider') == 1) || $item->attribs->slider == 1)
		{
			if (!empty($item->slides))
			{
				foreach ($item->slides as $key => $slide)
				{
					if ($throttleEnable == 0)
					{
						if (!is_file($checkingPath))
						{
							$noCover = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_movie_cover.png';
							$item->slides[$key]->image = $noCover;
							$item->slides[$key]->th_image = $noCover;
							$dimension = KAContentHelper::getImageSize(
								JPATH_ROOT . '/media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_movie_cover.png',
								false
							);
							$item->slides[$key]->th_image_width = $dimension['width'];
							$item->slides[$key]->th_image_height = $dimension['height'];
						}
						else
						{
							$slideFsAlias = rawurlencode($item->fs_alias);

							if (StringHelper::substr($params->get('media_posters_root_www'), 0, 1) == '/')
							{
								$item->slides[$key]->image = JUri::base() . StringHelper::substr($params->get('media_scr_root_www'), 1)
									. '/' . $slideFsAlias . '/' . $item->id . '/screenshots/' . $slide->filename;
								$item->slides[$key]->th_image = JUri::base() . StringHelper::substr($params->get('media_scr_root_www'), 1)
									. '/' . $slideFsAlias . '/' . $item->id . '/screenshots/thumb_' . $slide->filename;
							}
							else
							{
								$item->slides[$key]->image = $params->get('media_scr_root_www') . '/'
									. $slideFsAlias . '/' . $item->id . '/screenshots/' . $slide->filename;
								$item->slides[$key]->th_image = $params->get('media_scr_root_www') . '/'
									. $slideFsAlias . '/' . $item->id . '/screenshots/thumb_' . $slide->filename;
							}

							$dimension = KAContentHelper::getImageSize(
								$item->slides[$key]->th_image,
								true,
								(int) $params->get('size_x_scr'),
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
							JUri::base() . $item->slides[$key]->th_image,
							true,
							(int) $params->get('size_x_scr'),
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

		$item->event = new stdClass;
		$item->params = new JObject;
		$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $item->id . '&Itemid=' . $this->itemid, false));

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->params = $params;
		$this->config = $config;
		$this->item = $item;

		// Reviews
		$this->items = $items;

		$this->user = $user;
		$this->pagination = $pagination;
		$this->metadata = json_decode($item->metadata);
		$this->form = $form;
		$this->lang = $lang;
		$this->view = $app->input->getWord('view');

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

		if (count($errors = $this->get('Errors')) || is_null($item) || !$item)
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		$params = JComponentHelper::getParams('com_kinoarhiv');

		$item->text = '';
		$item->event = new stdClass;
		$item->params = new JObject;
		$item->params->set(
			'url',
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=cast&id=' . $item->id . '&Itemid=' . $this->itemid, false)
		);

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->params = $params;
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
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$item = $this->get('MovieData');
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors')) || is_null($items) || !$item || !$items)
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_movie_wallpp === '' && $params->get('tab_movie_wallpp') === '0') || $item->attribs->tab_movie_wallpp === '0')
		{
			$id = $app->input->get('id', null, 'int');
			$app->redirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $id . '&Itemid=' . $this->itemid, false));

			return false;
		}

		$item->text = '';
		$throttle_enable = $params->get('throttle_image_enable', 0);

		foreach ($items as $key => $_item)
		{
			if ($throttle_enable == 0)
			{
				$checking_path = JPath::clean(
					$params->get('media_wallpapers_root') . '/' . $item->fs_alias . '/' . $item->id . '/wallpapers/' . $_item->filename
				);

				if (!is_file($checking_path))
				{
					$items[$key]->image = 'javascript:void(0);';
					$items[$key]->th_image = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/images/no_wp.png';
					$dimension = KAContentHelper::getImageSize(
						JPATH_ROOT . '/media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_wp.png',
						false
					);
					$items[$key]->th_image_width = $dimension['width'];
					$items[$key]->th_image_height = $dimension['height'];
				}
				else
				{
					$fs_alias = rawurlencode($item->fs_alias);

					if (StringHelper::substr($params->get('media_wallpapers_root_www'), 0, 1) == '/')
					{
						$items[$key]->image = JUri::base() . StringHelper::substr($params->get('media_wallpapers_root_www'), 1) . '/'
							. $fs_alias . '/' . $item->id . '/wallpapers/' . $_item->filename;
						$items[$key]->th_image = JUri::base() . StringHelper::substr($params->get('media_wallpapers_root_www'), 1) . '/'
							. $fs_alias . '/' . $item->id . '/wallpapers/thumb_' . $_item->filename;
					}
					else
					{
						$items[$key]->image = $params->get('media_wallpapers_root_www') . '/' . $fs_alias . '/' . $item->id . '/wallpapers/' . $item->_filename;
						$items[$key]->th_image = $params->get('media_wallpapers_root_www') . '/' . $fs_alias . '/'
							. $item->id . '/wallpapers/thumb_' . $item->_filename;
					}

					$dimension = KAContentHelper::getImageSize(
						$items[$key]->th_image,
						true,
						(int) $params->get('size_x_wallpp'),
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
					JUri::base() . $items[$key]->th_image,
					true,
					(int) $params->get('size_x_wallpp'),
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
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->params = $params;
		$this->item = $item;
		$this->items = $items;
		$this->filters = $this->getDimensionList();
		$this->pagination = $pagination;
		$this->metadata = json_decode($item->metadata);

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
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$item = $this->get('MovieData');
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors')) || is_null($items) || !$item || !$items)
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_movie_posters === '' && $params->get('tab_movie_posters') === '0') || $item->attribs->tab_movie_posters === '0')
		{
			$id = $app->input->get('id', null, 'int');
			$app->redirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $id . '&Itemid=' . $this->itemid, false));

			return false;
		}

		$item->text = '';
		$throttle_enable = $params->get('throttle_image_enable', 0);

		foreach ($items as $key => $_item)
		{
			if ($throttle_enable == 0)
			{
				$checking_path = JPath::clean(
					$params->get('media_posters_root') . '/' . $item->fs_alias . '/' . $item->id . '/posters/' . $_item->filename
				);

				if (!is_file($checking_path))
				{
					$items[$key]->image = 'javascript:void(0);';
					$items[$key]->th_image = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_poster.png';
					$dimension = KAContentHelper::getImageSize(
						JPATH_ROOT . '/media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_poster.png',
						false
					);
					$items[$key]->th_image_width = $dimension['width'];
					$items[$key]->th_image_height = $dimension['height'];
				}
				else
				{
					$fs_alias = rawurlencode($item->fs_alias);

					if (StringHelper::substr($params->get('media_posters_root_www'), 0, 1) == '/')
					{
						$items[$key]->image = JUri::base() . StringHelper::substr($params->get('media_posters_root_www'), 1) . '/'
							. $fs_alias . '/' . $item->id . '/posters/' . $_item->filename;
						$items[$key]->th_image = JUri::base() . StringHelper::substr($params->get('media_posters_root_www'), 1) . '/'
							. $fs_alias . '/' . $item->id . '/posters/thumb_' . $_item->filename;
					}
					else
					{
						$items[$key]->image = $params->get('media_posters_root_www') . '/' . $fs_alias . '/' . $item->id . '/posters/' . $item->_filename;
						$items[$key]->th_image = $params->get('media_posters_root_www') . '/' . $fs_alias . '/'
							. $item->id . '/posters/thumb_' . $item->_filename;
					}

					$dimension = KAContentHelper::getImageSize(
						$items[$key]->th_image,
						true,
						(int) $params->get('size_x_posters'),
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
					JUri::base() . $items[$key]->th_image,
					true,
					(int) $params->get('size_x_posters'),
					$_item->dimension
				);
				$items[$key]->th_image_width = $dimension['width'];
				$items[$key]->th_image_height = $dimension['height'];
			}
		}

		$item->event = new stdClass;
		$item->params = new JObject;
		$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=posters&id=' . $item->id . '&Itemid=' . $this->itemid, false));

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->params = $params;
		$this->item = $item;
		$this->items = $items;
		$this->pagination = $pagination;
		$this->metadata = json_decode($item->metadata);

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
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$item = $this->get('MovieData');
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');

		if (count($errors = $this->get('Errors')) || is_null($items) || !$item || !$items)
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_movie_scr === '' && $params->get('tab_movie_scr') === '0') || $item->attribs->tab_movie_scr === '0')
		{
			$id = $app->input->get('id', null, 'int');
			$app->redirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $id . '&Itemid=' . $this->itemid, false));

			return false;
		}

		$item->text = '';
		$throttle_enable = $params->get('throttle_image_enable', 0);

		foreach ($items as $key => $_item)
		{
			if ($throttle_enable == 0)
			{
				$checking_path = JPath::clean(
					$params->get('media_scr_root') . '/' . $item->fs_alias . '/' . $item->id . '/screenshots/' . $_item->filename
				);

				if (!is_file($checking_path))
				{
					$items[$key]->image = 'javascript:void(0);';
					$items[$key]->th_image = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_movie_cover.png';
					$dimension = KAContentHelper::getImageSize(
						JPATH_ROOT . '/media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_movie_cover.png',
						false
					);
					$items[$key]->th_image_width = $dimension['width'];
					$items[$key]->th_image_height = $dimension['height'];
				}
				else
				{
					$fs_alias = rawurlencode($item->fs_alias);

					if (StringHelper::substr($params->get('media_scr_root_www'), 0, 1) == '/')
					{
						$items[$key]->image = JUri::base() . StringHelper::substr($params->get('media_scr_root_www'), 1) . '/'
							. $fs_alias . '/' . $item->id . '/screenshots/' . $_item->filename;
						$items[$key]->th_image = JUri::base() . StringHelper::substr($params->get('media_scr_root_www'), 1) . '/'
							. $fs_alias . '/' . $item->id . '/screenshots/thumb_' . $_item->filename;
					}
					else
					{
						$items[$key]->image = $params->get('media_scr_root_www') . '/' . $fs_alias . '/' . $item->id . '/screenshots/' . $item->_filename;
						$items[$key]->th_image = $params->get('media_scr_root_www') . '/' . $fs_alias . '/'
							. $item->id . '/screenshots/thumb_' . $item->_filename;
					}

					$dimension = KAContentHelper::getImageSize(
						$items[$key]->th_image,
						true,
						(int) $params->get('size_x_scr'),
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
					JUri::base() . $items[$key]->th_image,
					true,
					(int) $params->get('size_x_scr'),
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
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->params = $params;
		$this->item = $item;
		$this->items = $items;
		$this->pagination = $pagination;
		$this->metadata = json_decode($item->metadata);

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
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$item = $this->get('Awards');

		if (count($errors = $this->get('Errors')) || is_null($item) || !$item)
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_movie_awards === '' && $params->get('tab_movie_awards') === '0') || $item->attribs->tab_movie_awards === '0')
		{
			$id = $app->input->get('id', null, 'int');
			$app->redirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $id . '&Itemid=' . $this->itemid, false));

			return false;
		}

		// Prepare the data
		$item->text = '';
		$item->event = new stdClass;
		$item->params = new JObject;
		$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=awards&id=' . $item->id . '&Itemid=' . $this->itemid, false));

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->params = $params;
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
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$item = $this->get('Trailers');

		if (count($errors = $this->get('Errors')) || is_null($item) || !$item)
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_movie_tr === '' && $params->get('tab_movie_tr') === '0') || $item->attribs->tab_movie_tr === '0')
		{
			$id = $app->input->get('id', null, 'int');
			$app->redirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $id . '&Itemid=' . $this->itemid, false));

			return false;
		}

		// Check if player folder exists.
		if (!file_exists(JPATH_ROOT . '/media/com_kinoarhiv/players/' . $params->get('player_type')))
		{
			$playerLayout = ($params->get('player_type') == '-1') ? 'trailer' : 'trailer_' . $params->get('player_type');
			KAComponentHelper::eventLog(JText::sprintf('COM_KA_PLAYER_FOLDER_NOT_FOUND', $playerLayout));

			$params->set('player_type', '-1');
		}

		$user = JFactory::getUser();
		$item->text = '';
		$item->event = new stdClass;
		$item->params = new JObject;
		$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=trailers&id=' . $item->id . '&Itemid=' . $this->itemid, false));

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->params = $params;
		$this->item = $item;
		$this->user = $user;
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
		$this->user = JFactory::getUser();
		$app        = JFactory::getApplication();
		$params     = JComponentHelper::getParams('com_kinoarhiv');
		$lang       = JFactory::getLanguage();
		$item       = $this->get('SoundtrackAlbums');

		if (count($errors = $this->get('Errors')) || is_null($item) || !$item)
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

		if (($item->attribs->tab_movie_snd === '' && $params->get('tab_movie_snd') === '0') || $item->attribs->tab_movie_snd === '0')
		{
			$id = $app->input->get('id', null, 'int');
			$app->redirect(JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $id . '&Itemid=' . $this->itemid, false));

			return false;
		}

		$albums         = $item->albums;
		$item->text     = '';
		$throttleEnable = $params->get('throttle_image_enable', 0);

		foreach ($albums as $key => $album)
		{
			if (!empty($album->rate) && !empty($album->rate_sum))
			{
				$plural = $lang->getPluralSuffixes($album->rate);
				$albums[$key]->rate_loc = round($album->rate_sum / $album->rate, (int) $params->get('vote_summ_precision'));
				$albums[$key]->rate_loc_label = JText::sprintf('COM_KA_RATE_LOCAL_' . $plural[0], $album->rate_loc, (int) $params->get('vote_summ_num'));
				$albums[$key]->rate_loc_label_class = ' has-rating';
			}
			else
			{
				$albums[$key]->rate_loc = 0;
				$albums[$key]->rate_loc_label = JText::_('COM_KA_RATE_NO');
				$albums[$key]->rate_loc_label_class = ' no-rating';
			}

			$checkingPath = JPath::clean($albums[$key]->covers_path . '/' . $albums[$key]->cover_filename);

			if ($throttleEnable == 0)
			{
				if (!is_file($checkingPath))
				{
					$albums[$key]->cover = JUri::base() . 'media/com_kinoarhiv/images/themes/' . $params->get('ka_theme') . '/no_album_cover.png';
					$albums[$key]->coverWidth  = 250;
					$albums[$key]->coverHeight = 250;
				}
				else
				{
					$albums[$key]->cover = $albums[$key]->covers_path_www . '/' . $albums[$key]->cover_filename;
					$dimension = KAContentHelper::getImageSize(
						$checkingPath,
						true,
						(int) $params->get('music_covers_size')
					);
					$albums[$key]->coverWidth  = $dimension['width'];
					$albums[$key]->coverHeight = $dimension['height'];
				}
			}
			else
			{
				$albums[$key]->itemid = $this->itemid;
				$albums[$key]->cover  = KAContentHelper::getAlbumCoverLink($albums[$key]);
				$dimension            = KAContentHelper::getImageSize(
					$checkingPath,
					true,
					(int) $params->get('music_covers_size')
				);
				$albums[$key]->coverWidth  = $dimension['width'];
				$albums[$key]->coverHeight = $dimension['height'];
			}

			$registry = new Registry($album->attribs);
			$albums[$key]->attribs = $registry->toArray();
		}

		$item->event = new stdClass;
		$item->params = new JObject;
		$item->params->set(
			'url',
			JRoute::_('index.php?option=com_kinoarhiv&view=movie&page=soundtracks&id=' . $item->id . '&Itemid=' . $this->itemid, false)
		);

		$dispatcher = JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.movies', &$item, &$params, 0));

		$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.movie', &$item, &$item->params, 0));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->params = $params;
		$this->item = $item;
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
		$app = JFactory::getApplication();
		$active = $app->input->get('dim_filter', '0', 'string');
		$dimensions = $this->get('DimensionFilters');
		array_push($dimensions, array('width' => '0', 'title' => JText::_('COM_KA_FILTERS_DIMENSION_NOSORT')));

		// Build select
		$list = '<label for="dim_filter">' . JText::_('COM_KA_FILTERS_DIMENSION') . '</label>
		<select name="dim_filter" id="dim_filter" class="inputbox" onchange="this.form.submit()" autocomplete="off">';

		foreach ($dimensions as $dimension)
		{
			$selected = ($dimension['width'] == $active) ? ' selected="selected"' : '';
			$list .= '<option value="' . $dimension['width'] . '"' . $selected . '>' . $dimension['title'] . '</option>';
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
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$menu = $menus->getActive();
		$pathway = $app->getPathway();

		$title = ($menu && $menu->title && $menu->link == 'index.php?option=com_kinoarhiv&view=movies') ? $menu->title : JText::_('COM_KA_MOVIES');

		// Create a new pathway object
		$path = (object) array(
			'name' => $title,
			'link' => 'index.php?option=com_kinoarhiv&view=movies&Itemid=' . $this->itemid
		);

		$pathway->setPathway(array($path));
		$titleAdd = empty($this->page) ? '' : ' - ' . JText::_('COM_KA_MOVIE_TAB_' . StringHelper::ucwords($this->page));
		$this->document->setTitle(KAContentHelper::formatItemTitle($this->item->title, '', $this->item->year) . $titleAdd);

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
