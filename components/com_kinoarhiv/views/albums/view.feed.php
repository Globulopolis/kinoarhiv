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
 * Albums feed View class
 *
 * @since  3.1
 */
class KinoarhivViewAlbums extends JViewLegacy
{
	/**
	 * Albums data object
	 *
	 * @var    object
	 * @since  1.6
	 */
	protected $items = null;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  false|void
	 *
	 * @since  3.1
	 */
	public function display($tpl = null)
	{
		$items = $this->get('Items');

		if (count($errors = $this->get('Errors')))
		{
			KAComponentHelper::eventLog(implode("\n", $errors));

			return false;
		}

		$app       = JFactory::getApplication();
		$document  = JFactory::getDocument();
		$params    = JComponentHelper::getParams('com_kinoarhiv');
		$feedEmail = $app->get('feed_email', 'author');
		$siteEmail = $app->get('mailfrom');
		$itemid    = $app->input->get('Itemid', 0, 'int');
		$introtextLinks = $params->get('introtext_links', 1);

		$document->setTitle(JText::_('COM_KA_MUSIC_ALBUMS'));
		$document->setDescription($params->get('meta_description'));
		$document->link = JRoute::_('index.php?option=com_kinoarhiv&view=albums');

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

		// Get proper itemid for &view=?&Itemid=? links.
		$namesItemid = KAContentHelper::getItemid('names');

		// Prepare the data
		foreach ($items as $row)
		{
			$title   = $this->escape(KAContentHelper::formatItemTitle($row->title, '', $row->year));
			$attribs = json_decode($row->attribs);

			$item         = new JFeedItem;
			$item->title  = html_entity_decode($title, ENT_COMPAT, 'UTF-8');
			$item->link   = JRoute::_('index.php?option=com_kinoarhiv&view=album&id=' . $row->id . '&Itemid=' . $itemid);
			$item->author = ($attribs->show_author === '' && !empty($row->username)) ? $row->username : '';

			if ($feedEmail == 'site')
			{
				$item->authorEmail = $siteEmail;
			}
			elseif ($feedEmail === 'author')
			{
				$item->authorEmail = $row->author_email;
			}

			// Replace genres BB-code
			$row->text = preg_replace_callback('#\[genres\s+ln=(.+?)\](.*?)\[/genres\]#i', function ($matches)
			{
				return JText::_($matches[1]) . $matches[2];
			},
				$row->text
			);

			// Replace person BB-code
			$row->text = preg_replace_callback('#\[names\s+ln=(.+?)\](.*?)\[/names\]#i', function ($matches) use ($namesItemid, $introtextLinks)
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

				return $html . $name;
			},
				$row->text
			);

			$row->cover = KAContentHelper::getAlbumCover($row, $params);
			$item->description = '<div class="feed-description">
				<div class="poster">
					<img src="' . $row->cover->coverThumb . '" width="' . $row->cover->coverThumbWidth . '"
						 height="' . $row->cover->coverThumbHeight . '" />
				</div>
				<div class="introtext">' . $row->text . '</div>
			</div>';

			/** @var $document JDocumentFeed */
			$document->addItem($item);
		}
	}
}
