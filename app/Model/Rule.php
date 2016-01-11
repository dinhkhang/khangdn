<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
App::uses('AppModel', 'Model');

class Rule extends AppModel {

    public $useTable = 'configurations';

    public function getRule() {
        $configuration = $this->find('first');
        if(empty($configuration)){
            return $rule_des = '';
        }

        $rule_des = $configuration['Rule']['rule_description'];
        return !empty($rule_des) ? $rule_des : null;
    }

}
