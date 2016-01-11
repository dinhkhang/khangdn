<?php

class TagsController extends AppController {

    public $uses = array(
        'Tag',
        'TagClientVersion',
    );
    public $debug_mode = 3;

    public function index() {

        $this->setInit();

        $tags_conf = Configure::read('sysconfig.Tags');
        $tags_client_file_uri = $tags_conf['tags_client_file_uri'];
        $tags_client_file_name = $tags_conf['tags_client_file_name'];
        $tags_client_file_zip = $tags_conf['tags_client_file_zip'];
        $tags_client_max_change = $tags_conf['tags_client_max_change'];
        $tags_client_force_download = $tags_conf['tags_client_force_download'];

        $file_path = $tags_client_file_uri . $tags_client_file_name;
        $file_zip = $tags_client_file_uri . $tags_client_file_zip;

        $maxTagClientVersion = $this->TagClientVersion->find('first', array('order' => ['version' => 'DESC']));
        if (empty($maxTagClientVersion)) {

            $this->resError('#tag001');
        }

        $server_version = $maxTagClientVersion['TagClientVersion']['version'];
        $client_version = (int) $this->request->query('client_version');

        /**
         * đề phòng trường hợp hệ thống lỗi, ép buộc phải ghi đè lại
         */
        if ($tags_client_force_download || !$client_version) {

            $this->resSuccess(array(
                'status' => 'success',
                'data' => array(
                    'type' => 'file',
                    'path' => $file_path,
                    'zip_path' => $file_zip,
                    'version' => $server_version,
                ),
            ));
        }

        // nếu client_version truyền lên khác rỗng, thực hiện truy vấn vào tag_client_versions với tag_client_versions.version = client_version, 
        $result = $this->TagClientVersion->find('first', array(
            'conditions' => array(
                'version' => $client_version,
        )));

        // nếu không tồn tại version
        if (empty($result)) {

            $this->resSuccess(array(
                'status' => 'success',
                'data' => array(
                    'type' => 'file',
                    'path' => $file_path,
                    'zip_path' => $file_zip,
                    'version' => $server_version,
                ),
            ));
        }

        // nếu client_version chính bằng max version trên server
        if ($client_version == $server_version) {

            $this->resSuccess(array(
                'status' => 'success',
                'data' => null,
            ));
        }

        // thực hiện so sánh số lương tag ở phiên bản client_version và số lượng tag ở server_version
        $cms_count = $maxTagClientVersion['TagClientVersion']['cms_count'] - $result['TagClientVersion']['cms_count'];
        $cms_public_count = abs($maxTagClientVersion['TagClientVersion']['cms_public_count'] - $result['TagClientVersion']['cms_public_count']);
        if ($cms_count < 0) {

            $this->resSuccess(array(
                'type' => 'file',
                'path' => $file_path,
                'zip_path' => $file_zip,
                'version' => $server_version,
            ));
        } elseif ($cms_count > $tags_client_max_change || $cms_public_count > $tags_client_max_change) {

            $this->resSuccess(array(
                'type' => 'file',
                'path' => $file_path,
                'zip_path' => $file_zip,
                'version' => $server_version,
            ));
        } else {

            $tags = $this->Tag->find('all', array('conditions' => array(
                    'modified' => array(
                        '$gt' => new MongoDate($client_version),
                        '$lte' => new MongoDate($server_version, 999999),
                    ),
            )));
            if (empty($tags)) {

                $res = array(
                    'status' => 'success',
                    'data' => null,
                );
                $this->resSuccess($res);
            }

            $content = array();
            foreach ($tags AS $tag) {
                $content[] = array(
                    'id' => $tag['Tag']['id'],
                    'name' => $tag['Tag']['name_ascii'],
                    'type' => $tag['Tag']['object_type_code'],
                    'status' => $tag['Tag']['status'],
                    'lang_code' => 'vi',
                    'modified' => $tag['Tag']['modified']->sec,
                );
            }
            $this->resSuccess(array(
                'status' => 'success',
                'data' => array(
                    'type' => 'json',
                    'content' => $content,
                    'version' => $server_version,
                ),
            ));
        }
    }

    public function reqSearch() {

        $this->autoRender = false;

        $keyword = trim($this->request->query('keyword'));
        $keyword_asii = $this->convert_vi_to_en($keyword);

        $type = trim($this->request->query('type'));
        $limit = 5;
        $conditions = array(
            'status' => 2,
            'name_ascii' => array(
                '$regex' => new MongoRegex("/" . mb_strtolower($keyword_asii) . "/i"),
            ),
        );
        if (!empty($type)) {

            if (in_array($type, array('places', 'regions'))) {

                $conditions['object_type_code']['$in'] = array('places', 'regions');
            } else {

                $conditions['object_type_code'] = $type;
            }
        }

        $options = array(
            'limit' => $limit,
        );

        $options['conditions']['aggregate'][] = array(
            '$match' => $conditions,
        );
        $options['conditions']['aggregate'][] = array(
            '$group' => array(
                '_id' => '$name_ascii',
                'name_ascii' => array(
                    '$first' => '$name_ascii',
                ),
            ),
        );

        $tags = $this->{$this->modelClass}->find('all', $options);
        $tag_names = Hash::extract($tags, '{n}.' . $this->modelClass . '.name_ascii');
        echo json_encode($tag_names);
    }

}
