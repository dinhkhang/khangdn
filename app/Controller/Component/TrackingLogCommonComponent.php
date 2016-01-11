<?php

App::uses('Component', 'Controller');
App::uses('TrackingLog', 'Model');

class TrackingLogCommonComponent extends Component {

    public $controller = null;

    public function initialize(\Controller $controller) {
        parent::initialize($controller);

        $this->controller = $controller;
    }

    public function daily($save_data, $options = array()) {

        if (empty($options['model_pattern'])) {

            $model_pattern = 'tracking_%s_%s_%s_%s';
        } else {

            $model_pattern = $options['model_pattern'];
        }

        if (empty($options['date'])) {

            $date = date('d-m-Y H:i:s');
        } else {

            $date = $options['date'];
        }

        $year = date('Y', strtotime($date));
        $month = date('m', strtotime($date));
        $day = date('d', strtotime($date));

        $model_name = sprintf($model_pattern, $year, $month, $day);
        $TrackingLog = new TrackingLog(array(
            'table' => $model_name,
        ));

        $default_save_data = array(
            'host' => $this->controller->request->host(),
            'client_ip' => $this->controller->request->clientIp(),
            'path' => $this->controller->request->here(),
            'referer' => $this->controller->request->referer(),
            'user_agent' => $this->controller->request->header('User-Agent'),
        );
        $save_data = Hash::merge($default_save_data, $save_data);

        if (!empty($options['async']) && $options['async'] === false) {

            $TrackingLog->create();

            return $TrackingLog->save($save_data);
        } else {

            if (empty($save_data['created'])) {

                $save_data['created'] = new MongoDate();
            }
            if (empty($save_data['modified'])) {

                $save_data['modified'] = new MongoDate();
            }

            $mongo = $TrackingLog->getDataSource();
            $mongoCollectionObject = $mongo->getMongoCollection($TrackingLog);
            return $mongoCollectionObject->insert($save_data, array('w' => 0));
        }
    }

    public function logAction($action, $screen_code, $date = null, $options = array()) {

        if (empty($options['model_pattern'])) {

            $model_pattern = 'tracking_%s_%s_%s_%s';
        } else {

            $model_pattern = $options['model_pattern'];
        }

        if (empty($date)) {

            $date = date('d-m-Y H:i:s');
        }

        $year = date('Y', strtotime($date));
        $month = date('m', strtotime($date));
        $day = date('d', strtotime($date));

        $model_name = sprintf($model_pattern, $action, $year, $month, $day);
        $TrackingLog = new TrackingLog(array(
            'table' => $model_name,
        ));

        if (!empty($options['token'])) {

            $token = $options['token'];
        } else {

            $token = $this->controller->request->header('token');
        }
        if (!empty($options['payload'])) {

            $payload = $options['payload'];
        } else {

            $payload = $this->controller->request->query;
        }

        $save_data = array(
            'host' => $this->controller->request->host(),
            'client_ip' => $this->controller->request->clientIp(),
            'screen_code' => $screen_code,
            'payload' => $payload,
            'path' => $this->controller->request->here(),
            'referer' => $this->controller->request->referer(),
            'user_agent' => $this->controller->request->header('User-Agent'),
            'os_name' => !empty($options['os_name']) ? $options['os_name'] : $this->controller->os_name,
            'os_version' => !empty($options['os_version']) ? $options['os_version'] : $this->controller->os_version,
            'visitor_username' => !empty($options['visitor_username']) ? $options['visitor_username'] : $this->controller->username,
            'token' => !empty($token) ? $token : null,
            'distribution_channel_code' => !empty($options['distribution_channel_code']) ? $options['distribution_channel_code'] : null,
        );
        $save_data['modified'] = $save_data['created'] = new MongoDate(strtotime($date));

        if (!empty($options['async']) && $options['async'] === false) {

            $TrackingLog->create();

            return $TrackingLog->save($save_data);
        } else {

            $mongo = $TrackingLog->getDataSource();
            $mongoCollectionObject = $mongo->getMongoCollection($TrackingLog);
            return $mongoCollectionObject->insert($save_data, array('w' => 0));
        }
    }

}
