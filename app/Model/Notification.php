<?php

class Notification extends AppModel {

    public $useTable = 'notifications';

    public function getVisitorNotificationCount($visitor_notification_groups) {

        // find notification for visitor match with visitor group
        return $this->find('count', [
                    'conditions' => [
                        'visitor_notification_groups' => array(
                            '$in' => $visitor_notification_groups,
                        ),
                        'status' => Configure::read('sysconfig.Common.STATUS_APPROVED'),
                    ],
        ]);
    }

    public function getVisitorNotification($visitor_notification_groups, $visitorId, $limit = 10, $page = 1) {

        App::uses('VisitorNotificationView', 'Model');
        $visitorNotificationViewModel = new VisitorNotificationView();

        // get all notification which user read before
        $visitorNotificationViews = $visitorNotificationViewModel->find('list', [
            'conditions' => [
                'visitor' => new MongoId($visitorId),
            ],
            'fields' => ['id', 'notification'],
        ]);

        // find notification for visitor match with visitor group
        $result = $this->find('all', [
            'order' => [
                'order' => 'asc',
                'modified' => 'desc',
            ],
            'limit' => $limit,
            'page' => $page,
            'conditions' => [
                'visitor_notification_groups' => array(
                    '$in' => $visitor_notification_groups,
                ),
                'status' => Configure::read('sysconfig.Common.STATUS_APPROVED'),
        ]]);

        if (empty($result)) {

            return null;
        }

        // check notification is read or not
        foreach ($result AS $key => $value) {

            $result[$key] = $value[$this->alias];

            if (in_array(new MongoId($value[$this->alias]['id']), $visitorNotificationViews)) {

                $result[$key]['read'] = 1;
            } else {

                $result[$key]['read'] = 0;
            }

            unset($result[$key]['visitor_notification_groups']);
            unset($result[$key]['created']);
            unset($result[$key]['user']);
            unset($result[$key]['status']);
            unset($result[$key]['order']);
        }

        return $result ? $result : array();
    }

}
