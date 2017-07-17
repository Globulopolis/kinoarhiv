<?php
/**
 * @package     Kinoarhiv.Administrator
 * @subpackage  com_kinoarhiv
 *  
 * @copyright   Copyright (C) 2017 Libra.ms. All rights reserved.
 * @license     GNU General Public License version 2 or later
 * @url         http://киноархив.com
 */

defined('_JEXEC') or die;

$url = 'index.php?option=com_kinoarhiv&task=mediamanager.upload&format=raw&section=' . $this->section
	. '&type=' . $this->type . '&id=' . $this->id . '&item_id=' . $this->trailer_id;
$token = JSession::getFormToken();

echo JHtml::_('bootstrap.startTabSet', 'upload_video_tab', array('active' => 'video'));
	echo JHtml::_('bootstrap.addTab', 'upload_video_tab', 'video', JText::_('COM_KA_TRAILERS_VIDEO_UPLOAD_TITLE'));

	echo JLayoutHelper::render(
		'layouts.edit.upload_file_body',
		array(
			'url'              => $url . '&upload=video',
			'multipart_params' => '{"' . $token . '": 1}',
			'content-type'     => 'video',
			'max_file_size'    => $this->params->get('upload_limit'),
			'multiple_queues'  => 'true',
			'filters'          => '{"title": "Video files", "extensions": "' . $this->params->get('upload_mime_video') . '"}',
			'chunk_size'       => $this->params->get('upload_chunk_size'),
			'info'             => array(
				'text'  => JText::sprintf('COM_KA_TRAILERS_EDIT_UPLOAD_FILENAME_CONVERT_VIDEO', $this->params->get('upload_mime_video')),
				'close' => false
			)
		),
		JPATH_COMPONENT
	);

	echo JHtml::_('bootstrap.endTab');
	echo JHtml::_('bootstrap.addTab', 'upload_video_tab', 'subtitles', JText::_('COM_KA_TRAILERS_UPLOAD_FILES_SUBTITLES'));

	echo JLayoutHelper::render(
		'layouts.edit.upload_file_body',
		array(
			'url'              => $url . '&upload=subtitles',
			'multipart_params' => '{"' . $token . '": 1}',
			'content-type'     => 'subtitles',
			'max_file_size'    => $this->params->get('upload_limit'),
			'multiple_queues'  => 'true',
			'filters'          => '{"title": "Subtitle files", "extensions": "' . $this->params->get('upload_mime_subtitles') . '"}',
			'chunk_size'       => $this->params->get('upload_chunk_size'),
			'info'             => array(
				'text'  => JText::sprintf(
						'COM_KA_TRAILERS_EDIT_UPLOAD_FILENAME_CONVERT_SUBTITLES',
						$this->params->get('upload_mime_subtitles')
					) . JText::_('COM_KA_TRAILERS_SUBTITLES_WARN'),
				'close' => false
			)
		),
		JPATH_COMPONENT
	);

	echo JHtml::_('bootstrap.endTab');
	echo JHtml::_('bootstrap.addTab', 'upload_video_tab', 'chapters', JText::_('COM_KA_TRAILERS_UPLOAD_FILES_CHAPTERS'));

	echo JLayoutHelper::render(
		'layouts.edit.upload_file_body',
		array(
			'url'              => $url . '&upload=chapters',
			'multipart_params' => '{"' . $token . '": 1}',
			'content-type'     => 'chapters',
			'multi_selection'  => false,
			'max_files'        => 1,
			'max_file_size'    => $this->params->get('upload_limit'),
			'multiple_queues'  => 'true',
			'filters'          => '{"title": "Chapter files", "extensions": "' . $this->params->get('upload_mime_chapters') . '"}',
			'chunk_size'       => $this->params->get('upload_chunk_size'),
			'info'             => array(
				'text'  => JText::sprintf('COM_KA_TRAILERS_EDIT_UPLOAD_FILENAME_CONVERT_CHAPTERS', $this->params->get('upload_mime_chapters')),
				'close' => false
			)
		),
		JPATH_COMPONENT
	);

	echo JHtml::_('bootstrap.endTab');
echo JHtml::_('bootstrap.endTabSet');
