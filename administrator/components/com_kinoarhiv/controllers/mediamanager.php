<?php defined('_JEXEC') or die;

class KinoarhivControllerMediamanager extends JControllerLegacy {
	public function upload() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		/*jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_kinoarhiv');
		$model = $this->getModel('mediamanager');

		JResponse::setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true);
		JResponse::setHeader('Last-Modified', gmdate('D, d M Y H:i:s'), true);
		JResponse::setHeader('Cache-Control', 'no-store, no-cache, must-revalidate', true);
		JResponse::setHeader('Cache-Control', 'post-check=0, pre-check=0', true);
		JResponse::setHeader('Pragma', 'no-cache', true);
		JResponse::sendHeaders();

		$dest_dir = $model->getPath();
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

		// Proccess watermarks and thumbnails
		JLoader::register('KAImage', JPATH_COMPONENT.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'image.php');
		$image = new KAImage();

		if ($app->input->get('section', '', 'word') == 'movie') {
			if ($app->input->get('type') == 'gallery') {
				$tab = $app->input->get('tab', 0, 'int');
				$orig_image = @getimagesize($file_path);

				if ($tab == 1) {
					$width = (int)$params->get('size_x_wallpp');
					$height = ($width * $orig_image[1]) / $orig_image[0];
				} elseif ($tab == 2) {
					$width = (int)$params->get('size_x_posters');
					$height = ($width * $orig_image[1]) / $orig_image[0];
				} elseif ($tab == 3) {
					$width = (int)$params->get('size_x_scr');
					$height = ($width * $orig_image[1]) / $orig_image[0];
				}

				// Add watermark
				$watermark_img = $params->get('upload_gallery_watermark_image');

				if (!empty($watermark_img) && file_exists($watermark_img)) {
					$image->addWatermark($dest_dir, $filename, $watermark_img);
				}

				$image->_createThumbs($dest_dir, $filename, $width.'x'.$height, 1, $dest_dir, false);
				$model->saveImageInDB($image, $filename, $orig_image, $tab, $app->input->get('id', 0, 'int'));
			}
		}*/

		// Success
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
	}

	public function gallery() {
		$view = $this->getView('mediamanager', 'raw');
		$model = $this->getModel('mediamanager');
		$view->setModel($model, true);

		$view->display('movie_gallery_list');

		return $this;
	}

	/**
	 * Proxy for $this->fp_on() method
	 *
	 */
	public function fpOff() {
		$this->fpOn(1);
	}

	/**
	 * Method to publish or unpublish posters on movie info page(not on posters page)
	 *
	 * @param	int		 $action		  0 - unpublish from frontpage, 1 - publish poster on frontpage
	 *
	 */
	public function fpOn($action=0) {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('mediamanager');

		// Unpublish item from frontpage
		$result = $model->publishOnFrontpage((int)$action);

		$this->setRedirect(JURI::getInstance()->toString(), $result);
	}

	public function unpublish() {
		$this->publish(0);
	}

	public function publish($action=1) {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('mediamanager');
		$result = $model->publish((int)$action);

		$this->setRedirect(JURI::getInstance()->toString(), $result);
	}

	public function remove() {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('mediamanager');
		$result = $model->remove();

		$this->setRedirect(JURI::getInstance()->toString(), implode("<br />", $result));
	}
}
