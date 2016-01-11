<?php

class DistributionChannel extends AppModel {

    public $useTable = 'distribution_channels';

    public function get_sharing_by_code($code = '') {
        $option = array(
            'conditions' => array(
                'code' => $code,
            )
        );
        $record = $this->find('first', $option);

        if (!empty($record)) {
            $detail = $record[$this->alias];
            return $detail['sharing'];
        } else {

            return null;
        }
    }

    public function get_distributor_code_by_code($code = '') {
        $option = array(
            'conditions' => array(
                'code' => $code,
            )
        );
        $record = $this->find('first', $option);

        if (!empty($record)) {
            $detail = $record[$this->alias];
            return $detail['distributor_code'];
        } else {

            return null;
        }
    }

    public function findByAgencyCode($agency_code) {

        return $this->find('first', array(
                    'conditions' => array(
                        'agency_code' => $agency_code,
                    ),
        ));
    }

}
