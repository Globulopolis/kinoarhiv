<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2018 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

class KinoarhivModelUpdate extends JModelLegacy
{
	public function update($version)
	{
		$db = $this->getDbo();
		$query = true;

		if ($version == '306')
		{
			$db->setQuery("SELECT `id`, `attribs` FROM `#__ka_movies`");
			$rows = $db->loadObjectlist();

			$db->setDebug(true);
			$db->lockTable('#__ka_movies');
			$db->transactionStart();

			foreach ($rows as $row)
			{
				$attribs = json_decode($row->attribs, true);

				if (!array_key_exists('allow_votes', $attribs))
				{
					$attribs['allow_votes'] = 1;
					unset($attribs['show_vote']);
				}

				if (!array_key_exists('ratings_show_local', $attribs))
				{
					$attribs['ratings_show_local'] = 1;
				}

				if (!array_key_exists('ratings_show_remote', $attribs))
				{
					$attribs['ratings_show_remote'] = 1;
				}

				$attribs = json_encode($attribs);

				$query = $db->getQuery(true)
					->update($db->quoteName('#__ka_movies'))
					->set($db->quoteName('attribs') . " = '" . $db->escape($attribs) . "'")
					->where($db->quoteName('id') . ' = ' . (int) $row->id . ';');

				$db->setQuery($query);
				$result = $db->execute();

				if ($result === false)
				{
					$query = false;
					break;
				}
			}

			if ($query === false)
			{
				$db->transactionRollback();
				$this->setError(JText::_('COM_KA_UPDATE_DB_ERROR'));
			}
			else
			{
				$db->transactionCommit();
			}

			$db->unlockTables();
			$db->setDebug(false);
		}

		return $query;
	}
}
