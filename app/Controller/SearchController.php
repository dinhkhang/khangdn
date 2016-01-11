<?php

App::uses('AppController', 'Controller');

class SearchController extends AppController {

    public $settings = array();
    public $error = array();
    public $type = null;
    public $keyword = null;
    public $res = array();
    public $page = 1;
    public $limit = 3;
    public $lang_code = 'en';

    protected function setInit() {

        $this->settings = Configure::read('sysconfig.Search');
        $type = trim($this->request->query('type'));
        if (!strlen($type)) {

            exit();
        }
        $this->type = $type;

        $keyword = trim($this->request->query('keyword'));
        if (!strlen($keyword)) {

            exit();
        }
        $this->keyword = $keyword;

        $lang_code = trim($this->request->query('lang_code'));
        if (strlen($lang_code)) {

            $this->lang_code = $lang_code;
        }

        $page = trim($this->request->query('page'));
        if (strlen($page)) {

            $this->page = (int) $page;
        }

        $limit = trim($this->request->query('limit'));
        if (strlen($limit)) {

            $this->limit = (int) $limit;
        }
    }

    public function index() {

        $this->autoRender = false;

        $this->setInit();
        $res = array();
        if ($this->type == 0) {

            $models = $this->settings['models'];
            if (empty($models)) {

                return null;
            }

            foreach ($models as $m) {

                $alias = strtolower($m);
                $res['arr_' . $alias] = $this->searchByModel($m);
            }
        } else {

            if (!empty($this->settings['types'][$this->type])) {

                $m = $this->settings['types'][$this->type];
                $alias = strtolower($m);
                $res['arr_' . $alias] = $this->searchByModel($m);
            }
        }

        header('Content-Type: application/json');
        echo json_encode($res);
    }

    protected function searchByModel($model_name) {

        if (!isset($this->$model_name)) {

            $this->loadModel($model_name);
        }

        if (empty($this->settings['filter_fields'][$model_name])) {

            return array();
        }

        $filter_fields = $this->settings['filter_fields'][$model_name];
        $options = array();
        $options['limit'] = $this->limit;
        $options['page'] = $this->page;
//        $options['conditions']['status'] = Configure::read('App.constants.STATUS_APPROVED');
//        $options['conditions']['lang_code'] = $this->lang_code;
        foreach ($filter_fields as $field) {

            $options['conditions']['$or'][][$field]['$regex'] = new MongoRegex("/" . mb_strtolower($this->keyword) . "/i");
        }

        if (empty($this->settings['return_fields'][$model_name])) {

            return array();
        }

        $return_fields = $this->settings['return_fields'][$model_name];

        $mapping_fields = array();
        $options['fields'] = array();
        foreach ($return_fields as $k => $v) {

            if (is_numeric($k)) {

                $options['fields'][] = $v;
                $mapping_fields[$v] = $v;
                continue;
            }

            if (strpos($k, '$') !== false) {
                
            } else {

                $options['fields'][] = $v;
                $mapping_fields[$k] = $v;
            }
        }

        $list_data = $this->$model_name->find('all', $options);
        if (empty($list_data)) {

            return array();
        }

        $res_data = array();
        foreach ($list_data as $k => $v) {

            foreach ($mapping_fields as $target => $source) {

                $raw_target_value = Hash::get($v[$model_name], $source);
                $target_value = $this->processCustomFields($target, $raw_target_value);
                $res_data[$k][$target] = $target_value;
            }
        }

        return $res_data;
    }

    protected function processCustomFields($target_field, $target_value) {

        if (empty($this->settings['custom_fields']) || empty($this->settings['custom_fields'][$target_field])) {

            return $target_value;
        }

        $custom_field_type = $this->settings['custom_fields'][$target_field];

        switch ($custom_field_type) {

            case 'single_uri':
                $base_url = Configure::read('sysconfig.Common.URL_BASE_FILE_MANAGER');
                if (!is_array($target_value) || empty($target_value)) {

                    return null;
                }
                $target_value = array_values($target_value);
                return $base_url . $target_value[0];
            case 'multiple_uri':
                $base_url = Configure::read('sysconfig.Common.URL_BASE_FILE_MANAGER');
                if (!is_array($target_value) || empty($target_value)) {

                    return null;
                }
                $target_value = array_values($target_value);
                foreach ($target_value as $k => $v) {

                    $target_value[$k] = $base_url . $v;
                }
                return $target_value;
            case 'datetime':
                if (strlen($target_value) <= 0 || is_null($target_value)) {

                    return null;
                }
                $format_field = $this->settings['format_fields'];
                if ($target_value instanceof MongoDate) {

                    return date($format_field['datetime'], $target_value->sec);
                }
                return date($format_field['datetime'], strtotime($target_value));
            default :
                return $target_value;
        }
    }

}
