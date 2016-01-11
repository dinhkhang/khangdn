<?php

/**
 * CakePHP Behavior
 * @author User
 */
class ContentProviderPermBehavior extends ModelBehavior {

        public function beforeFind(\Model $model, $query) {
                parent::beforeFind($model, $query);

                $user = CakeSession::read('Auth.User');
                if (
                        $user['type'] == 'CONTENT_PROVIDER' &&
                        !empty($user['content_provider_code'])
                ) {

                        App::import('Model', 'User');
                        $User = new User();
                        $user_ids = $User->getUserIdsByCPcode($user['content_provider_code']);

                        $query['conditions']['user']['$in'] = $user_ids;
                }
                return $query;
        }

        public function beforeSave(\Model $model, $options = array()) {
                parent::beforeSave($model, $options);

                $user = CakeSession::read('Auth.User');
                if (
                        !isset($model->data[$model->alias]['status']) &&
                        empty($model->data[$model->alias]['id']) &&
                        $user['type'] == 'CONTENT_PROVIDER' &&
                        !empty($user['content_provider_code'])
                ) {

                        $model->data[$model->alias]['status'] = Configure::read('sysconfig.App.constants.STATUS_WAIT_REVIEW');
                }

                return true;
        }

}
