<?php defined('_JEXEC') or die;

class KinoarhivViewMediamanager extends JViewLegacy {
	protected $items;
	protected $pagination;
	protected $state;
	protected $form;

	public function display($tpl = null) {
		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$items = $this->get('Items');
		$pagination = $this->get('Pagination');
		$state = $this->get('State');

		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$type = $app->input->get('type', '', 'word');
		$tab = $app->input->get('tab', 0, 'int');

		if ($app->input->get('section', '', 'word') == 'movie') {
			if ($type == 'gallery') {
				if ($tab == 1) {
					$path = $params->get('media_wallpapers_root');
					$path_www = $params->get('media_wallpapers_root_www');
					$folder = 'wallpapers';
				} elseif ($tab == 2) {
					$path = $params->get('media_posters_root');
					$path_www = $params->get('media_posters_root_www');
					$folder = 'posters';
				} elseif ($tab == 3) {
					$path = $params->get('media_scr_root');
					$path_www = $params->get('media_scr_root_www');
					$folder = 'screenshots';
				}

				foreach ($items as $item) {
					$file_path = $path.DIRECTORY_SEPARATOR.JString::substr($item->alias, 0, 1).DIRECTORY_SEPARATOR.$item->movie_id.DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR;
					$item->error = '';

					if (!file_exists($file_path.$item->filename)) {
						$item->filepath = 'javascript:void(0);';
						$item->folderpath = '';
						$item->error .= JText::_('COM_KA_MOVIES_GALLERY_ERROR_FILENOTFOUND');
					} else {
						$item->folderpath = $file_path;
						$item->filepath = JURI::root().$path_www.'/'.JString::substr($item->alias, 0, 1).'/'.$item->movie_id.'/'.$folder.'/'.$item->filename;
					}

					if (!file_exists($file_path.'thumb_'.$item->filename)) {
						$item->th_filepath = '';
						$item->error .= JText::_('COM_KA_MOVIES_GALLERY_ERROR_THUMB_FILENOTFOUND');
					} else {
						$item->th_filepath = JURI::root().$path_www.'/'.JString::substr($item->alias, 0, 1).'/'.$item->movie_id.'/'.$folder.'/thumb_'.$item->filename;
					}
				}

				$this->items = &$items;
				$this->pagination = &$pagination;
				$this->state = &$state;
				$this->params = &$params;

				$this->addToolbar();

				parent::display($tpl);
			} elseif ($type == 'trailers') {
				$form = $this->get('Form');
				$this->form = &$form;

				$this->items = &$items;
				$this->pagination = &$pagination;
				$this->state = &$state;
				$this->params = &$params;

				$this->addToolbar();

				if ($app->input->get('task', '', 'cmd') == 'edit') {
					parent::display('upload_trailer');
				} else {
					parent::display($tpl);
				}
			}
		}
	}

	protected function addToolbar($task='') {
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		JToolbarHelper::title(JText::_('COM_KA_MEDIAMANAGER'), 'cpanel.png');

		if ($user->authorise('core.create', 'com_kinoarhiv')) {
			JToolbarHelper::custom('upload', 'upload', 'upload', JText::_('JTOOLBAR_UPLOAD'), false);
			JToolbarHelper::divider();
		}

		if ($user->authorise('core.edit.state', 'com_kinoarhiv')) {
			JToolbarHelper::publishList();
			JToolbarHelper::unpublishList();
			JToolbarHelper::divider();
		}

		if ($user->authorise('core.delete', 'com_kinoarhiv')) {
			JToolbarHelper::deleteList(JText::_('COM_KA_DELETE_SELECTED'), 'remove');
		}
	}

	protected function getSortFields() {
		$input = JFactory::getApplication()->input;

		if ($input->get('type') == 'gallery') {
			return array(
				'g.filename' => JText::_('COM_KA_MOVIES_GALLERY_HEADING_FILENAME'),
				'g.dimension' => JText::_('COM_KA_MOVIES_GALLERY_HEADING_DIMENSION'),
				'g.poster_frontpage' => JText::_('COM_KA_MOVIES_GALLERY_HEADING_FRONTPAGE'),
				'g.state' => JText::_('JSTATUS'),
				'g.id' => JText::_('JGRID_HEADING_ID')
			);
		} elseif ($input->get('type') == 'trailers') {
			return array(
				'g.filename' => JText::_('COM_KA_MOVIES_GALLERY_HEADING_FILENAME'),
				'g.access' => JText::_('JGRID_HEADING_ACCESS'),
				'language' => JText::_('JGRID_HEADING_LANGUAGE'),
				'g.frontpage' => JText::_('COM_KA_MOVIES_GALLERY_HEADING_FRONTPAGE'),
				'g.state' => JText::_('JSTATUS'),
				'g.id' => JText::_('JGRID_HEADING_ID')
			);
		}
	}
}
