<?php

class QuestionCategory extends AppModel {

	public $useTable = 'question_categories';
        
        public $customSchema = array(
            'id' => null,
            'name' => '',
            'code' => '',
            'description' => '', 
            'order' => 0,
            'status' => 0,
            'user' => null,
            'created_at' => null,
            'updated_at' => null,
        );

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
		'code' => array(
			'required' => array(
				'rule' => array('notEmpty'),
				'message' => 'not_empty_validate',
			),
			'unique' => array(
				'rule' => 'isUnique',
				'message' => 'is_unique_validate',
			),
			'notWhiteSpace' => array(
				'rule' => 'notWhiteSpace',
				'message' => 'not_white_space_validate',
			),
		),
	);

	public function beforeValidate($options = array()) {
		parent::beforeValidate($options);

		if (empty($this->id)) {

			$this->data[$this->alias]['created_at'] = $this->data[$this->alias]['updated_at'] = new MongoDate(time());
		} else {

			$this->data[$this->alias]['updated_at'] = new MongoDate(time());
		}
	}

}
