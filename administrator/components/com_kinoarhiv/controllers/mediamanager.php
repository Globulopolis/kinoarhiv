<?php defined('_JEXEC') or die;

class KinoarhivControllerMediamanager extends JControllerLegacy {
	public function upload() {
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$app = JFactory::getApplication();

		JResponse::setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true);
		JResponse::setHeader('Last-Modified', gmdate('D, d M Y H:i:s'), true);
		JResponse::setHeader('Cache-Control', 'no-store, no-cache, must-revalidate', true);
		JResponse::setHeader('Cache-Control', 'post-check=0, pre-check=0', true);
		JResponse::setHeader('Pragma', 'no-cache', true);
		JResponse::sendHeaders();

		$dest_dir = $this->getPath();
		$cleanup_dir = true;
		$max_file_age = 5 * 3600;
		@set_time_limit(0);

		$chunk = $app->input->get('chunk', 0, 'int');
		$chunks = $app->input->get('chunks', 0, 'int');
		$filename = JFile::makeSafe($app->input->get('name', '', 'string'));

		if ($chunks < 2 && file_exists($dest_dir.DIRECTORY_SEPARATOR.$filename)) {
			$ext = strrpos($filename, '.');
			$fileName_a = substr($filename, 0, $ext);
			$fileName_b = substr($filename, $ext);

			$count = 1;
			while (file_exists($dest_dir.DIRECTORY_SEPARATOR.$fileName_a.'_'.$count.$fileName_b))
				$count++;

			$filename = $fileName_a.'_'.$count.$fileName_b;
		}

		$file_path = $dest_dir.DIRECTORY_SEPARATOR.$filename;

		if (!file_exists($dest_dir)) {
			JFolder::create($dest_dir);
		}

		if ($cleanup_dir) {
			if (is_dir($dest_dir) && ($dir = opendir($dest_dir))) {
				while (($file = readdir($dir)) !== false) {
					$tmpfilePath = $dest_dir.DIRECTORY_SEPARATOR.$file;

					// Remove temp file if it is older than the max age and is not the current file
					if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $max_file_age) && ($tmpfilePath != "{$file_path}.part")) {
						JFile::delete($tmpfilePath);
					}
				}
				closedir($dir);
			} else {
				die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
			}
		}

		if (isset($_SERVER["HTTP_CONTENT_TYPE"])) {
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
		}

		if (isset($_SERVER["CONTENT_TYPE"])) {
			$contentType = $_SERVER["CONTENT_TYPE"];
		}

		if (strpos($contentType, "multipart") !== false) {
			if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
				// Open temp file
				$out = @fopen("{$file_path}.part", $chunk == 0 ? "wb" : "ab");
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = @fopen($_FILES['file']['tmp_name'], "rb");

					if ($in) {
						while ($buff = fread($in, 4096))
							fwrite($out, $buff);
					} else
						die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
					@fclose($in);
					@fclose($out);
					JFile::delete($_FILES['file']['tmp_name']);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
		} else {
			// Open temp file
			$out = @fopen("{$file_path}.part", $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = @fopen("php://input", "rb");

				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

				@fclose($in);
				@fclose($out);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}

		// Check if file has been uploaded
		if (!$chunks || $chunk == $chunks - 1) {
			// Strip the temp .part suffix off 
			rename("{$file_path}.part", $file_path);
		}

		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
	}

	protected function getPath() {
		$model = $this->getModel('mediamanager');
		$path = $model->getPath();

		return $path;
	}

	public function gallery() {
		$view = $this->getView('mediamanager', 'raw');
		$model = $this->getModel('mediamanager');
		$view->setModel($model, true);

		$view->display('movie_gallery_list');

		return $this;
	}
}
