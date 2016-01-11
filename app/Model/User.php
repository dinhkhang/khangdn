<?php

App::uses('AppModel', 'Model');

class User extends AppModel {

        public $useTable = 'users';

        public function beforeSave($options = array()) {
                parent::beforeSave($options);

                // thực hiện hash password trước khi lưu vào database
                unset($this->data[$this->alias]['password_confirm']);
                if (!empty($this->data[$this->alias]['password'])) {

                        $this->data[$this->alias]['password'] = Security::hash(trim($this->data[$this->alias]['password']), null, true);
                }

                if (!empty($this->data[$this->alias]['user_group'])) {

                        $this->data[$this->alias]['user_group'] = new MongoId($this->data[$this->alias]['user_group']);
                }
        }

        /**
         * Returns true if a record with particular ID exists.
         *
         * If $id is not passed it calls `Model::getID()` to obtain the current record ID,
         * and then performs a `Model::find('count')` on the currently configured datasource
         * to ascertain the existence of the record in persistent storage.
         *
         * @param int|string $id ID of record to check for existence
         * @return bool True if such a record exists
         */
        public function exists($id = null) {
                if ($id === null) {
                        $id = $this->getID();
                }

                if ($id === false) {
                        return false;
                }

                $conditions = array(
                    $this->alias . '.' . $this->primaryKey => $id,
                );

                // với user là admin, tuy nhiên admin cấp dưới, không được phép nhìn thấy user thuộc group dành cho admin cấp trên
                $user = CakeSession::read('Auth.User');
                if (!empty($user['deny_groups'])) {

                        $conditions['group']['$nin'] = $user['deny_groups'];
                }

                return (bool) $this->find('count', array(
                            'conditions' => $conditions,
                            'recursive' => -1,
                            'callbacks' => false
                ));
        }

        public function afterFind($results, $primary = false) {
                parent::afterFind($results, $primary);

                if ($primary && !empty($results)) {

                        foreach ($results as $k => $v) {

                                if (isset($v[$this->alias]['user_group']) && $v[$this->alias]['user_group'] instanceof MongoId) {

                                        $results[$k][$this->alias]['user_group'] = (string) $v[$this->alias]['user_group'];
                                }
                        }
                }

                return $results;
        }

        public function getUserIdsByCPcode($content_provider_code) {

                $user_ids = $this->find('list', array(
                    'conditions' => array(
                        'status' => array(
                            '$eq' => Configure::read('sysconfig.App.constants.STATUS_ACTIVE'),
                        ),
                        'content_provider_code' => array(
                            '$eq' => $content_provider_code,
                        ),
                    ),
                    'fields' => array(
                        'id', 'id',
                    ),
                ));

                if (empty($user_ids)) {

                        return array();
                }

                foreach ($user_ids as $k => $v) {

                        $user_ids[$k] = new MongoId($v);
                }

                return array_values($user_ids);
        }

}
