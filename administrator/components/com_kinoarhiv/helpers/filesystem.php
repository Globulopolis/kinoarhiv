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

/**
 * Class KAFilesystemHelper
 *
 * @since  3.0
 */
class KAFilesystemHelper
{
	/**
	 * Moves a folder and files.
	 *
	 * @param   mixed  $src   The path to the source folder or an array of paths. If $src is array when the folder content
	 *                        move into $dest.
	 * @param   mixed  $dest  The path to the destination folder or an array of paths.
	 * @param   bool   $copy  If false when just copy content, copy and remove otherwise.
	 *
	 * @return  boolean  True on success.
	 */
	public static function move($src, $dest, $copy = false)
	{
		if (is_array($src))
		{
			foreach ($src as $key => $source)
			{
				if (is_array($dest))
				{
					self::_moveItem($source, $dest[$key], $copy);
				}
				else
				{
					self::_moveItem($source, str_replace(basename($dest), '', $dest) . basename($source), $copy);
				}
			}
		}
		else
		{
			self::_moveItem($src, $dest, $copy);
		}

		return true;
	}

	/**
	 * Moves a folder and files.
	 *
	 * @param   mixed  $src   The path to the source folder or an array of paths. If $src is array when the folder content
	 *                        move into $dest.
	 * @param   mixed  $dest  The path to the destination folder or an array of paths.
	 * @param   bool   $copy  If false when just copy content, copy and remove otherwise.
	 *
	 * @return  boolean  True on success.
	 */
	protected static function _moveItem($src, $dest, $copy)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		if (!file_exists($dest))
		{
			JFolder::create($dest);
		}

		foreach (glob($src . DIRECTORY_SEPARATOR . '*.*', GLOB_NOSORT) as $filename)
		{
			if (JFile::copy($filename, $dest . DIRECTORY_SEPARATOR . basename($filename)))
			{
				// Delete source file
				if (!$copy)
				{
					if (!JFile::delete($filename))
					{
						JLog::add(__METHOD__ . ': ' . JText::sprintf('JLIB_FILESYSTEM_DELETE_FAILED', $filename), JLog::WARNING, 'jerror');
						break;
					}
				}
			}
			else
			{
				JLog::add(__METHOD__ . ': ' . JText::_('JLIB_FILESYSTEM_ERROR_COPY_FAILED') . ': ' . $filename, JLog::WARNING, 'jerror');
				break;
			}
		}

		if (!$copy)
		{
			if (file_exists($src))
			{
				JFolder::delete($src);
			}
		}

		return true;
	}

	/**
	 * Some mime-types.
	 *
	 * @return  array
	 */
	public static function mimes()
	{
		return array(
			'swf'   => 'application/x-shockwave-flash',
			'mid'   => 'audio/midi',
			'midi'  => 'audio/midi',
			'mpga'  => 'audio/mpeg',
			'mp2'   => 'audio/mpeg',
			'mp3'   => array('audio/mpeg', 'audio/mpg', 'audio/mpeg3', 'audio/mp3'),
			'aif'   => array('audio/x-aiff', 'audio/aiff'),
			'aiff'  => array('audio/x-aiff', 'audio/aiff'),
			'aifc'  => 'audio/x-aiff',
			'ram'   => 'audio/x-pn-realaudio',
			'rm'    => 'audio/x-pn-realaudio',
			'rpm'   => 'audio/x-pn-realaudio-plugin',
			'ra'    => 'audio/x-realaudio',
			'rv'    => 'video/vnd.rn-realvideo',
			'wav'   => array('audio/x-wav', 'audio/wave', 'audio/wav'),
			'bmp'   => array('image/bmp', 'image/x-bmp', 'image/x-bitmap', 'image/x-xbitmap', 'image/x-win-bitmap', 'image/x-windows-bmp', 'image/ms-bmp', 'image/x-ms-bmp', 'application/bmp', 'application/x-bmp', 'application/x-win-bitmap'),
			'gif'   => 'image/gif',
			'jpeg'  => array('image/jpeg', 'image/pjpeg'),
			'jpg'   => array('image/jpeg', 'image/pjpeg'),
			'jpe'   => array('image/jpeg', 'image/pjpeg'),
			'jp2'   => array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
			'j2k'   => array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
			'jpf'   => array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
			'jpg2'  => array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
			'jpx'   => array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
			'jpm'   => array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
			'mj2'   => array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
			'mjp2'  => array('image/jp2', 'video/mj2', 'image/jpx', 'image/jpm'),
			'png'   => array('image/png', 'image/x-png'),
			'xml'   => array('application/xml', 'text/xml', 'text/plain'),
			'mpeg'  => 'video/mpeg',
			'mpg'   => 'video/mpeg',
			'mpe'   => 'video/mpeg',
			'qt'    => 'video/quicktime',
			'mov'   => 'video/quicktime',
			'avi'   => array('video/x-msvideo', 'video/msvideo', 'video/avi', 'application/x-troff-msvideo'),
			'movie' => 'video/x-sgi-movie',
			'3g2'   => 'video/3gpp2',
			'3gp'   => array('video/3gp', 'video/3gpp'),
			'mp4'   => 'video/mp4',
			'm4a'   => 'audio/x-m4a',
			'f4v'   => array('video/mp4', 'video/x-f4v'),
			'flv'   => 'video/x-flv',
			'webm'  => 'video/webm',
			'aac'   => 'audio/x-acc',
			'm4u'   => 'application/vnd.mpegurl',
			'm3u'   => 'text/plain',
			'wmv'   => array('video/x-ms-wmv', 'video/x-ms-asf'),
			'au'    => 'audio/x-au',
			'ac3'   => 'audio/ac3',
			'flac'  => 'audio/x-flac',
			'ogg'   => array('audio/ogg', 'video/ogg', 'application/ogg'),
			'wma'   => array('audio/x-ms-wma', 'video/x-ms-asf'),
			'srt'   => array('text/srt', 'text/plain'),
			'vtt'   => array('text/vtt', 'text/plain')
		);
	}
}
