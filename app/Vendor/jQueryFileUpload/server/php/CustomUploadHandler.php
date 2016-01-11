<?php

require_once 'UploadHandler.php';

class CustomUploadHandler extends UploadHandler {

	public function __construct($options = null, $initialize = true, $error_messages = null) {

		// ghi đè lại thư mục cần upload
		$options = array(
			'script_url' => $this->get_full_url() . '/',
			'upload_dir' => dirname($this->get_server_var('SCRIPT_FILENAME')) . '/tmp/',
			'upload_url' => $this->get_full_url() . '/tmp/',
		);

		// ghi đè không cho tự động xử lý upload file khi khởi tạo object
		$initialize = false;

		parent::__construct($options, $initialize, $error_messages);
	}

	protected function trim_file_name($file_path, $name, $size, $type, $error, $index, $content_range) {

		// ghi đè vào phương thức đã có, làm đẹp file_name và tạo unique
		$unique = $this->generateRandomLetters(5);
		$ext = pathinfo($name, PATHINFO_EXTENSION);
		$origin_name = basename($name, "." . $ext);
		$unique_name = $this->normalizeUrl($origin_name) . '_' . $unique . '.' . $ext;

		// Remove path information and dots around the filename, to prevent uploading
		// into different directories or replacing hidden system files.
		// Also remove control characters and spaces (\x00..\x20) around the filename:
		$name = trim(basename(stripslashes($unique_name)), ".\x00..\x20");
		// Use a timestamp for empty filenames:
		if (!$name) {
			$name = str_replace('.', '-', microtime(true));
		}
		return $name;
	}

	protected function handle_form_data($file, $index) {
		// Handle form data, e.g. $_REQUEST['description'][$index]
		// thêm tham số form data để chỉnh sửa lại tên name của file trong trường hợp
		// upload file theo hướng Programmatic file upload: https://github.com/blueimp/jQuery-File-Upload/wiki/API
		// nếu không thêm code này vào thì mặc định file được upload lên theo kiểu blob sẽ luôn có tên là blob
		// đọc thêm về cách khắc phục: https://github.com/blueimp/jQuery-File-Upload/issues/812
		// đọc thêm về cách thêm vào form data: https://github.com/blueimp/jQuery-File-Upload/wiki/How-to-submit-additional-Form-Data
		if (isset($_POST['filename_blob']) && is_array($_POST['filename_blob'])) {

			$file->name = $_POST['filename_blob'][$index];
		}
	}

	/**
	 * generateRandomLetters
	 * thực tạo ra các kí tự ngẫu nhiên
	 * 
	 * @param int $length
	 * @return string
	 */
	public function generateRandomLetters($length) {

		$random = '';

		for ($i = 0; $i < $length; $i++) {

			$random .= chr(rand(ord('a'), ord('z')));
		}

		return $random;
	}

	/**
	 * convert_vi_to_en method
	 * hàm chuyền đổi tiếng việt có dấu sang tiếng việt không dấu
	 * 
	 * @param string $str
	 * @return string
	 */
	protected function convert_vi_to_en($str) {

		$str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
		$str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
		$str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
		$str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
		$str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
		$str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
		$str = preg_replace("/(đ)/", 'd', $str);
		$str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
		$str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
		$str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
		$str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
		$str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
		$str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
		$str = preg_replace("/(Đ)/", 'D', $str);
//$str = str_replace(" ", "-", str_replace("&*#39;","",$str));
		return $str;
	}

	/**
	 * normalizeUrl
	 * hàm chuyển đổi các kí tự đặc biệt, dấu cách thành dạng có dấu gạch ngang và viết thường
	 * trong việc tạo ra folder trong dựa vào file name
	 * 
	 * @param string $str
	 * @return string
	 */
	protected function normalizeUrl($str) {

		$str = $this->convert_vi_to_en($str);
		$str = preg_replace("![^a-z0-9]+!i", "-", mb_strtolower($str, "UTF-8"));

		return $str;
	}

}
