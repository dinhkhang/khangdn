<?php

App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
set_time_limit(-1);

class TagShell extends AppShell {

    public $uses = array('TagLite', 'Tag', 'TagClientVersion');

    public function main() {
        // count all tag name ascii distinct
        $countNewTag = $this->Tag->find('count', array(
            'conditions' => array(
                'aggregate' => array(
                    array(
                        '$group' => array(
                            '_id' => '$name_ascii',
                            'name_ascii' => array(
                                '$first' => '$name_ascii',
                            ),
                        ),
                    ),
                ),
        )));
        
        // get data from db
        $maxModifiedFromTagDbLite = $this->fetchMaxModifiedFromTag();
        $option = $maxModifiedFromTagDbLite ? array(
            'conditions' => array(
                'aggregate' => array(
                    array(
                        '$group' => array(
                            '_id' => '$name_ascii',
                            'name_ascii' => array(
                                '$first' => '$name_ascii',
                            ),
                            'name' => array(
                                '$first' => '$name',
                            ),
                            'modified' => array(
                                '$first' => '$modified',
                            ),
                        ),
                    ),
                ),
            )) : array();
        if(!Configure::read('sysconfig.Console.FORCE_READ_ALL_TAG')) {
                $option['conditions']['aggregate'][]['$match'] = array(
                            'modified' => array('$gt' => $maxModifiedFromTagDbLite),
                        );
        }
        $tags = $this->Tag->find('all', $option);

        // save new tag
        if ($tags) {
            foreach ($tags AS $tag) {
                if (isset($tag['Tag']['name_ascii']) && strlen($tag['Tag']['name_ascii']) > 0) {
                    $this->TagLite->create();
                    $this->TagLite->save(array(
                        'name' => $tag['Tag']['name_ascii'],
                        'modified' => $tag['Tag']['modified']->sec
                    ));
                } else {

                    $this->log($tag);
                }
            }

            // save to TagClientVersion
            if ($countNewTag) {
                $maxModifiedFromTagDbLite = $this->fetchMaxModifiedFromTag();

                $client = $this->TagClientVersion->find('first', array('conditions' => array('version' => $maxModifiedFromTagDbLite->sec)));
                if ($client) {
                    $client['count'] = $countNewTag;
                    $this->TagClientVersion->save($client);
                } else {
                    $this->TagClientVersion->create();
                    $this->TagClientVersion->save(array('version' => $maxModifiedFromTagDbLite->sec, 'count' => $countNewTag));
                }
            }
            // copy tags.db to tags_client.db
            $file = new File(APP . WEBROOT_DIR . DS . 'tags.db', false, 0777);
            $fileClient = new File(APP . WEBROOT_DIR . DS . 'tags_client.db', false, 0777);
            $fileClient->create();
            $file->copy(APP . WEBROOT_DIR . DS . 'tags_client.db');

            $this->out('Done');
        } else {
            $this->out('System has not new tag');
        }
    }

    protected function fetchMaxModifiedFromTag() {
        $tagOld = $this->TagLite->find('first', array('order' => array('modified' => "DESC")));
        return isset($tagOld['TagLite']['modified']) ? new MongoDate($tagOld['TagLite']['modified']) : '';
    }

}
