<?php

App::uses('Component', 'Controller');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class FileCommonComponent extends Component {

        public $controller = '';

        public function initialize(\Controller $controller) {

                parent::initialize($controller);

                $this->controller = $controller;
        }

        public function getFiles($request_data_file) {

                if (empty($request_data_file)) {

                        return false;
                }

                if (!isset($this->controller->FileManaged)) {

                        $this->controller->loadModel('FileManaged');
                }

                $results = array();

                foreach ($request_data_file as $type => $file) {

                        if (empty($file)) {

                                continue;
                        }

                        foreach ($file as $v) {

                                if ($v instanceof MongoId) {

                                        $file_obj = $this->controller->FileManaged->find('first', array(
                                            'conditions' => array(
                                                'id' => $v,
                                            ),
                                            'fields' => array(
                                                'name', 'size', 'mime', 'status', 'uri',
                                            ),
                                        ));

                                        $results[$type][] = !empty($file_obj['FileManaged']) ?
                                                json_encode($file_obj['FileManaged']) : json_encode(array());
                                }
                        }
                }

                return $results;
        }

        /**
         * autoSetFiles
         * Thực hiện lấy ra thông tin chi tiết của file từ FileManaged
         * 
         * @param reference array $request_data
         * 
         * @return boolean
         */
        public function autoSetFiles(&$request_data, $options = array()) {

                if (empty($request_data)) {

                        return false;
                }

                if (empty($request_data['files']) || !is_array($request_data['files'])) {

                        $request_data['files'] = array();
                        return;
                }

                if (!isset($this->controller->FileManaged)) {

                        $this->controller->loadModel('FileManaged');
                }

                foreach ($request_data['files'] as $type => $file) {

                        if (empty($file)) {

                                continue;
                        }

                        foreach ($file as $k => $v) {

                                if ($v instanceof MongoId) {

                                        $file_obj = $this->controller->FileManaged->find('first', array(
                                            'conditions' => array(
                                                'id' => $v,
                                            ),
                                            'fields' => array(
                                                'name', 'size', 'mime', 'status', 'uri',
                                            ),
                                        ));

                                        $request_data['files'][$type][$k] = !empty($file_obj['FileManaged']) ?
                                                json_encode($file_obj['FileManaged']) : json_encode(array());
                                } else {

                                        $request_data['files'][$type][$k] = json_encode(array());
                                }
                        }
                }

                $this->autoSetFilesRecursive($request_data, $options);
        }

        protected function autoSetFilesRecursive(&$request_data, $options = array()) {

                if (empty($options['recursive']) || empty($options['recursive_path'])) {

                        return;
                }

                if (!is_array($options['recursive_path'])) {

                        $options['recursive_path'] = array($options['recursive_path']);
                }

                foreach ($options['recursive_path'] as $recursive_path) {

                        $check = Hash::check($request_data, $recursive_path);
                        if (!$check) {

                                continue;
                        }
                        $extract_data = Hash::extract($request_data, $recursive_path);
                        if (strpos($recursive_path, '{n}') !== false) {

                                foreach ($extract_data as $k => $v) {

                                        $fix_path = str_replace('{n}', $k, $recursive_path);
                                        $index = explode('.', $fix_path);
                                        foreach ($v as $k1 => $v1) {

                                                $path_index = $this->joinRecursivePath($index);
                                                $path_index = $path_index . '["' . $k1 . '"]';

                                                foreach ($v1 as $k2 => $v2) {

                                                        $fix_path_index = $path_index . '[' . $k2 . ']';
                                                        if ($v2 instanceof MongoId) {

                                                                $file_obj = $this->controller->FileManaged->find('first', array(
                                                                    'conditions' => array(
                                                                        'id' => $v2,
                                                                    ),
                                                                ));

                                                                $file_json = !empty($file_obj['FileManaged']) ?
                                                                        json_encode($file_obj['FileManaged']) : json_encode(array());
                                                                $express = '$request_data' . $fix_path_index . '= $file_json;';
                                                                eval($express);
                                                        } else {

                                                                $express = '$request_data' . $fix_path_index . '= json_encode(array());';
                                                                eval($express);
                                                        }
                                                }
                                        }
                                }
                        } else {

                                foreach ($extract as $k => $v) {

                                        $index = explode('.', $recursive_path);
                                        $path_index = $this->joinRecursivePath($index);
                                        $path_index = $path_index . '["' . $k . '"]';

                                        foreach ($v as $k1 => $v1) {

                                                $fix_path_index = $path_index . '[' . $k1 . ']';
                                                if ($v1 instanceof MongoId) {

                                                        $file_obj = $this->controller->FileManaged->find('first', array(
                                                            'conditions' => array(
                                                                'id' => $v1,
                                                            ),
                                                        ));

                                                        $file_json = !empty($file_obj['FileManaged']) ?
                                                                json_encode($file_obj['FileManaged']) : json_encode(array());
                                                        $express = '$request_data' . $fix_path_index . '= $file_json;';
                                                        eval($express);
                                                } else {

                                                        $express = '$request_data' . $fix_path_index . '= json_encode(array());';
                                                        eval($express);
                                                }
                                        }
                                }
                        }
                }
        }

        protected function joinRecursivePath($path = array()) {

                if (empty($path)) {

                        return;
                }

                $join_path = '';
                foreach ($path as $v) {

                        if (is_numeric($v)) {

                                $join_path .= '[' . $v . ']';
                        } else {

                                $join_path .= '["' . $v . '"]';
                        }
                }

                return $join_path;
        }

        /**
         * generateFolderStructure
         * Thực hiện tạo ra cấu trúc thư mục lưu trữ [Tên module/Tên ext/nămtháng/ngày]
         * 
         * @param string $module_name
         * @param string $ext
         * 
         * @return string
         */
        public function generateFolderStructure($module_name, $ext, $absolute = false) {

                $data_root_name = Configure::read('sysconfig.App.data_file_root');
                $ext = strtoupper($ext);
                $year = date('Y');
                $month = date('m');
                $day = date('d');
//		$block = rand(1, 2000);
                $folder_structure = array(
                    $data_root_name,
                    $module_name,
                    $ext,
                    $year . $month,
                    $day,
//			$block,
                );
                $folder_path = APP;

                foreach ($folder_structure as $item) {

                        $folder_path .= DS . $item;
                        $folder = new Folder($folder_path, false, 0777);
                        if (!$folder->inPath($folder_path)) {

                                $folder = new Folder($folder_path, true, 0777);
                        }
                }

                if ($absolute) {

                        return $folder_path . DS;
                }

                return str_replace(APP, '', $folder_path . DS);
        }

        public function process($request_data_file, $module_name) {

                if (empty($request_data_file) || !is_array($request_data_file)) {

                        return false;
                }

                $results = array();
                foreach ($request_data_file as $k => $v) {

                        if (empty($v)) {

                                continue;
                        }

                        $file_ids = $this->moveFromTmp($v, $module_name);
                        if ($file_ids !== false) {

                                $results[$k] = $file_ids;
                        }
                }

                return $results;
        }

        /**
         * autoProcess
         * Tự động xử lý liên quan tới files, gọi trước khi thực hiện save vào database
         * 
         * @param reference array $save_data
         * @param string $module_name - Là tên thư mục lưu trữ dành cho Module, mặc định đọc trong 'sysconfig.' . $this->controller->name . '.data_file_root'
         * 
         * @return boolean
         * @throws CakeException
         */
        public function autoProcess(&$save_data, $module_name = null, $options = array()) {

                if (empty($module_name)) {

                        $module_name = Configure::read('sysconfig.' . $this->controller->name . '.data_file_root');
                }

                if (empty($module_name)) {

                        throw new CakeException(__('Invalid sysconfig, make sure that %s was defined', 'sysconfig.' . $this->controller->name . '.data_file_root'));
                }

                if (empty($save_data)) {

                        return false;
                }

                if (empty($save_data['files']) || !is_array($save_data['files'])) {

                        return false;
                }

                foreach ($save_data['files'] as $type => $file) {

                        $file_ids = $this->moveFromTmp($file, $module_name);
                        if ($file_ids === false) {

                                unset($save_data['files'][$type]);
                                continue;
                        }

                        $save_data['files'][$type] = $file_ids;
                }

                $this->autoProcessRecursive($save_data, $module_name, $options);
        }

        protected function autoProcessRecursive(&$save_data, $module_name = null, $options = array()) {

                if (empty($options['recursive']) || empty($options['recursive_path'])) {

                        return;
                }

                if (!is_array($options['recursive_path'])) {

                        $options['recursive_path'] = array($options['recursive_path']);
                }

                foreach ($options['recursive_path'] as $recursive_path) {

                        $check = Hash::check($save_data, $recursive_path);
                        if (!$check) {

                                continue;
                        }

                        $extract_save_data = Hash::extract($save_data, $recursive_path);
                        if (strpos($recursive_path, '{n}') !== false) {

                                foreach ($extract_save_data as $k => $v) {

                                        $fix_path = str_replace('{n}', $k, $recursive_path);
                                        $index = explode('.', $fix_path);

                                        foreach ($v as $kk => $vv) {

                                                $path_index = '["' . implode('"]["', $index) . '"]';
                                                $path_index = $path_index . '["' . $kk . '"]';

                                                $file_ids = $this->moveFromTmp($vv, $module_name);
                                                if ($file_ids === false) {

                                                        $express = 'unset($save_data' . $path_index . ');';
                                                        eval($express);
                                                        continue;
                                                }

                                                $express = '$save_data' . $path_index . '= $file_ids;';
                                                eval($express);
                                        }
                                }
                        } else {

                                foreach ($extract_save_data as $k => $v) {

                                        $index = explode('.', $recursive_path);
                                        $path_index = '["' . implode('"]["', $index) . '"]';
                                        $path_index = $path_index . '["' . $k . '"]';

                                        $file_ids = $this->moveFromTmp($v, $module_name);
                                        if ($file_ids === false) {

                                                $express = 'unset($save_data' . $path_index . ');';
                                                eval($express);
                                                continue;
                                        }

                                        $express = '$save_data' . $path_index . '= $file_ids;';
                                        eval($express);
                                }
                        }
                }
        }

        /**
         * moveFromTmp
         * Thực hiện chuyển file từ thư mục tmp vào thư mục target
         * 
         * @param array $file
         * @param string $module_name - Tên thư mục Module cần chuyển file vào
         * 
         * @return boolean|\MongoId
         * @throws CakeException
         */
        public function moveFromTmp($file, $module_name) {

                if (empty($file)) {

                        return false;
                }

                $status_file_upload_completed = Configure::read('sysconfig.App.constants.STATUS_FILE_UPLOAD_COMPLETED');
                $file_ids = array();

                if (!is_array($file)) {

                        $file = array($file);
                }

                foreach ($file as $v) {

                        $item = json_decode($v, true);
                        if (empty($item)) {

                                throw new CakeException(__('The input file is invalid'));
                        }

                        // kiểm tra xem file đã được move hay chưa?
                        if ($item['status'] == $status_file_upload_completed) {

                                $file_ids[] = new MongoId($item['id']);
                                continue;
                        }

                        $file_uri = APP . WEBROOT_DIR . DS . $item['uri'];

                        // thực hiện support cho môi trường windows
                        if (DIRECTORY_SEPARATOR == '\\') {

                                $file_uri = str_replace('\\', '/', $file_uri);
                        }

                        $file_obj = new File($file_uri, false, 0755);
                        if (!$file_obj->exists()) {

                                throw new CakeException(__('The input file is not exist'));
                        }

                        $file_name = basename($item['uri']);
                        $file_ext = substr(strrchr($file_name, '.'), 1);

                        $target = $this->generateFolderStructure($module_name, $file_ext);

                        $copy = $file_obj->copy(APP . $target . $file_name);

                        if (!$copy) {

                                throw new CakeException(__('Can not copy file from %s to %s', $file_obj->path, APP . $target . $file_name));
                        }
                        $file_obj->delete();

                        // cập nhật lại đường dẫn file và set status = 1
                        $item['uri'] = $target . $file_name;
                        $item['status'] = $status_file_upload_completed;

                        if (!isset($this->controller->FileManaged)) {

                                $this->controller->loadModel('FileManaged');
                        }

                        if (!$this->controller->FileManaged->save($item)) {

                                throw new CakeException(__('Cant save file data into File Collection'));
                        }

                        $file_ids[] = new MongoId($item['id']);
                }

                return $file_ids;
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

}
