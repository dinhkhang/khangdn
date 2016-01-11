<?php

class VisitorBlacklist extends AppModel {

    public $useTable = 'visitor_blacklists';

    const CACHE_KEY = 'all_visitor_blacklists';

    public function readCacheAll() {

        return $this->find('list', array(
                    'fields' => array(
                        'mobile',
                        'mobile',
                    ),
        ));

//        $model = $this;
//        return Cache::remember(self::CACHE_KEY, function() use ($model) {
//
//                    return $model->find('list', array(
//                                'fields' => array(
//                                    'mobile',
//                                    'mobile',
//                                ),
//                    ));
//                });
    }

    public function writeCacheAll() {

        return Cache::write(self::CACHE_KEY, $this->find('list', array(
                            'fields' => array(
                                'mobile',
                                'mobile',
                            ),
        )));
    }

}
