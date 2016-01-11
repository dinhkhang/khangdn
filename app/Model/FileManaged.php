<?php

App::uses('AppModel', 'Model');

class FileManaged extends AppModel {

	public $useTable = 'files';

	public function beforeSave($options = array()) {
		parent::beforeSave($options);

		$status_file_upload_to_tmp = Configure::read('sysconfig.App.constants.STATUS_FILE_UPLOAD_TO_TMP');

		if (
				isset($this->data[$this->alias]['status']) &&
				$this->data[$this->alias]['status'] == $status_file_upload_to_tmp
		) {

			$this->data[$this->alias]['uri'] = str_replace(WEBROOT_DIR . DS, '', $this->data[$this->alias]['uri']);
		}

		if (DIRECTORY_SEPARATOR == '\\' && !empty($this->data[$this->alias]['uri'])) {

			$this->data[$this->alias]['uri'] = str_replace('\\', '/', $this->data[$this->alias]['uri']);
		}
	}

}
