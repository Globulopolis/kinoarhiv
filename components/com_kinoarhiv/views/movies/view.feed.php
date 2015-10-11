<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

use Joomla\String\String;

defined('_JEXEC') or die;

/**
 * Movies feed View class
 *
 * @since  3.0
 */
class KinoarhivViewMovies extends JViewLegacy
{
	protected $items = null;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$lang = JFactory::getLanguage();

		$items = $this->get('Items');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors));

			return false;
		}

		JLoader::register('KAContentHelper', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'content.php');

		$document = JFactory::getDocument();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$feedEmail = $app->get('feed_email', 'author');
		$siteEmail = $app->get('mailfrom');
		$this->itemid = $app->input->get('Itemid', 0, 'int');
		$ka_theme = $params->get('ka_theme');
		$itemid = $this->itemid;
		$throttle_enable = $params->get('throttle_image_enable', 0);

		$document->setTitle(JText::_('COM_KA_MOVIES'));
		$document->setDescription($params->get('meta_description'));
		$document->link = JRoute::_('index.php?option=com_kinoarhiv&view=movies');

		if ($params->get('generator') == 'none')
		{
			$document->setGenerator('');
		}
		elseif ($params->get('generator') == 'site')
		{
			$document->setGenerator($document->getGenerator());
		}
		else
		{
			$document->setGenerator($params->get('generator'));
		}

		$app->input->set('limit', $app->get('feed_limit'));

		// Prepare the data
		foreach ($items as $row)
		{
			$year_str = ($row->year != '0000') ? ' (' . $row->year . ')' : '';
			$title = $this->escape($row->title . $year_str);
			$title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');
			$link = JRoute::_('index.php?option=com_kinoarhiv&view=movie&id=' . $row->id . '&Itemid=' . $this->itemid);
			$attribs = json_decode($row->attribs);

			$item = new JFeedItem;
			$item->title = $title;
			$item->link = $link;
			$item->author = ($attribs->show_author === '' && !empty($row->username)) ? $row->username : '';

			if ($feedEmail == 'site')
			{
				$item->authorEmail = $siteEmail;
			}
			elseif ($feedEmail === 'author')
			{
				$item->authorEmail = $row->author_email;
			}

			// Replace country BB-code
			$row->text = preg_replace_callback('#\[country\s+ln=(.+?)\](.*?)\[/country\]#i', function ($matches) use ($ka_theme)
			{
				$html = JText::_($matches[1]);

				$cn = preg_replace('#\[cn=(.+?)\](.+?)\[/cn\]#', '<img src="' . JURI::base() . 'components/com_kinoarhiv/assets/themes/component/' . $ka_theme . '/images/icons/countries/$1.png" border="0" alt="$2" class="ui-icon-country" /> $2', $matches[2]);

				return $html . $cn;
			},
			$row->text
			);

			// Replace genres BB-code
			$row->text = preg_replace_callback('#\[genres\s+ln=(.+?)\](.*?)\[/genres\]#i', function ($matches)
			{
				return JText::_($matches[1]) . $matches[2];
			},
			$row->text
			);


			// Replace person BB-code
			$row->text = preg_replace_callback('#\[names\s+ln=(.+?)\](.*?)\[/names\]#i', function ($matches) use ($itemid)
			{
				$html = JText::_($matches[1]);

				$name = preg_replace('#\[name=(.+?)\](.+?)\[/name\]#', '<a href="' . JRoute::_(JUri::base() . 'index.php?option=com_kinoarhiv&view=name&id=$1&Itemid=' . $itemid) . '" title="$2">$2</a>', $matches[2]);

				return $html . $name;
			},
			$row->text
			);

			if ($throttle_enable == 0)
			{
				$checking_path = JPath::clean(
					$params->get('media_posters_root') . DIRECTORY_SEPARATOR . $row->fs_alias .
					DIRECTORY_SEPARATOR . $row->id . DIRECTORY_SEPARATOR . 'posters' . DIRECTORY_SEPARATOR . $row->filename
				);

				if (!is_file($checking_path))
				{
					$row->poster = JURI::base() . 'components/com_kinoarhiv/assets/themes/component/' . $params->get('ka_theme') . '/images/no_movie_cover.png';
				}
				else
				{
					$row->fs_alias = rawurlencode($row->fs_alias);

					if (String::substr($params->get('media_posters_root_www'), 0, 1) == '/')
					{
						$row->poster = JURI::base() . String::substr($params->get('media_posters_root_www'), 1) . '/' . $row->fs_alias . '/' . $row->id . '/posters/thumb_' . $row->filename;
					}
					else
					{
						$row->poster = $params->get('media_posters_root_www') . '/' . $row->fs_alias . '/' . $row->id . '/posters/thumb_' . $row->filename;
					}
				}
			}
			else
			{
				$row->poster = JRoute::_(
					'index.php?option=com_kinoarhiv&task=media.view&element=movie&content=image&type=2&id=' . $row->id .
					'&fa=' . urlencode($row->fs_alias) . '&fn=' . $row->filename . '&format=raw&Itemid=' . $itemid . '&thumbnail=1'
				);
			}

			$row->plot = '<div class="feed-plot">' . JHtml::_('string.truncate', $row->plot, $params->get('limit_text')) . '</div>';
			$item->description = '<div class="feed-description">
				<div class="poster"><img src="' . $row->poster . '" border="0" /></div>
				<div class="introtext">' . $row->text . $row->plot . '</div>
			</div>';

			$document->addItem($item);
		}
	}
}
