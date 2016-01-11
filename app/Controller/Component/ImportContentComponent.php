<?php

App::uses('Component', 'Controller');
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

class ImportContentComponent extends Component {

        public $controller = '';
        public $location_models = array('Country', 'Region', 'Location');
        public $location_required = array('region', 'country_code', 'name', 'address', 'latitude', 'longitude');
        public $file_models = array('FileManaged');
        public $file_required = array('logo', 'thumbnails');
        public $file_module_name = '';
        public $components = array('FileCommon');
        public $default_settings = array(
            'file_module_name' => '',
            'location_models' => array('Country', 'Region', 'Location'),
            'location_required' => array('region', 'country_code', 'name', 'address', 'latitude', 'longitude'),
            'file_models' => array('FileManaged'),
            'file_required' => array('logo', 'thumbnails'),
            'remove_fields' => array(), // những fields sẽ không xử lý khi import - những trường nhập sai
            'additional_schemas' => array(), // mảng chứa cấu trúc thêm vào để fill đầy như schema đã thiết kế
        );

        public function initialize(\Controller $controller) {

                parent::initialize($controller);

                $this->controller = $controller;
                if (!isset($this->controller->import_error)) {

                        $this->controller->import_error = array();
                }

                $this->settings = Hash::merge($this->default_settings, $this->settings);
        }

        public function init(&$save_data) {

                if (!empty($this->settings['remove_fields'])) {

                        foreach ($this->settings['remove_fields'] as $field) {

                                unset($save_data[$field]);
                        }
                }

                if (!empty($this->settings['additional_schemas'])) {

                        $save_data = Hash::merge($save_data, $this->settings['additional_schemas']);
                }

                $save_data['status'] = 1;
                $save_data['lang_code'] = 'eng';
        }

        public function saveLocation(&$save_data, $data, $required = true) {

                // Nạp vào các Model liên quan
                $this->loadModel($this->location_models);

                $is_valid = 1;
                if ($required && !$this->checkRequired($data, 'location', $this->location_required)) {

                        $is_valid = 0;
                }

                if (!$is_valid) {

                        return false;
                }

                // thực hiện insert country
                $check_country = $this->controller->Country->find('first', array(
                    'conditions' => array(
                        'code' => $data['location']['country_code'],
                    ),
                ));

                // nếu không tồn tại thực hiện insert
                if (empty($check_country)) {

                        $country_data = array(
                            'name' => $data['location']['country_code'],
                            'code' => $data['location']['country_code'],
                        );

                        $this->controller->Country->create();
                        if (!$this->controller->Country->save($country_data)) {

                                $this->controller->import_error = array_merge($this->controller->import_error, $this->controller->Country->validationErrors);
                                return false;
                        }
                }

                // thực hiện insert region
                $check_region = $this->controller->Region->find('first', array(
                    'conditions' => array(
                        'name' => $data['location']['region'],
                        'country_code' => $data['location']['country_code'],
                    ),
                ));

                // nếu không tồn tại thực hiện insert
                if (empty($check_region)) {

                        $region_data = array(
                            'name' => $data['location']['region'],
                            'country_code' => $data['location']['country_code'],
                            'status' => 2,
                        );

                        $this->controller->Region->create();
                        if (!$this->controller->Region->save($region_data)) {

                                $this->controller->import_error = array_merge($this->controller->import_error, $this->controller->Region->validationErrors);
                                return false;
                        }

                        $region_id = $this->controller->Region->getLastInsertID();
                } else {

                        $region_id = $check_region['Region']['id'];
                }

                // thực hiện insert location
                $location_data = array(
                    'country_code' => $data['location']['country_code'],
                    'region' => $region_id,
                    'name' => $data['location']['name'],
                    'latitude' => $data['location']['latitude'],
                    'longitude' => $data['location']['longitude'],
                    'status' => 2,
                    'address' => $data['location']['address'],
                );
                
                $this->controller->Location->create();
                if (!$this->controller->Location->save($location_data)) {

                        $this->controller->import_error = array_merge($this->controller->import_error, $this->controller->Location->validationErrors);
                        return false;
                }

                $location_id = $this->controller->Location->getLastInsertID();

                unset($save_data['location']);
//                $save_data['location'] = array(
//                    'country_code' => $data['location']['country_code'],
//                    'region' => new MongoId($region_id),
//                    'id' => new MongoId($location_id),
//                );

                $save_data['location'] = $location_id;
                return true;
        }

        public function saveFiles(&$save_data, $dir_path, $data, $required = true) {

                // Nạp vào các Model liên quan
                $this->loadModel($this->file_models);

                $is_valid = 1;
                if ($required && !$this->checkRequired($data, 'files', $this->settings['file_required'])) {

                        $is_valid = 0;
                        return $is_valid;
                }

                // validate sự tồn tại của files
                foreach ($data['files'] as $k => $v) {

                        if (!$this->validateFiles($dir_path, $k, $v)) {

                                $is_valid = 0;
                        }
                }

                if (!$is_valid) {

                        return $is_valid;
                }

                $save_data['files'] = array();
                foreach ($data['files'] as $k => $v) {

                        $this->proccessFiles($save_data, $dir_path, $k, $v);
                }

                return true;
        }

        protected function validateFiles($dir_path, $type, $paths) {

                $is_valid = 1;
                // kiểm tra sự tồn tại của file
                if (in_array($type, $this->settings['file_required'])) {

                        foreach ($paths as $path) {

                                $file_path = $dir_path . DS . trim($path);
                                // hot fix
                                if (strpos($file_path, '.jpg') !== false) {

                                        $file_path = str_replace('.jpg', '.jpeg', $file_path);
                                }
                                // thực hiện support cho môi trường windows
                                if (DIRECTORY_SEPARATOR == '\\') {

//                                        $file_path = str_replace('\\', '/', $file_path);
                                        $file_path = str_replace('/', '\\', $file_path);
                                }

                                if (!$this->checkFileExist($type, $file_path)) {

                                        $is_valid = 0;
                                }
                        }
                } else {

                        foreach ($paths as $path) {

                                $path = trim($path);
                                if (!strlen($path)) {

                                        continue;
                                }

                                $file_path = $dir_path . DS . trim($path);
                                // hot fix
                                if (strpos($file_path, '.jpg') !== false) {

                                        $file_path = str_replace('.jpg', '.jpeg', $file_path);
                                }

                                // thực hiện support cho môi trường windows
                                if (DIRECTORY_SEPARATOR == '\\') {

//                                        $file_path = str_replace('\\', '/', $file_path);
                                        $file_path = str_replace('/', '\\', $file_path);
                                }

                                if (!$this->checkFileExist($type, $file_path)) {

                                        $is_valid = 0;
                                }
                        }
                }

                return $is_valid;
        }

        protected function proccessFiles(&$save_data, $dir_path, $type, $paths) {

                // thực hiện move và lưu file vào database
                foreach ($paths as $path) {

                        $path = trim($path);
                        if (!in_array($type, $this->settings['file_required']) && strlen($path) <= 0) {

                                continue;
                        }

                        $file_path = $dir_path . DS . $path;
                        // hot fix
                        if (strpos($file_path, '.jpg') !== false) {

                                $file_path = str_replace('.jpg', '.jpeg', $file_path);
                        }

                        // thực hiện support cho môi trường windows
                        if (DIRECTORY_SEPARATOR == '\\') {

//                                $file_path = str_replace('/', '\\', $file_path);
                                $file_path = str_replace('/', '\\', $file_path);
                        }

                        $file = new File($file_path);
                        $file_ext = $file->ext();
                        $file_name = $file->name() . $this->FileCommon->generateRandomLetters(5) . '.' . $file_ext;
                        $target_file_path = $this->FileCommon->generateFolderStructure($this->file_module_name, $file_ext) . $file_name;
                        $file->copy(APP . $target_file_path);

                        $file_data = array(
                            'name' => $file_name,
                            'uri' => $target_file_path,
                            'mime' => $file->mime(),
                            'size' => $file->size(),
                            'status' => 1,
                        );
                        $this->controller->FileManaged->create();
                        $this->controller->FileManaged->save($file_data);
                        $save_data['files'][$type][] = new MongoId($this->controller->FileManaged->getLastInsertID());
                }
        }

        protected function checkFileExist($type, $file_path) {

                $file = new File($file_path, false, 0755);
                if (!$file->exists()) {

                        $this->controller->import_error[] = 'Không tồn tại file theo đường dẫn ' . $file_path . ' trong ' . 'files.' . $type;
                        return false;
                }

                return true;
        }

        protected function loadModel($models) {

                foreach ($models as $model) {

                        if (!isset($this->controller->$model)) {

                                $this->controller->loadModel($model);
                        }
                }
        }

        protected function checkRequired($data, $field, $children_fields = array()) {

                $is_valid = 1;
                if (empty($data[$field])) {

                        $this->controller->import_error[] = 'Không tồn tại thông tin trong ' . $field;
                        $is_valid = 0;
                }

                if (!empty($data[$field]) && !is_array($data[$field])) {

                        $this->controller->import_error[] = 'Thông tin trong ' . $field . ' không đúng định dạng';
                        $is_valid = 0;
                }

                if (empty($children_fields)) {

                        return $is_valid;
                }

                foreach ($children_fields as $v) {

                        if (empty($data[$field][$v])) {

                                $this->controller->import_error[] = 'Không tồn tại thông tin trong ' . $field . '.' . $v;
                                $is_valid = 0;
                        } elseif (is_string($data[$field][$v]) && strlen(trim($data[$field][$v])) <= 0) {

                                $this->controller->import_error[] = 'Chứa chuỗi rỗng trong ' . $field . '.' . $v;
                                $is_valid = 0;
                        }

                        // đối với trường hợp đặc biệt $field = "files", thì các trường con của "files" bắt buộc là array
//                        if ($field == 'files' && !$this->checkRequiredForFile($field, $v, $data[$field][$v])) {
//                                
//                        }
                }

                return $is_valid;
        }

        protected function checkRequiredForFile($field, $child_field, $value) {

                $is_valid = 1;
                if (!is_array($value)) {

                        $this->controller->import_error[] = 'Thông tin không đúng định dạng dữ liệu trong ' . $field . '.' . $child_field;
                        $is_valid = 0;

                        return $is_valid;
                }

                foreach ($value as $k => $v) {

                        $v = trim($v);
                        if (!strlen($v)) {

                                $this->controller->import_error[] = 'Thông tin không đúng định dạng dữ liệu trong ' . $field . '.' . $child_field . '.' . $k;
                                $is_valid = 0;
                        }
                }

                return $is_valid;
        }

}
