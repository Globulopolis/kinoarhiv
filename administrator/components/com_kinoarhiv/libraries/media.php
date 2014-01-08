<?php defined('_JEXEC') or die;

class KAMedia {
	protected static $instance;

	public function __construct() {
		$this->params = JComponentHelper::getParams('com_kinoarhiv');
	}

	public static function getInstance() {
		// Only create the object if it doesn't exist.
		if (empty(self::$instance)) {
			self::$instance = new KAMedia;
		}

		return self::$instance;
	}

	public function createScreenshot(&$data) {
		if (!empty($data['screenshot']) && file_exists($data['folder'].$data['screenshot'])) {
			@unlink($data['folder'].$data['screenshot']);
		}

		$ffmpeg_path = $this->params->get('ffmpeg_path', '', 'string');
		if ($ffmpeg_path  != '') {
			$result_filename = pathinfo($data['filename'], PATHINFO_FILENAME).'.png';
			$video_info = $this->getVideoInfo($data['folder'].$data['filename']);
			$video_info = json_decode($video_info);

			$scr_w = (int)$this->params->get('player_width');
			$scr_h = ($video_info->streams[0]->height * $scr_w) / $video_info->streams[0]->width;

			@set_time_limit(0);

			if (IS_WIN) {
				$output = shell_exec(escapeshellcmd($ffmpeg_path).' -hide_banner -nostats -i '.escapeshellcmd($data['folder'].$data['filename']).' -ss '.$data['time'].' -f image2 -vframes 1 -s '.floor($scr_w).'x'.floor($scr_h).' '.$data['folder'].$result_filename." 2>&1");
			} else {
				$output = shell_exec(escapeshellcmd($ffmpeg_path).' -hide_banner -nostats -i '.escapeshellcmd($data['folder'].$data['filename']).' -ss '.$data['time'].' -f image2 -vframes 1 -s '.floor($scr_w).'x'.floor($scr_h).' '.$data['folder'].$result_filename." 2>%1");
			}

			return '<pre>'.$output.'</pre>';
		} else {
			die(JText::_('COM_KA_MOVIES_GALLERY_ERROR_FILENOTFOUND'));
		}
	}

	public function detectMime($path) {
		if (!empty($path) && file_exists($path)) {
			if (function_exists('finfo_open')) {
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mime = finfo_file($finfo, $path);
				finfo_close($finfo);
			} elseif (function_exists('mime_content_type')) {
				$mime = mime_content_type($path);
			}
		} else {
			$mime = 'text/plain';
		}

		return $mime;
	}

	public function getVideoInfo($path, $stream='v:0', $format='json') {
		if (IS_WIN) {
			$output = shell_exec(escapeshellcmd($this->params->get('ffprobe_path')).' -v quiet -print_format '.(string)$format.' -show_streams -select_streams '.$stream.' '.escapeshellcmd($path).' 2>&1');
		} else {
			$output = shell_exec(escapeshellcmd($this->params->get('ffprobe_path')).' -v quiet -print_format '.(string)$format.' -show_streams -select_streams '.$stream.' '.escapeshellcmd($path).' 2>%1');
		}

		return $output;
	}
}
