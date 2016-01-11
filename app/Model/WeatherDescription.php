<?php

class WeatherDescription extends AppModel {

        public $useTable = 'weather_descriptions';
        public $validate = array(
            'code' => array(
                'alphaNumeric' => array(
                    'rule' => 'isUnique',
                    'required' => true,
                    'message' => 'This code is used'
                ),
            ),
        );

        public function afterSave($created, $options = array()) {
                parent::afterSave($created, $options);
                if (isset($this->data['WeatherDescription']['files']['icon_uri'])) {
                        // check uri is mapping?
                        if (array_key_exists($this->data['WeatherDescription']['files']['icon']['0']->{'$id'}, $this->data['WeatherDescription']['files']['icon_uri'][0])) {
                                return true;
                        }
                        // if no file return
                } elseif (!isset($this->data['WeatherDescription']['files'])) {
                        return true;
                }
                App::uses('FileManaged', 'Model');
                $file = new FileManaged();

                $this->id = $this->data['WeatherDescription']['id'];
                $file->id = $this->data['WeatherDescription']['files']['icon']['0']->{'$id'};
                $object = $this->read();
                $object['WeatherDescription']['files']['icon_uri'][] = [$file->id => $file->field('uri')];
                $this->save($object);
        }

}
