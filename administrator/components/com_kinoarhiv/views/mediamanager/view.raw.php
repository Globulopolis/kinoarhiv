<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *
 * @copyright   Copyright (C) 2010 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url            http://киноархив.com/
 */

defined('_JEXEC') or die;

class KinoarhivViewMediamanager extends JViewLegacy
{
	protected $data;

	protected $form;

	protected $params;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function display($tpl = null)
	{
		$input = JFactory::getApplication()->input;

		if ($input->get('type', '', 'word') == 'trailers')
		{
			$this->form = $this->get('Form');
		}

		if ($tpl == 'trailer_subtitles_lang_edit')
		{
			$this->data = $this->get('SubtitleEdit');
		}
		elseif ($tpl == 'trailer_videodata_edit')
		{
			$this->data = $this->get('VideoDataEdit');
		}

		$this->params = JComponentHelper::getParams('com_kinoarhiv');

		parent::display($tpl);
	}
}
