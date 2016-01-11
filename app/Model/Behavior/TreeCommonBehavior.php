<?php

/**
 * CakePHP Behavior
 * @author User
 */
class TreeCommonBehavior extends ModelBehavior {

        /**
         * generateTreeList
         * Tạo ra danh sách danh mục list thể hiện sự phân cấp
         * 
         * @param \Model $model
         * @param array $options
         * @param string $key
         * @param string $value
         * @param string $spacer
         * 
         * @return array
         */
        public function generateTreeList(\Model $model, $options = array(), $key = 'id', $value = 'name', $spacer = '--') {

                $list = $model->find('threaded', $options);
                if (empty($list)) {

                        return array();
                }
                $tree_list = array();

                foreach ($list as $v) {

                        $tree_list[$v[$model->alias][$key]] = $v[$model->alias][$value];
                        $children = $v['children'];
                        while (!empty($children)) {

                                $repeat = 1;
                                foreach ($children as $vv) {

                                        $tree_list[$vv[$model->alias][$key]] = str_repeat($spacer, $repeat) . ' ' . $vv[$model->alias][$value];
                                        $children = $vv['children'];
                                }
                                $repeat++;
                        }
                }

                return $tree_list;
        }

        protected function parseTreeList(&$tree_list, &$repeat, $data, $model, $key = 'id', $value = 'name', $spacer = '--') {

                foreach ($data as $v) {

                        $tree_list[$v[$model->alias][$key]] = $v[$model->alias][$value];
                        $children = $v['children'];
                        if (!empty($children)) {

                                $this->parseTreeList($tree_list, $repeat, $data, $model, $key, $value, $spacer);
                        }
                }
        }

        public function afterDelete(\Model $model) {
                parent::afterDelete($model);

                // khi xóa danh mục, thực hiện xóa toàn bộ danh mục con liên quan tới danh mục cha
                $conditions = array(
                    'parent_id' => new MongoId($model->id),
                );
                $model->deleteAll($conditions, false);
        }

        public function afterSave(\Model $model, $created, $options = array()) {
                parent::afterSave($model, $created, $options);

                // đối với trường hợp edit
                if (!$created) {

                        $status_approved = Configure::read('sysconfig.App.constants.STATUS_APPROVED');
                        // nếu danh mục cha bị set status khác với STATUS_APPROVED 
                        // thì thực hiện set status của toàn bộ danh mục con liên quan thành status của danh mục cha
                        if (
                                isset($model->data[$model->alias]['status']) &&
                                $model->data[$model->alias]['status'] != $status_approved
                        ) {

                                $conditions = array(
                                    'parent_id' => new MongoId($model->id),
                                );
                                $model->updateAll(array('status' => $model->data[$model->alias]['status']), $conditions);
                        }
                        // nếu là danh mục con, được set status thành STATUS_APPROVED
                        // thì thực hiện set status của danh mục cha thành STATUS_APPROVED
                        elseif (
                                isset($model->data[$model->alias]['status']) &&
                                $model->data[$model->alias]['status'] != $status_approved
                        ) {

                                // nếu có danh mục cha
                                if (!empty($model->data[$model->alias]['parent_id'])) {

                                        $parent_id = (string) $model->data[$model->alias]['parent_id'];
                                        $model->save(array(
                                            'id' => $parent_id,
                                            'status' => $status_approved,
                                        ));
                                } else {

                                        // đọc lại thông tin để lấy ra parent_id
                                        $get_data = $model->read(null, $model->id);
                                        $parent_id = (string) $get_data[$model->alias]['parent_id'];
                                        // nếu có danh mục cha
                                        if (!empty($parent_id)) {

                                                $model->save(array(
                                                    'id' => $parent_id,
                                                    'status' => $status_approved,
                                                ));
                                        }
                                }
                        }
                }
        }

        public function saveSerialize(\Model $model, $data) {

                if (empty($data['serialize'])) {

                        return false;
                }

                $serialize = json_decode($data['serialize'], true);
                $jsonErrorCode = json_last_error();
                if ($jsonErrorCode !== JSON_ERROR_NONE) {
                        throw new \RuntimeException(
                        'API response not well-formed (json error code: ' . $jsonErrorCode . ')'
                        );
                }

                $save_data = array();
                $order = 1;
                $this->parseSerialize($save_data, $order, $serialize);

                if ($model->saveAll($save_data)) {

                        return true;
                } else {

                        return false;
                }
        }

        protected function parseSerialize(&$save_data, &$order, $data, $parent_id = '') {

                foreach ($data as $v) {

                        if (!empty($v['children'])) {

                                $parent_id = '';
                        }
                        $save_data[] = array(
                            'id' => $v['id'],
                            'order' => $order,
                            'parent_id' => $parent_id,
                        );
                        $order++;
                        if (!empty($v['children'])) {

                                $parent_id = $v['id'];
                                $this->parseSerialize($save_data, $order, $v['children'], $parent_id);
                        }
                }
        }

}
