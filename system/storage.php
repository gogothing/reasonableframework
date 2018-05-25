<?php
/**
 * @file stroage.php
 * @date 2018-05-21
 * @author Go Namhyeon <gnh1201@gmail.com>
 * @brief Stroage module
 */

if(!function_exists("get_storage_dir")) {
	function get_storage_dir() {
		$config = get_config();
		return get_value_in_array("storage_dir", $config, "storage");
	}
}

if(!function_exists("get_storage_path")) {
	function get_storage_path($type="data") {
		$dir_path = sprintf("./%s/%s", get_storage_dir(), $type);
		if(!is_dir($dir_path)) {
			if(!mkdir($dir_path, 0777)) {
				set_error("can not create directory");
				show_errors();
			}
		}
		return $dir_path;
	}
}

if(!function_exists("get_storage_url")) {
	function get_storage_url($type="data") {
		return sprintf("%s%s/%s", base_url(), get_storage_dir(), $type);
	}
}

if(!function_exists("move_uploaded_file_to_storage")) {
	function move_uploaded_file_to_stroage($options=array()) {
		$files = array();
		$requests = get_requests();

		$storage_type = get_value_in_array("storage_type", $options, "data");
		$upload_base_dir = get_storage_path($storage_type);
		$upload_base_url = get_storage_url($storage_type);

		if(!array_key_empty("only_image", $options)) {
			$upload_allow_ext = array(
				"png", "gif", "jpg", "jpeg", "tif"
			);
		} elseif(!array_key_empty("only_docs", $options)) {
			$upload_allow_ext = array(
				"png", "gif", "jpg", "jpeg", "tif",
				"xls", "ppt", "doc", "xlsx", "pptx",
				"docx", "odt", "odp", "ods", "xlsm",
				"tiff", "pdf", "xlsm"
			);
		} elseif(!array_key_empty("only_audio", $options)) {
			$upload_allow_ext = array(
				"mp3", "ogg", "m4a", "wma", "wav"
			);
		} else {
			$upload_allow_ext = array();
		}

		foreach($files as $k=>$file) {
			$upload_ext = get_file_extension($files[$k]['name']);
			$upload_name = make_random_id(32) . (empty($upload_ext) ? "" : "." . $upload_ext);
			$upload_file = $upload_base_dir . $upload_name;
			$upload_url = $upload_base_url . $upload_name;

			if(count($upload_allow_ext) == 0 || in_array($upload_ext, $upload_allow_ext)) {
				if(move_uploaded_file($files[$k]['tmp_name'], $upload_file)) {
					$response['files'][] = array(
						"storage_type" => $storage_type,
						"upload_ext" => $upload_ext,
						"upload_name" => $upload_name,
						"upload_file" => $upload_file,
						"upload_url" => $upload_url,
						"upload_error" => ""
					);
				} else {
					$response['files'][] = array(
						"upload_error" => "File write error."
					);
				}
			} else {
				$response['files'][] = array(
					"upload_error" => "Not allowed file type."
				);
			}
		}

		return $response['files'];
	}
}

if(!function_exists("read_storage_file")) {
	function read_storage_file($filename, $options=array()) {
		$result = false;

		$storage_type = get_value_in_array("storage_type", $options, "data");
		$upload_base_path = get_storage_path($storage_type);
		$upload_base_url = get_storage_url($storage_type);
		$upload_filename = $upload_base_path . "/" . $filename;

		if(file_exists($upload_filename)) {
			if($fhandle = fopen($upload_filename, "r")) {
				$result = fread($fhandle, filesize($upload_filename));
				fclose($fhandle);
			}

			if(!array_key_empty("encode_base64", $options)) {
				$result = base64_encode($result);
			}
		}

		return $result;
	}
}

if(!function_exists("write_storage_file")) {
	function write_storage_file($data, $options=array()) {
		$result = false;

		$filename = get_value_in_array("filename", $options, make_random_id(32));
		$storage_type = get_value_in_array("storage_type", $options, "data");
		$upload_base_path = get_storage_path($storage_type);
		$upload_base_url = get_storage_url($storage_type);
		$upload_filename = $upload_base_path . "/" . $filename;

		if(file_exists($upload_filename)) {
			if(!array_key_empty("filename", $options)) {
				$result = $upload_filename;
			} else {
				$result = write_storage_file($data, $options);
			}
		} else {
			if($fhandle = fopen($upload_filename, "w")) {
				if(fwrite($fhandle, $data)) {
					$result = $upload_filename;
				}
				fclose($fhandle);
			} else {
				set_error("maybe, your storage is write-protected.");
				show_errors();
			}
		}

		return $result;
	}
}

if(!function_exists("get_real_path")) {
	function get_real_path($file) {
		return file_exists($file) ? realpath($file) : false;
	}
}

function retrieve_storage_files($type, $recursive=false, $excludes=array(".", ".."), $files=array()) {
        $storage_path = get_storage_path($type);
        if(is_dir($storage_path)) {
                if($handle = opendir($storage_path)) {
                        while(false !== ($file = readdir($handle))) {
                                if(!in_array($file, $exclude)) {
					$file_path = $storage_path . "/" . $file;
                                        if(is_file($file_path)) {
                                                $files[] = $file_path;
                                        } elseif($recursive) {
                                                $files = retrieve_storage_dir($type . "/" . $file, $recursive, $excludes, $files);
                                        }
                                }
                        }
                        closedir($handle);
                }
        }
        return $files;
}

function get_file_extension($file) {
        return pathinfo($file, PATHINFO_EXTENSION);
}

function check_file_extension($file, $extension) {
        return (get_file_extension($file) === $extension);
}
