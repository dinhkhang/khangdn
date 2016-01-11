<?php

App::uses('Component', 'Controller');

class LocationCommonComponent extends Component {

	public $controller = '';
	public $models = array('Country', 'Region', 'Location');
	public $countries = array();
	public $regions = array();
	public $locations = array();

	public function initialize(\Controller $controller) {

		parent::initialize($controller);

		$this->controller = $controller;

		// Nạp vào các Model liên quan
		foreach ($this->models as $model) {

			if (!isset($this->controller->$model)) {

				$this->controller->loadModel($model);
			}
		}
	}

	public function reqLocation($type = '') {

		$this->controller->autoRender = false;

		$country = $this->controller->request->data('country');
		$region = $this->controller->request->data('region');
		$name = $this->controller->request->data('name');
		$model_name = Inflector::camelize($type);

		$options = array();
		$options['conditions']['status']['$eq'] = Configure::read('sysconfig.App.constants.STATUS_APPROVED');
		$options['conditions']['name']['$regex'] = new MongoRegex("/" . mb_strtolower($name) . "/i");

		if ($model_name != 'Country') {

			$country_code = $this->controller->Country->getCountryCodeByCountryId($country);
			$options['conditions']['country_code']['$eq'] = $country_code;
		}

		if ($model_name == 'Location') {

			$options['conditions']['region']['$eq'] = new MongoId($region);
		}

		$result = $this->controller->$model_name->find('all', $options);
		$pretty_result = Hash::extract($result, '{n}.' . $model_name);
		$json = array('items' => $pretty_result, 'type' => $type);

		echo json_encode($json);
	}

	public function autoProcess(&$save_data) {

		$location = null;
		if (empty($save_data['country'])) {

			return false;
		}

		if (!empty($save_data['region'])) {

			$location = $save_data['region'];
		} else {

			return false;
		}
		if (!empty($save_data['location'])) {

			$location = $save_data['location'];
		} else {

			// đọc ra thông tin region
			$region = $this->controller->Region->read(null, $save_data['region']);
			if (empty($region)) {

				return false;
			}

			$check_exist = $this->controller->Location->find('first', array(
				'conditions' => array(
					'name' => array(
						'$eq' => $region['Region']['name'],
					),
				),
			));

			if (!empty($check_exist)) {

				$location = $check_exist['Location']['id'];
			}
			// thực hiện insert thông tin region vào location
			else {

				$this->controller->Location->save(array(
					'name' => $region['Region']['name'],
					'country_code' => $region['Region']['country_code'],
					'region' => new MongoId($save_data['region']),
					'latitude' => $region['Region']['latitude'],
					'longitude' => $region['Region']['longitude'],
					'status' => Configure::read('sysconfig.App.constants.STATUS_APPROVED'),
				));

				$location = $this->controller->Location->getLastInsertID();
			}
		}

		unset($save_data['country']);
		unset($save_data['region']);

		$save_data['location'] = new MongoId($location);
	}

	public function autoSetLocations(&$request_data) {

		if (empty($request_data['location'])) {

			return false;
		}

		// đọc ra thông tin của location
		$location = $this->controller->Location->find('first', array(
			'conditions' => array(
				'id' => $request_data['location'],
			),
		));
		if (empty($location)) {

			return false;
		}

		$request_data['location'] = (string) $request_data['location'];
		$request_data['locations'] = array(
			$location['Location']['id'] => $location['Location']['name'],
		);

		$country_code = $location['Location']['country_code'];
		// lấy ra country_id dựa vào country_code
		$country_id = $this->controller->Country->getCountryIdByCountryCode($country_code);
		$request_data['country'] = (string) $country_id;
		$request_data['country_code'] = (string) $country_code;

		$region = $location['Location']['region'];
		$request_data['region'] = (string) $region;

		// lấy dữ liệu của country
		$countries = $this->controller->Country->find('list', array(
			'conditions' => array(
				'code' => array(
					'$eq' => $country_code,
				),
			),
			'fields' => array(
				'id', 'name',
			),
		));
		$request_data['countries'] = $countries;

		// lấy dữ liệu của region
		$regions = $this->controller->Region->find('list', array(
			'conditions' => array(
				'id' => $region,
			),
			'fields' => array(
				'id', 'name',
			),
		));
		$request_data['regions'] = $regions;
	}

}
