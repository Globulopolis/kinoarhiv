<?php
/**
 * @package     Kinoarhiv.Site
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

/**
 * Movie View class
 *
 * @since  3.0
 */
class KinoarhivViewMovie extends JViewLegacy
{
	protected $item = null;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed
	 */
	public function display($tpl = null)
	{
		JLoader::register('KAContentHelper', JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'content.php');

		$this->watch(JFactory::getApplication()->input->get->get('type', 'trailer'));
	}

	protected function watch($type)
	{
		$user = JFactory::getUser();
		$params = JComponentHelper::getParams('com_kinoarhiv');

		if ($params->get('allow_guest_watch') == 0 && $user->get('guest') && $type == 'movie')
		{
			echo '<div style="width: 200px; margin: 20px 5px 5px 5px;">' . KAComponentHelper::showMsg(JText::_('JGLOBAL_AUTH_ACCESS_DENIED'), array('icon' => 'alert', 'type' => 'error')) . '</div>';

			return;
		}

		if ($type == 'trailer' || $type == 'movie')
		{
			$this->item = $this->get('Trailer');
			$this->params = $params;
			$this->user = $user;

			if ($params->get('player_type') == '-1')
			{
				parent::display('trailer');
			}
			else
			{
				parent::display('trailer_' . $params->get('player_type'));
			}
		}
		else
		{
			echo '<div style="width: 200px; margin: 20px 5px 5px 5px;">' . KAComponentHelper::showMsg(JText::_('ERROR'), array('icon' => 'alert', 'type' => 'error')) . '</div>';
		}
	}
}
