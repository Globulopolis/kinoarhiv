<?php defined('_JEXEC') or die;

/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */
class KinoarhivModelUpdate extends JModelLegacy
{
	public function update($version)
	{
		$db = $this->getDbo();
		$query = true;

		if ($version == '306') {
			$db->setQuery("SELECT `id`, `attribs` FROM `#__ka_movies`");
			$rows = $db->loadObjectlist();

			$db->setDebug(true);
			$db->lockTable('#__ka_movies');
			$db->transactionStart();

			foreach ($rows as $row) {
				$attribs = json_decode($row->attribs, true);

				if (!array_key_exists('allow_votes', $attribs)) {
					$attribs['allow_votes'] = 1;
					unset($attribs['show_vote']);
				}
				if (!array_key_exists('ratings_show_local', $attribs)) {
					$attribs['ratings_show_local'] = 1;
				}
				if (!array_key_exists('ratings_show_remote', $attribs)) {
					$attribs['ratings_show_remote'] = 1;
				}

				$attribs = json_encode($attribs);

				$db->setQuery("UPDATE " . $db->quoteName('#__ka_movies') . " SET `attribs` = '" . $db->escape($attribs) . "' WHERE `id` = " . (int)$row->id . ";");
				$result = $db->execute();

				if ($result === false) {
					$query = false;
					break;
				}
			}

			if ($query === false) {
				$db->transactionRollback();
				$this->setError(JText::_('COM_KA_UPDATE_DB_ERROR'));
			} else {
				$db->transactionCommit();
			}

			$db->unlockTables();
			$db->setDebug(false);
		}

		return $query;
	}
}
