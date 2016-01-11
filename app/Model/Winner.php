<?php

App::uses('AppModel', 'Model');

class Winner extends AppModel {

	public $useTable = 'winner';
	// trường datetime có dạng MongoDate
	protected $datetime_fields = array(
		'date',
	);

	public function afterFind($results, $primary = false) {
		parent::afterFind($results, $primary);

		if (!empty($results)) {

			// thực hiện chuyển kiểu datetime dạng MongoDate thành dạng datetime string
			foreach ($results as $k => $v) {

				foreach ($this->datetime_fields as $field) {

					if (isset($v[$this->alias][$field]) && $v[$this->alias][$field] instanceof MongoDate) {

						$results[$k][$this->alias][$field] = date('d-m-Y H:i:s', $v[$this->alias][$field]->sec);
					}
				}
			}
		}

		return $results;
	}

	public function beforeValidate($options = array()) {
		parent::beforeValidate($options);

		if (isset($this->data[$this->alias]['type'])) {

			$this->data[$this->alias]['type'] = (int) $this->data[$this->alias]['type'];
		}

		if (
				empty($this->data[$this->alias]['id']) &&
				isset($this->data[$this->alias]['type']) &&
				isset($this->data[$this->alias]['phone']) &&
				isset($this->data[$this->alias]['date'])
		) {

			$check_exist = $this->find('first', array(
				'conditions' => array(
					'type' => (int) $this->data[$this->alias]['type'],
					'phone' => $this->data[$this->alias]['phone'],
					'date' => $this->data[$this->alias]['date'],
				),
			));

			if (!empty($check_exist)) {

				return false;
			}
		}
	}

}
