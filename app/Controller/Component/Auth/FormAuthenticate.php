<?php

/**
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
App::uses('BaseAuthenticate', 'Controller/Component/Auth');

/**
 * An authentication adapter for AuthComponent. Provides the ability to authenticate using POST
 * data. Can be used by configuring AuthComponent to use it via the AuthComponent::$authenticate setting.
 *
 * {{{
 * 	$this->Auth->authenticate = array(
 * 		'Form' => array(
 * 			'scope' => array('User.active' => 1)
 * 		)
 * 	)
 * }}}
 *
 * When configuring FormAuthenticate you can pass in settings to which fields, model and additional conditions
 * are used. See FormAuthenticate::$settings for more information.
 *
 * @package       Cake.Controller.Component.Auth
 * @since 2.0
 * @see AuthComponent::$authenticate
 */
class FormAuthenticate extends BaseAuthenticate {

	/**
	 * Checks the fields to ensure they are supplied.
	 *
	 * @param CakeRequest $request The request that contains login information.
	 * @param string $model The model used for login verification.
	 * @param array $fields The fields to be checked.
	 * @return boolean False if the fields have not been supplied. True if they exist.
	 */
	protected function _checkFields(CakeRequest $request, $model, $fields) {
		if (empty($request->data[$model])) {
			return false;
		}
		if (
				empty($request->data[$model][$fields['username']]) ||
				empty($request->data[$model][$fields['password']])
		) {
			return false;
		}
		return true;
	}

	/**
	 * Authenticates the identity contained in a request. Will use the `settings.userModel`, and `settings.fields`
	 * to find POST data that is used to find a matching record in the `settings.userModel`. Will return false if
	 * there is no post data, either username or password is missing, of if the scope conditions have not been met.
	 *
	 * @param CakeRequest $request The request that contains login information.
	 * @param CakeResponse $response Unused response object.
	 * @return mixed False on login failure. An array of User data on success.
	 */
	public function authenticate(CakeRequest $request, CakeResponse $response) {
		$userModel = $this->settings['userModel'];
		list(, $model) = pluginSplit($userModel);

		$fields = $this->settings['fields'];
		if (!$this->_checkFields($request, $model, $fields)) {
			return false;
		}
		return $this->_findUser(
						$request->data[$model][$fields['username']], $request->data[$model][$fields['password']]
		);
	}

	/**
	 * Find a user record using the standard options.
	 *
	 * The $username parameter can be a (string)username or an array containing
	 * conditions for Model::find('first'). If the $password param is not provided
	 * the password field will be present in returned array.
	 *
	 * Input passwords will be hashed even when a user doesn't exist. This
	 * helps mitigate timing attacks that are attempting to find valid usernames.
	 *
	 * @param string|array $username The username/identifier, or an array of find conditions.
	 * @param string $password The password, only used if $username param is string.
	 * @return bool|array Either false on failure, or an array of user data.
	 */
	protected function _findUser($username, $password = null) {
		$userModel = $this->settings['userModel'];
		list(, $model) = pluginSplit($userModel);
		$fields = $this->settings['fields'];

		if (is_array($username)) {
			$conditions = $username;
		} else {
			$conditions = array(
				$model . '.' . $fields['username'] => array(
					'$eq' => $username,
				),
			);
		}

		if (!empty($this->settings['scope'])) {
			$conditions = array_merge($conditions, $this->settings['scope']);
		}

		$userFields = $this->settings['userFields'];
		if ($password !== null && $userFields !== null) {
			$userFields[] = $model . '.' . $fields['password'];
		}

		$result = ClassRegistry::init($userModel)->find('first', array(
			'conditions' => $conditions,
			'recursive' => $this->settings['recursive'],
			'fields' => $userFields,
			'contain' => $this->settings['contain'],
		));
		if (empty($result[$model])) {
			$this->passwordHasher()->hash($password);
			return false;
		}

		$user = $result[$model];
		if ($password !== null) {
			if (!$this->passwordHasher()->check($password, $user[$fields['password']])) {
				return false;
			}
			unset($user[$fields['password']]);
		}

		unset($result[$model]);
		return array_merge($user, $result);
	}

}
