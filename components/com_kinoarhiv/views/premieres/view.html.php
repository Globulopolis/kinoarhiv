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
 * Premieres View class
 *
 * @since  3.0
 */
class KinoarhivViewPremieres extends JViewLegacy
{
	protected $items = null;

	protected $pagination = null;

	protected $user;

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
	 * @return  mixed
	 *
	 * @since  3.0
	 */
	public function display($tpl = null)
	{
		$this->user       = JFactory::getUser();
		$app              = JFactory::getApplication();
		$this->lang       = JFactory::getLanguage();
		$items            = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->itemid     = $app->input->get('Itemid', 0, 'int');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors), 'ui');

			return false;
		}

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
		$params       = $mergedParams;

		// Get proper itemid for &view=names&Itemid=? links.
		$namesItemid = KAContentHelper::getItemid('names');
		$this->moviesItemid = KAContentHelper::getItemid('movies');

		// Prepare the data
		foreach ($items as &$item)
		{
			$item->attribs = json_decode($item->attribs);

			// Replace country BB-code
			$item->text = preg_replace_callback('#\[country\s+ln=(.+?)\](.*?)\[/country\]#i', function ($matches)
			{
				$html = JText::_($matches[1]);

				$cn = preg_replace(
					'#\[cn=(.+?)\](.+?)\[/cn\]#',
					'<img src="media/com_kinoarhiv/images/icons/countries/$1.png" alt="$2" class="ui-icon-country" /> $2',
					$matches[2]
				);

				return $html . $cn;
			},
				$item->text
			);

			// Replace genres BB-code
			$item->text = preg_replace_callback('#\[genres\s+ln=(.+?)\](.*?)\[/genres\]#i', function ($matches)
			{
				return JText::_($matches[1]) . $matches[2];
			},
				$item->text
			);

			// Replace person BB-code
			$item->text = preg_replace_callback('#\[names\s+ln=(.+?)\](.*?)\[/names\]#i', function ($matches) use ($namesItemid)
			{
				$html = JText::_($matches[1]);

				$name = preg_replace(
					'#\[name=(.+?)\](.+?)\[/name\]#',
					'<a href="' . JRoute::_('index.php?option=com_kinoarhiv&view=name&id=$1&Itemid=' . $namesItemid, false) . '" title="$2">$2</a>',
					$matches[2]
				);

				return $html . $name;
			},
				$item->text
			);

			$item->poster = KAContentHelper::getMoviePoster($item, $params);
			$item->plot = JHtml::_('string.truncate', $item->plot, $params->get('limit_text'));

			if ($params->get('ratings_show_frontpage') == 1)
			{
				if (!empty($item->rate_sum_loc) && !empty($item->rate_loc))
				{
					$plural = $this->lang->getPluralSuffixes($item->rate_loc);
					$item->rate_loc_value = round($item->rate_sum_loc / $item->rate_loc, (int) $params->get('vote_summ_precision'));
					$item->rate_loc_label = JText::sprintf(
						'COM_KA_RATE_LOCAL_' . $plural[0],
						$item->rate_loc_value,
						(int) $params->get('vote_summ_num'),
						$item->rate_loc
					);
					$item->rate_loc_label_class = ' has-rating';
				}
				else
				{
					$item->rate_loc_value = 0;
					$item->rate_loc_label = JText::_('COM_KA_RATE_NO');
					$item->rate_loc_label_class = ' no-rating';
				}
			}

			$item->event  = new stdClass;
			$item->params = new JObject;
			$item->params->set('url', JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $item->id . '&Itemid=' . $this->itemid, false));

			$dispatcher = JEventDispatcher::getInstance();
			JPluginHelper::importPlugin('content');
			$dispatcher->trigger('onContentPrepare', array('com_kinoarhiv.premieres', &$item, &$params, 0));

			$results = $dispatcher->trigger('onContentAfterTitle', array('com_kinoarhiv.premieres', &$item, &$item->params, 0));
			$item->event->afterDisplayTitle = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_kinoarhiv.premieres', &$item, &$item->params, 0));
			$item->event->beforeDisplayContent = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentAfterDisplay', array('com_kinoarhiv.premieres', &$item, &$item->params, 0));
			$item->event->afterDisplayContent = trim(implode("\n", $results));
		}

		$this->params = $params;
		$this->items  = $items;
		$this->view   = $app->input->getWord('view');

		$this->prepareDocument();
		parent::addTemplatePath(JPath::clean(JPATH_COMPONENT . '/views/movies/tmpl'));

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
		$title   = ($this->menu && $this->menu->title) ? $this->menu->title : JText::_('COM_KA_PREMIERES');

		// Create a new pathway object
		$path = (object) array(
			'name' => $title,
			'link' => 'index.php?option=com_kinoarhiv&view=premieres&Itemid=' . $this->itemid
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

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
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
