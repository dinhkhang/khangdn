<?php

/**
 * Application model for CakePHP.
 *
 * This file is application-wide model file. You can put all
 * application-wide model-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Model
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
App::uses('Model', 'Model');

/**
 * Application model for Cake.
 *
 * Add your application-wide methods in the class below, your models
 * will inherit them.
 *
 * @package       app.Model
 */
class AppModel extends Model {

    public function beforeValidate($options = array()) {
        parent::beforeValidate($options);

        // nếu có định nghĩa schema, bắt chặt dữ liệu theo cấu trúc của schema chi khi create
        // đồng thời tự tạo ra các fields nếu trong $this->data đầu vào không tồn tại
        if (!empty($this->customSchema) && empty($this->data[$this->alias]['id'])) {

            $schema_data = $this->mergeCustomSchema($this->customSchema, $this->data[$this->alias]);
            $this->data[$this->alias] = $schema_data;
        }
    }

    /**
     * mergeCustomSchema
     * Thực hiện merge schema với dữ liệu data input đầu vào
     * Đảm bảo các fields trong schema, luôn được lưu vào trong database
     * Đảm bảo những fields thừa trong data input đầu vào, sẽ bị loại bỏ, không lưu vào trong database
     * 
     * @param array $customSchema
     * @param reference array $data
     */
    protected function mergeCustomSchema($customSchema, $data) {

        App::import('Lib', 'ExtendedUtility');
        $reduce = ExtendedUtility::array_intersect_key_recursive($data, $customSchema);
        $map = Hash::merge($customSchema, $reduce);
        $data = $map;

        return $data;
    }

    public function notWhiteSpace($check) {

        $check = array_values($check);
        $value = trim($check[0]);

        if (strpos($value, ' ') !== false) {
            return false;
        }

        return true;
    }

    /**
     * Returns false if any fields passed match any (by default, all if $or = false) of their matching values.
     *
     * Can be used as a validation method. When used as a validation method, the `$or` parameter
     * contains an array of fields to be validated.
     *
     * @param array $fields Field/value pairs to search (if no values specified, they are pulled from $this->data)
     * @param bool|array $or If false, all fields specified must match in order for a false return value
     * @return bool False if any records matching any fields are found
     */
    public function isUnique($fields, $or = true) {
        if (is_array($or)) {
            $isRule = (
                    array_key_exists('rule', $or) &&
                    array_key_exists('required', $or) &&
                    array_key_exists('message', $or)
                    );
            if (!$isRule) {
                $args = func_get_args();
                $fields = $args[1];
                $or = isset($args[2]) ? $args[2] : true;
            }
        }
        if (!is_array($fields)) {
            $fields = func_get_args();
            $fieldCount = count($fields) - 1;
            if (is_bool($fields[$fieldCount])) {
                $or = $fields[$fieldCount];
                unset($fields[$fieldCount]);
            }
        }

        foreach ($fields as $field => $value) {
            if (is_numeric($field)) {
                unset($fields[$field]);

                $field = $value;
                $value = null;
                if (isset($this->data[$this->alias][$field])) {
                    $value = $this->data[$this->alias][$field];
                }
            }

            if (strpos($field, '.') === false) {
                unset($fields[$field]);
//				$fields[$this->alias . '.' . $field] = $value;
                $fields[$this->alias . '.' . $field]['$regex'] = new MongoRegex("/^" . $value . "$/i"); // sửa lại cho tương thích với Mongodb
            }
        }

        if ($or) {
//			$fields = array('or' => $fields);
            $fields = array('$or' => array($fields)); // sửa lại cho tương thích với Mongodb
        }

        if (!empty($this->id)) {
//			$fields[$this->alias . '.' . $this->primaryKey . ' !='] = $this->id;
            $fields[$this->alias . '.' . $this->primaryKey]['$ne'] = $this->id; // sửa lại cho tương thích với Mongodb
        }

        return !$this->find('count', array('conditions' => $fields, 'recursive' => -1));
    }

    /**
     * convert_vi_to_en method
     * hàm chuyền đổi tiếng việt có dấu sang tiếng việt không dấu
     * @param string $str
     * @return string
     */
    public function convert_vi_to_en($str) {

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
        $str = preg_replace("/(Đ|Ð)/", 'D', $str);
        //$str = str_replace(" ", "-", str_replace("&*#39;","",$str));
        // thực hiện cưỡng ép chuyển sang ascii
        $str = $this->forceConvertASCII($str);

        return $str;
    }

    /**
     * forceConvertASCII
     * 
     * @param string $str
     * @return string
     */
    public function forceConvertASCII($str) {

        try {

            $ascii_str = @iconv("UTF-8", "us-ascii//TRANSLIT", $str);
        } catch (Exception $e) {

            $this->log($e, 'notice');
            $this->log($str, 'notice');
        }
        return $ascii_str;
    }

    /**
     * isASCII
     * Thực hiện kiểm tra chuỗi string có phải là ASCII k?
     * 
     * @param string $str
     * @return boolean
     */
    public function isASCII($str) {

        return mb_detect_encoding($str, 'ASCII', true);
    }

    /**
     * In the event of ambiguous results returned (multiple top level results, with different parent_ids)
     * top level results with different parent_ids to the first result will be dropped
     *
     * @param string $state Either "before" or "after".
     * @param array $query Query.
     * @param array $results Results.
     * @return array Threaded results
     */
    protected function _findThreaded($state, $query, $results = array()) {
        if ($state === 'before') {
            return $query;
        }

        $parent = 'parent_id';
        if (isset($query['parent'])) {
            $parent = $query['parent'];
        }

        if (!empty($results)) {

            foreach ($results as $k => $v) {

                if (!empty($v[$this->alias][$parent]) && $v[$this->alias][$parent] instanceof MongoId) {

                    $results[$k][$this->alias][$parent] = (string) $v[$this->alias][$parent];
                }
            }
        }

        return Hash::nest($results, array(
                    'idPath' => '{n}.' . $this->alias . '.' . $this->primaryKey,
                    'parentPath' => '{n}.' . $this->alias . '.' . $parent
        ));
    }

    public function getTotal($options = array()) {

        unset($options['page']);
        unset($options['limit']);
        unset($options['order']);
        $options['fields'] = 'id';

        return $this->find('count', $options);
    }

    /**
     * logAnyFile
     * 
     * @param mixed $content
     * @param string $file_name
     */
    protected function logAnyFile($content, $file_name) {

        CakeLog::config($file_name, array(
            'engine' => 'File',
            'types' => array($file_name),
            'file' => $file_name,
        ));

        $this->log($content, $file_name);
    }

    protected function getPackageAlias($package) {

        $package_alias = null;
        if ($package == 'G1') {

            $package_alias = 'package_day';
        } elseif ($package == 'G7') {

            $package_alias = 'package_week';
        } elseif ($package == 'G30') {

            $package_alias = 'package_month';
        }

        return $package_alias;
    }

}
