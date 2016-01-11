<?php

App::uses('AppModel', 'Model');

class UserGroup extends AppModel {

	public $useTable = 'user_groups';
	public $validate = array(
		'name' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'not_empty_validate',
			),
			'unique' => array(
				'rule' => 'isUnique',
				'message' => 'is_unique_validate',
			),
		),
		'permissions' => array(
			'multiple' => array(
				'rule' => array('multiple', array(
						'min' => 1,
					)),
				'message' => 'not_empty_validate',
			)
		),
	);

	public function beforeFind($query) {
		parent::beforeFind($query);

		// với user là admin, tuy nhiên admin cấp dưới, không được phép nhìn thấy group dành cho admin cấp trên
		$user = CakeSession::read('Auth.User');
		if (!empty($user['deny_groups'])) {

			$query['conditions']['id']['$nin'] = $user['deny_groups'];
		}

		return $query;
	}

}
